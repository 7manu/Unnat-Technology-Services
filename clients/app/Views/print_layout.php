<?php use App\Config\Env; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= htmlspecialchars($title ?? 'Document') ?> | <?= htmlspecialchars((string) Env::get('APP_NAME', 'Unnat Technology Services')) ?></title>
  <link rel="icon" href="/favicon.webp">
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="print-page">
  <div class="print-toolbar no-print">
    <a class="ghost-button" href="<?= htmlspecialchars($backUrl ?? '/projects') ?>">&larr; Back</a>
    <button class="primary-button" type="button" onclick="window.print()">Print / Save as PDF</button>
  </div>
  <?= $content ?>
</body>
</html>
