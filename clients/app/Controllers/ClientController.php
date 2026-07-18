<?php

namespace App\Controllers;

use App\Models\ClientLead;
use App\Models\Project;
use App\Services\Auth;
use App\Services\Csrf;
use App\Services\Response;
use App\Services\View;

final class ClientController
{
    public function index(string $projectId): void
    {
        $this->guardProjectAccess($projectId);
        $project = (new Project())->find($projectId);
        if (!$project) {
            $_SESSION['flash_error'] = 'Project not found.';
            Response::redirect('/projects');
        }

        $search = trim($_GET['q'] ?? '');
        $status = trim($_GET['status'] ?? '');
        View::render('clients', [
            'title' => 'Clients',
            'project' => $project,
            'clients' => (new ClientLead())->all($projectId, $search, $status),
            'search' => $search,
            'status' => $status,
            'error' => $_SESSION['flash_error'] ?? null,
            'success' => $_SESSION['flash_success'] ?? null,
        ]);
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);
    }

    public function store(string $projectId): void
    {
        $this->guardProjectAccess($projectId);
        $this->guardToken($projectId);
        $errors = $this->validate($_POST);
        if ($errors) {
            $_SESSION['flash_error'] = implode(' ', $errors);
            Response::redirect('/projects/' . $projectId . '/clients');
        }

        (new ClientLead())->create($projectId, $_POST);
        $_SESSION['flash_success'] = 'Client added.';
        Response::redirect('/projects/' . $projectId . '/clients');
    }

    public function update(string $projectId, string $id): void
    {
        $this->guardProjectAccess($projectId);
        $this->guardToken($projectId);
        $errors = $this->validate($_POST);
        if ($errors || !(new ClientLead())->update($id, $_POST)) {
            $_SESSION['flash_error'] = $errors ? implode(' ', $errors) : 'Client not found.';
            Response::redirect('/projects/' . $projectId . '/clients');
        }
        $_SESSION['flash_success'] = 'Client updated.';
        Response::redirect('/projects/' . $projectId . '/clients');
    }

    public function destroy(string $projectId, string $id): void
    {
        $this->guardProjectAccess($projectId);
        $this->guardToken($projectId);
        (new ClientLead())->delete($id);
        $_SESSION['flash_success'] = 'Client deleted.';
        Response::redirect('/projects/' . $projectId . '/clients');
    }

    private function validate(array $data): array
    {
        $errors = [];
        if (trim($data['name'] ?? '') === '') {
            $errors[] = 'Client name is required.';
        }
        if (trim($data['mobile_phone'] ?? '') === '') {
            $errors[] = 'Mobile phone is required.';
        }
        if (($data['email'] ?? '') !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email address is invalid.';
        }
        if (!in_array(($data['status'] ?? 'New'), ['New', 'Contacted', 'Meeting Scheduled', 'Won', 'Lost'], true)) {
            $errors[] = 'Invalid client status.';
        }
        if (($data['meeting_at'] ?? '') !== '' && strtotime((string) $data['meeting_at']) === false) {
            $errors[] = 'Meeting date and time is invalid.';
        }
        return $errors;
    }

    private function guardToken(string $projectId): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            $_SESSION['flash_error'] = 'Security token expired. Please try again.';
            Response::redirect('/projects/' . $projectId . '/clients');
        }
    }

    private function guardProjectAccess(string $projectId): void
    {
        if (!Auth::canManageClients() || !Auth::canAccessProject($projectId)) {
            $_SESSION['flash_error'] = 'You do not have access to that project.';
            Response::redirect('/projects');
        }
    }
}
