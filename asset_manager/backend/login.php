<?php
session_start();
include '_conn.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $conn->prepare("SELECT id, password_hash FROM admin_users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password_hash'])) {
        $_SESSION['user'] = ['id' => $user['id'], 'username' => $username];
        header("Location: ../dashboard.php");
        exit();
    }
}

header("Location: ../index.php?error=Invalid credentials");
exit();
?>