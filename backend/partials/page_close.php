<?php
/** Closing shell for every public page: footer, floating WhatsApp button, body-end snippets. */
declare(strict_types=1);

require_once __DIR__ . '/../cms.php';

$socialLinks = [
    ['class' => 'social-linkedin', 'url' => cms_raw('global.social.linkedin_url'), 'label' => cms_raw('global.social.linkedin_label'), 'icon' => 'assets/images/linkedin.svg'],
    ['class' => 'social-instagram', 'url' => cms_raw('global.social.instagram_url'), 'label' => cms_raw('global.social.instagram_label'), 'icon' => 'assets/images/instagram.svg'],
    ['class' => 'social-facebook', 'url' => cms_raw('global.social.facebook_url'), 'label' => cms_raw('global.social.facebook_label'), 'icon' => 'assets/images/facebook.svg'],
];

$footerColumns = [
    ['title' => cms_raw('footer.column_1.title'), 'menu' => 'footer_explore'],
    ['title' => cms_raw('footer.column_2.title'), 'menu' => 'footer_platforms'],
    ['title' => cms_raw('footer.column_3.title'), 'menu' => 'footer_contact'],
];

$copyright = str_replace('{year}', date('Y'), cms_raw('footer.bottom.copyright'));
$whatsappUrl = 'https://wa.me/' . rawurlencode(cms_raw('global.contact.whatsapp_number'))
    . '?text=' . rawurlencode(cms_raw('global.contact.whatsapp_message'));
?>
    </main>

    <footer class="site-footer">
      <div class="container">
        <div class="footer-grid">
          <div class="footer-brand">
            <a class="brand" href="index.php"><img src="<?= cms_text('global.brand.logo') ?>" width="46" height="46" alt="" loading="lazy" /><span><?= cms_text('global.brand.name') ?></span></a>
            <p><?= cms_text('footer.brand.copy') ?></p>
            <div class="social-links">
              <?php foreach ($socialLinks as $social): ?>
                <?php if (cms_safe_external_url($social['url']) !== '#'): ?>
                  <a class="<?= e($social['class']) ?>" href="<?= e(cms_safe_external_url($social['url'])) ?>" target="_blank" rel="noopener" aria-label="<?= e($social['label']) ?>"><img src="<?= e($social['icon']) ?>" alt="" /></a>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
          </div>
          <?php foreach ($footerColumns as $column): ?>
            <div class="footer-column">
              <h3><?= e($column['title']) ?></h3>
              <?php foreach (cms_nav($column['menu']) as $item): ?>
                <?php
                  $target = (string) ($item['link_target'] ?? '_self');
                  $rel = (string) ($item['rel'] ?? '');
                  if ($target === '_blank' && $rel === '') {
                      $rel = 'noopener';
                  }
                ?>
                <a href="<?= e(cms_safe_link((string) $item['url'])) ?>"<?= $target !== '_self' ? ' target="' . e($target) . '"' : '' ?><?= $rel !== '' ? ' rel="' . e($rel) . '"' : '' ?>><?= e((string) $item['label']) ?></a>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="footer-bottom">
          <span><?= e($copyright) ?></span>
          <span><?= cms_text('footer.bottom.note') ?></span>
        </div>
      </div>
    </footer>

    <a class="whatsapp-float" href="<?= e($whatsappUrl) ?>" target="_blank" rel="noopener" aria-label="<?= cms_text('global.contact.whatsapp_aria') ?>"><img src="assets/images/whatsapp.svg" alt="" width="47" height="47" /></a>
    <?php if (cms_setting('custom_body_end_html') !== ''): ?><?= cms_setting('custom_body_end_html') ?><?php endif; ?>
  </body>
</html>
