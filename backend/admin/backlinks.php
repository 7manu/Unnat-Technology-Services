<?php
/** Backlink register — every inbound link earned, requested or lost. */
declare(strict_types=1);

$editId = (int) ($_GET['id'] ?? 0);
$editing = $editId > 0 ? admin_row('SELECT * FROM `cms_backlinks` WHERE `id` = ? LIMIT 1', 'i', [$editId]) : null;
$backlinks = admin_rows("SELECT * FROM `cms_backlinks` ORDER BY FIELD(`status`, 'live', 'pending', 'lost', 'rejected'), `authority` DESC, `id` DESC");

$defaults = [
    'id' => 0, 'source_url' => '', 'target_route' => '/', 'anchor_text' => '',
    'link_type' => 'dofollow', 'placement' => 'directory', 'authority' => 0,
    'status' => 'live', 'acquired_on' => '', 'last_checked' => '', 'notes' => '',
];
$form = $editing !== null ? array_merge($defaults, $editing) : $defaults;

$linkTypes = ['dofollow' => 'Dofollow (passes value)', 'nofollow' => 'Nofollow', 'ugc' => 'UGC', 'sponsored' => 'Sponsored'];
$placements = [
    'directory' => 'Business directory', 'guest post' => 'Guest post', 'blog' => 'Blog mention',
    'press' => 'Press / news', 'social' => 'Social profile', 'partner' => 'Partner site',
    'citation' => 'Local citation', 'forum' => 'Forum / community', 'other' => 'Other',
];
$statuses = ['live' => 'Live', 'pending' => 'Pending', 'lost' => 'Lost', 'rejected' => 'Rejected'];

$liveCount = count(array_filter($backlinks, static fn(array $row): bool => $row['status'] === 'live'));
$dofollowCount = count(array_filter($backlinks, static fn(array $row): bool => $row['status'] === 'live' && $row['link_type'] === 'dofollow'));
$domains = count(array_unique(array_column(array_filter($backlinks, static fn(array $row): bool => $row['status'] === 'live'), 'source_domain')));
?>
<div class="admin-page-head">
  <div>
    <h1>Backlinks</h1>
    <p>Track every site that links to you: where the link is, which page it points at, the anchor text and whether it still exists. Directories, partner sites and guest posts are the practical starting points.</p>
  </div>
</div>

<div class="admin-stat-grid">
  <div class="admin-stat-card"><strong><?= $liveCount ?></strong><span>Live backlinks</span></div>
  <div class="admin-stat-card"><strong><?= $dofollowCount ?></strong><span>Live dofollow links</span></div>
  <div class="admin-stat-card"><strong><?= $domains ?></strong><span>Referring domains</span></div>
  <div class="admin-stat-card"><strong><?= count($backlinks) ?></strong><span>Records in total</span></div>
</div>

<div class="admin-card">
  <h2><?= $editing ? 'Edit backlink' : 'Log a backlink' ?></h2>
  <form action="backend/admin_action.php" method="post">
    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
    <input type="hidden" name="action" value="backlink.save" />
    <input type="hidden" name="return_view" value="backlinks" />
    <input type="hidden" name="id" value="<?= (int) $form['id'] ?>" />
    <div class="admin-form-grid">
      <div class="admin-field full"><label for="source_url">Page that links to you</label><input id="source_url" name="source_url" type="url" value="<?= e((string) $form['source_url']) ?>" placeholder="https://directory.example.com/listing/unnat" required /></div>
      <div class="admin-field"><label for="target_route">Page it links to</label><?= admin_select('target_route', admin_route_options(), (string) $form['target_route'], 'target_route') ?></div>
      <div class="admin-field"><label for="anchor_text">Anchor text</label><input id="anchor_text" name="anchor_text" type="text" value="<?= e((string) $form['anchor_text']) ?>" placeholder="Unnat Technology Services" /></div>
      <div class="admin-field"><label for="link_type">Link type</label><?= admin_select('link_type', $linkTypes, (string) $form['link_type'], 'link_type') ?></div>
      <div class="admin-field"><label for="placement">Placement</label><?= admin_select('placement', $placements, (string) $form['placement'], 'placement') ?></div>
      <div class="admin-field"><label for="authority">Domain authority (0–100)</label><input id="authority" name="authority" type="number" min="0" max="100" value="<?= (int) $form['authority'] ?>" /></div>
      <div class="admin-field"><label for="status">Status</label><?= admin_select('status', $statuses, (string) $form['status'], 'status') ?></div>
      <div class="admin-field"><label for="acquired_on">Acquired on</label><input id="acquired_on" name="acquired_on" type="date" value="<?= e((string) $form['acquired_on']) ?>" /></div>
      <div class="admin-field"><label for="last_checked">Last checked</label><input id="last_checked" name="last_checked" type="date" value="<?= e((string) $form['last_checked']) ?>" /></div>
      <div class="admin-field full"><label for="notes">Notes</label><textarea id="notes" name="notes" rows="2" placeholder="Contact, cost, renewal date or follow-up"><?= e((string) $form['notes']) ?></textarea></div>
    </div>
    <div class="admin-form-actions">
      <button class="admin-button" type="submit"><?= $editing ? 'Save backlink' : 'Add backlink' ?></button>
      <?php if ($editing): ?><a class="admin-button ghost" href="admin.php?view=backlinks">Cancel</a><?php endif; ?>
    </div>
  </form>
</div>

<?php if ($backlinks): ?>
  <div class="admin-table-wrap">
    <table class="admin-data-table">
      <thead><tr><th>Source</th><th>Anchor</th><th>Points to</th><th>Type</th><th>Placement</th><th>DA</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($backlinks as $row): ?>
          <tr>
            <td><a href="<?= e(cms_safe_external_url((string) $row['source_url'])) ?>" target="_blank" rel="noopener nofollow"><?= e((string) $row['source_domain']) ?> ↗</a><div class="muted"><?= e(cms_excerpt((string) $row['source_url'], 55)) ?></div></td>
            <td><?= (string) $row['anchor_text'] !== '' ? e((string) $row['anchor_text']) : '<span class="muted">—</span>' ?></td>
            <td><code><?= e((string) $row['target_route']) ?></code></td>
            <td><span class="pill <?= $row['link_type'] === 'dofollow' ? 'green' : '' ?>"><?= e((string) $row['link_type']) ?></span></td>
            <td><?= e((string) $row['placement']) ?></td>
            <td><?= (int) $row['authority'] ?></td>
            <td><span class="pill <?= $row['status'] === 'live' ? 'green' : ($row['status'] === 'pending' ? 'amber' : 'red') ?>"><?= e((string) $row['status']) ?></span></td>
            <td class="actions">
              <a class="admin-button ghost small" href="admin.php?view=backlinks&amp;id=<?= (int) $row['id'] ?>">Edit</a>
              <form class="admin-inline-form" action="backend/admin_action.php" method="post" data-confirm="Delete this backlink record?">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
                <input type="hidden" name="action" value="backlink.delete" />
                <input type="hidden" name="return_view" value="backlinks" />
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
  <div class="admin-empty">No backlinks recorded yet. Start with Google Business Profile, Justdial, IndiaMART, Sulekha, Clutch and your LinkedIn company page.</div>
<?php endif; ?>
