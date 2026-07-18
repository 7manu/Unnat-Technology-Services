<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Asset Manager</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">|
    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="assets/Images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/Images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/Images/favicon-16x16.png">
    <link rel="shortcut icon" href="assets/Images/favicon.ico">
    <meta name="theme-color" content="#0d6efd">

    <link rel="manifest" href="pwa/manifest.json">
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/Assets/pwa/sw.js')  // ✅ Full correct path
            .then(() => console.log("✅ Service Worker Registered"))
            .catch(err => console.error("SW error:", err));
        }
    </script>

</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-body">
                        <h4 class="card-title text-center">Admin Login</h4>
                        <?php if(isset($_GET['error'])): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                        <?php endif; ?>
                        <form action="backend/login.php" method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

