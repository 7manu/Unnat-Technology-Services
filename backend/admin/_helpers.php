<?php
/** Shared helpers for the admin panel views and the action controller. */
declare(strict_types=1);

require_once __DIR__ . '/../_admin_auth.php';
require_once __DIR__ . '/../cms.php';

const ADMIN_UPLOAD_DIR = 'assets/uploads';
const ADMIN_MAX_UPLOAD_BYTES = 5 * 1024 * 1024;

function admin_root(): string
{
    return dirname(__DIR__, 2);
}

function admin_redirect(string $view, string $status = '', array $extra = []): void
{
    $query = ['view' => $view];
    if ($status !== '') {
        $query['status'] = $status;
    }
    $query += $extra;

    header('Location: ../admin.php?' . http_build_query($query), true, 303);
    exit;
}

function admin_post(string $key, string $default = ''): string
{
    $value = $_POST[$key] ?? $default;

    return is_string($value) ? trim($value) : $default;
}

function admin_post_int(string $key, int $default = 0): int
{
    return isset($_POST[$key]) && is_numeric($_POST[$key]) ? (int) $_POST[$key] : $default;
}

function admin_post_bool(string $key): int
{
    return isset($_POST[$key]) && $_POST[$key] !== '0' ? 1 : 0;
}

/** Restricts free-text choices (status, robots directives …) to known values. */
function admin_enum(string $key, array $allowed, string $default): string
{
    $value = admin_post($key, $default);

    return in_array($value, $allowed, true) ? $value : $default;
}

/**
 * Stores an uploaded image under assets/uploads and registers it in cms_media.
 *
 * @return string Web path of the stored file, or '' when nothing was uploaded.
 * @throws RuntimeException when the file is present but rejected.
 */
function admin_store_upload(string $field, string $altText = ''): string
{
    $upload = $_FILES[$field] ?? null;
    if (!$upload || ($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    if ($upload['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The file could not be uploaded.');
    }

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/avif' => 'avif',
        'image/gif' => 'gif',
        'image/svg+xml' => 'svg',
    ];

    $mime = (string) (new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);
    if (!isset($allowedTypes[$mime])) {
        throw new RuntimeException('Only JPG, PNG, WebP, AVIF, GIF or SVG images are allowed.');
    }
    if ((int) $upload['size'] > ADMIN_MAX_UPLOAD_BYTES) {
        throw new RuntimeException('Images must be smaller than 5 MB.');
    }

    $directory = admin_root() . '/' . ADMIN_UPLOAD_DIR;
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('The uploads folder could not be created.');
    }

    $baseName = cms_slugify(pathinfo((string) $upload['name'], PATHINFO_FILENAME), 'image');
    $fileName = sprintf('%s-%s.%s', substr($baseName, 0, 60), bin2hex(random_bytes(3)), $allowedTypes[$mime]);
    $webPath = ADMIN_UPLOAD_DIR . '/' . $fileName;

    if (!move_uploaded_file($upload['tmp_name'], $directory . '/' . $fileName)) {
        throw new RuntimeException('The uploaded file could not be saved.');
    }

    $conn = cms_db();
    if ($conn instanceof mysqli) {
        try {
            $size = (int) $upload['size'];
            $statement = $conn->prepare('INSERT INTO `cms_media` (`file_name`, `file_path`, `mime_type`, `file_size`, `alt_text`) VALUES (?, ?, ?, ?, ?)');
            $statement->bind_param('sssis', $fileName, $webPath, $mime, $size, $altText);
            $statement->execute();
        } catch (Throwable $exception) {
            error_log('UTS media registration failed: ' . $exception->getMessage());
        }
    }

    return $webPath;
}

/**
 * Returns the image to store for a field that accepts either an upload or a
 * manually typed path, falling back to the existing value.
 */
function admin_resolve_image(string $uploadField, string $pathField, string $existing = ''): string
{
    $uploaded = admin_store_upload($uploadField);
    if ($uploaded !== '') {
        return $uploaded;
    }

    $typed = admin_post($pathField);

    return $typed !== '' ? $typed : $existing;
}

function admin_status_messages(): array
{
    return [
        'saved' => 'Changes saved successfully.',
        'created' => 'The item was created successfully.',
        'deleted' => 'The item was deleted.',
        'installed' => 'Database tables checked and content keys synchronised.',
        'password-changed' => 'The password was updated. Use it the next time you sign in.',
        'product-added' => 'Product added successfully.',
        'product-deleted' => 'Product deleted successfully.',
        'query-completed' => 'Inquiry marked as completed.',
        'query-deleted' => 'Inquiry deleted successfully.',
        'invalid' => 'That action could not be verified. Please try again.',
        'invalid-product' => 'Please check all product fields and use a valid URL.',
        'invalid-image' => 'Upload a JPG, PNG, WebP or AVIF image smaller than 3 MB.',
        'invalid-input' => 'Some fields were missing or invalid. Nothing was saved.',
        'duplicate' => 'That key, slug or route is already in use.',
        'password-mismatch' => 'The current password was incorrect, or the new passwords did not match.',
        'error' => 'The action could not be completed. Check the server logs or database connection.',
    ];
}

function admin_is_error_status(string $status): bool
{
    return in_array($status, ['invalid', 'invalid-product', 'invalid-image', 'invalid-input', 'duplicate', 'password-mismatch', 'error'], true);
}

/** Renders a <select> populated from an associative array. */
function admin_select(string $name, array $options, string $current, string $id = ''): string
{
    $html = '<select name="' . e($name) . '"' . ($id !== '' ? ' id="' . e($id) . '"' : '') . '>';
    foreach ($options as $value => $label) {
        $html .= '<option value="' . e((string) $value) . '"' . ((string) $value === $current ? ' selected' : '') . '>' . e((string) $label) . '</option>';
    }

    return $html . '</select>';
}

/** Routes that can be targeted by keywords, backlinks and SEO records. */
function admin_route_options(): array
{
    $routes = cms_seo_core_routes();
    foreach (cms_pages(false) as $page) {
        $routes['/' . cms_page_url($page)] = 'Page: ' . $page['title'];
    }
    foreach (cms_posts(false) as $post) {
        $routes['/' . cms_post_url($post)] = 'Article: ' . $post['title'];
    }

    return $routes;
}

function admin_robots_options(): array
{
    return ['index' => 'index (allow in search results)', 'noindex' => 'noindex (hide from search results)'];
}

function admin_follow_options(): array
{
    return ['follow' => 'follow (pass link value)', 'nofollow' => 'nofollow (do not pass link value)'];
}

function admin_changefreq_options(): array
{
    return [
        'always' => 'always', 'hourly' => 'hourly', 'daily' => 'daily', 'weekly' => 'weekly',
        'monthly' => 'monthly', 'yearly' => 'yearly', 'never' => 'never',
    ];
}

function admin_priority_options(): array
{
    return ['1.0' => '1.0', '0.9' => '0.9', '0.8' => '0.8', '0.7' => '0.7', '0.6' => '0.6', '0.5' => '0.5', '0.4' => '0.4', '0.3' => '0.3'];
}

function admin_table_count(string $table): int
{
    $conn = cms_db();
    if (!$conn instanceof mysqli) {
        return 0;
    }

    try {
        $result = $conn->query('SELECT COUNT(*) AS `total` FROM `' . $table . '`');

        return (int) ($result->fetch_assoc()['total'] ?? 0);
    } catch (Throwable $exception) {
        return 0;
    }
}

function admin_rows(string $sql): array
{
    $conn = cms_db();
    if (!$conn instanceof mysqli) {
        return [];
    }

    $rows = [];
    try {
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    } catch (Throwable $exception) {
        error_log('UTS admin query failed: ' . $exception->getMessage());
    }

    return $rows;
}

function admin_row(string $sql, string $types = '', array $params = []): ?array
{
    $conn = cms_db();
    if (!$conn instanceof mysqli) {
        return null;
    }

    try {
        $statement = $conn->prepare($sql);
        if ($types !== '') {
            $statement->bind_param($types, ...$params);
        }
        $statement->execute();

        return $statement->get_result()->fetch_assoc() ?: null;
    } catch (Throwable $exception) {
        error_log('UTS admin row query failed: ' . $exception->getMessage());

        return null;
    }
}
