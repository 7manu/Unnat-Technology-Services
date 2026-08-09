<?php
use App\Services\Auth;
use App\Services\Billing;

$projectId = (string) $project->_id;
$progress = $summary['grand_total'] > 0 ? min(100, (int) round($summary['paid'] / $summary['grand_total'] * 100)) : 0;
?>
<section class="page-heading">
  <div>
    <a class="back-link" href="<?= Auth::isClientUser() ? '/projects' : '/projects/' . $projectId . '/clients' ?>">&larr; Back</a>
    <p class="eyebrow">Billing</p>
    <h1><?= htmlspecialchars($project->name ?? 'Project') ?></h1>
    <p class="muted">Invoice <?= htmlspecialchars($summary['invoice_number']) ?> &middot; billed to <?= htmlspecialchars($billTo['name']) ?></p>
  </div>
  <div class="heading-actions">
    <a class="primary-button" href="/projects/<?= $projectId ?>/invoice">Print bill</a>
    <?php if (Auth::isAdmin()): ?>
      <button class="ghost-button" type="button" data-modal-open="project-edit-<?= $projectId ?>">Update payments</button>
    <?php endif; ?>
  </div>
</section>

<section class="payment-summary">
  <article class="stat-card"><span>Project value</span><strong><?= Billing::money($summary['subtotal']) ?></strong></article>
  <?php if ($summary['tax_percent'] > 0): ?>
    <article class="stat-card"><span>Tax (<?= rtrim(rtrim(number_format($summary['tax_percent'], 2), '0'), '.') ?>%)</span><strong><?= Billing::money($summary['tax_amount']) ?></strong></article>
  <?php endif; ?>
  <article class="stat-card"><span>Received</span><strong class="positive"><?= Billing::money($summary['paid']) ?></strong></article>
  <article class="stat-card"><span>Balance due</span><strong class="<?= $summary['balance'] > 0 ? 'due' : 'positive' ?>"><?= Billing::money($summary['balance']) ?></strong></article>
</section>

<section class="billing-progress">
  <div class="billing-progress-head">
    <div>
      <span class="muted">Payment collected</span>
      <strong><?= $progress ?>%</strong>
    </div>
    <span class="status-pill <?= $summary['is_settled'] ? 'is-paid' : ($summary['paid'] > 0 ? 'is-partial' : 'is-due') ?>">
      <?= $summary['is_settled'] ? 'Paid in full' : ($summary['paid'] > 0 ? 'Part paid' : 'Payment due') ?>
    </span>
  </div>
  <div class="progress-meter large"><span style="width: <?= $progress ?>%"></span></div>
  <p class="muted">Total billable <?= Billing::money($summary['grand_total']) ?> across <?= count($summary['payments']) ?> recorded payment<?= count($summary['payments']) === 1 ? '' : 's' ?>.</p>
</section>

<section class="table-panel">
  <div class="panel-head">
    <h2>Part payments</h2>
    <p class="muted">Every payment can be printed as its own receipt, so a client gets a bill for each part payment.</p>
  </div>
  <table class="stacked-table">
    <thead><tr><th>#</th><th>Receipt no.</th><th>Received on</th><th>Method</th><th>Amount</th><th>Balance after</th><th>Statement</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($summary['payments'] as $payment): ?>
        <tr>
          <td data-label="#"><?= (int) $payment['installment'] ?></td>
          <td data-label="Receipt no."><strong><?= htmlspecialchars($payment['receipt_number']) ?></strong></td>
          <td data-label="Received on"><?= htmlspecialchars($payment['paid_at_text']) ?></td>
          <td data-label="Method"><?= htmlspecialchars($payment['method']) ?><?php if ($payment['reference'] !== ''): ?><small><?= htmlspecialchars($payment['reference']) ?></small><?php endif; ?></td>
          <td data-label="Amount"><strong><?= Billing::money((float) $payment['amount']) ?></strong></td>
          <td data-label="Balance after"><?= Billing::money((float) $payment['balance_after']) ?></td>
          <td data-label="Statement"><?= $payment['statement'] !== '' ? htmlspecialchars($payment['statement']) : '<span class="muted">—</span>' ?></td>
          <td data-label="Actions" class="actions">
            <a class="link-button" href="/projects/<?= $projectId ?>/receipt/<?= htmlspecialchars($payment['id']) ?>">Print receipt</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$summary['payments']): ?>
        <tr><td colspan="8" class="empty">No part payments recorded yet.<?= Auth::isAdmin() ? ' Use “Update payments” to add the first one.' : '' ?></td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</section>

<?php if (($project->billing_notes ?? '') !== ''): ?>
  <section class="note-panel">
    <h2>Billing notes</h2>
    <p><?= nl2br(htmlspecialchars($project->billing_notes)) ?></p>
  </section>
<?php endif; ?>

<?php if (Auth::isAdmin()): ?>
  <?php App\Services\View::partial('project_modal', [
      'id' => 'project-edit-' . $projectId,
      'action' => '/projects/' . $projectId,
      'project' => $project,
      'returnTo' => '/projects/' . $projectId . '/billing',
  ]); ?>
<?php endif; ?>
