<?php
/** XML sitemap generated from the SEO manager, custom pages and blog posts. */
declare(strict_types=1);

require __DIR__ . '/backend/cms.php';

header('Content-Type: application/xml; charset=utf-8');

$entries = [];

/* Fixed routes configured in Admin → SEO */
$records = cms_seo_records();
foreach (cms_seo_core_routes() as $coreRoute => $label) {
    $record = $records[$coreRoute] ?? [];
    if (isset($record['sitemap_include']) && (int) $record['sitemap_include'] === 0) {
        continue;
    }
    if ($coreRoute === '/blog.php' && cms_setting('blog_enabled') !== '1') {
        continue;
    }
    $entries[] = [
        'loc' => cms_site_url(ltrim($coreRoute, '/')),
        'changefreq' => (string) ($record['sitemap_changefreq'] ?? cms_setting('sitemap_default_changefreq')),
        'priority' => (string) ($record['sitemap_priority'] ?? cms_setting('sitemap_default_priority')),
        'lastmod' => isset($record['updated_at']) ? date('Y-m-d', strtotime((string) $record['updated_at']) ?: time()) : '',
    ];
}

/* Any additional routes the administrator added to the SEO manager */
foreach ($records as $route => $record) {
    if (isset(cms_seo_core_routes()[$route]) || (int) ($record['sitemap_include'] ?? 1) === 0) {
        continue;
    }
    if (strpos($route, '?') !== false) {
        continue;
    }
    $entries[] = [
        'loc' => cms_site_url(ltrim((string) $route, '/')),
        'changefreq' => (string) $record['sitemap_changefreq'],
        'priority' => (string) $record['sitemap_priority'],
        'lastmod' => date('Y-m-d', strtotime((string) $record['updated_at']) ?: time()),
    ];
}

/* Custom pages */
if (cms_setting('sitemap_include_pages') === '1') {
    foreach (cms_pages() as $page) {
        if ((int) $page['sitemap_include'] === 0 || (string) $page['robots_index'] === 'noindex') {
            continue;
        }
        $entries[] = [
            'loc' => cms_site_url(cms_page_url($page)),
            'changefreq' => (string) $page['sitemap_changefreq'],
            'priority' => (string) $page['sitemap_priority'],
            'lastmod' => date('Y-m-d', strtotime((string) $page['updated_at']) ?: time()),
        ];
    }
}

/* Blog posts */
if (cms_setting('sitemap_include_posts') === '1' && cms_setting('blog_enabled') === '1') {
    foreach (cms_posts() as $post) {
        if ((int) $post['sitemap_include'] === 0 || (string) $post['robots_index'] === 'noindex') {
            continue;
        }
        $entries[] = [
            'loc' => cms_site_url(cms_post_url($post)),
            'changefreq' => (string) $post['sitemap_changefreq'],
            'priority' => (string) $post['sitemap_priority'],
            'lastmod' => date('Y-m-d', strtotime((string) $post['updated_at']) ?: time()),
        ];
    }
}

/* Deduplicate by location, keeping the first (highest priority) definition. */
$seen = [];
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($entries as $entry) {
    if ($entry['loc'] === '' || isset($seen[$entry['loc']])) {
        continue;
    }
    $seen[$entry['loc']] = true;
    echo '  <url><loc>' . e($entry['loc']) . '</loc>';
    if ($entry['lastmod'] !== '') {
        echo '<lastmod>' . e($entry['lastmod']) . '</lastmod>';
    }
    echo '<changefreq>' . e($entry['changefreq'] !== '' ? $entry['changefreq'] : 'monthly') . '</changefreq>';
    echo '<priority>' . e($entry['priority'] !== '' ? $entry['priority'] : '0.6') . '</priority></url>' . "\n";
}
echo '</urlset>' . "\n";
