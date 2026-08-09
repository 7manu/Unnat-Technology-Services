<?php
/** Menu and URL manager — add, edit, reorder or remove any link on the website. */
declare(strict_types=1);

$editId = (int) ($_GET['id'] ?? 0);
$editing = $editId > 0 ? admin_row('SELECT * FROM `cms_nav` WHERE `id` = ? LIMIT 1', 'i', [$editId]) : null;

$menus = [
    'primary' => 'Main navigation',
    'footer_explore' => 'Footer — column 1 (Explore)',
    'footer_platforms' => 'Footer — column 2 (Platforms)',
    'footer_contact' => 'Footer — column 3 (Contact)',
];

$defaults = ['id' => 0, 'menu' => 'primary', 'label' => '', 'url' => '', 'sort_order' => 100, 'link_target' => '_self', 'rel' => '', 'visibility' => 'all', 'is_button' => 0, 'is_active' => 1];
$form = $editing !== null ? array_merge($defaults, $editing) : $defaults;
?>
<div class="admin-page-head">
  <div>
    <h1>Links &amp; URLs</h1>
    <p>Every menu link on the website. Add new destinations, change where an existing link points, reorder them or remove them entirely.</p>
  </div>
</div>

<div class="admin-card">
  <h2><?= $editing ? 'Edit link' : 'Add a link' ?></h2>
  <form action="backend/admin_action.php" method="post">
    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
    <input type="hidden" name="action" value="nav.save" />
    <input type="hidden" name="return_view" value="navigation" />
    <input type="hidden" name="id" value="<?= (int) $form['id'] ?>" />
    <div class="admin-form-grid">
      <div class="admin-field"><label for="menu">Menu</label><?= admin_select('menu', $menus, (string) $form['menu'], 'menu') ?></div>
      <div class="admin-field"><label for="label">Link text</label><input id="label" name="label" type="text" value="<?= e((string) $form['label']) ?>" required /></div>
      <div class="admin-field full"><label for="url">Destination URL</label><input id="url" name="url" type="text" value="<?= e((string) $form['url']) ?>" placeholder="products.php, index.php#services, https://example.com, tel:+91…, mailto:…" required /></div>
      <div class="admin-field"><label for="sort_order">Position</label><input id="sort_order" name="sort_order" type="number" value="<?= (int) $form['sort_order'] ?>" /><span class="hint">Lower numbers appear first.</span></div>
      <div class="admin-field"><label for="link_target">Opens in</label><?= admin_select('link_target', ['_self' => 'Same tab', '_blank' => 'New tab'], (string) $form['link_target'], 'link_target') ?></div>
      <div class="admin-field"><label for="rel">rel attribute</label><input id="rel" name="rel" type="text" value="<?= e((string) $form['rel']) ?>" placeholder="noopener, nofollow, sponsored" /></div>
      <div class="admin-field"><label for="visibility">Show on</label><?= admin_select('visibility', ['all' => 'Header menu and mobile menu bar', 'desktop' => 'Header menu only (hidden on phones)', 'mobile' => 'Mobile menu bar only (hidden on desktop)'], (string) $form['visibility'], 'visibility') ?><span class="hint">Applies to the main navigation; footer links always show.</span></div>
      <div class="admin-field"><label class="admin-check"><input type="checkbox" name="is_button" value="1"<?= (int) $form['is_button'] === 1 ? ' checked' : '' ?> /> Style as a button</label></div>
      <div class="admin-field"><label class="admin-check"><input type="checkbox" name="is_active" value="1"<?= (int) $form['is_active'] === 1 ? ' checked' : '' ?> /> Visible on the website</label></div>
    </div>
    <div class="admin-form-actions">
      <button class="admin-button" type="submit"><?= $editing ? 'Save link' : 'Add link' ?></button>
      <?php if ($editing): ?><a class="admin-button ghost" href="admin.php?view=navigation">Cancel</a><?php endif; ?>
    </div>
  </form>
</div>

<?php foreach ($menus as $menuKey => $menuLabel): ?>
  <?php $items = cms_nav($menuKey, false); ?>
  <div class="admin-card">
    <h2><?= e($menuLabel) ?></h2>
    <p><?= count($items) ?> link<?= count($items) === 1 ? '' : 's' ?> in this menu.</p>
    <?php if ($items): ?>
      <div class="admin-table-wrap">
        <table class="admin-data-table">
          <thead><tr><th>#</th><th>Text</th><th>URL</th><th>Opens</th><th>Shown on</th><th>Style</th><th>Visible</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach ($items as $item): ?>
              <tr>
                <td class="muted"><?= (int) $item['sort_order'] ?></td>
                <td><strong><?= e((string) $item['label']) ?></strong></td>
                <td><code><?= e((string) $item['url']) ?></code></td>
                <td><?= (string) $item['link_target'] === '_blank' ? 'New tab' : 'Same tab' ?></td>
                <td>
                  <?php if ($menuKey !== 'primary'): ?>
                    <span class="muted">footer</span>
                  <?php else: ?>
                    <?php $visibility = (string) ($item['visibility'] ?? 'all'); ?>
                    <span class="pill <?= $visibility === 'all' ? 'green' : 'amber' ?>"><?= $visibility === 'all' ? 'header + mobile' : ($visibility === 'desktop' ? 'header only' : 'mobile only') ?></span>
                  <?php endif; ?>
                </td>
                <td><?= (int) $item['is_button'] === 1 ? '<span class="pill blue">button</span>' : '<span class="muted">text</span>' ?></td>
                <td><?= (int) $item['is_active'] === 1 ? '<span class="pill green">yes</span>' : '<span class="pill red">hidden</span>' ?></td>
                <td class="actions">
                  <?php if ((int) $item['id'] > 0): ?>
                    <a class="admin-button ghost small" href="admin.php?view=navigation&amp;id=<?= (int) $item['id'] ?>">Edit</a>
                    <form class="admin-inline-form" action="backend/admin_action.php" method="post" data-confirm="Delete this link?">
                      <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
                      <input type="hidden" name="action" value="nav.delete" />
                      <input type="hidden" name="return_view" value="navigation" />
                      <input type="hidden" name="id" value="<?= (int) $item['id'] ?>" />
                      <button class="admin-button danger small" type="submit">Delete</button>
                    </form>
                  <?php else: ?>
                    <span class="muted">built-in default</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="admin-empty">This menu is empty.</div>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
