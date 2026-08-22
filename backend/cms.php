<?php
/**
 * Unnat Technology Services — content, SEO and publishing core.
 *
 * Responsibilities:
 *   • create and upgrade every CMS table on demand (cms_install)
 *   • seed the content registry from backend/cms_defaults.php
 *   • read helpers used by the public pages (cms_text, cms_setting, cms_nav …)
 *   • SEO head rendering, sitemap/robots data and redirect handling
 *
 * Every read helper falls back to the static defaults when the database is
 * unreachable, so the public website never breaks because of a CMS problem.
 */
declare(strict_types=1);

/**
 * Bump this whenever cms_schema_statements(), cms_schema_upgrades() or
 * cms_defaults.php changes. The admin panel compares it against the value
 * stored in cms_settings and only runs the installer when they differ, so a
 * normal page load costs nothing.
 */
const CMS_SCHEMA_VERSION = '2026.08.10';

/* =====================================================================
 * Connection
 * ================================================================== */

function cms_db(): ?mysqli
{
    static $connection = null;
    static $attempted = false;

    if ($attempted) {
        return $connection;
    }
    $attempted = true;

    try {
        require_once __DIR__ . '/_conn.php';
        $connection = uts_db_connect();
    } catch (Throwable $exception) {
        error_log('UTS CMS database unavailable: ' . $exception->getMessage());
        $connection = null;
    }

    return $connection;
}

function cms_db_available(): bool
{
    return cms_db() instanceof mysqli;
}

/* =====================================================================
 * Small helpers
 * ================================================================== */

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

function cms_slugify(string $value, string $fallback = 'item'): string
{
    $slug = strtolower(trim($value));
    $slug = preg_replace('/[^a-z0-9]+/u', '-', $slug) ?? '';
    $slug = trim($slug, '-');

    return $slug !== '' ? substr($slug, 0, 120) : $fallback . '-' . bin2hex(random_bytes(3));
}

/** Only http(s) links are allowed to reach the public markup. */
function cms_safe_external_url(?string $url): string
{
    $url = trim($url ?? '');
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return '#';
    }
    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

    return in_array($scheme, ['http', 'https'], true) ? $url : '#';
}

/**
 * Accepts internal paths, anchors, query strings, mailto/tel and absolute
 * http(s) links. Anything carrying another scheme (javascript:, data:) or
 * pointing off-site protocol-relatively is replaced by the fallback.
 */
function cms_safe_link(?string $url, string $fallback = '#'): string
{
    $url = trim($url ?? '');
    if ($url === '') {
        return $fallback;
    }

    /* Absolute links we allow. */
    if (preg_match('#^(https?://|mailto:|tel:)#i', $url) === 1) {
        return $url;
    }

    /* //evil.com would silently leave the site. */
    if (str_starts_with($url, '//')) {
        return $fallback;
    }

    /* Root-relative paths, bare fragments and bare query strings. */
    if (preg_match('#^[/\#?]#', $url) === 1) {
        return $url;
    }

    /* Any other scheme is rejected before the relative branch below. */
    if (preg_match('#^[a-z][a-z0-9+.\-]*:#i', $url) === 1) {
        return $fallback;
    }

    /*
     * Everything left is a same-site relative link, with an optional query
     * string and fragment: products.php, page.php?slug=services,
     * index.php#services, blog.php?post=my-article#top
     */
    return $url;
}

function cms_excerpt(string $html, int $length = 160): string
{
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($html)) ?? '');
    if (mb_strlen($text) <= $length) {
        return $text;
    }

    return rtrim(mb_substr($text, 0, $length - 1)) . '…';
}

function cms_current_route(): string
{
    $path = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
    $path = '/' . ltrim($path, '/');
    if ($path === '/index.php' || $path === '/index.html') {
        $path = '/';
    }

    return $path;
}

/* =====================================================================
 * Content registry
 * ================================================================== */

function cms_content_defaults(): array
{
    static $defaults = null;
    if ($defaults === null) {
        $defaults = require __DIR__ . '/cms_defaults.php';
    }

    return $defaults;
}

function cms_content_default_map(): array
{
    static $map = null;
    if ($map === null) {
        $map = [];
        foreach (cms_content_defaults() as $item) {
            $map[$item['key']] = (string) $item['value'];
        }
    }

    return $map;
}

/** Loads every stored content row once per request. */
function cms_content_all(): array
{
    static $rows = null;
    if ($rows !== null) {
        return $rows;
    }

    $rows = [];
    $conn = cms_db();
    if ($conn instanceof mysqli) {
        try {
            $result = $conn->query('SELECT `content_key`, `content_value` FROM `cms_content`');
            while ($row = $result->fetch_assoc()) {
                $rows[(string) $row['content_key']] = (string) $row['content_value'];
            }
        } catch (Throwable $exception) {
            error_log('UTS CMS content read failed: ' . $exception->getMessage());
            $rows = [];
        }
    }

    return $rows;
}

/** Raw (unescaped) content value for a key. */
function cms_raw(string $key, string $fallback = ''): string
{
    $stored = cms_content_all();
    if (array_key_exists($key, $stored) && $stored[$key] !== '') {
        return $stored[$key];
    }

    $defaults = cms_content_default_map();
    if (array_key_exists($key, $defaults)) {
        return $defaults[$key];
    }

    return $fallback;
}

/** HTML-escaped content value — the helper used throughout the templates. */
function cms_text(string $key, string $fallback = ''): string
{
    return e(cms_raw($key, $fallback));
}

/** Content value that is allowed to contain markup (rich text fields). */
function cms_html(string $key, string $fallback = ''): string
{
    return cms_sanitize_html(cms_raw($key, $fallback));
}

/** Escaped link value. */
function cms_link(string $key, string $fallback = '#'): string
{
    return e(cms_safe_link(cms_raw($key, $fallback), $fallback));
}

/** Full content rows (value + labels) for the admin editor. */
function cms_content_rows(): array
{
    $conn = cms_db();
    if (!$conn instanceof mysqli) {
        return array_map(static function (array $item): array {
            return [
                'id' => 0,
                'content_key' => $item['key'],
                'page_label' => $item['page'],
                'section_label' => $item['section'],
                'field_label' => $item['label'],
                'field_type' => $item['type'],
                'content_value' => $item['value'],
                'is_custom' => 0,
                'updated_at' => null,
            ];
        }, cms_content_defaults());
    }

    $rows = [];
    try {
        $result = $conn->query('SELECT * FROM `cms_content` ORDER BY `page_label`, `section_label`, `id`');
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    } catch (Throwable $exception) {
        error_log('UTS CMS content rows failed: ' . $exception->getMessage());
    }

    return $rows;
}

/* =====================================================================
 * Settings
 * ================================================================== */

function cms_setting_defaults(): array
{
    return [
        /* Identity */
        'site_url' => 'https://unnattechnologyservices.com',
        'site_name' => 'Unnat Technology Services',
        'site_locale' => 'en_IN',
        'title_separator' => '|',
        'title_suffix' => 'Unnat Technology Services',

        /* Default SEO */
        'default_meta_title' => 'Unnat Technology Services | Web, Software & Automation Solutions',
        'default_meta_description' => 'Unnat Technology Services builds scalable web platforms, business software, automation systems and digital products for future-ready organizations.',
        'default_meta_keywords' => 'web development Moradabad, software company Moradabad, business software India, automation services, digital product engineering, AI solutions India',
        'default_og_image' => 'assets/images/uts-logo-removebg-removebg-preview-512x512.webp',
        'default_robots_index' => 'index',
        'default_robots_follow' => 'follow',
        'default_robots_extra' => 'max-image-preview:large, max-snippet:-1, max-video-preview:-1',
        'twitter_card' => 'summary_large_image',
        'twitter_site' => '',
        'twitter_creator' => '',
        'facebook_app_id' => '',

        /* Verification and analytics */
        'google_site_verification' => '',
        'bing_site_verification' => '',
        'yandex_verification' => '',
        'pinterest_verification' => '',
        'facebook_domain_verification' => '',
        'google_analytics_id' => '',
        'google_tag_manager_id' => '',
        'facebook_pixel_id' => '',
        'microsoft_clarity_id' => '',
        'hotjar_id' => '',
        'custom_head_html' => '',
        'custom_body_start_html' => '',
        'custom_body_end_html' => '',

        /* Structured data */
        'schema_enabled' => '1',
        'schema_type' => 'ProfessionalService',
        'schema_price_range' => '₹₹',
        'schema_founding_date' => '',
        'schema_opening_hours' => 'Mo-Sa 10:00-19:00',
        'schema_geo_lat' => '',
        'schema_geo_lng' => '',
        'schema_area_served' => 'India',

        /* Sitemap and robots */
        'sitemap_include_pages' => '1',
        'sitemap_include_posts' => '1',
        'sitemap_include_products' => '1',
        'sitemap_default_changefreq' => 'monthly',
        'sitemap_default_priority' => '0.6',
        'robots_txt' => "User-agent: *\nAllow: /\nDisallow: /backend/\nDisallow: /admin.php\nDisallow: /login.php\nDisallow: /asset_manager/\nDisallow: /clients/",

        /* Blog */
        'blog_enabled' => '1',
        'blog_posts_per_page' => '9',
        'blog_default_author' => 'Unnat Technology Services',
        'blog_base_path' => 'blog.php',

        /* Pages */
        'page_base_path' => 'page.php',
        'page_default_template' => 'standard',
    ];
}

function cms_settings_all(): array
{
    static $settings = null;
    if ($settings !== null) {
        return $settings;
    }

    $settings = cms_setting_defaults();
    $conn = cms_db();
    if ($conn instanceof mysqli) {
        try {
            $result = $conn->query('SELECT `setting_key`, `setting_value` FROM `cms_settings`');
            while ($row = $result->fetch_assoc()) {
                $settings[(string) $row['setting_key']] = (string) $row['setting_value'];
            }
        } catch (Throwable $exception) {
            error_log('UTS CMS settings read failed: ' . $exception->getMessage());
        }
    }

    return $settings;
}

function cms_setting(string $key, string $fallback = ''): string
{
    $settings = cms_settings_all();

    return array_key_exists($key, $settings) ? (string) $settings[$key] : $fallback;
}

function cms_site_url(string $path = ''): string
{
    $base = rtrim(cms_setting('site_url'), '/');
    $path = ltrim($path, '/');

    return $path === '' ? $base . '/' : $base . '/' . $path;
}

/* =====================================================================
 * Navigation menus
 * ================================================================== */

/**
 * `visibility` decides which screen sizes a header link appears on:
 *   all | desktop (hidden in the mobile drawer) | mobile (hidden on wide screens)
 */
function cms_nav_defaults(): array
{
    return [
        ['menu' => 'primary', 'label' => 'Services', 'url' => 'index.php#services', 'sort_order' => 10, 'link_target' => '_self', 'is_button' => 0, 'rel' => '', 'visibility' => 'all', 'is_active' => 1],
        ['menu' => 'primary', 'label' => 'Expertise', 'url' => 'index.php#expertise', 'sort_order' => 20, 'link_target' => '_self', 'is_button' => 0, 'rel' => '', 'visibility' => 'all', 'is_active' => 1],
        ['menu' => 'primary', 'label' => 'Work', 'url' => 'index.php#work', 'sort_order' => 30, 'link_target' => '_self', 'is_button' => 0, 'rel' => '', 'visibility' => 'all', 'is_active' => 1],
        ['menu' => 'primary', 'label' => 'Process', 'url' => 'index.php#process', 'sort_order' => 40, 'link_target' => '_self', 'is_button' => 0, 'rel' => '', 'visibility' => 'all', 'is_active' => 1],
        ['menu' => 'primary', 'label' => 'Products', 'url' => 'products.php', 'sort_order' => 50, 'link_target' => '_self', 'is_button' => 0, 'rel' => '', 'visibility' => 'all', 'is_active' => 1],
        ['menu' => 'primary', 'label' => 'Blog', 'url' => 'blog.php', 'sort_order' => 60, 'link_target' => '_self', 'is_button' => 0, 'rel' => '', 'visibility' => 'all', 'is_active' => 1],
        ['menu' => 'primary', 'label' => 'Start a project', 'url' => 'contact.html', 'sort_order' => 70, 'link_target' => '_self', 'is_button' => 1, 'rel' => '', 'visibility' => 'all', 'is_active' => 1],

        ['menu' => 'footer_explore', 'label' => 'Services', 'url' => 'index.php#services', 'sort_order' => 10, 'link_target' => '_self', 'is_button' => 0, 'rel' => '', 'visibility' => 'all', 'is_active' => 1],
        ['menu' => 'footer_explore', 'label' => 'Expertise', 'url' => 'index.php#expertise', 'sort_order' => 20, 'link_target' => '_self', 'is_button' => 0, 'rel' => '', 'visibility' => 'all', 'is_active' => 1],
        ['menu' => 'footer_explore', 'label' => 'Work', 'url' => 'index.php#work', 'sort_order' => 30, 'link_target' => '_self', 'is_button' => 0, 'rel' => '', 'visibility' => 'all', 'is_active' => 1],
        ['menu' => 'footer_explore', 'label' => 'Process', 'url' => 'index.php#process', 'sort_order' => 40, 'link_target' => '_self', 'is_button' => 0, 'rel' => '', 'visibility' => 'all', 'is_active' => 1],
        ['menu' => 'footer_explore', 'label' => 'Blog', 'url' => 'blog.php', 'sort_order' => 50, 'link_target' => '_self', 'is_button' => 0, 'rel' => '', 'visibility' => 'all', 'is_active' => 1],

        ['menu' => 'footer_platforms', 'label' => 'Our products', 'url' => 'products.php', 'sort_order' => 10, 'link_target' => '_self', 'is_button' => 0, 'rel' => '', 'visibility' => 'all', 'is_active' => 1],
        ['menu' => 'footer_platforms', 'label' => 'UTS Learning', 'url' => 'https://learning.unnattechnologyservices.com/', 'sort_order' => 20, 'link_target' => '_blank', 'is_button' => 0, 'rel' => 'noopener', 'visibility' => 'all', 'is_active' => 1],
        ['menu' => 'footer_platforms', 'label' => 'Client portal', 'url' => 'https://clients.unnattechnologyservices.com/', 'sort_order' => 30, 'link_target' => '_blank', 'is_button' => 0, 'rel' => 'noopener', 'visibility' => 'all', 'is_active' => 1],

        ['menu' => 'footer_contact', 'label' => '+91 96908 05228', 'url' => 'tel:+919690805228', 'sort_order' => 10, 'link_target' => '_self', 'is_button' => 0, 'rel' => '', 'visibility' => 'all', 'is_active' => 1],
        ['menu' => 'footer_contact', 'label' => 'Email our team', 'url' => 'mailto:unnattechnologyservices@gmail.com', 'sort_order' => 20, 'link_target' => '_self', 'is_button' => 0, 'rel' => '', 'visibility' => 'all', 'is_active' => 1],
        ['menu' => 'footer_contact', 'label' => 'Inquiry page', 'url' => 'contact.html', 'sort_order' => 30, 'link_target' => '_self', 'is_button' => 0, 'rel' => '', 'visibility' => 'all', 'is_active' => 1],
    ];
}

/** Links that must exist in every install, even one seeded before they were added. */
function cms_nav_required(): array
{
    return array_values(array_filter(cms_nav_defaults(), static function (array $item): bool {
        return $item['url'] === 'blog.php';
    }));
}

/** Extra class names that hide a header link on one screen size. */
function cms_nav_visibility_class(array $item): string
{
    return match ((string) ($item['visibility'] ?? 'all')) {
        'desktop' => ' nav-desktop-only',
        'mobile' => ' nav-mobile-only',
        default => '',
    };
}

function cms_nav(string $menu, bool $activeOnly = true): array
{
    static $cache = [];
    $cacheKey = $menu . ($activeOnly ? ':active' : ':all');
    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }

    $items = [];
    $conn = cms_db();
    if ($conn instanceof mysqli) {
        try {
            $sql = 'SELECT * FROM `cms_nav` WHERE `menu` = ?' . ($activeOnly ? ' AND `is_active` = 1' : '') . ' ORDER BY `sort_order`, `id`';
            $statement = $conn->prepare($sql);
            $statement->bind_param('s', $menu);
            $statement->execute();
            $result = $statement->get_result();
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        } catch (Throwable $exception) {
            error_log('UTS CMS navigation read failed: ' . $exception->getMessage());
        }
    }

    if ($items === []) {
        foreach (cms_nav_defaults() as $item) {
            if ($item['menu'] === $menu) {
                $items[] = $item + ['id' => 0];
            }
        }
    }

    $cache[$cacheKey] = $items;

    return $items;
}

/* =====================================================================
 * Custom pages
 * ================================================================== */

function cms_pages(bool $publishedOnly = true): array
{
    $conn = cms_db();
    if (!$conn instanceof mysqli) {
        return [];
    }

    $pages = [];
    try {
        $sql = 'SELECT * FROM `cms_pages`' . ($publishedOnly ? " WHERE `status` = 'published'" : '') . ' ORDER BY `sort_order`, `id` DESC';
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $pages[] = $row;
        }
    } catch (Throwable $exception) {
        error_log('UTS CMS pages read failed: ' . $exception->getMessage());
    }

    return $pages;
}

function cms_page(string $slug, bool $publishedOnly = true): ?array
{
    $conn = cms_db();
    if (!$conn instanceof mysqli) {
        return null;
    }

    try {
        $sql = 'SELECT * FROM `cms_pages` WHERE `slug` = ?' . ($publishedOnly ? " AND `status` = 'published'" : '') . ' LIMIT 1';
        $statement = $conn->prepare($sql);
        $statement->bind_param('s', $slug);
        $statement->execute();
        $row = $statement->get_result()->fetch_assoc();

        return $row ?: null;
    } catch (Throwable $exception) {
        error_log('UTS CMS page read failed: ' . $exception->getMessage());

        return null;
    }
}

function cms_page_url(array $page): string
{
    return cms_setting('page_base_path', 'page.php') . '?slug=' . rawurlencode((string) $page['slug']);
}

/* =====================================================================
 * Blog posts
 * ================================================================== */

function cms_posts(bool $publishedOnly = true, int $limit = 0): array
{
    $conn = cms_db();
    if (!$conn instanceof mysqli) {
        return [];
    }

    $posts = [];
    try {
        $sql = 'SELECT * FROM `cms_posts`' . ($publishedOnly ? " WHERE `status` = 'published'" : '')
            . ' ORDER BY COALESCE(`published_at`, `created_at`) DESC, `id` DESC';
        if ($limit > 0) {
            $sql .= ' LIMIT ' . $limit;
        }
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }
    } catch (Throwable $exception) {
        error_log('UTS CMS posts read failed: ' . $exception->getMessage());
    }

    return $posts;
}

function cms_post(string $slug, bool $publishedOnly = true): ?array
{
    $conn = cms_db();
    if (!$conn instanceof mysqli) {
        return null;
    }

    try {
        $sql = 'SELECT * FROM `cms_posts` WHERE `slug` = ?' . ($publishedOnly ? " AND `status` = 'published'" : '') . ' LIMIT 1';
        $statement = $conn->prepare($sql);
        $statement->bind_param('s', $slug);
        $statement->execute();
        $row = $statement->get_result()->fetch_assoc();

        return $row ?: null;
    } catch (Throwable $exception) {
        error_log('UTS CMS post read failed: ' . $exception->getMessage());

        return null;
    }
}

function cms_post_url(array $post): string
{
    return cms_setting('blog_base_path', 'blog.php') . '?post=' . rawurlencode((string) $post['slug']);
}

function cms_post_register_view(int $postId): void
{
    $conn = cms_db();
    if (!$conn instanceof mysqli) {
        return;
    }

    try {
        $statement = $conn->prepare('UPDATE `cms_posts` SET `views` = `views` + 1 WHERE `id` = ?');
        $statement->bind_param('i', $postId);
        $statement->execute();
    } catch (Throwable $exception) {
        error_log('UTS CMS view counter failed: ' . $exception->getMessage());
    }
}

/* =====================================================================
 * SEO
 * ================================================================== */

function cms_seo_defaults(): array
{
    return [
        'route' => '',
        'route_label' => '',
        'meta_title' => '',
        'meta_description' => '',
        'meta_keywords' => '',
        'canonical_url' => '',
        'robots_index' => cms_setting('default_robots_index', 'index'),
        'robots_follow' => cms_setting('default_robots_follow', 'follow'),
        'robots_extra' => cms_setting('default_robots_extra'),
        'og_type' => 'website',
        'og_title' => '',
        'og_description' => '',
        'og_image' => '',
        'twitter_card' => cms_setting('twitter_card', 'summary_large_image'),
        'twitter_title' => '',
        'twitter_description' => '',
        'twitter_image' => '',
        'schema_json' => '',
        'hreflang_json' => '',
        'sitemap_include' => 1,
        'sitemap_priority' => cms_setting('sitemap_default_priority', '0.6'),
        'sitemap_changefreq' => cms_setting('sitemap_default_changefreq', 'monthly'),
        'head_extra' => '',
    ];
}

function cms_seo_records(): array
{
    static $records = null;
    if ($records !== null) {
        return $records;
    }

    $records = [];
    $conn = cms_db();
    if ($conn instanceof mysqli) {
        try {
            $result = $conn->query('SELECT * FROM `cms_seo` ORDER BY `route`');
            while ($row = $result->fetch_assoc()) {
                $records[(string) $row['route']] = $row;
            }
        } catch (Throwable $exception) {
            error_log('UTS CMS SEO read failed: ' . $exception->getMessage());
        }
    }

    return $records;
}

function cms_seo(string $route): array
{
    $records = cms_seo_records();
    $record = $records[$route] ?? [];

    $seo = cms_seo_defaults();
    foreach ($record as $field => $value) {
        if ($value !== null && $value !== '') {
            $seo[$field] = $value;
        }
    }
    $seo['route'] = $route;

    return $seo;
}

/** Routes that always exist and can therefore always be tuned in the SEO manager. */
function cms_seo_core_routes(): array
{
    return [
        '/' => 'Home page',
        '/contact.html' => 'Contact page',
        '/products.php' => 'Products page',
        '/blog.php' => 'Blog index',
    ];
}

/**
 * Builds the complete SEO head block for a route.
 *
 * $overrides lets page templates supply values that come from a record
 * (a blog post or a custom page) instead of the cms_seo table.
 */
function cms_render_head(string $route, array $overrides = []): string
{
    $seo = array_merge(cms_seo($route), array_filter($overrides, static fn($value): bool => $value !== null && $value !== ''));

    $siteName = cms_setting('site_name');
    $separator = cms_setting('title_separator', '|');
    $suffix = cms_setting('title_suffix');

    $title = (string) ($seo['meta_title'] !== '' ? $seo['meta_title'] : cms_setting('default_meta_title'));
    if ($suffix !== '' && stripos($title, $suffix) === false) {
        $title .= ' ' . $separator . ' ' . $suffix;
    }

    $description = (string) ($seo['meta_description'] !== '' ? $seo['meta_description'] : cms_setting('default_meta_description'));
    $keywords = (string) ($seo['meta_keywords'] !== '' ? $seo['meta_keywords'] : cms_setting('default_meta_keywords'));
    $canonical = (string) ($seo['canonical_url'] !== '' ? $seo['canonical_url'] : cms_site_url(ltrim($route, '/')));

    $robotsParts = [(string) $seo['robots_index'], (string) $seo['robots_follow']];
    if ((string) $seo['robots_extra'] !== '') {
        $robotsParts[] = (string) $seo['robots_extra'];
    }
    $robots = implode(', ', array_filter($robotsParts));

    $ogImage = (string) ($seo['og_image'] !== '' ? $seo['og_image'] : cms_setting('default_og_image'));
    if ($ogImage !== '' && !preg_match('#^https?://#i', $ogImage)) {
        $ogImage = cms_site_url($ogImage);
    }
    $twitterImage = (string) ($seo['twitter_image'] !== '' ? $seo['twitter_image'] : $ogImage);
    if ($twitterImage !== '' && !preg_match('#^https?://#i', $twitterImage)) {
        $twitterImage = cms_site_url($twitterImage);
    }

    $out = [];
    $out[] = '<title>' . e($title) . '</title>';
    $out[] = '<meta name="description" content="' . e($description) . '" />';
    if ($keywords !== '') {
        $out[] = '<meta name="keywords" content="' . e($keywords) . '" />';
    }
    $out[] = '<meta name="robots" content="' . e($robots) . '" />';
    $out[] = '<meta name="googlebot" content="' . e($robots) . '" />';
    $out[] = '<link rel="canonical" href="' . e($canonical) . '" />';
    $out[] = '<meta name="author" content="' . e($siteName) . '" />';
    $out[] = '<meta name="publisher" content="' . e($siteName) . '" />';
    $out[] = '<meta name="geo.region" content="IN-' . e(strtoupper(substr(cms_raw('global.contact.region', 'Uttar Pradesh'), 0, 2))) . '" />';
    $out[] = '<meta name="geo.placename" content="' . cms_text('global.contact.city') . '" />';

    /* Open Graph */
    $out[] = '<meta property="og:type" content="' . e((string) $seo['og_type']) . '" />';
    $out[] = '<meta property="og:site_name" content="' . e($siteName) . '" />';
    $out[] = '<meta property="og:locale" content="' . e(cms_setting('site_locale', 'en_IN')) . '" />';
    $out[] = '<meta property="og:title" content="' . e((string) ($seo['og_title'] !== '' ? $seo['og_title'] : $title)) . '" />';
    $out[] = '<meta property="og:description" content="' . e((string) ($seo['og_description'] !== '' ? $seo['og_description'] : $description)) . '" />';
    $out[] = '<meta property="og:url" content="' . e($canonical) . '" />';
    if ($ogImage !== '') {
        $out[] = '<meta property="og:image" content="' . e($ogImage) . '" />';
        $out[] = '<meta property="og:image:alt" content="' . e($title) . '" />';
    }
    if (cms_setting('facebook_app_id') !== '') {
        $out[] = '<meta property="fb:app_id" content="' . e(cms_setting('facebook_app_id')) . '" />';
    }

    /* Twitter / X */
    $out[] = '<meta name="twitter:card" content="' . e((string) $seo['twitter_card']) . '" />';
    $out[] = '<meta name="twitter:title" content="' . e((string) ($seo['twitter_title'] !== '' ? $seo['twitter_title'] : $title)) . '" />';
    $out[] = '<meta name="twitter:description" content="' . e((string) ($seo['twitter_description'] !== '' ? $seo['twitter_description'] : $description)) . '" />';
    if ($twitterImage !== '') {
        $out[] = '<meta name="twitter:image" content="' . e($twitterImage) . '" />';
    }
    foreach (['twitter_site' => 'twitter:site', 'twitter_creator' => 'twitter:creator'] as $settingKey => $metaName) {
        if (cms_setting($settingKey) !== '') {
            $out[] = '<meta name="' . $metaName . '" content="' . e(cms_setting($settingKey)) . '" />';
        }
    }

    /* Search engine and platform verification */
    $verifications = [
        'google_site_verification' => 'google-site-verification',
        'bing_site_verification' => 'msvalidate.01',
        'yandex_verification' => 'yandex-verification',
        'pinterest_verification' => 'p:domain_verify',
        'facebook_domain_verification' => 'facebook-domain-verification',
    ];
    foreach ($verifications as $settingKey => $metaName) {
        if (cms_setting($settingKey) !== '') {
            $out[] = '<meta name="' . $metaName . '" content="' . e(cms_setting($settingKey)) . '" />';
        }
    }

    /* hreflang alternates */
    $hreflang = json_decode((string) $seo['hreflang_json'], true);
    if (is_array($hreflang)) {
        foreach ($hreflang as $language => $href) {
            $out[] = '<link rel="alternate" hreflang="' . e((string) $language) . '" href="' . e((string) $href) . '" />';
        }
    }

    /* Structured data */
    $schema = (string) $seo['schema_json'];
    if ($schema === '' && cms_setting('schema_enabled') === '1') {
        $schema = cms_default_schema();
    }
    if ($schema !== '' && json_decode($schema) !== null) {
        $out[] = '<script type="application/ld+json">' . $schema . '</script>';
    }

    /* Analytics and custom snippets */
    $out[] = cms_render_analytics();
    if (cms_setting('custom_head_html') !== '') {
        $out[] = cms_setting('custom_head_html');
    }
    if ((string) $seo['head_extra'] !== '') {
        $out[] = (string) $seo['head_extra'];
    }

    return implode("\n    ", array_filter($out));
}

function cms_default_schema(): string
{
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => cms_setting('schema_type', 'ProfessionalService'),
        'name' => cms_raw('global.brand.name'),
        'description' => cms_setting('default_meta_description'),
        'url' => cms_site_url(),
        'logo' => cms_site_url(cms_raw('global.brand.logo')),
        'image' => cms_site_url(cms_setting('default_og_image')),
        'email' => cms_raw('global.contact.email'),
        'telephone' => cms_raw('global.contact.phone_link'),
        'priceRange' => cms_setting('schema_price_range'),
        'areaServed' => cms_setting('schema_area_served'),
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => cms_raw('global.contact.street'),
            'addressLocality' => cms_raw('global.contact.city'),
            'postalCode' => cms_raw('global.contact.postal_code'),
            'addressRegion' => cms_raw('global.contact.region'),
            'addressCountry' => cms_raw('global.contact.country'),
        ],
        'sameAs' => array_values(array_filter([
            cms_raw('global.social.linkedin_url'),
            cms_raw('global.social.facebook_url'),
            cms_raw('global.social.instagram_url'),
        ])),
    ];

    if (cms_setting('schema_opening_hours') !== '') {
        $schema['openingHours'] = cms_setting('schema_opening_hours');
    }
    if (cms_setting('schema_founding_date') !== '') {
        $schema['foundingDate'] = cms_setting('schema_founding_date');
    }
    if (cms_setting('schema_geo_lat') !== '' && cms_setting('schema_geo_lng') !== '') {
        $schema['geo'] = [
            '@type' => 'GeoCoordinates',
            'latitude' => cms_setting('schema_geo_lat'),
            'longitude' => cms_setting('schema_geo_lng'),
        ];
    }

    return (string) json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

function cms_render_analytics(): string
{
    $out = [];

    $ga = cms_setting('google_analytics_id');
    if ($ga !== '' && preg_match('/^[A-Za-z0-9\-]+$/', $ga) === 1) {
        $out[] = '<script async src="https://www.googletagmanager.com/gtag/js?id=' . e($ga) . '"></script>';
        $out[] = "<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','" . e($ga) . "');</script>";
    }

    $gtm = cms_setting('google_tag_manager_id');
    if ($gtm !== '' && preg_match('/^GTM-[A-Z0-9]+$/i', $gtm) === 1) {
        $out[] = "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','" . e($gtm) . "');</script>";
    }

    $pixel = cms_setting('facebook_pixel_id');
    if ($pixel !== '' && preg_match('/^[0-9]+$/', $pixel) === 1) {
        $out[] = "<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','" . e($pixel) . "');fbq('track','PageView');</script>";
    }

    $clarity = cms_setting('microsoft_clarity_id');
    if ($clarity !== '' && preg_match('/^[A-Za-z0-9]+$/', $clarity) === 1) {
        $out[] = "<script>(function(c,l,a,r,i,t,y){c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};t=l.createElement(r);t.async=1;t.src='https://www.clarity.ms/tag/'+i;y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);})(window,document,'clarity','script','" . e($clarity) . "');</script>";
    }

    $hotjar = cms_setting('hotjar_id');
    if ($hotjar !== '' && preg_match('/^[0-9]+$/', $hotjar) === 1) {
        $out[] = "<script>(function(h,o,t,j,a,r){h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};h._hjSettings={hjid:" . e($hotjar) . ",hjsv:6};a=o.getElementsByTagName('head')[0];r=o.createElement('script');r.async=1;r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;a.appendChild(r);})(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');</script>";
    }

    return implode("\n    ", $out);
}

/**
 * Very small allow-list sanitiser for rich text coming from the admin panel.
 * Scripts, iframes, event handlers and javascript: URLs are removed.
 */
function cms_sanitize_html(string $html): string
{
    $html = preg_replace('#<\s*(script|style|iframe|object|embed|form)\b[^>]*>.*?<\s*/\s*\1\s*>#is', '', $html) ?? '';
    $html = preg_replace('#<\s*/?\s*(script|style|iframe|object|embed|form)\b[^>]*>#i', '', $html) ?? '';
    $html = preg_replace('#\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html) ?? '';
    $html = preg_replace('#(href|src)\s*=\s*(["\'])\s*javascript:[^"\']*\2#i', '$1="#"', $html) ?? '';

    return $html;
}

/* =====================================================================
 * Redirects
 * ================================================================== */

function cms_apply_redirects(): void
{
    $conn = cms_db();
    if (!$conn instanceof mysqli) {
        return;
    }

    $path = cms_current_route();
    try {
        $statement = $conn->prepare('SELECT `id`, `to_url`, `status_code` FROM `cms_redirects` WHERE `from_path` = ? AND `is_active` = 1 LIMIT 1');
        $statement->bind_param('s', $path);
        $statement->execute();
        $row = $statement->get_result()->fetch_assoc();
        if (!$row) {
            return;
        }

        $hit = $conn->prepare('UPDATE `cms_redirects` SET `hits` = `hits` + 1 WHERE `id` = ?');
        $hit->bind_param('i', $row['id']);
        $hit->execute();

        $code = (int) $row['status_code'];
        if ($code === 410) {
            http_response_code(410);
            exit;
        }

        header('Location: ' . (string) $row['to_url'], true, in_array($code, [301, 302, 307, 308], true) ? $code : 301);
        exit;
    } catch (Throwable $exception) {
        error_log('UTS CMS redirect check failed: ' . $exception->getMessage());
    }
}

/* =====================================================================
 * Products (existing table, surfaced through the CMS helpers)
 * ================================================================== */

function cms_products(): array
{
    $conn = cms_db();
    if (!$conn instanceof mysqli) {
        return [];
    }

    $products = [];
    try {
        $result = $conn->query('SELECT `id`, `name`, `description`, `url`, `image` FROM `products` ORDER BY `id` DESC');
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    } catch (Throwable $exception) {
        error_log('UTS CMS products read failed: ' . $exception->getMessage());
    }

    return $products;
}

function cms_product_image(array $product): string
{
    $direct = basename((string) ($product['image'] ?? ''));
    $legacy = basename((string) ($product['name'] ?? '') . (string) ($product['image'] ?? ''));
    $root = dirname(__DIR__);

    return is_file($root . '/assets/productimages/' . $direct) ? $direct : $legacy;
}

/* =====================================================================
 * Schema installation and seeding
 * ================================================================== */

function cms_schema_statements(): array
{
    return [
        'cms_content' => "CREATE TABLE IF NOT EXISTS `cms_content` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `content_key` VARCHAR(150) NOT NULL,
            `page_label` VARCHAR(80) NOT NULL DEFAULT '',
            `section_label` VARCHAR(80) NOT NULL DEFAULT '',
            `field_label` VARCHAR(160) NOT NULL DEFAULT '',
            `field_type` VARCHAR(20) NOT NULL DEFAULT 'text',
            `content_value` TEXT NULL,
            `is_custom` TINYINT(1) NOT NULL DEFAULT 0,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `content_key` (`content_key`),
            KEY `page_label` (`page_label`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'cms_settings' => "CREATE TABLE IF NOT EXISTS `cms_settings` (
            `setting_key` VARCHAR(80) NOT NULL,
            `setting_value` TEXT NULL,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'cms_nav' => "CREATE TABLE IF NOT EXISTS `cms_nav` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `menu` VARCHAR(40) NOT NULL DEFAULT 'primary',
            `label` VARCHAR(120) NOT NULL,
            `url` VARCHAR(500) NOT NULL,
            `sort_order` INT NOT NULL DEFAULT 0,
            `link_target` VARCHAR(10) NOT NULL DEFAULT '_self',
            `rel` VARCHAR(60) NOT NULL DEFAULT '',
            `visibility` VARCHAR(10) NOT NULL DEFAULT 'all',
            `is_button` TINYINT(1) NOT NULL DEFAULT 0,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `menu` (`menu`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'cms_pages' => "CREATE TABLE IF NOT EXISTS `cms_pages` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `slug` VARCHAR(150) NOT NULL,
            `title` VARCHAR(200) NOT NULL,
            `subtitle` VARCHAR(255) NOT NULL DEFAULT '',
            `cover_image` VARCHAR(255) NOT NULL DEFAULT '',
            `description` TEXT NULL,
            `body` MEDIUMTEXT NULL,
            `sections` LONGTEXT NULL,
            `template` VARCHAR(40) NOT NULL DEFAULT 'standard',
            `status` VARCHAR(20) NOT NULL DEFAULT 'draft',
            `show_in_nav` TINYINT(1) NOT NULL DEFAULT 0,
            `show_in_header` TINYINT(1) NOT NULL DEFAULT 0,
            `show_in_mobile` TINYINT(1) NOT NULL DEFAULT 0,
            `show_in_footer` TINYINT(1) NOT NULL DEFAULT 0,
            `nav_menu` VARCHAR(40) NOT NULL DEFAULT 'primary',
            `footer_menu` VARCHAR(40) NOT NULL DEFAULT 'footer_explore',
            `sort_order` INT NOT NULL DEFAULT 0,
            `meta_title` VARCHAR(255) NOT NULL DEFAULT '',
            `meta_description` TEXT NULL,
            `meta_keywords` TEXT NULL,
            `canonical_url` VARCHAR(500) NOT NULL DEFAULT '',
            `robots_index` VARCHAR(12) NOT NULL DEFAULT 'index',
            `robots_follow` VARCHAR(12) NOT NULL DEFAULT 'follow',
            `og_image` VARCHAR(500) NOT NULL DEFAULT '',
            `schema_json` TEXT NULL,
            `sitemap_include` TINYINT(1) NOT NULL DEFAULT 1,
            `sitemap_priority` VARCHAR(5) NOT NULL DEFAULT '0.6',
            `sitemap_changefreq` VARCHAR(20) NOT NULL DEFAULT 'monthly',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'cms_posts' => "CREATE TABLE IF NOT EXISTS `cms_posts` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `slug` VARCHAR(150) NOT NULL,
            `title` VARCHAR(200) NOT NULL,
            `cover_image` VARCHAR(255) NOT NULL DEFAULT '',
            `excerpt` TEXT NULL,
            `body` MEDIUMTEXT NULL,
            `author` VARCHAR(120) NOT NULL DEFAULT '',
            `category` VARCHAR(80) NOT NULL DEFAULT '',
            `tags` VARCHAR(255) NOT NULL DEFAULT '',
            `status` VARCHAR(20) NOT NULL DEFAULT 'draft',
            `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
            `published_at` DATETIME NULL,
            `views` INT UNSIGNED NOT NULL DEFAULT 0,
            `reading_minutes` INT UNSIGNED NOT NULL DEFAULT 0,
            `meta_title` VARCHAR(255) NOT NULL DEFAULT '',
            `meta_description` TEXT NULL,
            `meta_keywords` TEXT NULL,
            `canonical_url` VARCHAR(500) NOT NULL DEFAULT '',
            `robots_index` VARCHAR(12) NOT NULL DEFAULT 'index',
            `robots_follow` VARCHAR(12) NOT NULL DEFAULT 'follow',
            `og_image` VARCHAR(500) NOT NULL DEFAULT '',
            `schema_json` TEXT NULL,
            `sitemap_include` TINYINT(1) NOT NULL DEFAULT 1,
            `sitemap_priority` VARCHAR(5) NOT NULL DEFAULT '0.7',
            `sitemap_changefreq` VARCHAR(20) NOT NULL DEFAULT 'monthly',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`),
            KEY `status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'cms_seo' => "CREATE TABLE IF NOT EXISTS `cms_seo` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `route` VARCHAR(190) NOT NULL,
            `route_label` VARCHAR(120) NOT NULL DEFAULT '',
            `meta_title` VARCHAR(255) NOT NULL DEFAULT '',
            `meta_description` TEXT NULL,
            `meta_keywords` TEXT NULL,
            `canonical_url` VARCHAR(500) NOT NULL DEFAULT '',
            `robots_index` VARCHAR(12) NOT NULL DEFAULT 'index',
            `robots_follow` VARCHAR(12) NOT NULL DEFAULT 'follow',
            `robots_extra` VARCHAR(190) NOT NULL DEFAULT '',
            `og_type` VARCHAR(40) NOT NULL DEFAULT 'website',
            `og_title` VARCHAR(255) NOT NULL DEFAULT '',
            `og_description` TEXT NULL,
            `og_image` VARCHAR(500) NOT NULL DEFAULT '',
            `twitter_card` VARCHAR(40) NOT NULL DEFAULT 'summary_large_image',
            `twitter_title` VARCHAR(255) NOT NULL DEFAULT '',
            `twitter_description` TEXT NULL,
            `twitter_image` VARCHAR(500) NOT NULL DEFAULT '',
            `schema_json` TEXT NULL,
            `hreflang_json` TEXT NULL,
            `sitemap_include` TINYINT(1) NOT NULL DEFAULT 1,
            `sitemap_priority` VARCHAR(5) NOT NULL DEFAULT '0.6',
            `sitemap_changefreq` VARCHAR(20) NOT NULL DEFAULT 'monthly',
            `head_extra` TEXT NULL,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `route` (`route`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'cms_keywords' => "CREATE TABLE IF NOT EXISTS `cms_keywords` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `keyword` VARCHAR(190) NOT NULL,
            `target_route` VARCHAR(190) NOT NULL DEFAULT '',
            `search_intent` VARCHAR(20) NOT NULL DEFAULT 'commercial',
            `search_volume` INT NOT NULL DEFAULT 0,
            `difficulty` INT NOT NULL DEFAULT 0,
            `current_rank` INT NOT NULL DEFAULT 0,
            `priority` VARCHAR(10) NOT NULL DEFAULT 'medium',
            `status` VARCHAR(20) NOT NULL DEFAULT 'tracking',
            `notes` TEXT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `keyword` (`keyword`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'cms_backlinks' => "CREATE TABLE IF NOT EXISTS `cms_backlinks` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `source_url` VARCHAR(500) NOT NULL,
            `source_domain` VARCHAR(190) NOT NULL DEFAULT '',
            `target_route` VARCHAR(190) NOT NULL DEFAULT '/',
            `anchor_text` VARCHAR(255) NOT NULL DEFAULT '',
            `link_type` VARCHAR(20) NOT NULL DEFAULT 'dofollow',
            `placement` VARCHAR(40) NOT NULL DEFAULT 'directory',
            `authority` INT NOT NULL DEFAULT 0,
            `status` VARCHAR(20) NOT NULL DEFAULT 'live',
            `acquired_on` DATE NULL,
            `last_checked` DATE NULL,
            `notes` TEXT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `source_domain` (`source_domain`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'cms_redirects' => "CREATE TABLE IF NOT EXISTS `cms_redirects` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `from_path` VARCHAR(190) NOT NULL,
            `to_url` VARCHAR(500) NOT NULL,
            `status_code` INT NOT NULL DEFAULT 301,
            `hits` INT UNSIGNED NOT NULL DEFAULT 0,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `notes` VARCHAR(255) NOT NULL DEFAULT '',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `from_path` (`from_path`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'cms_media' => "CREATE TABLE IF NOT EXISTS `cms_media` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `file_name` VARCHAR(190) NOT NULL,
            `file_path` VARCHAR(255) NOT NULL,
            `mime_type` VARCHAR(60) NOT NULL DEFAULT '',
            `file_size` INT UNSIGNED NOT NULL DEFAULT 0,
            `alt_text` VARCHAR(255) NOT NULL DEFAULT '',
            `uploaded_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `file_path` (`file_path`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'cms_admins' => "CREATE TABLE IF NOT EXISTS `cms_admins` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(120) NOT NULL DEFAULT 'Administrator',
            `mobile` VARCHAR(20) NOT NULL,
            `password_hash` VARCHAR(255) NOT NULL,
            `role` VARCHAR(20) NOT NULL DEFAULT 'owner',
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `last_login_at` DATETIME NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `mobile` (`mobile`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'cms_audit' => "CREATE TABLE IF NOT EXISTS `cms_audit` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `admin_name` VARCHAR(120) NOT NULL DEFAULT '',
            `action` VARCHAR(60) NOT NULL,
            `entity` VARCHAR(60) NOT NULL DEFAULT '',
            `entity_ref` VARCHAR(190) NOT NULL DEFAULT '',
            `details` TEXT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ];
}

/**
 * Columns added after a table first shipped. Applied to installs that already
 * created the table with the older definition.
 */
function cms_schema_upgrades(): array
{
    return [
        'cms_nav' => [
            'visibility' => "VARCHAR(10) NOT NULL DEFAULT 'all' AFTER `rel`",
        ],
        'cms_pages' => [
            'sections' => 'LONGTEXT NULL AFTER `body`',
            'show_in_header' => 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `show_in_nav`',
            'show_in_mobile' => 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `show_in_header`',
            'show_in_footer' => 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `show_in_mobile`',
            'footer_menu' => "VARCHAR(40) NOT NULL DEFAULT 'footer_explore' AFTER `nav_menu`",
        ],
    ];
}

/** Adds any column from cms_schema_upgrades() that the live table is missing. */
function cms_apply_schema_upgrades(mysqli $conn, array &$errors): void
{
    foreach (cms_schema_upgrades() as $table => $columns) {
        try {
            $existing = [];
            $result = $conn->query('SHOW COLUMNS FROM `' . $table . '`');
            while ($row = $result->fetch_assoc()) {
                $existing[] = (string) $row['Field'];
            }
            foreach ($columns as $column => $definition) {
                if (!in_array($column, $existing, true)) {
                    $conn->query('ALTER TABLE `' . $table . '` ADD COLUMN `' . $column . '` ' . $definition);
                }
            }
        } catch (Throwable $exception) {
            $errors[] = $table . ' upgrade: ' . $exception->getMessage();
        }
    }
}

/**
 * Creates every missing table and seeds the default content, navigation,
 * SEO records and administrator. Safe to run repeatedly.
 *
 * @return array{created:int,seeded:int,errors:string[]}
 */
function cms_install(bool $resyncContent = true): array
{
    $report = ['created' => 0, 'seeded' => 0, 'errors' => []];
    $conn = cms_db();
    if (!$conn instanceof mysqli) {
        $report['errors'][] = 'Database connection unavailable.';

        return $report;
    }

    foreach (cms_schema_statements() as $table => $sql) {
        try {
            $conn->query($sql);
            $report['created']++;
        } catch (Throwable $exception) {
            $report['errors'][] = $table . ': ' . $exception->getMessage();
        }
    }

    cms_apply_schema_upgrades($conn, $report['errors']);

    /* Content registry — insert missing keys, never overwrite edited values. */
    if ($resyncContent) {
        try {
            $statement = $conn->prepare(
                'INSERT INTO `cms_content` (`content_key`, `page_label`, `section_label`, `field_label`, `field_type`, `content_value`)
                 VALUES (?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE `page_label` = VALUES(`page_label`), `section_label` = VALUES(`section_label`),
                     `field_label` = VALUES(`field_label`), `field_type` = VALUES(`field_type`)'
            );
            foreach (cms_content_defaults() as $item) {
                $statement->bind_param('ssssss', $item['key'], $item['page'], $item['section'], $item['label'], $item['type'], $item['value']);
                $statement->execute();
                $report['seeded']++;
            }
        } catch (Throwable $exception) {
            $report['errors'][] = 'content: ' . $exception->getMessage();
        }
    }

    /* Settings */
    try {
        $statement = $conn->prepare('INSERT IGNORE INTO `cms_settings` (`setting_key`, `setting_value`) VALUES (?, ?)');
        foreach (cms_setting_defaults() as $key => $value) {
            $statement->bind_param('ss', $key, $value);
            $statement->execute();
        }
    } catch (Throwable $exception) {
        $report['errors'][] = 'settings: ' . $exception->getMessage();
    }

    /* Navigation */
    try {
        $insertNav = $conn->prepare(
            'INSERT INTO `cms_nav` (`menu`, `label`, `url`, `sort_order`, `link_target`, `rel`, `visibility`, `is_button`, `is_active`)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $bindNav = static function (mysqli_stmt $statement, array $item): void {
            $statement->bind_param(
                'sssisssii',
                $item['menu'],
                $item['label'],
                $item['url'],
                $item['sort_order'],
                $item['link_target'],
                $item['rel'],
                $item['visibility'],
                $item['is_button'],
                $item['is_active']
            );
            $statement->execute();
        };

        $count = (int) ($conn->query('SELECT COUNT(*) AS `total` FROM `cms_nav`')->fetch_assoc()['total'] ?? 0);
        if ($count === 0) {
            foreach (cms_nav_defaults() as $item) {
                $bindNav($insertNav, $item);
            }
        } else {
            /* Backfill links introduced after this install was first seeded. */
            $lookup = $conn->prepare('SELECT COUNT(*) AS `total` FROM `cms_nav` WHERE `menu` = ? AND `url` = ?');
            foreach (cms_nav_required() as $item) {
                $lookup->bind_param('ss', $item['menu'], $item['url']);
                $lookup->execute();
                $exists = (int) ($lookup->get_result()->fetch_assoc()['total'] ?? 0) > 0;
                if (!$exists) {
                    $bindNav($insertNav, $item);
                }
            }
        }
    } catch (Throwable $exception) {
        $report['errors'][] = 'navigation: ' . $exception->getMessage();
    }

    /* SEO records for the fixed routes */
    try {
        $statement = $conn->prepare('INSERT IGNORE INTO `cms_seo` (`route`, `route_label`, `meta_title`, `meta_description`, `meta_keywords`) VALUES (?, ?, ?, ?, ?)');
        foreach (cms_seo_seed() as $seed) {
            $statement->bind_param('sssss', $seed['route'], $seed['label'], $seed['title'], $seed['description'], $seed['keywords']);
            $statement->execute();
        }
    } catch (Throwable $exception) {
        $report['errors'][] = 'seo: ' . $exception->getMessage();
    }

    /* Administrator */
    try {
        $count = (int) ($conn->query('SELECT COUNT(*) AS `total` FROM `cms_admins`')->fetch_assoc()['total'] ?? 0);
        if ($count === 0) {
            $name = 'Administrator';
            $mobile = getenv('UTS_ADMIN_MOBILE') ?: '9818059661';
            $hash = getenv('UTS_ADMIN_PASSWORD_HASH') ?: '$2y$12$gO86NL7JQ65mNYiPBjuTnO.9SG65MO1tXir2h6.T9kdSl/WWeHcAu';
            $statement = $conn->prepare('INSERT INTO `cms_admins` (`name`, `mobile`, `password_hash`, `role`) VALUES (?, ?, ?, ?)');
            $role = 'owner';
            $statement->bind_param('ssss', $name, $mobile, $hash, $role);
            $statement->execute();
        }
    } catch (Throwable $exception) {
        $report['errors'][] = 'admins: ' . $exception->getMessage();
    }

    /* Record the version so later page loads skip all of the above. */
    if ($report['errors'] === []) {
        cms_mark_installed();
    }

    return $report;
}

/** True when the database has not yet been set up for this code version. */
function cms_needs_install(): bool
{
    return cms_setting('cms_schema_version') !== CMS_SCHEMA_VERSION;
}

function cms_mark_installed(): void
{
    $conn = cms_db();
    if (!$conn instanceof mysqli) {
        return;
    }

    try {
        $key = 'cms_schema_version';
        $value = CMS_SCHEMA_VERSION;
        $statement = $conn->prepare('INSERT INTO `cms_settings` (`setting_key`, `setting_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)');
        $statement->bind_param('ss', $key, $value);
        $statement->execute();
    } catch (Throwable $exception) {
        error_log('UTS CMS version marker failed: ' . $exception->getMessage());
    }
}

function cms_seo_seed(): array
{
    return [
        [
            'route' => '/',
            'label' => 'Home page',
            'title' => 'Unnat Technology Services | Web, Software & Automation Solutions',
            'description' => 'Unnat Technology Services builds scalable web platforms, business software, automation systems and digital products for future-ready organizations in Moradabad and across India.',
            'keywords' => 'web development company Moradabad, software development India, business automation, custom software, AI solutions, digital product engineering',
        ],
        [
            'route' => '/contact.html',
            'label' => 'Contact page',
            'title' => 'Start a Project | Unnat Technology Services',
            'description' => 'Start a web, software, automation or digital product project with Unnat Technology Services in Moradabad, India.',
            'keywords' => 'contact software company Moradabad, hire web developers India, start a software project',
        ],
        [
            'route' => '/products.php',
            'label' => 'Products page',
            'title' => 'Digital Products | Unnat Technology Services',
            'description' => 'Explore digital products and platforms created by Unnat Technology Services for business, learning and public-service needs.',
            'keywords' => 'digital products India, software products Moradabad, learning platform, business platform',
        ],
        [
            'route' => '/blog.php',
            'label' => 'Blog index',
            'title' => 'Insights & Articles | Unnat Technology Services',
            'description' => 'Practical articles on web platforms, business software, automation and technology decisions from the Unnat Technology Services team.',
            'keywords' => 'technology blog India, software development insights, automation articles',
        ],
    ];
}

/* =====================================================================
 * Audit log
 * ================================================================== */

function cms_audit(string $action, string $entity = '', string $reference = '', string $details = ''): void
{
    $conn = cms_db();
    if (!$conn instanceof mysqli) {
        return;
    }

    try {
        $adminName = (string) ($_SESSION['uts_admin_name'] ?? 'Administrator');
        $statement = $conn->prepare('INSERT INTO `cms_audit` (`admin_name`, `action`, `entity`, `entity_ref`, `details`) VALUES (?, ?, ?, ?, ?)');
        $statement->bind_param('sssss', $adminName, $action, $entity, $reference, $details);
        $statement->execute();
    } catch (Throwable $exception) {
        error_log('UTS CMS audit write failed: ' . $exception->getMessage());
    }
}
