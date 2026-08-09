<?php
/**
 * Admin control centre.
 *
 * Single entry point (admin.php?view=…) that loads one module from
 * backend/admin/. Every mutation is posted to backend/admin_action.php.
 */
declare(strict_types=1);

require __DIR__ . '/backend/admin/_helpers.php';
requireAdmin('login.php');

/* Create any missing tables and sync new content keys once per session. */
if (empty($_SESSION['uts_cms_installed'])) {
    cms_install(true);
    $_SESSION['uts_cms_installed'] = true;
}

$menu = [
    'Overview' => [
        'dashboard' => ['label' => 'Dashboard', 'icon' => '◉'],
        'inquiries' => ['label' => 'Inquiries', 'icon' => '✉'],
        'activity' => ['label' => 'Activity log', 'icon' => '⧗'],
    ],
    'Content' => [
        'content' => ['label' => 'Website content', 'icon' => '¶'],
        'pages' => ['label' => 'Pages', 'icon' => '▤'],
        'blogs' => ['label' => 'Blog', 'icon' => '✎'],
        'products' => ['label' => 'Products', 'icon' => '◈'],
        'media' => ['label' => 'Media library', 'icon' => '🖼'],
    ],
    'Search engine optimisation' => [
        'seo' => ['label' => 'Page SEO', 'icon' => '⌕'],
        'keywords' => ['label' => 'Keywords', 'icon' => '#'],
        'backlinks' => ['label' => 'Backlinks', 'icon' => '⇄'],
        'navigation' => ['label' => 'Links & URLs', 'icon' => '⛓'],
        'redirects' => ['label' => 'Redirects', 'icon' => '↪'],
    ],
    'Configuration' => [
        'settings' => ['label' => 'SEO & site settings', 'icon' => '⚙'],
        'admins' => ['label' => 'Admin accounts', 'icon' => '☖'],
    ],
];

$allowedViews = [];
foreach ($menu as $group) {
    $allowedViews = array_merge($allowedViews, array_keys($group));
}

$view = (string) ($_GET['view'] ?? 'dashboard');
if (!in_array($view, $allowedViews, true)) {
    $view = 'dashboard';
}

$csrfToken = adminCsrfToken();
$statusCode = (string) ($_GET['status'] ?? '');
$statusMessage = admin_status_messages()[$statusCode] ?? '';
$statusIsError = admin_is_error_status($statusCode);
$databaseReady = cms_db_available();

$viewTitles = [];
foreach ($menu as $group) {
    foreach ($group as $key => $item) {
        $viewTitles[$key] = $item['label'];
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex, nofollow, noarchive" />
    <meta name="theme-color" content="#f4f7fd" />
    <link rel="icon" href="assets/images/uts-logo-removebg-removebg-preview-512x512.webp" type="image/webp" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Manrope:wght@500;600;700;800&amp;display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="assets/theme/css/uts-modern.css" />
    <link rel="stylesheet" href="assets/theme/css/uts-admin.css" />
    <title><?= e($viewTitles[$view] ?? 'Admin') ?> | UTS Admin</title>
    <script src="assets/theme/js/uts-admin.js" defer></script>
  </head>
  <body class="admin-shell">
    <a class="skip-link" href="#admin-content">Skip to admin content</a>

    <header class="admin-topbar">
      <button class="admin-menu-toggle" type="button" aria-controls="admin-sidebar" aria-expanded="false" aria-label="Toggle navigation">☰</button>
      <a class="brand" href="admin.php"><img src="assets/images/uts-logo-removebg-removebg-preview-512x512.webp" width="38" height="38" alt="" /><span>UTS Admin</span></a>
      <div class="admin-topbar-actions">
        <span class="admin-user" title="Signed in as"><?= e(adminName()) ?></span>
        <a class="button button-secondary button-small" href="index.php" target="_blank" rel="noopener">View site ↗</a>
        <a class="button button-primary button-small" href="backend/logout.php">Log out</a>
      </div>
    </header>

    <div class="admin-layout">
      <nav class="admin-sidebar" id="admin-sidebar" aria-label="Admin sections">
        <?php foreach ($menu as $groupName => $items): ?>
          <p class="admin-sidebar-group"><?= e($groupName) ?></p>
          <?php foreach ($items as $key => $item): ?>
            <a href="admin.php?view=<?= e($key) ?>"<?= $key === $view ? ' class="is-current" aria-current="page"' : '' ?>><span aria-hidden="true"><?= e($item['icon']) ?></span><?= e($item['label']) ?></a>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </nav>

      <main class="admin-content" id="admin-content">
        <?php if (!$databaseReady): ?>
          <div class="admin-alert error" role="alert">
            The database connection is unavailable. Confirm <code>backend/_conn.local.php</code> or the <code>UTS_DB_*</code> environment variables. The public website is still serving its built-in default content.
          </div>
        <?php elseif ($statusMessage !== ''): ?>
          <div class="admin-alert<?= $statusIsError ? ' error' : '' ?>" role="status"><?= e($statusMessage) ?></div>
        <?php endif; ?>

        <?php
        $viewFile = __DIR__ . '/backend/admin/' . $view . '.php';
        if (is_file($viewFile)) {
            require $viewFile;
        } else {
            echo '<div class="admin-alert error">This section is not available.</div>';
        }
        ?>
      </main>
    </div>
  </body>
</html>
