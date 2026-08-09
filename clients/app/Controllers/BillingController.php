<?php

namespace App\Controllers;

use App\Models\AdminUser;
use App\Models\Project;
use App\Services\Auth;
use App\Services\Billing;
use App\Services\Response;
use App\Services\View;

/**
 * Billing for a project: the working screen, the printable invoice and a
 * printable receipt for each part payment.
 */
final class BillingController
{
    /** Billing overview with links to print the invoice or any receipt. */
    public function index(string $projectId): void
    {
        [$project, $summary, $billTo] = $this->load($projectId);

        View::render('billing', [
            'title' => 'Billing',
            'project' => $project,
            'summary' => $summary,
            'billTo' => $billTo,
            'company' => Billing::company(),
            'error' => $_SESSION['flash_error'] ?? null,
            'success' => $_SESSION['flash_success'] ?? null,
        ]);
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);
    }

    /** Printable bill for the whole project, listing every part payment received. */
    public function invoice(string $projectId): void
    {
        [$project, $summary, $billTo] = $this->load($projectId);

        View::render('invoice', [
            'title' => 'Invoice ' . $summary['invoice_number'],
            'project' => $project,
            'summary' => $summary,
            'billTo' => $billTo,
            'company' => Billing::company(),
        ], 'print_layout');
    }

    /** Printable receipt for a single part payment. */
    public function receipt(string $projectId, string $paymentId): void
    {
        [$project, $summary, $billTo] = $this->load($projectId);
        $payment = Billing::payment($project, $paymentId);

        if (!$payment) {
            $_SESSION['flash_error'] = 'That part payment could not be found.';
            Response::redirect('/projects/' . $projectId . '/billing');
        }

        View::render('receipt', [
            'title' => 'Receipt ' . $payment['receipt_number'],
            'project' => $project,
            'summary' => $summary,
            'payment' => $payment,
            'billTo' => $billTo,
            'company' => Billing::company(),
        ], 'print_layout');
    }

    /**
     * Loads the project, refuses access early and returns everything the three
     * screens share.
     *
     * @return array{0:object,1:array,2:array}
     */
    private function load(string $projectId): array
    {
        if (!Auth::canAccessProject($projectId)) {
            $_SESSION['flash_error'] = 'You do not have access to that project.';
            Response::redirect('/projects');
        }

        $project = (new Project())->find($projectId);
        if (!$project) {
            $_SESSION['flash_error'] = 'Project not found.';
            Response::redirect('/projects');
        }

        $clientUsers = [];
        try {
            $clientUsers = (new AdminUser())->allForProject($projectId, 'client');
        } catch (\Throwable) {
            $clientUsers = [];
        }

        return [$project, Billing::summary($project), Billing::billTo($clientUsers, $project)];
    }
}
