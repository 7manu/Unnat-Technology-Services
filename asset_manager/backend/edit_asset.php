<?php
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    exit("Unauthorized");
}

include '_conn.php';

$id = $_POST['id'] ?? 0;
$name = $_POST['name'] ?? '';
$value = $_POST['value_per_month'] ?? '';
$start_date = $_POST['start_date'] ?? '';
$details = $_POST['details'] ?? '';

if (!$id || !$name || !$value || !$start_date) {
    http_response_code(400);
    exit("Missing required fields.");
}

$stmt = $conn->prepare("UPDATE assets SET name = ?, value_per_month = ?, start_date = ?, details = ? WHERE id = ?");
$stmt->bind_param("sdssi", $name, $value, $start_date, $details, $id);

if ($stmt->execute()) {
    echo "Asset updated successfully.";
} else {
    http_response_code(500);
    echo "Failed to update asset.";
}
?>
