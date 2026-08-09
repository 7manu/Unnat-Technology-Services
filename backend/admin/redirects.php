<?php
/** Redirect manager — keep old URLs and their search ranking alive after a change. */
declare(strict_types=1);

$editId = (int) ($_GET['id'] ?? 0);
$editing = $editId > 0 ? admin_row('SELECT * FROM `cms_redirects` WHERE `id` = ? LIMIT 1', 'i', [$editId]) : null;
$redirects = admin_rows('SELECT * FROM `cms_redirects` ORDER BY `from_path`');

$defaults = ['id' => 0, 'from_path' => '', 'to_url' => '', 'status_code' => 301, 'is_active' => 1, 'notes' => '', 'hits' => 0];
$form = $editing !== null ? array_merge($defaults, $editing) : $defaults;

$codes = [
    '301' => '301 — moved permanently (passes ranking)',
    '302' => '302 — temporary',
    '307' => '307 — temporary, keeps method',
    '308' => '308 — permanent, keeps method',
    '410' => '410 — gone (removed on purpose)',
];
?>
<div class="admin-page-head">
  <div>
    <h1>Redirects</h1>
    <p>When a URL changes, add a 301 here so visitors and search engines are forwarded to the new address instead of hitting a 404.</p>
  </div>
</div>

<div class="admin-card">
  <h2><?= $editing ? 'Edit redirect' : 'Add a redirect' ?></h2>
  <form action="backend/admin_action.php" method="post">
    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
    <input type="hidden" name="action" value="redirect.save" />
    <input type="hidden" name="return_view" value="redirects" />
    <input type="hidden" name="id" value="<?= (int) $form['id'] ?>" />
    <div class="admin-form-grid">
      <div class="admin-field"><label for="from_path">Old path on this site</label><input id="from_path" name="from_path" type="text" value="<?= e((string) $form['from_path']) ?>" placeholder="/old-page.html" required /></div>
      <div class="admin-field"><label for="to_url">New destination</label><input id="to_url" name="to_url" type="text" value="<?= e((string) $form['to_url']) ?>" placeholder="/products.php or https://example.com/new" required /></div>
      <div class="admin-field"><label for="status_code">Redirect type</label><?= admin_select('status_code', $codes, (string) $form['status_code'], 'status_code') ?></div>
      <div class="admin-field"><label class="admin-check"><input type="checkbox" name="is_active" value="1"<?= (int) $form['is_active'] === 1 ? ' checked' : '' ?> /> Active</label></div>
      <div class="admin-field full"><label for="notes">Notes</label><input id="notes" name="notes" type="text" value="<?= e((string) $form['notes']) ?>" /></div>
    </div>
    <div class="admin-form-actions">
      <button class="admin-button" type="submit"><?= $editing ? 'Save redirect' : 'Add redirect' ?></button>
      <?php if ($editing): ?><a class="admin-button ghost" href="admin.php?view=redirects">Cancel</a><?php endif; ?>
    </div>
  </form>
</div>

<?php if ($redirects): ?>
  <div class="admin-table-wrap">
    <table class="admin-data-table">
      <thead><tr><th>From</th><th>To</th><th>Type</th><th>Hits</th><th>Active</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($redirects as $row): ?>
          <tr>
            <td><code><?= e((string) $row['from_path']) ?></code></td>
            <td><code><?= e((string) $row['to_url']) ?></code><?php if ((string) $row['notes'] !== ''): ?><div class="muted"><?= e((string) $row['notes']) ?></div><?php endif; ?></td>
            <td><span class="pill blue"><?= (int) $row['status_code'] ?></span></td>
            <td><?= (int) $row['hits'] ?></td>
            <td><?= (int) $row['is_active'] === 1 ? '<span class="pill green">yes</span>' : '<span class="pill">paused</span>' ?></td>
            <td class="actions">
              <a class="admin-button ghost small" href="admin.php?view=redirects&amp;id=<?= (int) $row['id'] ?>">Edit</a>
              <form class="admin-inline-form" action="backend/admin_action.php" method="post" data-confirm="Delete this redirect?">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
                <input type="hidden" name="action" value="redirect.delete" />
                <input type="hidden" name="return_view" value="redirects" />
                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>" />
                <button class="admin-button danger small" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php else: ?>
  <div class="admin-empty">No redirects configured. Add one whenever you rename or remove a page.</div>
<?php endif; ?>
