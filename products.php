<?php
/** Product portfolio — copy is editable from Admin → Website content, items from Admin → Products. */
declare(strict_types=1);

require __DIR__ . '/backend/cms.php';
cms_apply_redirects();

$products = cms_products();
$productsUnavailable = !cms_db_available();

$route = '/products.php';
require __DIR__ . '/backend/partials/page_open.php';
?>
      <section class="inner-hero" aria-labelledby="page-title"><div class="container reveal visible"><p class="eyebrow"><?= cms_text('products.hero.eyebrow') ?></p><h1 id="page-title"><?= cms_text('products.hero.title_prefix') ?> <span class="gradient-text"><?= cms_text('products.hero.title_highlight') ?></span></h1><p><?= cms_text('products.hero.copy') ?></p></div></section>

      <section class="section" aria-labelledby="products-title">
        <div class="container">
          <div class="section-head reveal"><p class="eyebrow"><?= cms_text('products.list.eyebrow') ?></p><h2 id="products-title"><?= cms_text('products.list.title') ?></h2><p class="section-copy"><?= cms_text('products.list.copy') ?></p></div>
          <div class="product-grid">
            <?php if ($products): ?>
              <?php foreach ($products as $index => $product): ?>
                <?php
                  $imageFile = rawurlencode(cms_product_image($product));
                  $externalUrl = cms_safe_external_url($product['url'] ?? '');
                ?>
                <article class="product-card reveal" data-delay="<?= ($index % 3) * 70 ?>">
                  <div class="product-image"><img src="assets/productimages/<?= e($imageFile) ?>" alt="<?= e((string) $product['name']) ?> product preview" width="520" height="325" loading="lazy" decoding="async" /></div>
                  <div class="product-body">
                    <span class="case-tag"><?= cms_text('products.card.tag') ?></span>
                    <h3><?= e((string) $product['name']) ?></h3>
                    <p><?= e((string) $product['description']) ?></p>
                    <?php if ($externalUrl !== '#'): ?><a class="service-link" href="<?= e($externalUrl) ?>" target="_blank" rel="noopener noreferrer"><?= cms_text('products.card.link_label') ?> <span aria-hidden="true">↗</span></a><?php endif; ?>
                  </div>
                </article>
              <?php endforeach; ?>
            <?php elseif ($productsUnavailable): ?>
              <div class="empty-state"><h3><?= cms_text('products.unavailable.title') ?></h3><p><?= cms_text('products.unavailable.copy') ?></p><a class="button button-primary" href="contact.html"><?= cms_text('products.unavailable.button_label') ?></a></div>
            <?php else: ?>
              <div class="empty-state"><h3><?= cms_text('products.empty.title') ?></h3><p><?= cms_text('products.empty.copy') ?></p><a class="button button-primary" href="contact.html"><?= cms_text('products.empty.button_label') ?></a></div>
            <?php endif; ?>
          </div>
        </div>
      </section>

      <section class="section section-soft" aria-labelledby="product-cta"><div class="container cta-panel reveal"><div><h2 id="product-cta"><?= cms_text('products.cta.title') ?></h2><p><?= cms_text('products.cta.copy') ?></p></div><a class="button button-primary" href="<?= cms_link('products.cta.button_url') ?>"><?= cms_text('products.cta.button_label') ?> <span class="button-arrow" aria-hidden="true">↗</span></a></div></section>
<?php require __DIR__ . '/backend/partials/page_close.php'; ?>
