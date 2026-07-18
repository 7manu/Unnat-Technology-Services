<?php
use App\Services\Csrf;
$meetingValue = '';
if ($client && isset($client->meeting_at) && $client->meeting_at) {
    $meetingValue = $client->meeting_at->toDateTime()->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('Y-m-d\TH:i');
}
?>
<div class="modal" id="<?= htmlspecialchars($id) ?>" aria-hidden="true">
  <div class="modal-backdrop" data-modal-close></div>
  <div class="modal-panel wide" role="dialog" aria-modal="true">
    <button class="modal-close" type="button" data-modal-close aria-label="Close">x</button>
    <h2><?= $client ? 'Edit Client' : 'Add Client' ?></h2>
    <form method="post" action="<?= htmlspecialchars($action) ?>" class="form-grid">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
      <label>Name <input name="name" required value="<?= htmlspecialchars($client->name ?? '') ?>"></label>
      <label>Mobile phone <input name="mobile_phone" required value="<?= htmlspecialchars($client->mobile_phone ?? '') ?>"></label>
      <label>Alternate mobile phone <input name="alternate_mobile_phone" value="<?= htmlspecialchars($client->alternate_mobile_phone ?? '') ?>"></label>
      <label>Email <input type="email" name="email" value="<?= htmlspecialchars($client->email ?? '') ?>"></label>
      <label>Status
        <select name="status">
          <?php foreach (['New', 'Contacted', 'Meeting Scheduled', 'Won', 'Lost'] as $item): ?>
            <option value="<?= $item ?>" <?= (($client->status ?? 'New') === $item) ? 'selected' : '' ?>><?= $item ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Meeting schedule <input type="datetime-local" name="meeting_at" value="<?= htmlspecialchars($meetingValue) ?>"></label>
      <label class="span-2">Address <textarea name="address" rows="3"><?= htmlspecialchars($client->address ?? '') ?></textarea></label>
      <label class="span-2">Notes <textarea name="notes" rows="4"><?= htmlspecialchars($client->notes ?? '') ?></textarea></label>
      <button class="primary-button span-2" type="submit"><?= $client ? 'Save Client' : 'Add Client' ?></button>
    </form>
  </div>
</div>
