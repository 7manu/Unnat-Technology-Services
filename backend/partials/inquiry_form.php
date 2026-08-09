<?php
/**
 * Public inquiry form.
 *
 * Expected variable:
 *   string $returnTo  Where backend/contact.php should send the visitor back to.
 */
declare(strict_types=1);

require_once __DIR__ . '/../cms.php';

$returnTo = $returnTo ?? 'index.php#contact';
?>
<form class="contact-form reveal from-right" action="backend/contact.php" method="post">
  <div class="field"><label for="name"><?= cms_text('shared.inquiry_form.name_label') ?></label><input id="name" name="name" type="text" maxlength="25" autocomplete="name" required /></div>
  <div class="field"><label for="mobile"><?= cms_text('shared.inquiry_form.phone_label') ?></label><input id="mobile" name="mobile" type="tel" maxlength="10" autocomplete="tel" inputmode="numeric" pattern="[0-9]{10}" required /></div>
  <div class="field field-full"><label for="email"><?= cms_text('shared.inquiry_form.email_label') ?></label><input id="email" name="email" type="email" maxlength="50" autocomplete="email" /></div>
  <div class="field field-full"><label for="question"><?= cms_text('shared.inquiry_form.question_label') ?></label><textarea id="question" name="question" maxlength="150" required></textarea></div>
  <div class="field field-full"><label for="details"><?= cms_text('shared.inquiry_form.details_label') ?></label><textarea id="details" name="details" maxlength="500" placeholder="<?= cms_text('shared.inquiry_form.details_placeholder') ?>"></textarea></div>
  <input type="hidden" name="return_to" value="<?= e($returnTo) ?>" />
  <input class="visually-hidden-field" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" />
  <p class="form-note" data-form-message data-message-sent="<?= cms_text('system.form_success.message') ?>" data-message-error="<?= cms_text('system.form_error.message') ?>"><?= cms_text('shared.inquiry_form.consent_note') ?></p>
  <div class="field-full"><button class="button button-primary" type="submit"><?= cms_text('shared.inquiry_form.submit_label') ?> <span class="button-arrow" aria-hidden="true">↗</span></button></div>
</form>
