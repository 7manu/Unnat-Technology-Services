<?php
/**
 * Blog — article index (/blog.php) and single article (/blog.php?post=slug).
 * Posts are created and published from Admin → Blog.
 */
declare(strict_types=1);

require __DIR__ . '/backend/cms.php';
cms_apply_redirects();

if (cms_setting('blog_enabled') !== '1') {
    header('Location: index.php', true, 302);
    exit;
}

$requestedSlug = trim((string) ($_GET['post'] ?? ''));
$post = $requestedSlug !== '' ? cms_post($requestedSlug) : null;

if ($requestedSlug !== '' && $post === null) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

/* ------------------------------------------------------------------ *
 * Single article
 * ------------------------------------------------------------------ */
if ($post !== null) {
    cms_post_register_view((int) $post['id']);

    $publishedAt = (string) ($post['published_at'] ?: $post['created_at']);
    $postUrl = cms_site_url(cms_post_url($post));
    $articleSchema = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => (string) $post['title'],
        'description' => (string) ($post['meta_description'] ?: $post['excerpt']),
        'image' => (string) $post['cover_image'] !== '' ? cms_site_url((string) $post['cover_image']) : cms_site_url(cms_setting('default_og_image')),
        'datePublished' => date(DATE_ATOM, strtotime($publishedAt) ?: time()),
        'dateModified' => date(DATE_ATOM, strtotime((string) $post['updated_at']) ?: time()),
        'author' => ['@type' => 'Organization', 'name' => (string) ($post['author'] ?: cms_setting('blog_default_author'))],
        'publisher' => [
            '@type' => 'Organization',
            'name' => cms_setting('site_name'),
            'logo' => ['@type' => 'ImageObject', 'url' => cms_site_url(cms_raw('global.brand.logo'))],
        ],
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $postUrl],
        'keywords' => (string) $post['tags'],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $route = '/blog.php?post=' . $post['slug'];
    $seoOverrides = [
        'meta_title' => (string) ($post['meta_title'] ?: $post['title']),
        'meta_description' => (string) ($post['meta_description'] ?: cms_excerpt((string) $post['excerpt'] ?: (string) $post['body'])),
        'meta_keywords' => (string) ($post['meta_keywords'] ?: $post['tags']),
        'canonical_url' => (string) ($post['canonical_url'] ?: $postUrl),
        'robots_index' => (string) $post['robots_index'],
        'robots_follow' => (string) $post['robots_follow'],
        'og_type' => 'article',
        'og_image' => (string) $post['og_image'] ?: (string) $post['cover_image'],
        'schema_json' => (string) ($post['schema_json'] ?: $articleSchema),
    ];
    require __DIR__ . '/backend/partials/page_open.php';
    ?>
      <article class="post-article">
        <section class="inner-hero" aria-labelledby="post-title">
          <div class="container reveal visible">
            <p class="eyebrow"><a class="post-back" href="blog.php"><?= cms_text('blog.post.back_label') ?></a><?= (string) $post['category'] !== '' ? ' · ' . e((string) $post['category']) : '' ?></p>
            <h1 id="post-title"><?= e((string) $post['title']) ?></h1>
            <p class="post-meta">
              <?= cms_text('blog.post.author_label') ?> <strong><?= e((string) ($post['author'] ?: cms_setting('blog_default_author'))) ?></strong>
              · <?= cms_text('blog.post.published_label') ?> <time datetime="<?= e(date('Y-m-d', strtotime($publishedAt) ?: time())) ?>"><?= e(date('j F Y', strtotime($publishedAt) ?: time())) ?></time>
              <?php if ((int) $post['reading_minutes'] > 0): ?> · <?= (int) $post['reading_minutes'] ?> min read<?php endif; ?>
            </p>
          </div>
        </section>

        <?php if ((string) $post['cover_image'] !== ''): ?>
          <div class="container post-cover"><img src="<?= e((string) $post['cover_image']) ?>" alt="<?= e((string) $post['title']) ?>" loading="eager" decoding="async" /></div>
        <?php endif; ?>

        <section class="section">
          <div class="container post-layout">
            <div class="post-body reveal">
              <?php if ((string) $post['excerpt'] !== ''): ?><p class="post-lede"><?= e((string) $post['excerpt']) ?></p><?php endif; ?>
              <?= cms_sanitize_html((string) $post['body']) ?>
            </div>
            <?php if ((string) $post['tags'] !== ''): ?>
              <div class="post-tags" aria-label="Article tags">
                <?php foreach (array_filter(array_map('trim', explode(',', (string) $post['tags']))) as $tag): ?>
                  <span class="case-tag"><?= e($tag) ?></span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
            <div class="post-share">
              <h2><?= cms_text('blog.post.share_label') ?></h2>
              <div class="post-share-links">
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= rawurlencode($postUrl) ?>" target="_blank" rel="noopener">LinkedIn</a>
                <a href="https://twitter.com/intent/tweet?url=<?= rawurlencode($postUrl) ?>&amp;text=<?= rawurlencode((string) $post['title']) ?>" target="_blank" rel="noopener">X</a>
                <a href="https://wa.me/?text=<?= rawurlencode((string) $post['title'] . ' ' . $postUrl) ?>" target="_blank" rel="noopener">WhatsApp</a>
                <a href="mailto:?subject=<?= rawurlencode((string) $post['title']) ?>&amp;body=<?= rawurlencode($postUrl) ?>">Email</a>
              </div>
            </div>
          </div>
        </section>

        <section class="section section-soft" aria-labelledby="post-cta-title">
          <div class="container cta-panel reveal">
            <div><h2 id="post-cta-title"><?= cms_text('blog.post.cta_title') ?></h2><p><?= cms_text('blog.post.cta_copy') ?></p></div>
            <a class="button button-primary" href="<?= cms_link('blog.post.cta_button_url') ?>"><?= cms_text('blog.post.cta_button_label') ?> <span class="button-arrow" aria-hidden="true">↗</span></a>
          </div>
        </section>
      </article>
    <?php
    require __DIR__ . '/backend/partials/page_close.php';
    exit;
}

/* ------------------------------------------------------------------ *
 * Article index
 * ------------------------------------------------------------------ */
$perPage = max(1, (int) cms_setting('blog_posts_per_page', '9'));
$allPosts = cms_posts();
$totalPages = max(1, (int) ceil(count($allPosts) / $perPage));
$currentPage = min($totalPages, max(1, (int) ($_GET['page'] ?? 1)));
$pagePosts = array_slice($allPosts, ($currentPage - 1) * $perPage, $perPage);

$route = '/blog.php';
$seoOverrides = $currentPage > 1 ? ['canonical_url' => cms_site_url('blog.php?page=' . $currentPage)] : [];
require __DIR__ . '/backend/partials/page_open.php';
?>
      <section class="inner-hero" aria-labelledby="page-title"><div class="container reveal visible"><p class="eyebrow"><?= cms_text('blog.hero.eyebrow') ?></p><h1 id="page-title"><?= cms_text('blog.hero.title_prefix') ?> <span class="gradient-text"><?= cms_text('blog.hero.title_highlight') ?></span></h1><p><?= cms_text('blog.hero.copy') ?></p></div></section>

      <section class="section" aria-labelledby="articles-title">
        <div class="container">
          <div class="section-head reveal"><p class="eyebrow"><?= cms_text('blog.list.eyebrow') ?></p><h2 id="articles-title"><?= cms_text('blog.list.title') ?></h2></div>
          <div class="product-grid">
            <?php if ($pagePosts): ?>
              <?php foreach ($pagePosts as $index => $item): ?>
                <?php
                  $cover = (string) $item['cover_image'] !== '' ? (string) $item['cover_image'] : cms_raw('blog.list.placeholder_image');
                  $postedAt = (string) ($item['published_at'] ?: $item['created_at']);
                  $itemUrl = cms_post_url($item);
                ?>
                <article class="product-card post-card reveal" data-delay="<?= ($index % 3) * 70 ?>">
                  <a class="product-image post-card-image" href="<?= e($itemUrl) ?>" tabindex="-1" aria-hidden="true"><img src="<?= e($cover) ?>" alt="" width="520" height="325" loading="lazy" decoding="async" /></a>
                  <div class="product-body">
                    <?php if ((string) $item['category'] !== ''): ?><span class="case-tag"><?= e((string) $item['category']) ?></span><?php endif; ?>
                    <h3><a href="<?= e($itemUrl) ?>"><?= e((string) $item['title']) ?></a></h3>
                    <p class="post-card-meta">
                      <time datetime="<?= e(date('Y-m-d', strtotime($postedAt) ?: time())) ?>"><?= e(date('j M Y', strtotime($postedAt) ?: time())) ?></time>
                      <?php if ((int) $item['reading_minutes'] > 0): ?> · <?= (int) $item['reading_minutes'] ?> <?= cms_text('blog.list.reading_time_suffix') ?><?php endif; ?>
                    </p>
                    <p><?= e(cms_excerpt((string) ($item['excerpt'] !== '' ? $item['excerpt'] : $item['body']), 150)) ?></p>
                    <a class="button button-primary button-small post-card-button" href="<?= e($itemUrl) ?>"><?= cms_text('blog.list.read_more_label') ?> <span class="button-arrow" aria-hidden="true">→</span></a>
                  </div>
                </article>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="empty-state"><h3><?= cms_text('blog.list.empty_title') ?></h3><p><?= cms_text('blog.list.empty_copy') ?></p><a class="button button-primary" href="contact.html"><?= cms_text('products.empty.button_label') ?></a></div>
            <?php endif; ?>
          </div>

          <?php if ($totalPages > 1): ?>
            <nav class="pagination" aria-label="Article pages">
              <?php for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++): ?>
                <a href="blog.php?page=<?= $pageNumber ?>"<?= $pageNumber === $currentPage ? ' aria-current="page"' : '' ?>><?= $pageNumber ?></a>
              <?php endfor; ?>
            </nav>
          <?php endif; ?>
        </div>
      </section>
<?php require __DIR__ . '/backend/partials/page_close.php'; ?>
