<?php
/** Contact page — reachable at /contact.html and /contact.php. */
declare(strict_types=1);

require __DIR__ . '/backend/cms.php';
cms_apply_redirects();

$route = '/contact.html';
require __DIR__ . '/backend/partials/page_open.php';
?>
      <section class="inner-hero" aria-labelledby="page-title">
        <div class="container reveal visible"><p class="eyebrow"><?= cms_text('contact.hero.eyebrow') ?></p><h1 id="page-title"><?= cms_text('contact.hero.title_prefix') ?> <span class="gradient-text"><?= cms_text('contact.hero.title_highlight') ?></span></h1><p><?= cms_text('contact.hero.copy') ?></p></div>
      </section>

      <section class="section section-soft" id="contact-form" aria-labelledby="form-title">
        <div class="container contact-grid">
          <div class="reveal from-left">
            <p class="eyebrow"><?= cms_text('contact.form.eyebrow') ?></p>
            <h2 id="form-title"><?= cms_text('contact.form.title') ?></h2>
            <p class="section-copy"><?= cms_text('contact.form.copy') ?></p>
            <?php require __DIR__ . '/backend/partials/contact_cards.php'; ?>
          </div>
          <?php $returnTo = 'contact.html#contact-form'; require __DIR__ . '/backend/partials/inquiry_form.php'; ?>
        </div>
      </section>

      <section class="section" aria-labelledby="expect-title">
        <div class="container">
          <div class="section-head center reveal"><p class="eyebrow"><?= cms_text('contact.next.eyebrow') ?></p><h2 id="expect-title"><?= cms_text('contact.next.title') ?></h2></div>
          <div class="trust-grid">
            <?php for ($i = 1; $i <= 3; $i++): ?>
              <?php if (cms_raw('contact.next.card_' . $i . '_title') === '') { continue; } ?>
              <article class="trust-card reveal" data-delay="<?= ($i - 1) * 80 ?>"><h3><?= cms_text('contact.next.card_' . $i . '_title') ?></h3><p><?= cms_text('contact.next.card_' . $i . '_copy') ?></p></article>
            <?php endfor; ?>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/backend/partials/page_close.php'; ?>
