<?php
use App\Services\Csrf;

$renewalValue = '';
if ($project && isset($project->renewal_date) && $project->renewal_date) {
    $renewalValue = $project->renewal_date->toDateTime()->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('Y-m-d');
}
$partPayments = $project ? (array) ($project->part_payments ?? []) : [];
if (!$partPayments) {
    $partPayments = [['amount' => '', 'payment_at' => null, 'statement' => '']];
}
?>
<div class="modal" id="<?= htmlspecialchars($id) ?>" aria-hidden="true">
  <div class="modal-backdrop" data-modal-close></div>
  <div class="modal-panel wide" role="dialog" aria-modal="true">
    <button class="modal-close" type="button" data-modal-close aria-label="Close">x</button>
    <h2><?= $project ? 'Edit Project' : 'Create Project' ?></h2>
    <form method="post" action="<?= htmlspecialchars($action) ?>" class="form-grid">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
      <label>Project name <input name="name" required value="<?= htmlspecialchars($project->name ?? '') ?>"></label>
      <label>Status
        <select name="status">
          <?php foreach (['Active', 'Paused', 'Completed'] as $item): ?>
            <option value="<?= $item ?>" <?= (($project->status ?? 'Active') === $item) ? 'selected' : '' ?>><?= $item ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Completion percentage <input type="number" name="completion_percent" min="0" max="100" value="<?= htmlspecialchars((string) ($project->completion_percent ?? 0)) ?>"></label>
      <label>Project URL <input type="url" name="project_url" value="<?= htmlspecialchars($project->project_url ?? '') ?>" placeholder="https://example.com"></label>
      <label>Total payment <input type="number" name="total_payment" min="0" step="0.01" value="<?= htmlspecialchars((string) ($project->total_payment ?? 0)) ?>"></label>
      <label>Renewal date <input type="date" name="renewal_date" value="<?= htmlspecialchars($renewalValue) ?>"></label>
      <label class="span-2">Description <textarea name="description" rows="4"><?= htmlspecialchars($project->description ?? '') ?></textarea></label>
      <label class="span-2">Project notes and planning <textarea name="project_notes" rows="8"><?= htmlspecialchars($project->project_notes ?? '') ?></textarea></label>
      <fieldset class="span-2 payment-panel" data-payment-list>
        <legend>Part payment statements</legend>
        <?php foreach ($partPayments as $payment): ?>
          <?php
            $paymentAt = '';
            if (is_object($payment) && isset($payment->payment_at) && $payment->payment_at) {
                $paymentAt = $payment->payment_at->toDateTime()->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('Y-m-d\TH:i');
            } elseif (is_array($payment) && !empty($payment['payment_at'])) {
                $paymentAt = $payment['payment_at']->toDateTime()->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('Y-m-d\TH:i');
            }
            $amount = is_object($payment) ? ($payment->amount ?? '') : ($payment['amount'] ?? '');
            $statement = is_object($payment) ? ($payment->statement ?? '') : ($payment['statement'] ?? '');
          ?>
          <div class="payment-row">
            <label>Amount <input type="number" name="part_payment_amount[]" min="0" step="0.01" value="<?= htmlspecialchars((string) $amount) ?>"></label>
            <label>Date and time <input type="datetime-local" name="part_payment_at[]" value="<?= htmlspecialchars($paymentAt) ?>"></label>
            <label class="payment-statement">Statement <input name="part_payment_statement[]" value="<?= htmlspecialchars((string) $statement) ?>"></label>
            <button class="danger-button" type="button" data-payment-remove>Remove</button>
          </div>
        <?php endforeach; ?>
        <button class="ghost-button" type="button" data-payment-add>Add Part Payment</button>
      </fieldset>
      <button class="primary-button span-2" type="submit"><?= $project ? 'Save Project' : 'Create Project' ?></button>
    </form>
  </div>
</div>
