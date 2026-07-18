<?php

namespace App\Controllers;

use App\Models\Project;
use App\Services\Auth;
use App\Services\Csrf;
use App\Services\Response;
use App\Services\View;

final class ProjectController
{
    public function index(): void
    {
        $model = new Project();
        $search = trim($_GET['q'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $projects = Auth::isAdmin()
            ? $model->all($search, $status)
            : $model->allForProjectIds(Auth::user()['project_ids'] ?? [], $search, $status);
        View::render('projects', [
            'title' => 'Projects',
            'projects' => $projects,
            'search' => $search,
            'status' => $status,
            'error' => $_SESSION['flash_error'] ?? null,
            'success' => $_SESSION['flash_success'] ?? null,
        ]);
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);
    }

    public function progress(string $id): void
    {
        if (!Auth::canAccessProject($id)) {
            $_SESSION['flash_error'] = 'You do not have access to that project.';
            Response::redirect('/projects');
        }
        $project = (new Project())->find($id);
        if (!$project) {
            $_SESSION['flash_error'] = 'Project not found.';
            Response::redirect('/projects');
        }
        View::render('project_progress', [
            'title' => 'Project Progress',
            'project' => $project,
            'error' => $_SESSION['flash_error'] ?? null,
            'success' => $_SESSION['flash_success'] ?? null,
        ]);
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);
    }

    public function store(): void
    {
        $this->guardAdmin();
        $this->guardToken();
        $errors = $this->validate($_POST);
        if ($errors) {
            $_SESSION['flash_error'] = implode(' ', $errors);
            Response::redirect('/projects');
        }

        (new Project())->create($_POST);
        $_SESSION['flash_success'] = 'Project created.';
        Response::redirect('/projects');
    }

    public function update(string $id): void
    {
        $this->guardAdmin();
        $this->guardToken();
        $errors = $this->validate($_POST);
        if ($errors || !(new Project())->update($id, $_POST)) {
            $_SESSION['flash_error'] = $errors ? implode(' ', $errors) : 'Project not found.';
            Response::redirect('/projects');
        }
        $_SESSION['flash_success'] = 'Project updated.';
        Response::redirect('/projects');
    }

    public function destroy(string $id): void
    {
        $this->guardAdmin();
        $this->guardToken();
        (new Project())->delete($id);
        $_SESSION['flash_success'] = 'Project deleted.';
        Response::redirect('/projects');
    }

    private function validate(array $data): array
    {
        $errors = [];
        if (trim($data['name'] ?? '') === '') {
            $errors[] = 'Project name is required.';
        }
        if (!in_array(($data['status'] ?? 'Active'), ['Active', 'Paused', 'Completed'], true)) {
            $errors[] = 'Invalid project status.';
        }
        $percent = (int) ($data['completion_percent'] ?? 0);
        if ($percent < 0 || $percent > 100) {
            $errors[] = 'Completion percentage must be between 0 and 100.';
        }
        if (($data['project_url'] ?? '') !== '' && !filter_var($data['project_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Project URL is invalid.';
        }
        if ((float) ($data['total_payment'] ?? 0) < 0) {
            $errors[] = 'Total payment cannot be negative.';
        }
        if (($data['renewal_date'] ?? '') !== '' && strtotime((string) $data['renewal_date']) === false) {
            $errors[] = 'Renewal date is invalid.';
        }
        foreach ((array) ($data['part_payment_at'] ?? []) as $date) {
            if ($date !== '' && strtotime((string) $date) === false) {
                $errors[] = 'Part payment date and time is invalid.';
                break;
            }
        }
        return $errors;
    }

    private function guardToken(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            $_SESSION['flash_error'] = 'Security token expired. Please try again.';
            Response::redirect('/projects');
        }
    }

    private function guardAdmin(): void
    {
        if (!Auth::isAdmin()) {
            $_SESSION['flash_error'] = 'Only the main admin can manage projects.';
            Response::redirect('/projects');
        }
    }
}
