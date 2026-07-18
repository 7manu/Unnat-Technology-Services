<?php
//Dev
// $servername = "localhost";
// $username = "root";
// $connectionpassword = "";
// $dbname = "uts";

// Production
$servername = "localhost";
$username = "u770637491_uts";
$connectionpassword = "Unnat@125";
$dbname = "u770637491_uts";

// Create connection
$conn = new mysqli($servername, $username, $connectionpassword, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}