<?php
declare(strict_types=1);

require __DIR__ . '/_admin_auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyAdminCsrf()) {
    header('Location: ../admin.php?view=products&status=invalid', true, 303);
    exit;
}

$name = trim((string) ($_POST['name'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));
$url = trim((string) ($_POST['url'] ?? ''));
$upload = $_FILES['image'] ?? null;

if (
    $name === '' || mb_strlen($name) > 25
    || $description === '' || mb_strlen($description) > 50
    || !filter_var($url, FILTER_VALIDATE_URL) || mb_strlen($url) > 50
    || !$upload || $upload['error'] !== UPLOAD_ERR_OK
) {
    header('Location: ../admin.php?view=products&status=invalid-product', true, 303);
    exit;
}

$allowedTypes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/avif' => 'avif',
];

$mime = (new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);
if (!isset($allowedTypes[$mime]) || (int) $upload['size'] > 3 * 1024 * 1024) {
    header('Location: ../admin.php?view=products&status=invalid-image', true, 303);
    exit;
}

$imageName = sprintf('product-%s-%s.%s', date('YmdHis'), bin2hex(random_bytes(4)), $allowedTypes[$mime]);
$destination = __DIR__ . '/../assets/productimages/' . $imageName;

try {
    if (!move_uploaded_file($upload['tmp_name'], $destination)) {
        throw new RuntimeException('Uploaded product image could not be moved.');
    }

    require __DIR__ . '/_conn.php';
    $statement = $conn->prepare('INSERT INTO `products` (`name`, `url`, `description`, `image`) VALUES (?, ?, ?, ?)');
    $statement->bind_param('ssss', $name, $url, $description, $imageName);
    $statement->execute();
    header('Location: ../admin.php?view=products&status=product-added', true, 303);
} catch (Throwable $exception) {
    if (is_file($destination)) {
        unlink($destination);
    }
    error_log('UTS product creation failed: ' . $exception->getMessage());
    header('Location: ../admin.php?view=products&status=error', true, 303);
}
exit;
