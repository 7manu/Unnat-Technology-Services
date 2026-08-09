<?php
use App\Services\Billing;

$projectId = (string) $project->_id;
$percent = (int) ($project->completion_percent ?? 0);
$summary = Billing::summary($project);
$renewalText = '—';
if (isset($project->renewal_date) && $project->renewal_date) {
    $renewalText = $project->renewal_date->toDateTime()->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('d M Y');
}
?>
<section class="page-heading">
  <div>
    <a class="back-link" href="/projects">&larr; Back to projects</a>
    <p class="eyebrow">Project Progress</p>
    <h1><?= htmlspecialchars($project->name ?? 'Project') ?></h1>
  </div>
  <div class="heading-actions">
    <a class="ghost-button" href="/projects/<?= $projectId ?>/billing">Billing</a>
    <a class="ghost-button" href="/projects/<?= $projectId ?>/invoice">Print bill</a>
    <?php if (!empty($project->project_url)): ?>
      <a class="primary-button" href="<?= htmlspecialchars($project->project_url) ?>" target="_blank" rel="noopener">Open project</a>
    <?php endif; ?>
  </div>
</section>

<section class="progress-hero">
  <div>
    <span>Completion</span>
    <strong><?= $percent ?>%</strong>
  </div>
  <div class="progress-meter large"><span style="width: <?= $percent ?>%"></span></div>
  <p class="muted"><?= htmlspecialchars($project->description ?? '') ?></p>
</section>

<section class="payment-summary">
  <article class="stat-card"><span>Total payment</span><strong><?= Billing::money($summary['grand_total']) ?></strong></article>
  <article class="stat-card"><span>Received</span><strong class="positive"><?= Billing::money($summary['paid']) ?></strong></article>
  <article class="stat-card"><span>Balance</span><strong class="<?= $summary['balance'] > 0 ? 'due' : 'positive' ?>"><?= Billing::money($summary['balance']) ?></strong></article>
  <article class="stat-card"><span>Renewal date</span><strong><?= htmlspecialchars($renewalText) ?></strong></article>
</section>

<section class="table-panel payment-table">
  <div class="panel-head">
    <h2>Part payments</h2>
    <p class="muted">Invoice <?= htmlspecialchars($summary['invoice_number']) ?>. Print a receipt for any single payment.</p>
  </div>
  <table class="stacked-table">
    <thead><tr><th>#</th><th>Amount</th><th>Date and time</th><th>Method</th><th>Statement</th><th>Receipt</th></tr></thead>
    <tbody>
      <?php foreach ($summary['payments'] as $payment): ?>
        <tr>
          <td data-label="#"><?= (int) $payment['installment'] ?></td>
          <td data-label="Amount"><strong><?= Billing::money((float) $payment['amount']) ?></strong></td>
          <td data-label="Date and time"><?= htmlspecialchars($payment['paid_at_text']) ?></td>
          <td data-label="Method"><?= htmlspecialchars($payment['method']) ?><?php if ($payment['reference'] !== ''): ?><small><?= htmlspecialchars($payment['reference']) ?></small><?php endif; ?></td>
          <td data-label="Statement"><?= $payment['statement'] !== '' ? htmlspecialchars($payment['statement']) : '<span class="muted">—</span>' ?></td>
          <td data-label="Receipt" class="actions"><a class="link-button" href="/projects/<?= $projectId ?>/receipt/<?= htmlspecialchars($payment['id']) ?>"><?= htmlspecialchars($payment['receipt_number']) ?></a></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$summary['payments']): ?><tr><td colspan="6" class="empty">No part payments recorded.</td></tr><?php endif; ?>
    </tbody>
  </table>
</section>

<section class="diary-page">
  <div class="diary-header">
    <p class="eyebrow">Diary Notes</p>
    <span><?= date('d M Y') ?></span>
  </div>
  <div class="diary-lines">
    <?= nl2br(htmlspecialchars($project->project_notes ?? 'No notes have been added yet.')) ?>
  </div>
</section>
