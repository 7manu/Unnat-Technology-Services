<?php
/** Home page — every string on this page is editable from Admin → Website content. */
declare(strict_types=1);

require __DIR__ . '/backend/cms.php';
cms_apply_redirects();

$route = '/';
$headerScrolled = false;
require __DIR__ . '/backend/partials/page_open.php';
?>
      <section class="hero" aria-labelledby="hero-title">
        <canvas id="particle-field" aria-hidden="true"></canvas>
        <div class="container hero-layout">
          <div class="hero-content">
            <div class="hero-badge reveal visible"><?= cms_text('home.hero.badge') ?></div>
            <h1 id="hero-title" class="reveal visible" data-typewriter aria-label="<?= cms_text('home.hero.headline_aria') ?>"><span class="typewriter-content" aria-hidden="true"><?= cms_text('home.hero.headline_prefix') ?> <span class="gradient-text"><?= cms_text('home.hero.headline_highlight') ?></span> <?= cms_text('home.hero.headline_suffix') ?></span></h1>
            <p class="hero-copy reveal visible"><?= cms_text('home.hero.copy') ?></p>
            <div class="hero-actions reveal visible">
              <a class="button button-primary" data-magnetic href="<?= cms_link('home.hero.primary_cta_url') ?>"><?= cms_text('home.hero.primary_cta_label') ?> <span class="button-arrow" aria-hidden="true">↓</span></a>
              <a class="button button-secondary" data-magnetic href="<?= cms_link('home.hero.secondary_cta_url') ?>"><?= cms_text('home.hero.secondary_cta_label') ?> <span class="button-arrow" aria-hidden="true">↗</span></a>
            </div>
            <div class="hero-proof reveal visible" aria-label="Service qualities">
              <span><?= cms_text('home.hero.proof_1') ?></span>
              <span><?= cms_text('home.hero.proof_2') ?></span>
              <span><?= cms_text('home.hero.proof_3') ?></span>
            </div>
          </div>

          <div class="hero-stage" aria-hidden="true">
            <div class="orbital-system">
              <div class="orbit"></div>
              <div class="orbit"></div>
              <div class="orbit"></div>
              <div class="orbital-core"></div>
            </div>
            <div class="floating-chip chip-one"><i><?= cms_text('home.hero.chip_1_symbol') ?></i><span><?= nl2br(cms_text('home.hero.chip_1_text')) ?></span></div>
            <div class="floating-chip chip-two"><i><?= cms_text('home.hero.chip_2_symbol') ?></i><span><?= nl2br(cms_text('home.hero.chip_2_text')) ?></span></div>
            <div class="floating-chip chip-three"><i><?= cms_text('home.hero.chip_3_symbol') ?></i><span><?= nl2br(cms_text('home.hero.chip_3_text')) ?></span></div>
          </div>
        </div>
        <a class="scroll-cue" href="<?= cms_link('home.hero.scroll_cue_url') ?>"><?= cms_text('home.hero.scroll_cue_label') ?></a>
      </section>

      <div class="trust-strip" aria-label="<?= cms_text('home.trust_strip.aria') ?>">
        <div class="container trust-strip-inner">
          <strong><?= cms_text('home.trust_strip.title') ?></strong>
          <div class="trust-list">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <?php if (cms_raw('home.trust_strip.item_' . $i) !== ''): ?><span><?= cms_text('home.trust_strip.item_' . $i) ?></span><?php endif; ?>
            <?php endfor; ?>
          </div>
        </div>
      </div>

      <section class="section" id="introduction" aria-labelledby="intro-title">
        <div class="container intro-grid">
          <div class="intro-copy reveal from-left">
            <p class="eyebrow"><?= cms_text('home.intro.eyebrow') ?></p>
            <h2 id="intro-title"><?= cms_text('home.intro.title') ?></h2>
            <p class="section-copy"><?= cms_text('home.intro.copy') ?></p>
            <a class="button button-secondary" href="<?= cms_link('home.intro.cta_url') ?>"><?= cms_text('home.intro.cta_label') ?> <span class="button-arrow" aria-hidden="true">→</span></a>
          </div>
          <div class="value-stack">
            <?php for ($i = 1; $i <= 3; $i++): ?>
              <?php if (cms_raw('home.intro.card_' . $i . '_title') === '') { continue; } ?>
              <article class="value-card reveal from-right" data-delay="<?= $i * 80 ?>">
                <span class="card-index"><?= cms_text('home.intro.card_' . $i . '_index') ?></span>
                <div><h3><?= cms_text('home.intro.card_' . $i . '_title') ?></h3><p><?= cms_text('home.intro.card_' . $i . '_copy') ?></p></div>
              </article>
            <?php endfor; ?>
          </div>
        </div>
      </section>

      <section class="section section-soft" id="services" aria-labelledby="services-title">
        <div class="container">
          <div class="section-head reveal">
            <p class="eyebrow"><?= cms_text('home.services.eyebrow') ?></p>
            <h2 id="services-title"><?= cms_text('home.services.title') ?></h2>
            <p class="section-copy"><?= cms_text('home.services.copy') ?></p>
          </div>
          <div class="services-grid">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <?php if (cms_raw('home.services.card_' . $i . '_title') === '') { continue; } ?>
              <article class="service-card reveal" data-delay="<?= ($i - 1) * 70 ?>" data-tilt>
                <span class="service-icon" aria-hidden="true"><?= cms_text('home.services.card_' . $i . '_icon') ?></span>
                <h3><?= cms_text('home.services.card_' . $i . '_title') ?></h3>
                <p><?= cms_text('home.services.card_' . $i . '_copy') ?></p>
                <a class="service-link" href="<?= cms_link('home.services.card_' . $i . '_link_url') ?>"><?= cms_text('home.services.card_' . $i . '_link_label') ?> <span aria-hidden="true">→</span></a>
              </article>
            <?php endfor; ?>
          </div>
        </div>
      </section>

      <section class="section" id="expertise" aria-labelledby="expertise-title">
        <div class="container tech-panel reveal">
          <div class="section-head">
            <p class="eyebrow"><?= cms_text('home.expertise.eyebrow') ?></p>
            <h2 id="expertise-title"><?= cms_text('home.expertise.title') ?></h2>
            <p class="section-copy"><?= cms_text('home.expertise.copy') ?></p>
          </div>
          <div class="expertise-grid">
            <?php for ($i = 1; $i <= 4; $i++): ?>
              <?php if (cms_raw('home.expertise.card_' . $i . '_title') === '') { continue; } ?>
              <article class="expertise-card"><span class="tech-mark"><?= cms_text('home.expertise.card_' . $i . '_tag') ?></span><h3><?= cms_text('home.expertise.card_' . $i . '_title') ?></h3><p><?= cms_text('home.expertise.card_' . $i . '_copy') ?></p></article>
            <?php endfor; ?>
          </div>
        </div>
      </section>

      <section class="section section-soft" id="why-unnat" aria-labelledby="why-title">
        <div class="container">
          <div class="section-head center reveal"><p class="eyebrow"><?= cms_text('home.why.eyebrow') ?></p><h2 id="why-title"><?= cms_text('home.why.title') ?></h2><p class="section-copy"><?= cms_text('home.why.copy') ?></p></div>
          <div class="why-grid">
            <?php for ($i = 1; $i <= 6; $i++): ?>
              <?php if (cms_raw('home.why.card_' . $i . '_title') === '') { continue; } ?>
              <article class="trust-card reveal" data-delay="<?= (($i - 1) % 3) * 80 ?>"><h3><?= cms_text('home.why.card_' . $i . '_title') ?></h3><p><?= cms_text('home.why.card_' . $i . '_copy') ?></p></article>
            <?php endfor; ?>
          </div>
        </div>
      </section>

      <section class="section" id="process" aria-labelledby="process-title">
        <div class="container process-wrap">
          <div class="process-intro reveal from-left"><p class="eyebrow"><?= cms_text('home.process.eyebrow') ?></p><h2 id="process-title"><?= cms_text('home.process.title') ?></h2><p class="section-copy"><?= cms_text('home.process.copy') ?></p></div>
          <div class="process-list">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <?php if (cms_raw('home.process.step_' . $i . '_title') === '') { continue; } ?>
              <article class="process-step reveal from-right" data-delay="<?= ($i - 1) * 60 ?>"><span class="step-num"><?= cms_text('home.process.step_' . $i . '_number') ?></span><div><h3><?= cms_text('home.process.step_' . $i . '_title') ?></h3><p><?= cms_text('home.process.step_' . $i . '_copy') ?></p></div></article>
            <?php endfor; ?>
          </div>
        </div>
      </section>

      <section class="section section-soft" id="work" aria-labelledby="work-title">
        <div class="container">
          <div class="section-head reveal"><p class="eyebrow"><?= cms_text('home.work.eyebrow') ?></p><h2 id="work-title"><?= cms_text('home.work.title') ?></h2><p class="section-copy"><?= cms_text('home.work.copy') ?></p></div>
          <div class="case-grid">
            <?php for ($i = 1; $i <= 3; $i++): ?>
              <?php if (cms_raw('home.work.card_' . $i . '_title') === '') { continue; } ?>
              <article class="case-card reveal <?= $i === 1 ? 'from-left' : 'from-right' ?>"<?= $i === 3 ? ' data-delay="100"' : '' ?>><?php if ($i === 1): ?><div class="case-visual" aria-hidden="true"></div><?php endif; ?><span class="case-tag"><?= cms_text('home.work.card_' . $i . '_tag') ?></span><h3><?= cms_text('home.work.card_' . $i . '_title') ?></h3><p><?= cms_text('home.work.card_' . $i . '_copy') ?></p></article>
            <?php endfor; ?>
          </div>
          <div class="section-action"><a class="button button-secondary" href="<?= cms_link('home.work.cta_url') ?>"><?= cms_text('home.work.cta_label') ?> <span class="button-arrow" aria-hidden="true">→</span></a></div>
        </div>
      </section>

      <section class="section" id="industries" aria-labelledby="industries-title">
        <div class="container">
          <div class="section-head center reveal"><p class="eyebrow"><?= cms_text('home.industries.eyebrow') ?></p><h2 id="industries-title"><?= cms_text('home.industries.title') ?></h2><p class="section-copy"><?= cms_text('home.industries.copy') ?></p></div>
          <div class="industry-grid">
            <?php for ($i = 1; $i <= 6; $i++): ?>
              <?php if (cms_raw('home.industries.card_' . $i . '_title') === '') { continue; } ?>
              <article class="industry-card reveal" data-delay="<?= (($i - 1) % 3) * 70 ?>"><span class="industry-symbol" aria-hidden="true"><?= cms_text('home.industries.card_' . $i . '_symbol') ?></span><h3><?= cms_text('home.industries.card_' . $i . '_title') ?></h3><p><?= cms_text('home.industries.card_' . $i . '_copy') ?></p></article>
            <?php endfor; ?>
          </div>
        </div>
      </section>

      <section class="stats" aria-label="<?= cms_text('home.stats.aria') ?>">
        <div class="container stats-grid">
          <?php for ($i = 1; $i <= 4; $i++): ?>
            <?php if (cms_raw('home.stats.stat_' . $i . '_label') === '') { continue; } ?>
            <div class="stat"><strong class="stat-number" data-count="<?= cms_text('home.stats.stat_' . $i . '_number') ?>"<?= cms_raw('home.stats.stat_' . $i . '_suffix') !== '' ? ' data-suffix="' . cms_text('home.stats.stat_' . $i . '_suffix') . '"' : '' ?>>0</strong><span class="stat-label"><?= cms_text('home.stats.stat_' . $i . '_label') ?></span></div>
          <?php endfor; ?>
        </div>
      </section>

      <?php $latestPosts = cms_setting('blog_enabled') === '1' ? cms_posts(true, 3) : []; ?>
      <?php if ($latestPosts): ?>
        <section class="section section-soft" id="insights" aria-labelledby="insights-title">
          <div class="container">
            <div class="section-head reveal"><p class="eyebrow"><?= cms_text('blog.hero.eyebrow') ?></p><h2 id="insights-title"><?= cms_text('blog.list.title') ?></h2></div>
            <div class="product-grid">
              <?php foreach ($latestPosts as $index => $post): ?>
                <?php
                  $cover = (string) $post['cover_image'] !== '' ? (string) $post['cover_image'] : cms_raw('blog.list.placeholder_image');
                  $postUrl = cms_post_url($post);
                ?>
                <article class="product-card post-card reveal" data-delay="<?= $index * 80 ?>">
                  <a class="product-image post-card-image" href="<?= e($postUrl) ?>" tabindex="-1" aria-hidden="true"><img src="<?= e($cover) ?>" alt="" width="520" height="325" loading="lazy" decoding="async" /></a>
                  <div class="product-body">
                    <?php if ((string) $post['category'] !== ''): ?><span class="case-tag"><?= e((string) $post['category']) ?></span><?php endif; ?>
                    <h3><a href="<?= e($postUrl) ?>"><?= e((string) $post['title']) ?></a></h3>
                    <p><?= e(cms_excerpt((string) ($post['excerpt'] !== '' ? $post['excerpt'] : $post['body']), 140)) ?></p>
                    <a class="button button-primary button-small post-card-button" href="<?= e($postUrl) ?>"><?= cms_text('blog.list.read_more_label') ?> <span class="button-arrow" aria-hidden="true">→</span></a>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
            <div class="section-action"><a class="button button-secondary" href="blog.php"><?= cms_text('blog.list.eyebrow') ?> <span class="button-arrow" aria-hidden="true">→</span></a></div>
          </div>
        </section>
      <?php endif; ?>

      <section class="section section-soft" id="trust" aria-labelledby="trust-title">
        <div class="container">
          <div class="section-head center reveal"><p class="eyebrow"><?= cms_text('home.trust.eyebrow') ?></p><h2 id="trust-title"><?= cms_text('home.trust.title') ?></h2><p class="section-copy"><?= cms_text('home.trust.copy') ?></p></div>
          <div class="trust-grid">
            <?php for ($i = 1; $i <= 3; $i++): ?>
              <?php if (cms_raw('home.trust.card_' . $i . '_title') === '') { continue; } ?>
              <article class="trust-card reveal" data-delay="<?= ($i - 1) * 80 ?>"><h3><?= cms_text('home.trust.card_' . $i . '_title') ?></h3><p><?= cms_text('home.trust.card_' . $i . '_copy') ?></p></article>
            <?php endfor; ?>
          </div>
        </div>
      </section>

      <section class="section" aria-labelledby="cta-title">
        <div class="container cta-panel reveal">
          <div><h2 id="cta-title"><?= cms_text('home.cta.title') ?></h2><p><?= cms_text('home.cta.copy') ?></p></div>
          <a class="button button-primary" data-magnetic href="<?= cms_link('home.cta.button_url') ?>"><?= cms_text('home.cta.button_label') ?> <span class="button-arrow" aria-hidden="true">↗</span></a>
        </div>
      </section>

      <section class="section section-soft" id="contact" aria-labelledby="contact-title">
        <div class="container contact-grid">
          <div class="reveal from-left">
            <p class="eyebrow"><?= cms_text('home.contact.eyebrow') ?></p>
            <h2 id="contact-title"><?= cms_text('home.contact.title') ?></h2>
            <p class="section-copy"><?= cms_text('home.contact.copy') ?></p>
            <?php require __DIR__ . '/backend/partials/contact_cards.php'; ?>
          </div>
          <?php $returnTo = 'index.php#contact'; require __DIR__ . '/backend/partials/inquiry_form.php'; ?>
        </div>
      </section>
<?php require __DIR__ . '/backend/partials/page_close.php'; ?>
