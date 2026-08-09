<?php
declare(strict_types=1);

require __DIR__ . '/backend/_admin_auth.php';
if (adminIsAuthenticated()) {
    header('Location: admin.php', true, 303);
    exit;
}

$invalidLogin = ($_GET['error'] ?? '') === 'invalid';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex, nofollow, noarchive" />
    <meta name="theme-color" content="#f6f9ff" />
    <link rel="icon" href="assets/images/uts-logo-removebg-removebg-preview-512x512.webp" type="image/webp" />
    <link rel="stylesheet" href="assets/theme/css/uts-modern.css" />
    <?php /* Fonts load without blocking the first paint; the system stack shows meanwhile. */ ?>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Manrope:wght@500;600;700;800&amp;display=swap" media="print" onload="this.media='all'" />
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Manrope:wght@500;600;700;800&amp;display=swap" /></noscript>
    <title>Admin Login | Unnat Technology Services</title>
  </head>
  <body>
    <main class="login-page">
      <section class="login-card" aria-labelledby="login-title">
        <a class="brand" href="index.php"><img src="assets/images/uts-logo-removebg-removebg-preview-512x512.webp" width="46" height="46" alt="" /><span>Unnat Technology Services</span></a>
        <p class="eyebrow">Secure access</p>
        <h1 id="login-title">Admin login</h1>
        <p>Enter the authorized mobile number and password to manage inquiries and products.</p>
        <?php if ($invalidLogin): ?><div class="admin-alert error" role="alert">The mobile number or password is incorrect.</div><?php endif; ?>
        <form class="login-form" action="backend/login.php" method="post">
          <div class="field"><label for="mobile">Mobile number</label><input id="mobile" name="mobile" type="tel" maxlength="10" inputmode="numeric" autocomplete="username" required autofocus /></div>
          <div class="field"><label for="passcode">Password</label><input id="passcode" name="passcode" type="password" autocomplete="current-password" required /></div>
          <button class="button button-primary" type="submit">Enter dashboard <span aria-hidden="true">→</span></button>
        </form>
      </section>
    </main>
  </body>
</html>
