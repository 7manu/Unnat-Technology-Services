<?php use App\Services\Csrf; ?>
<section class="login-card">
  <div class="login-art">
    <div class="sun-ring"></div>
    <img src="/assets/img/logo-uts.webp" alt="Unnat Technology Services">
  </div>
  <div class="login-panel">
    <p class="eyebrow">Project Portal</p>
    <h1>Unnat Technology Services</h1>
    <p class="muted">Reach to 9690805228 in case of any issues.</p>
    <?php if (!empty($error)): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" action="/login" class="form-stack">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
      <label>Email <input type="email" name="email" required autocomplete="username"></label>
      <label>Password <input type="password" name="password" required autocomplete="current-password"></label>
      <button class="primary-button" type="submit">Login</button>
    </form>
  </div>
</section>
