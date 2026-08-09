<?php
use App\Services\Billing;

$projectId = (string) $project->_id;
$backUrl = '/projects/' . $projectId . '/billing';
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
      <h1>Payment receipt</h1>
      <dl>
        <dt>Receipt no.</dt><dd><?= htmlspecialchars($payment['receipt_number']) ?></dd>
        <dt>Against invoice</dt><dd><?= htmlspecialchars($summary['invoice_number']) ?></dd>
        <dt>Received on</dt><dd><?= htmlspecialchars($payment['paid_on_text']) ?></dd>
        <dt>Status</dt>
        <dd><span class="document-stamp <?= $summary['is_settled'] ? 'is-paid' : 'is-partial' ?>"><?= $summary['is_settled'] ? 'Final payment' : 'Part payment ' . (int) $payment['installment'] ?></span></dd>
      </dl>
    </div>
  </header>

  <section class="document-parties">
    <div>
      <h2>Received from</h2>
      <strong><?= htmlspecialchars($billTo['name']) ?></strong>
      <?php if ($billTo['address'] !== ''): ?><p><?= nl2br(htmlspecialchars($billTo['address'])) ?></p><?php endif; ?>
      <?php if ($billTo['phone'] !== ''): ?><p><?= htmlspecialchars($billTo['phone']) ?></p><?php endif; ?>
      <?php if ($billTo['email'] !== ''): ?><p><?= htmlspecialchars($billTo['email']) ?></p><?php endif; ?>
    </div>
    <div>
      <h2>For project</h2>
      <strong><?= htmlspecialchars($project->name ?? '') ?></strong>
      <p>Installment <?= (int) $payment['installment'] ?> of the agreed project value</p>
      <p><?= htmlspecialchars($payment['method']) ?><?= $payment['reference'] !== '' ? ' &middot; Ref ' . htmlspecialchars($payment['reference']) : '' ?></p>
    </div>
  </section>

  <section class="receipt-amount">
    <span>Amount received</span>
    <strong><?= Billing::money((float) $payment['amount']) ?></strong>
    <p><?= htmlspecialchars(Billing::amountInWords((float) $payment['amount'])) ?></p>
  </section>

  <?php if ($payment['statement'] !== ''): ?>
    <p class="document-words"><strong>Statement:</strong> <?= htmlspecialchars($payment['statement']) ?></p>
  <?php endif; ?>

  <table class="document-table compact">
    <thead><tr><th>Invoice total</th><th class="right">Paid before this payment</th><th class="right">This payment</th><th class="right">Paid to date</th><th class="right">Balance remaining</th></tr></thead>
    <tbody>
      <tr>
        <td><?= Billing::money($summary['grand_total']) ?></td>
        <td class="right"><?= Billing::money((float) $payment['paid_to_date'] - (float) $payment['amount']) ?></td>
        <td class="right"><strong><?= Billing::money((float) $payment['amount']) ?></strong></td>
        <td class="right"><?= Billing::money((float) $payment['paid_to_date']) ?></td>
        <td class="right"><strong><?= Billing::money((float) $payment['balance_after']) ?></strong></td>
      </tr>
    </tbody>
  </table>

  <?php if ((float) $payment['balance_after'] > 0): ?>
    <p class="document-words">A balance of <strong><?= Billing::money((float) $payment['balance_after']) ?></strong> remains on invoice <?= htmlspecialchars($summary['invoice_number']) ?>.</p>
  <?php else: ?>
    <p class="document-words">This payment settles invoice <?= htmlspecialchars($summary['invoice_number']) ?> in full. Thank you.</p>
  <?php endif; ?>

  <?php if ($company['bank'] !== ''): ?>
    <section class="document-notes"><div><h2>Payment details</h2><p><?= nl2br(htmlspecialchars($company['bank'])) ?></p></div></section>
  <?php endif; ?>

  <footer class="document-foot">
    <span>This is a computer-generated receipt and is valid without a signature.</span>
    <span><?= htmlspecialchars($company['website']) ?></span>
  </footer>
</article>
