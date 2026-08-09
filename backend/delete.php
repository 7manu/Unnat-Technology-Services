<?php
declare(strict_types=1);

require __DIR__ . '/_admin_auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyAdminCsrf()) {
    header('Location: ../admin.php?view=products&status=invalid', true, 303);
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: ../admin.php?view=products&status=invalid', true, 303);
    exit;
}

try {
    require __DIR__ . '/_conn.php';
    $find = $conn->prepare('SELECT `name`, `image` FROM `products` WHERE `id` = ?');
    $find->bind_param('i', $id);
    $find->execute();
    $find->bind_result($productName, $productImage);
    $hasProduct = $find->fetch();
    $find->close();

    $statement = $conn->prepare('DELETE FROM `products` WHERE `id` = ?');
    $statement->bind_param('i', $id);
    $statement->execute();

    if ($hasProduct) {
        $directPath = __DIR__ . '/../assets/productimages/' . basename((string) $productImage);
        $legacyPath = __DIR__ . '/../assets/productimages/' . basename((string) $productName . (string) $productImage);
        $imagePath = is_file($directPath) ? $directPath : $legacyPath;
        if (is_file($imagePath)) {
            unlink($imagePath);
        }
    }

    header('Location: ../admin.php?view=products&status=product-deleted', true, 303);
} catch (Throwable $exception) {
    error_log('UTS product deletion failed: ' . $exception->getMessage());
    header('Location: ../admin.php?view=products&status=error', true, 303);
}
exit;
