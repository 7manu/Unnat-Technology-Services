<?php
/**
 * Single CSRF-protected controller for every admin panel mutation.
 * Each branch validates its own input and redirects back to the calling view.
 */
declare(strict_types=1);

require __DIR__ . '/admin/_helpers.php';
requireAdmin('../login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyAdminCsrf()) {
    admin_redirect('dashboard', 'invalid');
}

$conn = cms_db();
if (!$conn instanceof mysqli) {
    admin_redirect('dashboard', 'error');
}

$action = admin_post('action');
$view = admin_post('return_view', 'dashboard');
$id = admin_post_int('id');

try {
    switch ($action) {

        /* ---------------------------------------------------------- *
         * Website content
         * ---------------------------------------------------------- */
        case 'content.save':
            $values = $_POST['content'] ?? [];
            if (!is_array($values)) {
                admin_redirect($view, 'invalid-input');
            }
            $statement = $conn->prepare('UPDATE `cms_content` SET `content_value` = ? WHERE `content_key` = ?');
            $saved = 0;
            foreach ($values as $key => $value) {
                $key = (string) $key;
                $value = is_string($value) ? $value : '';
                $statement->bind_param('ss', $value, $key);
                $statement->execute();
                $saved++;
            }
            cms_audit('update', 'content', admin_post('group', 'all'), $saved . ' field(s) updated');
            admin_redirect($view, 'saved', ['group' => admin_post('group')]);
            break;

        case 'content.add':
            $key = strtolower(preg_replace('/[^a-z0-9._-]/i', '', admin_post('content_key')) ?? '');
            $page = admin_post('page_label', 'Custom');
            $section = admin_post('section_label', 'Custom');
            $label = admin_post('field_label');
            $type = admin_enum('field_type', ['text', 'textarea', 'html', 'url', 'email', 'tel', 'image', 'number'], 'text');
            $value = (string) ($_POST['content_value'] ?? '');
            if ($key === '' || $label === '') {
                admin_redirect($view, 'invalid-input');
            }
            $statement = $conn->prepare(
                'INSERT INTO `cms_content` (`content_key`, `page_label`, `section_label`, `field_label`, `field_type`, `content_value`, `is_custom`)
                 VALUES (?, ?, ?, ?, ?, ?, 1)'
            );
            $statement->bind_param('ssssss', $key, $page, $section, $label, $type, $value);
            $statement->execute();
            cms_audit('create', 'content', $key);
            admin_redirect($view, 'created', ['group' => $page]);
            break;

        case 'content.delete':
            $statement = $conn->prepare('DELETE FROM `cms_content` WHERE `id` = ? AND `is_custom` = 1');
            $statement->bind_param('i', $id);
            $statement->execute();
            cms_audit('delete', 'content', (string) $id);
            admin_redirect($view, $statement->affected_rows > 0 ? 'deleted' : 'invalid-input');
            break;

        /* ---------------------------------------------------------- *
         * Custom pages
         * ---------------------------------------------------------- */
        case 'page.save':
            $title = admin_post('title');
            if ($title === '') {
                admin_redirect($view, 'invalid-input');
            }
            $slug = cms_slugify(admin_post('slug') !== '' ? admin_post('slug') : $title, 'page');
            $existing = $id > 0 ? admin_row('SELECT * FROM `cms_pages` WHERE `id` = ? LIMIT 1', 'i', [$id]) : null;
            $cover = admin_resolve_image('cover_upload', 'cover_image', (string) ($existing['cover_image'] ?? ''));

            $fields = [
                'slug' => $slug,
                'title' => $title,
                'subtitle' => admin_post('subtitle'),
                'cover_image' => $cover,
                'description' => admin_post('description'),
                'body' => cms_sanitize_html((string) ($_POST['body'] ?? '')),
                'template' => admin_enum('template', ['standard', 'wide', 'landing'], 'standard'),
                'status' => admin_enum('status', ['draft', 'published'], 'draft'),
                'show_in_nav' => (string) (admin_post_bool('show_in_header') || admin_post_bool('show_in_mobile') || admin_post_bool('show_in_footer') ? 1 : 0),
                'show_in_header' => (string) admin_post_bool('show_in_header'),
                'show_in_mobile' => (string) admin_post_bool('show_in_mobile'),
                'show_in_footer' => (string) admin_post_bool('show_in_footer'),
                'nav_menu' => 'primary',
                'footer_menu' => admin_enum('footer_menu', ['footer_explore', 'footer_platforms', 'footer_contact'], 'footer_explore'),
                'sort_order' => (string) admin_post_int('sort_order'),
                'meta_title' => admin_post('meta_title'),
                'meta_description' => admin_post('meta_description'),
                'meta_keywords' => admin_post('meta_keywords'),
                'canonical_url' => admin_post('canonical_url'),
                'robots_index' => admin_enum('robots_index', ['index', 'noindex'], 'index'),
                'robots_follow' => admin_enum('robots_follow', ['follow', 'nofollow'], 'follow'),
                'og_image' => admin_post('og_image'),
                'schema_json' => admin_post('schema_json'),
                'sitemap_include' => (string) admin_post_bool('sitemap_include'),
                'sitemap_priority' => admin_enum('sitemap_priority', array_keys(admin_priority_options()), '0.6'),
                'sitemap_changefreq' => admin_enum('sitemap_changefreq', array_keys(admin_changefreq_options()), 'monthly'),
            ];

            $columns = array_keys($fields);
            $params = array_values($fields);
            $types = str_repeat('s', count($params));

            if ($id > 0) {
                $assignments = implode(', ', array_map(static fn(string $column): string => "`$column` = ?", $columns));
                $statement = $conn->prepare("UPDATE `cms_pages` SET $assignments WHERE `id` = ?");
                $params[] = $id;
                $statement->bind_param($types . 'i', ...$params);
            } else {
                $placeholders = implode(', ', array_fill(0, count($columns), '?'));
                $columnList = '`' . implode('`, `', $columns) . '`';
                $statement = $conn->prepare("INSERT INTO `cms_pages` ($columnList) VALUES ($placeholders)");
                $statement->bind_param($types, ...$params);
            }
            $statement->execute();
            $pageId = $id > 0 ? $id : (int) $conn->insert_id;

            /* Keep the menu links in sync with the header / mobile / footer toggles. */
            $pageUrl = cms_setting('page_base_path', 'page.php') . '?slug=' . $slug;
            $order = (int) $fields['sort_order'];

            /* Which menus this page belongs in, and on which screen sizes. */
            $desired = [];
            $inHeader = $fields['show_in_header'] === '1';
            $inMobile = $fields['show_in_mobile'] === '1';
            if ($inHeader && $inMobile) {
                $desired['primary'] = 'all';
            } elseif ($inHeader) {
                $desired['primary'] = 'desktop';
            } elseif ($inMobile) {
                $desired['primary'] = 'mobile';
            }
            if ($fields['show_in_footer'] === '1') {
                $desired[$fields['footer_menu']] = 'all';
            }

            $navRows = [];
            $lookup = $conn->prepare('SELECT `id`, `menu` FROM `cms_nav` WHERE `url` = ?');
            $lookup->bind_param('s', $pageUrl);
            $lookup->execute();
            $lookupResult = $lookup->get_result();
            while ($navRow = $lookupResult->fetch_assoc()) {
                $navRows[] = $navRow;
            }

            foreach ($navRows as $navRow) {
                $menu = (string) $navRow['menu'];
                if (isset($desired[$menu])) {
                    $nav = $conn->prepare('UPDATE `cms_nav` SET `label` = ?, `visibility` = ?, `sort_order` = ?, `is_active` = 1 WHERE `id` = ?');
                    $nav->bind_param('ssii', $title, $desired[$menu], $order, $navRow['id']);
                    $nav->execute();
                    unset($desired[$menu]);
                } else {
                    $nav = $conn->prepare('DELETE FROM `cms_nav` WHERE `id` = ?');
                    $nav->bind_param('i', $navRow['id']);
                    $nav->execute();
                }
            }

            foreach ($desired as $menu => $visibility) {
                $nav = $conn->prepare('INSERT INTO `cms_nav` (`menu`, `label`, `url`, `sort_order`, `visibility`) VALUES (?, ?, ?, ?, ?)');
                $nav->bind_param('sssis', $menu, $title, $pageUrl, $order, $visibility);
                $nav->execute();
            }

            cms_audit($id > 0 ? 'update' : 'create', 'page', $slug);
            admin_redirect($view, $id > 0 ? 'saved' : 'created', ['id' => $pageId]);
            break;

        case 'page.delete':
            $page = admin_row('SELECT `slug` FROM `cms_pages` WHERE `id` = ? LIMIT 1', 'i', [$id]);
            $statement = $conn->prepare('DELETE FROM `cms_pages` WHERE `id` = ?');
            $statement->bind_param('i', $id);
            $statement->execute();
            if ($page) {
                $url = cms_setting('page_base_path', 'page.php') . '?slug=' . $page['slug'];
                $nav = $conn->prepare('DELETE FROM `cms_nav` WHERE `url` = ?');
                $nav->bind_param('s', $url);
                $nav->execute();
            }
            cms_audit('delete', 'page', (string) ($page['slug'] ?? $id));
            admin_redirect($view, 'deleted');
            break;

        /* ---------------------------------------------------------- *
         * Blog posts
         * ---------------------------------------------------------- */
        case 'post.save':
            $title = admin_post('title');
            if ($title === '') {
                admin_redirect($view, 'invalid-input');
            }
            $slug = cms_slugify(admin_post('slug') !== '' ? admin_post('slug') : $title, 'post');
            $existing = $id > 0 ? admin_row('SELECT * FROM `cms_posts` WHERE `id` = ? LIMIT 1', 'i', [$id]) : null;
            $cover = admin_resolve_image('cover_upload', 'cover_image', (string) ($existing['cover_image'] ?? ''));
            $body = cms_sanitize_html((string) ($_POST['body'] ?? ''));
            $publishedAt = admin_post('published_at');
            $status = admin_enum('status', ['draft', 'published'], 'draft');
            if ($status === 'published' && $publishedAt === '') {
                $publishedAt = date('Y-m-d H:i:s');
            }
            $publishedAt = $publishedAt !== '' ? date('Y-m-d H:i:s', strtotime($publishedAt) ?: time()) : null;
            $readingMinutes = max(1, (int) ceil(str_word_count(strip_tags($body)) / 200));

            $fields = [
                'slug' => $slug,
                'title' => $title,
                'cover_image' => $cover,
                'excerpt' => admin_post('excerpt'),
                'body' => $body,
                'author' => admin_post('author') !== '' ? admin_post('author') : cms_setting('blog_default_author'),
                'category' => admin_post('category'),
                'tags' => admin_post('tags'),
                'status' => $status,
                'is_featured' => (string) admin_post_bool('is_featured'),
                'published_at' => $publishedAt,
                'reading_minutes' => (string) $readingMinutes,
                'meta_title' => admin_post('meta_title'),
                'meta_description' => admin_post('meta_description'),
                'meta_keywords' => admin_post('meta_keywords'),
                'canonical_url' => admin_post('canonical_url'),
                'robots_index' => admin_enum('robots_index', ['index', 'noindex'], 'index'),
                'robots_follow' => admin_enum('robots_follow', ['follow', 'nofollow'], 'follow'),
                'og_image' => admin_post('og_image'),
                'schema_json' => admin_post('schema_json'),
                'sitemap_include' => (string) admin_post_bool('sitemap_include'),
                'sitemap_priority' => admin_enum('sitemap_priority', array_keys(admin_priority_options()), '0.7'),
                'sitemap_changefreq' => admin_enum('sitemap_changefreq', array_keys(admin_changefreq_options()), 'monthly'),
            ];

            $columns = array_keys($fields);
            $params = array_values($fields);
            $types = str_repeat('s', count($params));

            if ($id > 0) {
                $assignments = implode(', ', array_map(static fn(string $column): string => "`$column` = ?", $columns));
                $statement = $conn->prepare("UPDATE `cms_posts` SET $assignments WHERE `id` = ?");
                $params[] = $id;
                $statement->bind_param($types . 'i', ...$params);
            } else {
                $placeholders = implode(', ', array_fill(0, count($columns), '?'));
                $columnList = '`' . implode('`, `', $columns) . '`';
                $statement = $conn->prepare("INSERT INTO `cms_posts` ($columnList) VALUES ($placeholders)");
                $statement->bind_param($types, ...$params);
            }
            $statement->execute();

            cms_audit($id > 0 ? 'update' : 'create', 'post', $slug);
            admin_redirect($view, $id > 0 ? 'saved' : 'created', ['id' => $id > 0 ? $id : (int) $conn->insert_id]);
            break;

        case 'post.delete':
            $statement = $conn->prepare('DELETE FROM `cms_posts` WHERE `id` = ?');
            $statement->bind_param('i', $id);
            $statement->execute();
            cms_audit('delete', 'post', (string) $id);
            admin_redirect($view, 'deleted');
            break;

        /* ---------------------------------------------------------- *
         * SEO records
         * ---------------------------------------------------------- */
        case 'seo.save':
            $route = admin_post('route');
            if ($route === '') {
                admin_redirect($view, 'invalid-input');
            }
            $route = '/' . ltrim($route, '/');

            $fields = [
                'route_label' => admin_post('route_label'),
                'meta_title' => admin_post('meta_title'),
                'meta_description' => admin_post('meta_description'),
                'meta_keywords' => admin_post('meta_keywords'),
                'canonical_url' => admin_post('canonical_url'),
                'robots_index' => admin_enum('robots_index', ['index', 'noindex'], 'index'),
                'robots_follow' => admin_enum('robots_follow', ['follow', 'nofollow'], 'follow'),
                'robots_extra' => admin_post('robots_extra'),
                'og_type' => admin_enum('og_type', ['website', 'article', 'profile', 'product'], 'website'),
                'og_title' => admin_post('og_title'),
                'og_description' => admin_post('og_description'),
                'og_image' => admin_resolve_image('og_upload', 'og_image'),
                'twitter_card' => admin_enum('twitter_card', ['summary', 'summary_large_image'], 'summary_large_image'),
                'twitter_title' => admin_post('twitter_title'),
                'twitter_description' => admin_post('twitter_description'),
                'twitter_image' => admin_post('twitter_image'),
                'schema_json' => admin_post('schema_json'),
                'hreflang_json' => admin_post('hreflang_json'),
                'sitemap_include' => (string) admin_post_bool('sitemap_include'),
                'sitemap_priority' => admin_enum('sitemap_priority', array_keys(admin_priority_options()), '0.6'),
                'sitemap_changefreq' => admin_enum('sitemap_changefreq', array_keys(admin_changefreq_options()), 'monthly'),
                'head_extra' => (string) ($_POST['head_extra'] ?? ''),
            ];

            $columns = array_merge(['route'], array_keys($fields));
            $params = array_merge([$route], array_values($fields));
            $updates = implode(', ', array_map(static fn(string $column): string => "`$column` = VALUES(`$column`)", array_keys($fields)));
            $placeholders = implode(', ', array_fill(0, count($columns), '?'));
            $columnList = '`' . implode('`, `', $columns) . '`';

            $statement = $conn->prepare("INSERT INTO `cms_seo` ($columnList) VALUES ($placeholders) ON DUPLICATE KEY UPDATE $updates");
            $statement->bind_param(str_repeat('s', count($params)), ...$params);
            $statement->execute();

            cms_audit('update', 'seo', $route);
            admin_redirect($view, 'saved', ['route' => $route]);
            break;

        case 'seo.delete':
            $statement = $conn->prepare('DELETE FROM `cms_seo` WHERE `id` = ?');
            $statement->bind_param('i', $id);
            $statement->execute();
            cms_audit('delete', 'seo', (string) $id);
            admin_redirect($view, 'deleted');
            break;

        /* ---------------------------------------------------------- *
         * Keyword tracker
         * ---------------------------------------------------------- */
        case 'keyword.save':
            $keyword = admin_post('keyword');
            if ($keyword === '') {
                admin_redirect($view, 'invalid-input');
            }
            $target = admin_post('target_route');
            $intent = admin_enum('search_intent', ['informational', 'commercial', 'transactional', 'navigational', 'local'], 'commercial');
            $volume = admin_post_int('search_volume');
            $difficulty = admin_post_int('difficulty');
            $rank = admin_post_int('current_rank');
            $priority = admin_enum('priority', ['high', 'medium', 'low'], 'medium');
            $status = admin_enum('status', ['tracking', 'target', 'ranking', 'paused'], 'tracking');
            $notes = admin_post('notes');

            if ($id > 0) {
                $statement = $conn->prepare(
                    'UPDATE `cms_keywords` SET `keyword` = ?, `target_route` = ?, `search_intent` = ?, `search_volume` = ?,
                     `difficulty` = ?, `current_rank` = ?, `priority` = ?, `status` = ?, `notes` = ? WHERE `id` = ?'
                );
                $statement->bind_param('sssiiisssi', $keyword, $target, $intent, $volume, $difficulty, $rank, $priority, $status, $notes, $id);
            } else {
                $statement = $conn->prepare(
                    'INSERT INTO `cms_keywords` (`keyword`, `target_route`, `search_intent`, `search_volume`, `difficulty`, `current_rank`, `priority`, `status`, `notes`)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $statement->bind_param('sssiiisss', $keyword, $target, $intent, $volume, $difficulty, $rank, $priority, $status, $notes);
            }
            $statement->execute();
            cms_audit($id > 0 ? 'update' : 'create', 'keyword', $keyword);
            admin_redirect($view, $id > 0 ? 'saved' : 'created');
            break;

        case 'keyword.delete':
            $statement = $conn->prepare('DELETE FROM `cms_keywords` WHERE `id` = ?');
            $statement->bind_param('i', $id);
            $statement->execute();
            cms_audit('delete', 'keyword', (string) $id);
            admin_redirect($view, 'deleted');
            break;

        /* ---------------------------------------------------------- *
         * Backlinks
         * ---------------------------------------------------------- */
        case 'backlink.save':
            $sourceUrl = admin_post('source_url');
            if ($sourceUrl === '' || !filter_var($sourceUrl, FILTER_VALIDATE_URL)) {
                admin_redirect($view, 'invalid-input');
            }
            $sourceDomain = (string) (parse_url($sourceUrl, PHP_URL_HOST) ?: '');
            $target = admin_post('target_route', '/');
            $anchor = admin_post('anchor_text');
            $linkType = admin_enum('link_type', ['dofollow', 'nofollow', 'ugc', 'sponsored'], 'dofollow');
            $placement = admin_enum('placement', ['directory', 'guest post', 'blog', 'press', 'social', 'partner', 'citation', 'forum', 'other'], 'directory');
            $authority = admin_post_int('authority');
            $status = admin_enum('status', ['live', 'pending', 'lost', 'rejected'], 'live');
            $acquired = admin_post('acquired_on') !== '' ? admin_post('acquired_on') : null;
            $checked = admin_post('last_checked') !== '' ? admin_post('last_checked') : null;
            $notes = admin_post('notes');

            if ($id > 0) {
                $statement = $conn->prepare(
                    'UPDATE `cms_backlinks` SET `source_url` = ?, `source_domain` = ?, `target_route` = ?, `anchor_text` = ?,
                     `link_type` = ?, `placement` = ?, `authority` = ?, `status` = ?, `acquired_on` = ?, `last_checked` = ?, `notes` = ? WHERE `id` = ?'
                );
                $statement->bind_param('ssssssissssi', $sourceUrl, $sourceDomain, $target, $anchor, $linkType, $placement, $authority, $status, $acquired, $checked, $notes, $id);
            } else {
                $statement = $conn->prepare(
                    'INSERT INTO `cms_backlinks` (`source_url`, `source_domain`, `target_route`, `anchor_text`, `link_type`, `placement`, `authority`, `status`, `acquired_on`, `last_checked`, `notes`)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $statement->bind_param('ssssssissss', $sourceUrl, $sourceDomain, $target, $anchor, $linkType, $placement, $authority, $status, $acquired, $checked, $notes);
            }
            $statement->execute();
            cms_audit($id > 0 ? 'update' : 'create', 'backlink', $sourceDomain);
            admin_redirect($view, $id > 0 ? 'saved' : 'created');
            break;

        case 'backlink.delete':
            $statement = $conn->prepare('DELETE FROM `cms_backlinks` WHERE `id` = ?');
            $statement->bind_param('i', $id);
            $statement->execute();
            cms_audit('delete', 'backlink', (string) $id);
            admin_redirect($view, 'deleted');
            break;

        /* ---------------------------------------------------------- *
         * Redirects
         * ---------------------------------------------------------- */
        case 'redirect.save':
            $from = '/' . ltrim(admin_post('from_path'), '/');
            $to = admin_post('to_url');
            if ($from === '/' || $to === '') {
                admin_redirect($view, 'invalid-input');
            }
            $code = in_array(admin_post_int('status_code', 301), [301, 302, 307, 308, 410], true) ? admin_post_int('status_code', 301) : 301;
            $active = admin_post_bool('is_active');
            $notes = admin_post('notes');

            if ($id > 0) {
                $statement = $conn->prepare('UPDATE `cms_redirects` SET `from_path` = ?, `to_url` = ?, `status_code` = ?, `is_active` = ?, `notes` = ? WHERE `id` = ?');
                $statement->bind_param('ssiisi', $from, $to, $code, $active, $notes, $id);
            } else {
                $statement = $conn->prepare('INSERT INTO `cms_redirects` (`from_path`, `to_url`, `status_code`, `is_active`, `notes`) VALUES (?, ?, ?, ?, ?)');
                $statement->bind_param('ssiis', $from, $to, $code, $active, $notes);
            }
            $statement->execute();
            cms_audit($id > 0 ? 'update' : 'create', 'redirect', $from);
            admin_redirect($view, $id > 0 ? 'saved' : 'created');
            break;

        case 'redirect.delete':
            $statement = $conn->prepare('DELETE FROM `cms_redirects` WHERE `id` = ?');
            $statement->bind_param('i', $id);
            $statement->execute();
            cms_audit('delete', 'redirect', (string) $id);
            admin_redirect($view, 'deleted');
            break;

        /* ---------------------------------------------------------- *
         * Navigation links (site URLs)
         * ---------------------------------------------------------- */
        case 'nav.save':
            $label = admin_post('label');
            $url = admin_post('url');
            if ($label === '' || $url === '') {
                admin_redirect($view, 'invalid-input');
            }
            $menu = admin_enum('menu', ['primary', 'footer_explore', 'footer_platforms', 'footer_contact'], 'primary');
            $order = admin_post_int('sort_order');
            $target = admin_enum('link_target', ['_self', '_blank'], '_self');
            $rel = admin_post('rel');
            $visibility = admin_enum('visibility', ['all', 'desktop', 'mobile'], 'all');
            $isButton = admin_post_bool('is_button');
            $isActive = admin_post_bool('is_active');

            if ($id > 0) {
                $statement = $conn->prepare('UPDATE `cms_nav` SET `menu` = ?, `label` = ?, `url` = ?, `sort_order` = ?, `link_target` = ?, `rel` = ?, `visibility` = ?, `is_button` = ?, `is_active` = ? WHERE `id` = ?');
                $statement->bind_param('sssisssiii', $menu, $label, $url, $order, $target, $rel, $visibility, $isButton, $isActive, $id);
            } else {
                $statement = $conn->prepare('INSERT INTO `cms_nav` (`menu`, `label`, `url`, `sort_order`, `link_target`, `rel`, `visibility`, `is_button`, `is_active`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $statement->bind_param('sssisssii', $menu, $label, $url, $order, $target, $rel, $visibility, $isButton, $isActive);
            }
            $statement->execute();
            cms_audit($id > 0 ? 'update' : 'create', 'navigation', $label);
            admin_redirect($view, $id > 0 ? 'saved' : 'created');
            break;

        case 'nav.delete':
            $statement = $conn->prepare('DELETE FROM `cms_nav` WHERE `id` = ?');
            $statement->bind_param('i', $id);
            $statement->execute();
            cms_audit('delete', 'navigation', (string) $id);
            admin_redirect($view, 'deleted');
            break;

        /* ---------------------------------------------------------- *
         * Media library
         * ---------------------------------------------------------- */
        case 'media.upload':
            $stored = admin_store_upload('file', admin_post('alt_text'));
            cms_audit('create', 'media', $stored);
            admin_redirect($view, $stored !== '' ? 'created' : 'invalid-input');
            break;

        case 'media.delete':
            $media = admin_row('SELECT `file_path` FROM `cms_media` WHERE `id` = ? LIMIT 1', 'i', [$id]);
            if ($media) {
                $absolute = admin_root() . '/' . ltrim((string) $media['file_path'], '/');
                if (is_file($absolute) && strpos(realpath($absolute) ?: '', admin_root() . '/' . ADMIN_UPLOAD_DIR) === 0) {
                    unlink($absolute);
                }
            }
            $statement = $conn->prepare('DELETE FROM `cms_media` WHERE `id` = ?');
            $statement->bind_param('i', $id);
            $statement->execute();
            cms_audit('delete', 'media', (string) ($media['file_path'] ?? $id));
            admin_redirect($view, 'deleted');
            break;

        /* ---------------------------------------------------------- *
         * Products
         * ---------------------------------------------------------- */
        case 'product.save':
            $name = admin_post('name');
            $url = admin_post('url');
            $description = admin_post('description');

            /* The products table keeps the original tight column widths. */
            if (
                $name === '' || mb_strlen($name) > 25
                || $description === '' || mb_strlen($description) > 50
                || !filter_var($url, FILTER_VALIDATE_URL) || mb_strlen($url) > 50
            ) {
                admin_redirect($view, 'invalid-product', $id > 0 ? ['id' => $id] : []);
            }

            $existing = $id > 0 ? admin_row('SELECT * FROM `products` WHERE `id` = ? LIMIT 1', 'i', [$id]) : null;
            if ($id > 0 && !$existing) {
                admin_redirect($view, 'invalid-input');
            }

            $imageName = (string) ($existing['image'] ?? '');
            $upload = $_FILES['image'] ?? null;
            $hasUpload = $upload && ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

            if (!$hasUpload && $id === 0) {
                admin_redirect($view, 'invalid-product');
            }

            $newImagePath = '';
            if ($hasUpload) {
                $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/avif' => 'avif'];
                $mime = (string) (new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);
                if ($upload['error'] !== UPLOAD_ERR_OK || !isset($allowedTypes[$mime]) || (int) $upload['size'] > 3 * 1024 * 1024) {
                    admin_redirect($view, 'invalid-image', $id > 0 ? ['id' => $id] : []);
                }

                $newImageName = sprintf('product-%s-%s.%s', date('YmdHis'), bin2hex(random_bytes(4)), $allowedTypes[$mime]);
                $newImagePath = admin_root() . '/assets/productimages/' . $newImageName;
                if (!move_uploaded_file($upload['tmp_name'], $newImagePath)) {
                    admin_redirect($view, 'error', $id > 0 ? ['id' => $id] : []);
                }
                $imageName = $newImageName;
            }

            try {
                if ($id > 0) {
                    $statement = $conn->prepare('UPDATE `products` SET `name` = ?, `url` = ?, `description` = ?, `image` = ? WHERE `id` = ?');
                    $statement->bind_param('ssssi', $name, $url, $description, $imageName, $id);
                } else {
                    $statement = $conn->prepare('INSERT INTO `products` (`name`, `url`, `description`, `image`) VALUES (?, ?, ?, ?)');
                    $statement->bind_param('ssss', $name, $url, $description, $imageName);
                }
                $statement->execute();
            } catch (Throwable $exception) {
                /* Never leave an orphaned upload behind when the write fails. */
                if ($newImagePath !== '' && is_file($newImagePath)) {
                    unlink($newImagePath);
                }
                throw $exception;
            }

            /* Replace succeeded — remove the file the product no longer uses. */
            if ($hasUpload && $existing) {
                $oldDirect = admin_root() . '/assets/productimages/' . basename((string) $existing['image']);
                $oldLegacy = admin_root() . '/assets/productimages/' . basename((string) $existing['name'] . (string) $existing['image']);
                $oldPath = is_file($oldDirect) ? $oldDirect : $oldLegacy;
                if (is_file($oldPath) && $oldPath !== $newImagePath) {
                    unlink($oldPath);
                }
            }

            cms_audit($id > 0 ? 'update' : 'create', 'product', $name);
            admin_redirect($view, $id > 0 ? 'product-updated' : 'product-added');
            break;

        case 'product.delete':
            $existing = admin_row('SELECT `name`, `image` FROM `products` WHERE `id` = ? LIMIT 1', 'i', [$id]);
            $statement = $conn->prepare('DELETE FROM `products` WHERE `id` = ?');
            $statement->bind_param('i', $id);
            $statement->execute();

            if ($existing) {
                $direct = admin_root() . '/assets/productimages/' . basename((string) $existing['image']);
                $legacy = admin_root() . '/assets/productimages/' . basename((string) $existing['name'] . (string) $existing['image']);
                $imagePath = is_file($direct) ? $direct : $legacy;
                if (is_file($imagePath)) {
                    unlink($imagePath);
                }
            }

            cms_audit('delete', 'product', (string) ($existing['name'] ?? $id));
            admin_redirect($view, 'product-deleted');
            break;

        /* ---------------------------------------------------------- *
         * Global settings
         * ---------------------------------------------------------- */
        case 'settings.save':
            $values = $_POST['settings'] ?? [];
            if (!is_array($values)) {
                admin_redirect($view, 'invalid-input');
            }
            $known = cms_setting_defaults();
            $statement = $conn->prepare('INSERT INTO `cms_settings` (`setting_key`, `setting_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)');
            foreach ($values as $key => $value) {
                $key = (string) $key;
                if (!array_key_exists($key, $known)) {
                    continue;
                }
                $value = is_string($value) ? trim($value) : '';
                $statement->bind_param('ss', $key, $value);
                $statement->execute();
            }
            cms_audit('update', 'settings', admin_post('settings_group', 'all'));
            admin_redirect($view, 'saved', ['tab' => admin_post('tab')]);
            break;

        /* ---------------------------------------------------------- *
         * Administrator accounts
         * ---------------------------------------------------------- */
        case 'account.password':
            $current = (string) ($_POST['current_password'] ?? '');
            $next = (string) ($_POST['new_password'] ?? '');
            $confirm = (string) ($_POST['confirm_password'] ?? '');

            if (mb_strlen($next) < 10 || $next !== $confirm) {
                admin_redirect($view, 'password-mismatch');
            }

            $account = admin_row('SELECT `id`, `password_hash` FROM `cms_admins` WHERE `mobile` = ? LIMIT 1', 's', [adminMobile()]);
            $storedHash = (string) ($account['password_hash'] ?? (getenv('UTS_ADMIN_PASSWORD_HASH') ?: '$2y$12$gO86NL7JQ65mNYiPBjuTnO.9SG65MO1tXir2h6.T9kdSl/WWeHcAu'));
            if (!password_verify($current, $storedHash)) {
                admin_redirect($view, 'password-mismatch');
            }

            $newHash = password_hash($next, PASSWORD_BCRYPT, ['cost' => 12]);
            if ($account) {
                $statement = $conn->prepare('UPDATE `cms_admins` SET `password_hash` = ? WHERE `id` = ?');
                $statement->bind_param('si', $newHash, $account['id']);
            } else {
                $name = adminName();
                $mobile = adminMobile();
                $role = 'owner';
                $statement = $conn->prepare('INSERT INTO `cms_admins` (`name`, `mobile`, `password_hash`, `role`) VALUES (?, ?, ?, ?)');
                $statement->bind_param('ssss', $name, $mobile, $newHash, $role);
            }
            $statement->execute();
            cms_audit('update', 'account', adminMobile(), 'Password changed');
            admin_redirect($view, 'password-changed');
            break;

        case 'admin.save':
            $name = admin_post('name', 'Administrator');
            $mobile = preg_replace('/\D/', '', admin_post('mobile')) ?? '';
            $password = (string) ($_POST['password'] ?? '');
            $role = admin_enum('role', ['owner', 'editor'], 'editor');
            $isActive = admin_post_bool('is_active');

            if ($mobile === '' || ($id === 0 && mb_strlen($password) < 10)) {
                admin_redirect($view, 'invalid-input');
            }

            if ($id > 0) {
                if ($password !== '') {
                    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                    $statement = $conn->prepare('UPDATE `cms_admins` SET `name` = ?, `mobile` = ?, `role` = ?, `is_active` = ?, `password_hash` = ? WHERE `id` = ?');
                    $statement->bind_param('sssisi', $name, $mobile, $role, $isActive, $hash, $id);
                } else {
                    $statement = $conn->prepare('UPDATE `cms_admins` SET `name` = ?, `mobile` = ?, `role` = ?, `is_active` = ? WHERE `id` = ?');
                    $statement->bind_param('sssii', $name, $mobile, $role, $isActive, $id);
                }
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $statement = $conn->prepare('INSERT INTO `cms_admins` (`name`, `mobile`, `password_hash`, `role`, `is_active`) VALUES (?, ?, ?, ?, ?)');
                $statement->bind_param('ssssi', $name, $mobile, $hash, $role, $isActive);
            }
            $statement->execute();
            cms_audit($id > 0 ? 'update' : 'create', 'admin', $mobile);
            admin_redirect($view, $id > 0 ? 'saved' : 'created');
            break;

        case 'admin.delete':
            if ($id === adminId()) {
                admin_redirect($view, 'invalid-input');
            }
            $statement = $conn->prepare('DELETE FROM `cms_admins` WHERE `id` = ?');
            $statement->bind_param('i', $id);
            $statement->execute();
            cms_audit('delete', 'admin', (string) $id);
            admin_redirect($view, 'deleted');
            break;

        /* ---------------------------------------------------------- *
         * Maintenance
         * ---------------------------------------------------------- */
        case 'system.resync':
            $report = cms_install(true);
            cms_audit('update', 'system', 'resync', 'tables: ' . $report['created'] . ', keys: ' . $report['seeded']);
            admin_redirect($view, $report['errors'] === [] ? 'installed' : 'error');
            break;

        default:
            admin_redirect($view, 'invalid');
    }
} catch (mysqli_sql_exception $exception) {
    error_log('UTS admin action failed: ' . $exception->getMessage());
    admin_redirect($view, $exception->getCode() === 1062 ? 'duplicate' : 'error');
} catch (Throwable $exception) {
    error_log('UTS admin action failed: ' . $exception->getMessage());
    admin_redirect($view, 'error');
}
