<?php
$host = "localhost";
// $db = "asset-manager";  // renamed database
// $user = "root";
// $pass = ""; 

// Prod
$db = "u770637491_saajdecors";
$user = "u770637491_saajdecors";
$pass = "Cherry@125"; 
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
