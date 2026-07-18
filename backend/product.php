<?php
    $name = $_POST['name'];
    $description = $_POST['description'];
    $url = $_POST['url'];
    $image = $_FILES['image']['name'];
    $folder = "../assets/productimages/".$name.$image;
    if (isset($image)) {
        // Check if file already exists
        if (file_exists( $folder)) {
            echo "Sorry, file already exists. Try with different name";
        }
        else {
            include('_conn.php');
            $sql = "INSERT INTO `products`(`id`, `name`, `url`, `description`, `image`) VALUES ('','$name','$url','$description','$image')";
            if ($conn->query($sql) === TRUE) {
                $imageTmp = $_FILES['image']['tmp_name'];
                move_uploaded_file($imageTmp,$folder);
                header('Location: ../admin.php');
            }
            else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }
        
    }
    else {
        echo "Sorry, file is not uploading";
    }