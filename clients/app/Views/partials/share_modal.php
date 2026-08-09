<?php
/**
 * Credential share composer.
 *
 * Builds a ready-to-send message with the portal address, the client's login ID,
 * an optional password and a free-text note, then hands it to WhatsApp, email,
 * SMS, the clipboard or the device share sheet.
 *
 * Expected: $id, $clientUser, $portalUrl, optional $sharedPassword
 */
use App\Services\Csrf;

$clientId = (string) $clientUser->_id;
$sharedPassword = $sharedPassword ?? '';
$clientName = (string) ($clientUser->name ?? 'there');
$clientEmail = (string) ($clientUser->email ?? '');

/* wa.me needs digits only; a bare 10-digit Indian mobile gets the country code. */
$whatsappNumber = preg_replace('/\D/', '', (string) ($clientUser->mobile_phone ?? '')) ?? '';
if (strlen($whatsappNumber) === 10) {
    $whatsappNumber = '91' . $whatsappNumber;
}
?>
<div class="modal" id="<?= htmlspecialchars($id) ?>" aria-hidden="true">
  <div class="modal-backdrop" data-modal-close></div>
  <div class="modal-panel wide" role="dialog" aria-modal="true">
    <button class="modal-close" type="button" data-modal-close aria-label="Close">&times;</button>
    <h2>Send login details to <?= htmlspecialchars($clientName) ?></h2>

    <div class="share-composer"
         data-share-composer
         data-client-name="<?= htmlspecialchars($clientName) ?>"
         data-whatsapp="<?= htmlspecialchars($whatsappNumber) ?>"
         data-email="<?= htmlspecialchars($clientEmail) ?>">

      <?php if ($sharedPassword !== ''): ?>
        <p class="share-flash">This password is shown once. Send it now — after you leave this page it can only be replaced, never read back.</p>
      <?php endif; ?>

      <div class="form-grid">
        <label class="span-2">Login page
          <input type="url" data-share-field="url" value="<?= htmlspecialchars($portalUrl) ?>">
        </label>
        <label>Login ID (email)
          <input type="email" data-share-field="email" value="<?= htmlspecialchars($clientEmail) ?>" readonly>
        </label>
        <label>Password
          <input type="text" data-share-field="password" value="<?= htmlspecialchars($sharedPassword) ?>" placeholder="<?= $sharedPassword !== '' ? '' : 'Not readable — set a new one below' ?>">
          <small class="field-hint">Stored passwords are encrypted and cannot be read. Leave this empty to send only the link and ID.</small>
        </label>
        <label class="span-2">Your note
          <textarea rows="3" data-share-field="note" placeholder="Anything you want to add — what the portal is for, when you will call, who to contact."></textarea>
        </label>
      </div>

      <div class="share-preview">
        <div class="share-preview-head">
          <span>Message preview</span>
          <button class="link-button" type="button" data-share-copy>Copy message</button>
        </div>
        <pre data-share-preview aria-live="polite"></pre>
      </div>

      <div class="share-actions">
        <a class="primary-button" data-share-target="whatsapp" href="#" target="_blank" rel="noopener">Send on WhatsApp</a>
        <a class="ghost-button" data-share-target="email" href="#">Send by email</a>
        <a class="ghost-button" data-share-target="sms" href="#">Send by SMS</a>
        <button class="ghost-button" type="button" data-share-native hidden>Other apps…</button>
      </div>

      <form class="share-reset" method="post" action="/client-users/<?= $clientId ?>/reset-password"
            data-confirm="Set a new password for this client? Their current password will stop working immediately.">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
        <div>
          <strong>Need a password to send?</strong>
          <p class="field-hint">Set a new one and it will appear above, ready to share. Leave the box empty to have one generated.</p>
        </div>
        <div class="share-reset-controls">
          <input type="text" name="new_password" minlength="8" placeholder="Leave empty to generate" autocomplete="off">
          <button class="ghost-button" type="submit">Set new password</button>
        </div>
      </form>
    </div>
  </div>
</div>
