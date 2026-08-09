<?php
/** Administrator accounts and password management. */
declare(strict_types=1);

$editId = (int) ($_GET['id'] ?? 0);
$editing = $editId > 0 ? admin_row('SELECT * FROM `cms_admins` WHERE `id` = ? LIMIT 1', 'i', [$editId]) : null;
$accounts = admin_rows('SELECT * FROM `cms_admins` ORDER BY `id`');

$defaults = ['id' => 0, 'name' => '', 'mobile' => '', 'role' => 'editor', 'is_active' => 1];
$form = $editing !== null ? array_merge($defaults, $editing) : $defaults;
?>
<div class="admin-page-head">
  <div>
    <h1>Admin accounts</h1>
    <p>Sign-in uses a mobile number and a password. Passwords are stored as bcrypt hashes and can never be read back — only replaced.</p>
  </div>
</div>

<div class="admin-card">
  <h2>Change your own password</h2>
  <p>You are signed in as <strong><?= e(adminName()) ?></strong> (<?= e(adminMobile()) ?>). Use at least 10 characters.</p>
  <form action="backend/admin_action.php" method="post">
    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
    <input type="hidden" name="action" value="account.password" />
    <input type="hidden" name="return_view" value="admins" />
    <div class="admin-form-grid">
      <div class="admin-field"><label for="current_password">Current password</label><input id="current_password" name="current_password" type="password" autocomplete="current-password" required /></div>
      <div class="admin-field"><label for="new_password">New password</label><input id="new_password" name="new_password" type="password" minlength="10" autocomplete="new-password" required /></div>
      <div class="admin-field"><label for="confirm_password">Repeat new password</label><input id="confirm_password" name="confirm_password" type="password" minlength="10" autocomplete="new-password" required /></div>
    </div>
    <div class="admin-form-actions"><button class="admin-button" type="submit">Update password</button></div>
  </form>
</div>

<div class="admin-card">
  <h2><?= $editing ? 'Edit account' : 'Add an account' ?></h2>
  <form action="backend/admin_action.php" method="post">
    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
    <input type="hidden" name="action" value="admin.save" />
    <input type="hidden" name="return_view" value="admins" />
    <input type="hidden" name="id" value="<?= (int) $form['id'] ?>" />
    <div class="admin-form-grid">
      <div class="admin-field"><label for="name">Name</label><input id="name" name="name" type="text" value="<?= e((string) $form['name']) ?>" required /></div>
      <div class="admin-field"><label for="mobile">Mobile number</label><input id="mobile" name="mobile" type="tel" inputmode="numeric" maxlength="10" value="<?= e((string) $form['mobile']) ?>" required /><span class="hint">This is the sign-in ID.</span></div>
      <div class="admin-field"><label for="password">Password</label><input id="password" name="password" type="password" minlength="10" autocomplete="new-password"<?= $editing ? '' : ' required' ?> /><span class="hint"><?= $editing ? 'Leave empty to keep the current password.' : 'At least 10 characters.' ?></span></div>
      <div class="admin-field"><label for="role">Role</label><?= admin_select('role', ['owner' => 'Owner', 'editor' => 'Editor'], (string) $form['role'], 'role') ?></div>
      <div class="admin-field"><label class="admin-check"><input type="checkbox" name="is_active" value="1"<?= (int) $form['is_active'] === 1 ? ' checked' : '' ?> /> Account can sign in</label></div>
    </div>
    <div class="admin-form-actions">
      <button class="admin-button" type="submit"><?= $editing ? 'Save account' : 'Create account' ?></button>
      <?php if ($editing): ?><a class="admin-button ghost" href="admin.php?view=admins">Cancel</a><?php endif; ?>
    </div>
  </form>
</div>

<?php if ($accounts): ?>
  <div class="admin-table-wrap">
    <table class="admin-data-table">
      <thead><tr><th>Name</th><th>Mobile (sign-in ID)</th><th>Role</th><th>Active</th><th>Last sign-in</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($accounts as $account): ?>
          <tr>
            <td><strong><?= e((string) $account['name']) ?></strong></td>
            <td><code><?= e((string) $account['mobile']) ?></code></td>
            <td><span class="pill <?= $account['role'] === 'owner' ? 'blue' : '' ?>"><?= e((string) $account['role']) ?></span></td>
            <td><?= (int) $account['is_active'] === 1 ? '<span class="pill green">yes</span>' : '<span class="pill red">disabled</span>' ?></td>
            <td class="muted"><?= $account['last_login_at'] !== null ? e(date('j M Y, H:i', strtotime((string) $account['last_login_at']) ?: time())) : 'Never' ?></td>
            <td class="actions">
              <a class="admin-button ghost small" href="admin.php?view=admins&amp;id=<?= (int) $account['id'] ?>">Edit</a>
              <?php if ((int) $account['id'] !== adminId()): ?>
                <form class="admin-inline-form" action="backend/admin_action.php" method="post" data-confirm="Delete this administrator account?">
                  <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
                  <input type="hidden" name="action" value="admin.delete" />
                  <input type="hidden" name="return_view" value="admins" />
                  <input type="hidden" name="id" value="<?= (int) $account['id'] ?>" />
                  <button class="admin-button danger small" type="submit">Delete</button>
                </form>
              <?php else: ?>
                <span class="muted">signed in</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php else: ?>
  <div class="admin-empty">No accounts stored yet — you are signed in with the fallback credentials. Create an account above to manage passwords from this panel.</div>
<?php endif; ?>
