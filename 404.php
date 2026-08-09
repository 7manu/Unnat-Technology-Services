<?php
/** Not-found page — wording is editable from Admin → Website content (System pages). */
declare(strict_types=1);

require_once __DIR__ . '/backend/cms.php';

if (http_response_code() === 200) {
    http_response_code(404);
}

$route = '/404';
$seoOverrides = ['robots_index' => 'noindex', 'meta_title' => cms_raw('system.notfound.title')];
require __DIR__ . '/backend/partials/page_open.php';
?>
      <section class="inner-hero" aria-labelledby="page-title">
        <div class="container reveal visible">
          <p class="eyebrow"><?= cms_text('system.notfound.eyebrow') ?></p>
          <h1 id="page-title"><?= cms_text('system.notfound.title') ?></h1>
          <p><?= cms_text('system.notfound.copy') ?></p>
          <p><a class="button button-primary" href="<?= cms_link('system.notfound.button_url', '/') ?>"><?= cms_text('system.notfound.button_label') ?> <span class="button-arrow" aria-hidden="true">→</span></a></p>
        </div>
      </section>
<?php require __DIR__ . '/backend/partials/page_close.php'; ?>
