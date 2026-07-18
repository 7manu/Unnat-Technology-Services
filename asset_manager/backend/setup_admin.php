<?php
include '_conn.php';

// Setup admin user (run once then delete this file)
$username = 'Admin';
$password = 'Cherry@123'; // Change this as needed
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO admin_users (username, password_hash) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $hash);

if ($stmt->execute()) {
    echo "Admin user created successfully.";
} else {
    echo "Error: " . $conn->error;
}
?>
