<?php
/**
 * Opening shell for every public page: <head>, SEO block, header and navigation.
 *
 * Expected variables (all optional except $route):
 *   string $route          Canonical route used to look up the SEO record, e.g. '/contact.html'
 *   array  $seoOverrides   Per-record SEO values (blog posts, custom pages)
 *   string $bodyClass      Extra class names for <body>
 *   bool   $headerScrolled Render the header in its compact state (inner pages)
 */
declare(strict_types=1);

require_once __DIR__ . '/../cms.php';

$route = $route ?? cms_current_route();
$seoOverrides = $seoOverrides ?? [];
$bodyClass = $bodyClass ?? '';
$headerScrolled = $headerScrolled ?? true;
$navItems = cms_nav('primary');
$currentPath = cms_current_route();

/** Marks the navigation entry that points at the page being rendered. */
$navIsCurrent = static function (string $url) use ($currentPath): bool {
    $target = '/' . ltrim((string) parse_url($url, PHP_URL_PATH), '/');
    if ($target === '/index.php' || $target === '/index.html') {
        $target = '/';
    }

    return $target === $currentPath && strpos($url, '#') === false;
};
?>
<!doctype html>
<html lang="<?= cms_text('global.brand.language', 'en') ?>">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="theme-color" content="<?= cms_text('global.brand.theme_color') ?>" />
    <?= cms_render_head($route, $seoOverrides) ?>

    <link rel="icon" href="<?= cms_text('global.brand.logo') ?>" type="image/webp" />
    <link rel="apple-touch-icon" href="<?= cms_text('global.brand.logo_apple_touch') ?>" />
    <link rel="manifest" href="manifest.json" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Manrope:wght@500;600;700;800&amp;display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="assets/theme/css/uts-modern.css" />
    <script>document.documentElement.classList.add("js");</script>
    <script src="assets/theme/js/uts-modern.js" defer></script>
    <script src="assets/theme/js/uts-assistant.js" defer></script>
    <script src="sw-connect.js" defer></script>
  </head>
  <body<?= $bodyClass !== '' ? ' class="' . e($bodyClass) . '"' : '' ?>>
    <?php if (cms_setting('custom_body_start_html') !== ''): ?><?= cms_setting('custom_body_start_html') ?><?php endif; ?>
    <a class="skip-link" href="#main-content"><?= cms_text('header.skip_link.label') ?></a>

    <header class="site-header<?= $headerScrolled ? ' scrolled' : '' ?>" aria-label="<?= cms_text('header.nav.header_aria') ?>">
      <div class="container nav-wrap">
        <a class="brand" href="index.php" aria-label="<?= cms_text('header.brand.aria_label') ?>">
          <img src="<?= cms_text('global.brand.logo') ?>" width="46" height="46" alt="" fetchpriority="high" />
          <span><?= cms_text('header.brand.text') ?></span>
        </a>
        <button class="nav-toggle" type="button" aria-label="<?= cms_text('header.nav.toggle_label') ?>" aria-controls="primary-navigation" aria-expanded="false"><span></span></button>
        <nav class="primary-nav" id="primary-navigation" aria-label="<?= cms_text('header.nav.aria_label') ?>">
          <?php foreach ($navItems as $item): ?>
            <?php
              $url = cms_safe_link((string) $item['url']);
              $isButton = (int) $item['is_button'] === 1;
              $target = (string) ($item['link_target'] ?? '_self');
              $rel = (string) ($item['rel'] ?? '');
              if ($target === '_blank' && $rel === '') {
                  $rel = 'noopener';
              }
              $classes = trim(($isButton ? 'button button-primary button-small' : '') . cms_nav_visibility_class($item));
            ?>
            <a<?= $classes !== '' ? ' class="' . e($classes) . '"' : '' ?> href="<?= e($url) ?>"<?= $target !== '_self' ? ' target="' . e($target) . '"' : '' ?><?= $rel !== '' ? ' rel="' . e($rel) . '"' : '' ?><?= $navIsCurrent((string) $item['url']) ? ' aria-current="page"' : '' ?>><?= e((string) $item['label']) ?><?= $isButton ? ' <span class="button-arrow" aria-hidden="true">↗</span>' : '' ?></a>
          <?php endforeach; ?>
        </nav>
      </div>
    </header>

    <main id="main-content">
