<?php
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    include '_conn.php';
    $sql = "UPDATE `query` SET `status`='1' WHERE `id` = '$id'";
    if ($conn->query($sql) === TRUE) {
        header('Location: ../admin.php');
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} else {
    header("location: ../admin.php");
}
