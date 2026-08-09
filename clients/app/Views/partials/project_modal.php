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
$returnTo = $returnTo ?? '';
$methods = App\Models\Project::PAYMENT_METHODS;
?>
<div class="modal" id="<?= htmlspecialchars($id) ?>" aria-hidden="true">
  <div class="modal-backdrop" data-modal-close></div>
  <div class="modal-panel wide" role="dialog" aria-modal="true">
    <button class="modal-close" type="button" data-modal-close aria-label="Close">x</button>
    <h2><?= $project ? 'Edit Project' : 'Create Project' ?></h2>
    <form method="post" action="<?= htmlspecialchars($action) ?>" class="form-grid">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
      <?php if ($returnTo !== ''): ?><input type="hidden" name="return_to" value="<?= htmlspecialchars($returnTo) ?>"><?php endif; ?>
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
      <label>Tax percentage <input type="number" name="tax_percent" min="0" max="100" step="0.01" value="<?= htmlspecialchars((string) ($project->tax_percent ?? 0)) ?>"><small class="field-hint">Leave 0 if the bill has no tax.</small></label>
      <label>Renewal date <input type="date" name="renewal_date" value="<?= htmlspecialchars($renewalValue) ?>"></label>
      <label class="span-2">Description <textarea name="description" rows="4"><?= htmlspecialchars($project->description ?? '') ?></textarea></label>
      <label class="span-2">Project notes and planning <textarea name="project_notes" rows="8"><?= htmlspecialchars($project->project_notes ?? '') ?></textarea></label>
      <label class="span-2">Billing notes (printed on the bill) <textarea name="billing_notes" rows="3"><?= htmlspecialchars($project->billing_notes ?? '') ?></textarea></label>
      <fieldset class="span-2 payment-panel" data-payment-list>
        <legend>Part payments</legend>
        <p class="field-hint">Each row becomes a numbered receipt you can print from the billing page.</p>
        <p class="payment-total" data-payment-total aria-live="polite"></p>
        <?php foreach ($partPayments as $payment): ?>
          <?php
            $read = static function (string $key, mixed $default = '') use ($payment): mixed {
                if (is_object($payment)) {
                    return $payment->{$key} ?? $default;
                }
                return $payment[$key] ?? $default;
            };
            $paymentDate = $read('payment_at', null);
            $paymentAt = $paymentDate
                ? $paymentDate->toDateTime()->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('Y-m-d\TH:i')
                : '';
            $amount = $read('amount', '');
            $statement = $read('statement', '');
            $method = (string) $read('method', 'Bank transfer');
            $reference = (string) $read('reference', '');
          ?>
          <div class="payment-row">
            <input type="hidden" name="part_payment_id[]" value="<?= htmlspecialchars((string) $read('id', '')) ?>">
            <label>Amount <input type="number" name="part_payment_amount[]" min="0" step="0.01" value="<?= htmlspecialchars((string) $amount) ?>"></label>
            <label>Date and time <input type="datetime-local" name="part_payment_at[]" value="<?= htmlspecialchars($paymentAt) ?>"></label>
            <label>Method
              <select name="part_payment_method[]">
                <?php foreach ($methods as $item): ?>
                  <option value="<?= htmlspecialchars($item) ?>" <?= $method === $item ? 'selected' : '' ?>><?= htmlspecialchars($item) ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <label>Reference <input name="part_payment_reference[]" value="<?= htmlspecialchars($reference) ?>" placeholder="UTR / cheque no."></label>
            <label class="payment-statement">Statement <input name="part_payment_statement[]" value="<?= htmlspecialchars((string) $statement) ?>"></label>
            <button class="danger-button" type="button" data-payment-remove>Remove</button>
          </div>
        <?php endforeach; ?>
        <button class="ghost-button" type="button" data-payment-add>Add part payment</button>
      </fieldset>
      <button class="primary-button span-2" type="submit"><?= $project ? 'Save Project' : 'Create Project' ?></button>
    </form>
  </div>
</div>
