<?php
if (isset($_POST['name'])) {
    $name = $_POST['name'];
    $mobile = $_POST['mobile'];
    $email =  $_POST['email'];
    $question =  $_POST['question'];
    $details =  $_POST['details'];
    include '_conn.php';
    $sql = "INSERT INTO `query`(`id`, `name`, `mobile`, `email`, `question`, `details`, `status`) VALUES ('','$name','$mobile','$email','$question','$details', '0')";
    if ($conn->query($sql) === TRUE) {
        header('Location: ../index.html');
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} else {
    header("location: ../contact.html");
}
