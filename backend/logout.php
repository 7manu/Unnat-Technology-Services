<?php
setcookie("user", "", time() - 3600, "/");
header("location: ../login.php");