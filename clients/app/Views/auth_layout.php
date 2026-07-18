<?php use App\Services\Csrf; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title ?? 'Login') ?> | Unnat Technology Services</title>
  <link rel="icon" href="/favicon.webp">
  <link rel="manifest" href="/manifest.json">
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="auth-page">
  <main class="auth-shell">
    <?= $content ?>
  </main>
</body>
</html>
