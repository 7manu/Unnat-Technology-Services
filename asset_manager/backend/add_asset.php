<?php
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit();
}

include '_conn.php';

// Get form data
$name = $_POST['name'] ?? '';
$value = $_POST['value_per_month'] ?? '';
$start_date = $_POST['start_date'] ?? '';
$details = $_POST['details'] ?? '';

// Validate
if (empty($name) || empty($value) || empty($start_date)) {
    http_response_code(400);
    echo "Please fill all required fields.";
    exit();
}

// Insert into DB
$stmt = $conn->prepare("INSERT INTO assets (name, value_per_month, start_date, details) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sdss", $name, $value, $start_date, $details);

if ($stmt->execute()) {
    echo "Asset added successfully.";
} else {
    http_response_code(500);
    echo "Error: " . $conn->error;
}
?>
