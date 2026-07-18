<?php
use App\Config\Env;
use App\Services\Auth;
use App\Services\Csrf;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#fff8dc">
  <title><?= htmlspecialchars($title ?? 'Dashboard') ?> | Unnat Technology Services</title>
  <link rel="icon" href="/favicon.webp">
  <link rel="manifest" href="/manifest.json">
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
  <button class="sidebar-backdrop" type="button" data-sidebar-close aria-label="Close menu"></button>
  <aside class="sidebar" id="app-sidebar">
    <div class="sidebar-brand">
      <img src="/assets/img/logo-uts.webp" alt="Unnat Technology Services">
      <strong><?= htmlspecialchars((string) Env::get('APP_NAME', 'Unnat Technology Services')) ?></strong>
    </div>
    <nav class="side-nav">
      <a href="/projects">Projects</a>
      <?php if (Auth::isAdmin()): ?>
        <a href="/subadmins">Subadmins</a>
        <a href="/client-users">Client Access</a>
      <?php endif; ?>
      <form method="post" action="/logout">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
        <button type="submit">Logout</button>
      </form>
    </nav>
  </aside>

  <header class="mobile-topbar">
    <button class="menu-button" type="button" data-sidebar-open aria-controls="app-sidebar" aria-label="Open menu">
      <span></span><span></span><span></span>
    </button>
    <a class="mobile-brand" href="/projects">
      <img src="/assets/img/logo-uts.webp" alt="Unnat Technology Services">
      <strong><?= htmlspecialchars((string) Env::get('APP_NAME', 'Unnat Technology Services')) ?></strong>
    </a>
  </header>

  <main class="app-shell">
    <?php if (!empty($error)): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if (!empty($success)): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?= $content ?>
  </main>

  <script>
    window.UTS = { csrf: "<?= htmlspecialchars(Csrf::token()) ?>" };
  </script>
  <script src="/assets/js/app.js" defer></script>
</body>
</html>
