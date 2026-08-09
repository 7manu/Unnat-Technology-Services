<?php
declare(strict_types=1);

require __DIR__ . '/_admin_auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyAdminCsrf()) {
    header('Location: ../admin.php?view=inquiries&status=invalid', true, 303);
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: ../admin.php?view=inquiries&status=invalid', true, 303);
    exit;
}

try {
    require __DIR__ . '/_conn.php';
    $statement = $conn->prepare('DELETE FROM `query` WHERE `id` = ?');
    $statement->bind_param('i', $id);
    $statement->execute();
    header('Location: ../admin.php?view=inquiries&status=query-deleted', true, 303);
} catch (Throwable $exception) {
    error_log('UTS query deletion failed: ' . $exception->getMessage());
    header('Location: ../admin.php?view=inquiries&status=error', true, 303);
}
exit;
