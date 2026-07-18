<?php

namespace App\Controllers;

use App\Models\AdminUser;
use App\Models\Project;
use App\Services\Auth;
use App\Services\Csrf;
use App\Services\Response;
use App\Services\View;

final class SubadminController
{
    public function index(): void
    {
        $this->guardAdmin();
        View::render('subadmins', [
            'title' => 'Subadmins',
            'subadmins' => (new AdminUser())->all(),
            'projects' => (new Project())->all(),
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
            Response::redirect('/subadmins');
        }
        (new AdminUser())->create($this->payload($_POST));
        $_SESSION['flash_success'] = 'Subadmin created.';
        Response::redirect('/subadmins');
    }

    public function update(string $id): void
    {
        $this->guardAdmin();
        $this->guardToken();
        $errors = $this->validate($_POST, false);
        if ($errors || !(new AdminUser())->update($id, $this->payload($_POST))) {
            $_SESSION['flash_error'] = $errors ? implode(' ', $errors) : 'Subadmin not found.';
            Response::redirect('/subadmins');
        }
        $_SESSION['flash_success'] = 'Subadmin updated.';
        Response::redirect('/subadmins');
    }

    public function destroy(string $id): void
    {
        $this->guardAdmin();
        $this->guardToken();
        (new AdminUser())->delete($id);
        $_SESSION['flash_success'] = 'Subadmin deleted.';
        Response::redirect('/subadmins');
    }

    private function payload(array $data): array
    {
        $projectIds = array_values(array_filter((array) ($data['project_ids'] ?? []), fn($id) => (bool) preg_match('/^[a-f\d]{24}$/i', (string) $id)));
        return [
            'name' => trim($data['name'] ?? ''),
            'email' => trim($data['email'] ?? ''),
            'password' => (string) ($data['password'] ?? ''),
            'role' => 'subadmin',
            'project_ids' => $projectIds,
            'active' => isset($data['active']),
        ];
    }

    private function validate(array $data, bool $passwordRequired): array
    {
        $errors = [];
        if (trim($data['name'] ?? '') === '') {
            $errors[] = 'Name is required.';
        }
        if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required.';
        }
        if ($passwordRequired && strlen((string) ($data['password'] ?? '')) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }
        return $errors;
    }

    private function guardAdmin(): void
    {
        if (!Auth::isAdmin()) {
            $_SESSION['flash_error'] = 'Only the main admin can manage subadmins.';
            Response::redirect('/projects');
        }
    }

    private function guardToken(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            $_SESSION['flash_error'] = 'Security token expired. Please try again.';
            Response::redirect('/subadmins');
        }
    }
}
