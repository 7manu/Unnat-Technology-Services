<?php
use App\Config\Env;
use App\Services\Auth;
use App\Services\Csrf;

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$navItems = [['/projects', 'Projects', 'Every project you can access']];
if (Auth::isAdmin()) {
    $navItems[] = ['/subadmins', 'Subadmins', 'Team logins and project access'];
    $navItems[] = ['/client-users', 'Client Access', 'Client logins and preview'];
}
$user = Auth::user() ?? [];
$initial = strtoupper(substr((string) ($user['name'] ?? 'A'), 0, 1));
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
  <a class="skip-link" href="#main-content">Skip to content</a>
  <button class="sidebar-backdrop" type="button" data-sidebar-close aria-label="Close menu"></button>

  <aside class="sidebar" id="app-sidebar">
    <div class="sidebar-brand">
      <img src="/assets/img/logo-uts.webp" alt="">
      <strong><?= htmlspecialchars((string) Env::get('APP_NAME', 'Unnat Technology Services')) ?></strong>
    </div>

    <nav class="side-nav" aria-label="Sections">
      <?php foreach ($navItems as [$href, $label, $hint]): ?>
        <?php $isCurrent = $currentPath === $href || str_starts_with($currentPath, $href . '/'); ?>
        <a href="<?= $href ?>"<?= $isCurrent ? ' class="is-current" aria-current="page"' : '' ?>>
          <span><?= htmlspecialchars($label) ?></span>
          <small><?= htmlspecialchars($hint) ?></small>
        </a>
      <?php endforeach; ?>
    </nav>

    <div class="sidebar-foot">
      <div class="sidebar-user">
        <span class="avatar" aria-hidden="true"><?= htmlspecialchars($initial) ?></span>
        <div>
          <strong><?= htmlspecialchars((string) ($user['name'] ?? 'Administrator')) ?></strong>
          <small><?= htmlspecialchars((string) ($user['role'] ?? 'admin')) ?></small>
        </div>
      </div>
      <form method="post" action="/logout">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
        <button class="logout-button" type="submit">Log out</button>
      </form>
    </div>
  </aside>

  <header class="mobile-topbar">
    <button class="menu-button" type="button" data-sidebar-open aria-controls="app-sidebar" aria-label="Open menu">
      <span></span><span></span><span></span>
    </button>
    <a class="mobile-brand" href="/projects">
      <img src="/assets/img/logo-uts.webp" alt="">
      <strong><?= htmlspecialchars((string) Env::get('APP_NAME', 'Unnat Technology Services')) ?></strong>
    </a>
  </header>

  <main class="app-shell" id="main-content">
    <?php if (!empty($error)): ?>
      <div class="alert alert-error" role="alert"><span><?= htmlspecialchars($error) ?></span><button type="button" data-alert-dismiss aria-label="Dismiss">&times;</button></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
      <div class="alert alert-success" role="status"><span><?= htmlspecialchars($success) ?></span><button type="button" data-alert-dismiss aria-label="Dismiss">&times;</button></div>
    <?php endif; ?>
    <?= $content ?>
  </main>

  <script>
    window.UTS = { csrf: "<?= htmlspecialchars(Csrf::token()) ?>" };
  </script>
  <script src="/assets/js/app.js" defer></script>
</body>
</html>
