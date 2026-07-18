<?php
$percent = (int) ($project->completion_percent ?? 0);
$paidTotal = 0;
foreach ((array) ($project->part_payments ?? []) as $payment) {
    $paidTotal += (float) (is_object($payment) ? ($payment->amount ?? 0) : ($payment['amount'] ?? 0));
}
$totalPayment = (float) ($project->total_payment ?? 0);
$balance = max(0, $totalPayment - $paidTotal);
$renewalText = '-';
if (isset($project->renewal_date) && $project->renewal_date) {
    $renewalText = $project->renewal_date->toDateTime()->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('d M Y');
}
?>
<section class="page-heading">
  <div>
    <p class="eyebrow">Project Progress</p>
    <h1><?= htmlspecialchars($project->name ?? 'Project') ?></h1>
  </div>
  <?php if (!empty($project->project_url)): ?>
    <a class="primary-button" href="<?= htmlspecialchars($project->project_url) ?>" target="_blank" rel="noopener">Open Project</a>
  <?php endif; ?>
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
  <article class="stat-card"><span>Total Payment</span><strong><?= number_format($totalPayment, 2) ?></strong></article>
  <article class="stat-card"><span>Part Payment</span><strong><?= number_format($paidTotal, 2) ?></strong></article>
  <article class="stat-card"><span>Balance</span><strong><?= number_format($balance, 2) ?></strong></article>
  <article class="stat-card"><span>Renewal Date</span><strong><?= htmlspecialchars($renewalText) ?></strong></article>
</section>

<section class="table-panel payment-table">
  <table>
    <thead><tr><th>Amount</th><th>Date and Time</th><th>Statement</th></tr></thead>
    <tbody>
      <?php foreach ((array) ($project->part_payments ?? []) as $payment): ?>
        <?php
          $amount = is_object($payment) ? ($payment->amount ?? 0) : ($payment['amount'] ?? 0);
          $statement = is_object($payment) ? ($payment->statement ?? '') : ($payment['statement'] ?? '');
          $paymentAt = '-';
          $paymentDate = is_object($payment) ? ($payment->payment_at ?? null) : ($payment['payment_at'] ?? null);
          if ($paymentDate) {
              $paymentAt = $paymentDate->toDateTime()->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('d M Y, h:i A');
          }
        ?>
        <tr>
          <td><strong><?= number_format((float) $amount, 2) ?></strong></td>
          <td><?= htmlspecialchars($paymentAt) ?></td>
          <td><?= htmlspecialchars((string) $statement) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($project->part_payments)): ?><tr><td colspan="3" class="empty">No part payments recorded.</td></tr><?php endif; ?>
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
