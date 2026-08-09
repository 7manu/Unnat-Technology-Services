<?php
/** robots.txt served from the SEO settings so it can be edited in the admin panel. */
declare(strict_types=1);

require __DIR__ . '/backend/cms.php';

header('Content-Type: text/plain; charset=utf-8');

$body = trim(cms_setting('robots_txt'));
echo $body === '' ? "User-agent: *\nAllow: /" : $body;
echo "\n\nSitemap: " . cms_site_url('sitemap.xml') . "\n";
