<?php
use App\Services\Billing;

$projectId = (string) $project->_id;
$backUrl = '/projects/' . $projectId . '/billing';
$isPartlyPaid = $summary['paid'] > 0 && !$summary['is_settled'];
?>
<article class="document">
  <header class="document-head">
    <div class="document-brand">
      <img src="/assets/img/logo-uts.webp" alt="">
      <div>
        <strong><?= htmlspecialchars($company['name']) ?></strong>
        <p><?= nl2br(htmlspecialchars($company['address'])) ?></p>
        <p><?= htmlspecialchars($company['phone']) ?> &middot; <?= htmlspecialchars($company['email']) ?></p>
        <?php if ($company['tax_id'] !== ''): ?><p><?= htmlspecialchars($company['tax_label']) ?>: <?= htmlspecialchars($company['tax_id']) ?></p><?php endif; ?>
      </div>
    </div>
    <div class="document-meta">
      <h1>Invoice</h1>
      <dl>
        <dt>Invoice no.</dt><dd><?= htmlspecialchars($summary['invoice_number']) ?></dd>
        <dt>Date</dt><dd><?= htmlspecialchars($summary['invoice_date']) ?></dd>
        <dt>Status</dt>
        <dd>
          <span class="document-stamp <?= $summary['is_settled'] ? 'is-paid' : ($isPartlyPaid ? 'is-partial' : 'is-due') ?>">
            <?= $summary['is_settled'] ? 'Paid in full' : ($isPartlyPaid ? 'Partly paid' : 'Payment due') ?>
          </span>
        </dd>
      </dl>
    </div>
  </header>

  <section class="document-parties">
    <div>
      <h2>Billed to</h2>
      <strong><?= htmlspecialchars($billTo['name']) ?></strong>
      <?php if ($billTo['address'] !== ''): ?><p><?= nl2br(htmlspecialchars($billTo['address'])) ?></p><?php endif; ?>
      <?php if ($billTo['phone'] !== ''): ?><p><?= htmlspecialchars($billTo['phone']) ?></p><?php endif; ?>
      <?php if ($billTo['email'] !== ''): ?><p><?= htmlspecialchars($billTo['email']) ?></p><?php endif; ?>
      <?php if ($billTo['is_placeholder']): ?><p class="muted no-print">No client access user is assigned to this project yet, so the project name is used.</p><?php endif; ?>
    </div>
    <div>
      <h2>Project</h2>
      <strong><?= htmlspecialchars($project->name ?? '') ?></strong>
      <p><?= (int) ($project->completion_percent ?? 0) ?>% complete &middot; <?= htmlspecialchars($project->status ?? 'Active') ?></p>
      <?php if (isset($project->renewal_date) && $project->renewal_date): ?>
        <p>Renewal: <?= htmlspecialchars($project->renewal_date->toDateTime()->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('d M Y')) ?></p>
      <?php endif; ?>
    </div>
  </section>

  <table class="document-table">
    <thead><tr><th>Description</th><th class="right">Amount</th></tr></thead>
    <tbody>
      <tr>
        <td>
          <strong><?= htmlspecialchars($project->name ?? 'Project') ?></strong>
          <?php if (($project->description ?? '') !== ''): ?><small><?= htmlspecialchars($project->description) ?></small><?php endif; ?>
        </td>
        <td class="right"><?= Billing::money($summary['subtotal']) ?></td>
      </tr>
    </tbody>
    <tfoot>
      <tr><th class="right">Subtotal</th><td class="right"><?= Billing::money($summary['subtotal']) ?></td></tr>
      <?php if ($summary['tax_percent'] > 0): ?>
        <tr><th class="right"><?= htmlspecialchars($company['tax_label']) ?> @ <?= rtrim(rtrim(number_format($summary['tax_percent'], 2), '0'), '.') ?>%</th><td class="right"><?= Billing::money($summary['tax_amount']) ?></td></tr>
      <?php endif; ?>
      <tr class="grand"><th class="right">Total</th><td class="right"><?= Billing::money($summary['grand_total']) ?></td></tr>
      <tr><th class="right">Received</th><td class="right">&minus; <?= Billing::money($summary['paid']) ?></td></tr>
      <tr class="grand"><th class="right">Balance due</th><td class="right"><?= Billing::money($summary['balance']) ?></td></tr>
    </tfoot>
  </table>

  <p class="document-words"><strong>Amount in words:</strong> <?= htmlspecialchars(Billing::amountInWords($summary['grand_total'])) ?></p>

  <section class="document-section">
    <h2>Payments received</h2>
    <?php if ($summary['payments']): ?>
      <table class="document-table compact">
        <thead><tr><th>#</th><th>Receipt no.</th><th>Date</th><th>Method</th><th>Reference</th><th class="right">Amount</th><th class="right">Balance after</th></tr></thead>
        <tbody>
          <?php foreach ($summary['payments'] as $payment): ?>
            <tr>
              <td><?= (int) $payment['installment'] ?></td>
              <td><?= htmlspecialchars($payment['receipt_number']) ?></td>
              <td><?= htmlspecialchars($payment['paid_on_text']) ?></td>
              <td><?= htmlspecialchars($payment['method']) ?></td>
              <td><?= $payment['reference'] !== '' ? htmlspecialchars($payment['reference']) : '—' ?></td>
              <td class="right"><?= Billing::money((float) $payment['amount']) ?></td>
              <td class="right"><?= Billing::money((float) $payment['balance_after']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p class="muted">No payments have been received against this invoice yet.</p>
    <?php endif; ?>
  </section>

  <?php if (($project->billing_notes ?? '') !== '' || $company['bank'] !== '' || $company['terms'] !== ''): ?>
    <section class="document-notes">
      <?php if (($project->billing_notes ?? '') !== ''): ?>
        <div><h2>Notes</h2><p><?= nl2br(htmlspecialchars($project->billing_notes)) ?></p></div>
      <?php endif; ?>
      <?php if ($company['bank'] !== ''): ?>
        <div><h2>Payment details</h2><p><?= nl2br(htmlspecialchars($company['bank'])) ?></p></div>
      <?php endif; ?>
      <?php if ($company['terms'] !== ''): ?>
        <div><h2>Terms</h2><p><?= nl2br(htmlspecialchars($company['terms'])) ?></p></div>
      <?php endif; ?>
    </section>
  <?php endif; ?>

  <footer class="document-foot">
    <span>This is a computer-generated invoice and is valid without a signature.</span>
    <span><?= htmlspecialchars($company['website']) ?></span>
  </footer>
</article>
