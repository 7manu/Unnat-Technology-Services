<?php

namespace App\Controllers;

use App\Config\Env;
use App\Models\AdminUser;
use App\Models\Project;
use App\Services\Auth;
use App\Services\Csrf;
use App\Services\Response;
use App\Services\View;

final class ClientUserController
{
    public function index(): void
    {
        $this->guardAdmin();

        /**
         * A password only exists in readable form for the one request in which it
         * was set, so it is handed to the view through a single-use flash and then
         * discarded. Nothing readable is ever stored.
         */
        $shared = $_SESSION['share_credentials'] ?? null;
        unset($_SESSION['share_credentials']);

        View::render('client_users', [
            'title' => 'Client Access',
            'clientUsers' => (new AdminUser())->all('client'),
            'projects' => (new Project())->all(),
            'portalUrl' => $this->portalUrl(),
            'sharedCredentials' => $shared,
            'openShareFor' => (string) ($_GET['share'] ?? ''),
            'error' => $_SESSION['flash_error'] ?? null,
            'success' => $_SESSION['flash_success'] ?? null,
        ]);
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);
    }

    /**
     * Issues a new password so it can be shared with the client.
     * The plain password is returned to the next page render only.
     */
    public function resetPassword(string $id): void
    {
        $this->guardAdmin();
        $this->guardToken();

        $model = new AdminUser();
        $clientUser = $model->find($id);
        if (!$clientUser || (string) ($clientUser->role ?? '') !== 'client') {
            $_SESSION['flash_error'] = 'Client access user not found.';
            Response::redirect('/client-users');
        }

        $custom = trim((string) ($_POST['new_password'] ?? ''));
        if ($custom !== '' && strlen($custom) < 8) {
            $_SESSION['flash_error'] = 'A password must be at least 8 characters.';
            Response::redirect('/client-users');
        }

        $password = $custom !== '' ? $custom : self::generatePassword();
        if (!$model->setPassword($id, $password)) {
            $_SESSION['flash_error'] = 'The password could not be updated.';
            Response::redirect('/client-users');
        }

        $_SESSION['share_credentials'] = ['id' => $id, 'password' => $password];
        $_SESSION['flash_success'] = 'New password set. Share it with the client before leaving this page — it cannot be shown again.';
        Response::redirect('/client-users?share=' . $id);
    }

    /** Readable but strong: two words, digits and a symbol, no ambiguous characters. */
    public static function generatePassword(): string
    {
        $words = ['Unnat', 'Portal', 'Bright', 'Signal', 'Summit', 'Harbor', 'Orbit', 'Canvas', 'Meadow', 'Falcon'];
        $letters = 'abcdefghjkmnpqrstuvwxyz';

        $word = $words[random_int(0, count($words) - 1)];
        $tail = '';
        for ($i = 0; $i < 3; $i++) {
            $tail .= $letters[random_int(0, strlen($letters) - 1)];
        }

        return sprintf('%s@%d%s', $word, random_int(1000, 9999), $tail);
    }

    /** Address the client signs in at, overridable per environment. */
    private function portalUrl(): string
    {
        $url = (string) Env::get('CLIENT_PORTAL_URL', 'https://clients.unnattechnologyservices.com');

        return rtrim($url, '/') . '/login';
    }

    /**
     * Read-only preview of a client account: their profile and the exact
     * dashboard they see after signing in.
     */
    public function preview(string $id): void
    {
        $this->guardAdmin();

        $clientUser = (new AdminUser())->find($id);
        if (!$clientUser || (string) ($clientUser->role ?? '') !== 'client') {
            $_SESSION['flash_error'] = 'Client access user not found.';
            Response::redirect('/client-users');
        }

        $projectModel = new Project();
        $projectIds = array_values((array) ($clientUser->project_ids ?? []));
        $projects = $projectIds ? $projectModel->allForProjectIds($projectIds) : [];

        View::render('client_preview', [
            'title' => 'Client Preview',
            'clientUser' => $clientUser,
            'projects' => $projects,
            'allProjects' => $projectModel->all(),
            'portalUrl' => $this->portalUrl(),
            'error' => $_SESSION['flash_error'] ?? null,
            'success' => $_SESSION['flash_success'] ?? null,
        ]);
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);
    }

    public function store(): void
    {
        $this->guardAdmin();
        $this->guardToken();
        $errors = $this->validate($_POST, true);
        if ($errors) {
            $_SESSION['flash_error'] = implode(' ', $errors);
            Response::redirect('/client-users');
        }
        $payload = $this->payload($_POST);
        $id = (new AdminUser())->create($payload);

        /* Hand the just-typed password straight to the share composer. */
        $_SESSION['share_credentials'] = ['id' => $id, 'password' => (string) $payload['password']];
        $_SESSION['flash_success'] = 'Client access created. Share the login details below — the password cannot be shown again later.';
        Response::redirect('/client-users?share=' . $id);
    }

    public function update(string $id): void
    {
        $this->guardAdmin();
        $this->guardToken();
        $errors = $this->validate($_POST, false);
        $payload = $this->payload($_POST);
        if ($errors || !(new AdminUser())->update($id, $payload)) {
            $_SESSION['flash_error'] = $errors ? implode(' ', $errors) : 'Client access user not found.';
            Response::redirect('/client-users');
        }

        /* A password is only present here when the admin chose to change it. */
        if ((string) $payload['password'] !== '') {
            $_SESSION['share_credentials'] = ['id' => $id, 'password' => (string) $payload['password']];
            $_SESSION['flash_success'] = 'Client access updated. Share the new password below — it cannot be shown again later.';
            Response::redirect('/client-users?share=' . $id);
        }

        $_SESSION['flash_success'] = 'Client access updated.';
        Response::redirect('/client-users');
    }

    public function destroy(string $id): void
    {
        $this->guardAdmin();
        $this->guardToken();
        (new AdminUser())->delete($id, 'client');
        $_SESSION['flash_success'] = 'Client access deleted.';
        Response::redirect('/client-users');
    }

    private function payload(array $data): array
    {
        $projectIds = array_values(array_filter((array) ($data['project_ids'] ?? []), fn($id) => (bool) preg_match('/^[a-f\d]{24}$/i', (string) $id)));
        return [
            'name' => trim($data['name'] ?? ''),
            'email' => trim($data['email'] ?? ''),
            'mobile_phone' => trim($data['mobile_phone'] ?? ''),
            'address' => trim($data['address'] ?? ''),
            'password' => (string) ($data['password'] ?? ''),
            'role' => 'client',
            'project_ids' => $projectIds,
            'active' => isset($data['active']),
        ];
    }

    private function validate(array $data, bool $passwordRequired): array
    {
        $errors = [];
        if (trim($data['name'] ?? '') === '') {
            $errors[] = 'Client name is required.';
        }
        if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required.';
        }
        if (trim($data['mobile_phone'] ?? '') === '') {
            $errors[] = 'Client mobile is required.';
        }
        if ($passwordRequired && strlen((string) ($data['password'] ?? '')) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }
        return $errors;
    }

    private function guardAdmin(): void
    {
        if (!Auth::isAdmin()) {
            $_SESSION['flash_error'] = 'Only the main admin can manage client access.';
            Response::redirect('/projects');
        }
    }

    private function guardToken(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            $_SESSION['flash_error'] = 'Security token expired. Please try again.';
            Response::redirect('/client-users');
        }
    }
}
