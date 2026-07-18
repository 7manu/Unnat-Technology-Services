<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
    <link rel="shortcut icon" href="assets/images/uts-logo-removebg-removebg-preview.png" type="image/x-icon">
    <meta name="description" content="">


    <title>Admin</title>
    <link rel="stylesheet" href="assets/Material-Design-Icons/css/material.css">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap-grid.min.css">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap-reboot.min.css">
    <link rel="stylesheet" href="assets/animatecss/animate.css">
    <link rel="stylesheet" href="assets/chatbutton/floating-wpp.css">
    <link rel="stylesheet" href="assets/popup-overlay-plugin/style.css">
    <link rel="stylesheet" href="assets/dropdown/css/style.css">
    <link rel="stylesheet" href="assets/datatables/vanilla-dataTables.min.css">
    <link rel="stylesheet" href="assets/theme/css/style.css">
    <link rel="preload" href="https://fonts.googleapis.com/css?family=Playfair+Display:400,500,600,700,800,900,400i,500i,600i,700i,800i,900i&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Playfair+Display:400,500,600,700,800,900,400i,500i,600i,700i,800i,900i&display=swap">
    </noscript>
    <link rel="preload" href="https://fonts.googleapis.com/css?family=Oswald:200,300,400,500,600,700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Oswald:200,300,400,500,600,700&display=swap">
    </noscript>
    <link rel="preload" as="style" href="assets/mobirise/css/mbr-additional.css?v=vbvbsa">
    <link rel="stylesheet" href="assets/mobirise/css/mbr-additional.css?v=vbvbsa" type="text/css">





    <meta name="theme-color" content="#edefeb">
    <link rel="manifest" href="manifest.json">
    <script src="sw-connect.js"></script>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="apple-touch-startup-image" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)" href="assets/images/apple-launch-640x1136.png">
    <link rel="apple-touch-startup-image" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)" href="assets/images/apple-launch-750x1334.png">
    <link rel="apple-touch-startup-image" media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)" href="assets/images/apple-launch-1242x2208.png">
    <link rel="apple-touch-startup-image" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)" href="assets/images/apple-launch-1125x2436.png">
    <link rel="apple-touch-startup-image" media="(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)" href="assets/images/apple-launch-1536x2048.png">
    <link rel="apple-touch-startup-image" media="(device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)" href="assets/images/apple-launch-1668x2224.png">
    <link rel="apple-touch-startup-image" media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)" href="assets/images/apple-launch-2048x2732.png">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="">
    <link rel="apple-touch-icon" href="apple-touch-icon.png">
</head>

<body>
    <?php
    if (!isset($_COOKIE['user'])) {
        header("location: login.php");
    }
    include 'backend/_conn.php';
    ?>
    <section data-bs-version="5.1" class="menu menu5 cid-ufLHqODcHy" once="menu" id="menu05-10" data-sortbtn="btn-primary">


        <nav class="navbar navbar-dropdown navbar-fixed-top navbar-expand-lg">
            <div class="container">
                <div class="navbar-brand">
                    <span class="navbar-logo">
                        <a href="admin.php">
                            <img src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" alt="admin" style="height: 4.3rem;" loading="lazy" class="lazyload" data-src="assets/images/uts-logo-removebg-removebg-preview.png">
                        </a>
                    </span>
                    <span class="navbar-caption-wrap"><a class="navbar-caption text-black text-primary display-4" href="admin.php">UNNAT TECHNOLOGY SERVICES</a></span>
                </div>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-bs-toggle="collapse" data-target="#navbarSupportedContent" data-bs-target="#navbarSupportedContent" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                    <div class="hamburger">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">

                    <div class="navbar-buttons mbr-section-btn"><a class="btn btn-info display-4" href="#" data-toggle="modal" data-bs-toggle="modal" data-target="#mbr-popup-12" data-bs-target="#mbr-popup-12">ADD PRODUCT</a> <a class="btn btn-danger display-4" href="backend/logout.php">LOGOUT</a></div>
                </div>
            </div>
        </nav>
    </section>

    <section data-bs-version="5.1" class="table1 directm4_table1 section-table cid-ufLKmjcDCX" id="table1-14" data-sortbtn="btn-primary">









        <div class="container-fluid">
            <div class="media-container-row align-center">
                <div class="col-12 col-md-12">
                    <h2 class="mbr-section-title mbr-fonts-style mbr-black pb-3 display-5">
                        All Requests</h2>

                    <div class="table-wrapper mt-5">
                        <div class="container">

                        </div>
                        <div class="container scroll">
                            <table class="table mx-auto isSearch" cellspacing="0" data-empty="No matching records found">
                                <thead>
                                    <tr class="table-heads">
                                        <th class="head-item mbr-fonts-style mbr-bold display-4">Name</th>
                                        <th class="head-item mbr-fonts-style mbr-bold display-4">Mobile</th>
                                        <th class="head-item mbr-fonts-style mbr-bold display-4">Email</th>
                                        <th class="head-item mbr-fonts-style mbr-bold display-4">Questions</th>
                                        <th class="head-item mbr-fonts-style mbr-bold display-4">Details</th>
                                        <th class="head-item mbr-fonts-style mbr-bold display-4">Status</th>
                                        <th class="head-item mbr-fonts-style mbr-bold display-4">Delete</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $sql = "SELECT * FROM `query` ORDER BY `id` DESC";
                                        $result = $conn->query($sql);
                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                echo '  <tr>
                                            <td class="body-item mbr-fonts-style display-4">' . $row["name"] . '
                                            </td>
                                            <td class="body-item mbr-fonts-style display-4">' . $row["mobile"] . '</td>
                                            <td class="body-item mbr-fonts-style display-4">' . $row["email"] . '
                                            </td>
                                            <td class="body-item mbr-fonts-style display-4">' . $row["question"] . '</td>
                                            <td class="body-item mbr-fonts-style display-4">' . $row["details"] . '</td>
                                            <td class="body-item mbr-fonts-style display-4"> ';
                                                if ($row["status"] == 0) {
                                                    echo '<a href="backend/confirmQuery.php?id=' . $row["id"] . '" class="btn btn-primary">✔️</a>';
                                                } else if ($row["status"] == 1) {
                                                    echo "Completed";
                                                }
                                                echo '</td>
                                             <td class="body-item mbr-fonts-style display-4"> ';
                                                    echo '<a href="backend/deleteQuery.php?id=' . $row["id"] . '" class="btn btn-primary">❌</a>';
                                                echo '</td>
                                        </tr>';
                                            }
                                        } else {
                                            echo '<p>0 RESULTS</p>';
                                        }
                                    ?>
                                </tbody>
                            </table>
                            <div class="container table-info-container">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <section data-bs-version="5.1" class="table1 directm4_table1 section-table cid-ufLQg7gx59" id="list02-15" data-sortbtn="btn-primary">









        <div class="container-fluid">
            <div class="media-container-row align-center">
                <div class="col-12 col-md-12">
                    <h2 class="mbr-section-title mbr-fonts-style mbr-black pb-3 display-5">
                        Our Products</h2>

                    <div class="table-wrapper mt-5">
                        <div class="container">

                        </div>
                        <div class="container scroll">
                            <table class="table mx-auto isSearch" cellspacing="0" data-empty="No matching records found">
                                <thead>
                                    <tr class="table-heads">




                                        <th class="head-item mbr-fonts-style mbr-bold display-4">Name</th>
                                        <th class="head-item mbr-fonts-style mbr-bold display-4">Description</th>
                                        <th class="head-item mbr-fonts-style mbr-bold display-4">URL</th>
                                        <th class="head-item mbr-fonts-style mbr-bold display-4">Image</th>
                                        <th class="head-item mbr-fonts-style mbr-bold display-4">Status</th>
                                    </tr>
                                </thead>

                                <tbody>



                                        <?php
                                             $sql = "SELECT * FROM `products` ORDER BY `id` DESC";
                                             $result = $conn->query($sql);
                                             if ($result->num_rows > 0) {
                                                 while ($row = $result->fetch_assoc()) {
                                                     echo ' <tr>
                                        <td class="body-item mbr-fonts-style display-4">' . $row["name"] . '
                                        </td>
                                        <td class="body-item mbr-fonts-style display-4">' . $row["description"] . '</td>
                                        <td class="body-item mbr-fonts-style display-4">' . $row["url"] . '
                                        </td>
                                        <td class="body-item mbr-fonts-style display-4"><img height="100px" width="50px" src="assets/productimages/'.$row["name"]. $row["image"] . '" style="width: 77%; float: none;"></td>
                                        <td class="body-item mbr-fonts-style display-4"><a type="submit" class="btn btn-primary" href="backend/delete.php?id=' . $row["id"] . '">❌</a></td>
                                    </tr>';
                                                 }
                                             } else {
                                                 echo '<p>0 RESULTS</p>';
                                             }
                                        ?>
                                </tbody>
                            </table>
                            <div class="container table-info-container">



                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <div class="modal mbr-popup cid-ufLIHVEmDr fade" tabindex="-1" role="dialog" data-overlay-color="#000000" data-overlay-opacity="0.8" id="mbr-popup-12" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="container position-static margin-center-pos">
                    <div class="modal-header pb-0">
                        <h5 class="modal-title mbr-fonts-style display-5">Add Product</h5>
                        <button type="button" class="close d-flex" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="23" height="23" viewBox="0 0 23 23" fill="currentColor">
                                <path d="M13.4 12l10.3 10.3-1.4 1.4L12 13.4 1.7 23.7.3 22.3 10.6 12 .3 1.7 1.7.3 12 10.6 22.3.3l1.4 1.4L13.4 12z">
                                </path>
                            </svg>
                        </button>
                    </div>

                    <div class="modal-body">
                        <p class="mbr-text mbr-fonts-style display-7">
                            Enter The Details</p>

                        <div>
                            <div class="form-wrapper">
                                <form enctype="multipart/form-data"  action="backend/product.php" method="POST" class="mbr-form form-with-styler">
                                    <div class="container">
                                        <div class="row form-content">
                                            <div class="col-12 form-group" data-for="name">
                                                <input maxlength="25" type="text" name="name" placeholder="Name" data-form-field="Name" required="required" class="form-control display-7" id="name-mbr-popup-12">
                                            </div>
                                            <div class="col-12 form-group" data-for="Image">
                                                <input maxlength="50" type="file" name="image" placeholder=" Image" data-form-field="Last Name" required="required" class="form-control display-7" id="image-mbr-popup-12">
                                            </div>
                                            <div class="form-group col-12" data-for="description">
                                                <input maxlength="50" type="text" name="description" placeholder="Description" data-form-field="Email" required="required" class="form-control display-7" id="description-mbr-popup-12">
                                            </div>
                                            <div class="form-group col-12" data-for="url">
                                                <input maxlength="50" type="text" name="url" placeholder="URL" data-form-field="Email" required="required" class="form-control display-7" id="url-mbr-popup-12">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer pt-0">
                                        <div class="mbr-section-btn"><button class="btn btn-md btn-primary display-4" type="submit"><span class="mdi-content-add-circle mbr-iconfont mbr-iconfont-btn"></span>ADD&nbsp;</button></div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section data-bs-version="5.1" class="footer2 stepm5 cid-ufLgx16h1z mbr-reveal" once="footers" id="afooter2-z" data-sortbtn="btn-primary">




        <div class="container">
            <div class="media-container-row align-center mbr-white">
                <div class="col-12">
                    <p class="mbr-text mb-0 mbr-fonts-style align-center display-4">
                        © Copyright 2024 UTS - All Rights Reserved
                    </p>
                </div>
            </div>
        </div>
    </section>


    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/web/assets/cookies-alert-plugin/cookies-alert-core.js"></script>
    <script src="assets/web/assets/cookies-alert-plugin/cookies-alert-script.js"></script>
    <script src="assets/smoothscroll/smooth-scroll.js"></script>
    <script src="assets/ytplayer/index.js"></script>
    <script src="assets/chatbutton/floating-wpp.js"></script>
    <script src="assets/chatbutton/script.js"></script>
    <script src="assets/dropdown/js/navbar-dropdown.js"></script>
    <script src="assets/datatables/vanilla-dataTables.min.js"></script>
    <script src="assets/popup-plugin/script.js"></script>
    <script src="assets/popup-overlay-plugin/script.js"></script>
    <script src="assets/theme/js/script.js"></script>
    <script src="assets/formoid/formoid.min.js"></script>



    <input name="cookieData" type="hidden" data-cookie-cookiesalerttype="false" data-cookie-customdialogselector="null" data-cookie-colortext="#424a4d" data-cookie-colorbg="rgb(255, 255, 255)" data-cookie-opacityoverlay="0" data-cookie-bgopacity="100" data-cookie-textbutton="Got it" data-cookie-rejecttext="REJECT" data-cookie-colorbutton="#ffeb69" data-cookie-rejectcolor="#ffffff" data-cookie-colorlink="#424a4d" data-cookie-underlinelink="true" data-cookie-text="We use cookies to give you the best experience.">
    <div id="scrollToTop" class="scrollToTop mbr-arrow-up"><a style="text-align: center;"><i class="mbr-arrow-up-icon mbr-arrow-up-icon-cm cm-icon cm-icon-smallarrow-up"></i></a></div>
    <input name="animation" type="hidden">

    <script>
        "use strict";
        if ("loading" in HTMLImageElement.prototype) {
            document.querySelectorAll('img[loading="lazy"]').forEach(e => {
                e.src = e.dataset.src;
                if (e.getAttribute("style")) {
                    e.setAttribute("data-temp-style", e.getAttribute("style"))
                };
                if (e.getAttribute("data-aspectratio")) {
                    e.style.paddingTop = 100 * e.getAttribute("data-aspectratio") + "%";
                    e.style.height = 0;
                }
                e.onload = function() {
                    if (e.getAttribute("data-temp-style")) {
                        e.setAttribute("style", e.getAttribute("data-temp-style"))
                    } else {
                        e.removeAttribute("style")
                    };
                    e.removeAttribute("data-temp-style")
                }
            })
        } else {
            const e = document.createElement("script");
            e.src = "https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.1.2/lazysizes.min.js";
            document.body.appendChild(e)
        }
    </script>
</body>

</html>