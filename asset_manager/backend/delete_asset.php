<?php
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    exit("Unauthorized");
}

include '_conn.php';

$id = $_POST['id'] ?? 0;
if (!$id) {
    http_response_code(400);
    exit("Invalid asset ID");
}

$stmt = $conn->prepare("DELETE FROM assets WHERE id = ?");
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    echo "Asset deleted successfully.";
} else {
    http_response_code(500);
    echo "Failed to delete asset.";
}
?>
