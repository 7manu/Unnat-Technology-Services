<?php
/** Call / email / visit cards shown beside the inquiry form. */
declare(strict_types=1);

require_once __DIR__ . '/../cms.php';
?>
<div class="contact-list">
  <a class="contact-item" href="tel:<?= cms_text('global.contact.phone_link') ?>"><i aria-hidden="true"><?= cms_text('shared.contact_cards.call_icon') ?></i><span><strong><?= cms_text('shared.contact_cards.call_title') ?></strong><br /><?= cms_text('global.contact.phone_display') ?></span></a>
  <a class="contact-item" href="mailto:<?= cms_text('global.contact.email') ?>"><i aria-hidden="true"><?= cms_text('shared.contact_cards.email_icon') ?></i><span><strong><?= cms_text('shared.contact_cards.email_title') ?></strong><br /><?= cms_text('global.contact.email') ?></span></a>
  <a class="contact-item" href="<?= e(cms_safe_external_url(cms_raw('global.contact.map_url'))) ?>" target="_blank" rel="noopener"><i aria-hidden="true"><?= cms_text('shared.contact_cards.visit_icon') ?></i><span><strong><?= cms_text('shared.contact_cards.visit_title') ?></strong><br /><?= cms_text('global.contact.location_short') ?></span></a>
</div>
