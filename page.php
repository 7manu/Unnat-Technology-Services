<?php
/**
 * Custom pages created from Admin → Pages (/page.php?slug=my-page).
 *
 * Templates:
 *   standard  — cover image above the content
 *   wide      — full-width content, no cover
 *   landing   — cover hero plus a closing call to action
 */
declare(strict_types=1);

require __DIR__ . '/backend/cms.php';
require __DIR__ . '/backend/cms_blocks.php';
cms_apply_redirects();

$slug = trim((string) ($_GET['slug'] ?? ''));
$page = $slug !== '' ? cms_page($slug) : null;

if ($page === null) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$pageUrl = cms_site_url(cms_page_url($page));
$template = (string) ($page['template'] ?: 'standard');
$sections = cms_sections_normalise((string) ($page['sections'] ?? ''));
$faqSchema = cms_sections_faq_schema($sections);

$route = '/page.php?slug=' . $page['slug'];
$seoOverrides = [
    'meta_title' => (string) ($page['meta_title'] ?: $page['title']),
    'meta_description' => (string) ($page['meta_description'] ?: cms_excerpt((string) $page['description'])),
    'meta_keywords' => (string) $page['meta_keywords'],
    'canonical_url' => (string) ($page['canonical_url'] ?: $pageUrl),
    'robots_index' => (string) $page['robots_index'],
    'robots_follow' => (string) $page['robots_follow'],
    'og_type' => 'article',
    'og_image' => (string) $page['og_image'] ?: (string) $page['cover_image'],
    'schema_json' => (string) ($page['schema_json'] ?: $faqSchema),
];
require __DIR__ . '/backend/partials/page_open.php';
?>
      <?php if ($template !== 'minimal'): ?>
        <section class="inner-hero" aria-labelledby="page-title">
          <div class="container reveal visible">
            <?php if ((string) $page['subtitle'] !== ''): ?><p class="eyebrow"><?= e((string) $page['subtitle']) ?></p><?php endif; ?>
            <h1 id="page-title"><?= e((string) $page['title']) ?></h1>
            <?php if ((string) $page['description'] !== ''): ?><p><?= e((string) $page['description']) ?></p><?php endif; ?>
          </div>
        </section>
      <?php else: ?>
        <h1 class="visually-hidden-heading" id="page-title"><?= e((string) $page['title']) ?></h1>
      <?php endif; ?>

      <?php if ((string) $page['cover_image'] !== '' && $template !== 'minimal'): ?>
        <?php /* Wide pages run the cover edge to edge; the others keep it inside the container. */ ?>
        <div class="<?= $template === 'wide' ? 'post-cover post-cover-wide' : 'container post-cover' ?>"><img src="<?= e((string) $page['cover_image']) ?>" alt="<?= e((string) $page['title']) ?>" loading="eager" decoding="async" /></div>
      <?php endif; ?>

      <?php if (trim((string) $page['body']) !== ''): ?>
        <section class="section">
          <div class="container <?= in_array($template, ['wide', 'minimal'], true) ? '' : 'post-layout' ?>">
            <div class="post-body reveal"><?= cms_sanitize_html((string) $page['body']) ?></div>
          </div>
        </section>
      <?php endif; ?>

      <?php /* Section blocks built in the page designer. */ ?>
      <?= cms_render_sections($sections) ?>

      <?php if ($template === 'landing'): ?>
        <section class="section section-soft" aria-labelledby="page-cta-title">
          <div class="container cta-panel reveal">
            <div><h2 id="page-cta-title"><?= cms_text('home.cta.title') ?></h2><p><?= cms_text('home.cta.copy') ?></p></div>
            <a class="button button-primary" href="contact.html"><?= cms_text('home.cta.button_label') ?> <span class="button-arrow" aria-hidden="true">↗</span></a>
          </div>
        </section>
      <?php endif; ?>
<?php require __DIR__ . '/backend/partials/page_close.php'; ?>
