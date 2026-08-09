<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = "localhost";
// $db = "asset-manager";  // renamed database
// $user = "root";
// $pass = ""; 

// Prod
$db = "u770637491_saajdecors";
$user = "u770637491_saajdecors";
$pass = "Cherry@125";
$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset('utf8mb4');
?>
