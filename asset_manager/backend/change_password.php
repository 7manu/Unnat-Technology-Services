<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

include '_conn.php';

$uid = $_SESSION['user']['id'];
$current = $_POST['current_password'] ?? '';
$new = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($new !== $confirm) {
    header("Location: ../change_password.php?msg=New passwords do not match");
    exit();
}

// Fetch current password hash
$stmt = $conn->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || !password_verify($current, $user['password_hash'])) {
    header("Location: ../change_password.php?msg=Incorrect current password");
    exit();
}

// Update to new password
$new_hash = password_hash($new, PASSWORD_DEFAULT);
$update = $conn->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
$update->bind_param("si", $new_hash, $uid);

if ($update->execute()) {
    header("Location: ../change_password.php?msg=Password updated successfully");
} else {
    header("Location: ../change_password.php?msg=Error updating password");
}
?>
