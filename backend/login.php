<?php
if(isset($_POST['mobile'])&&isset($_POST['passcode'])){
    $mobile = $_POST['mobile'];
    $passcode = $_POST['passcode'];
    if($mobile == "9818059661" && $passcode == "Blackbox243@") {
        $cookie_name = "user";
        $cookie_value = "Admin";
        setcookie($cookie_name, $cookie_value, time() + (300), "/");
        header('Location: ../admin.php');
    }
    else {
        header('Location: ../login.php');
    }
}
else {
    header('Location: ../login.php');
}