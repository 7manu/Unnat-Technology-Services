<?php
/** Keyword planner and rank tracker. */
declare(strict_types=1);

$editId = (int) ($_GET['id'] ?? 0);
$editing = $editId > 0 ? admin_row('SELECT * FROM `cms_keywords` WHERE `id` = ? LIMIT 1', 'i', [$editId]) : null;
$keywords = admin_rows("SELECT * FROM `cms_keywords` ORDER BY FIELD(`priority`, 'high', 'medium', 'low'), `keyword`");

$defaults = [
    'id' => 0, 'keyword' => '', 'target_route' => '/', 'search_intent' => 'commercial',
    'search_volume' => 0, 'difficulty' => 0, 'current_rank' => 0,
    'priority' => 'medium', 'status' => 'tracking', 'notes' => '',
];
$form = $editing !== null ? array_merge($defaults, $editing) : $defaults;

$intents = ['informational' => 'Informational', 'commercial' => 'Commercial', 'transactional' => 'Transactional', 'navigational' => 'Navigational', 'local' => 'Local'];
$statuses = ['tracking' => 'Tracking', 'target' => 'Target', 'ranking' => 'Ranking', 'paused' => 'Paused'];
$priorities = ['high' => 'High', 'medium' => 'Medium', 'low' => 'Low'];
?>
<div class="admin-page-head">
  <div>
    <h1>Keywords</h1>
    <p>Plan the search terms each page should win, record volume and difficulty, and track where you currently rank. Assigned keywords can be pasted into the meta keywords field of the matching page.</p>
  </div>
</div>

<div class="admin-card">
  <h2><?= $editing ? 'Edit keyword' : 'Add a keyword' ?></h2>
  <form action="backend/admin_action.php" method="post">
    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
    <input type="hidden" name="action" value="keyword.save" />
    <input type="hidden" name="return_view" value="keywords" />
    <input type="hidden" name="id" value="<?= (int) $form['id'] ?>" />
    <div class="admin-form-grid">
      <div class="admin-field full"><label for="keyword">Keyword or phrase</label><input id="keyword" name="keyword" type="text" value="<?= e((string) $form['keyword']) ?>" placeholder="software development company in Moradabad" required /></div>
      <div class="admin-field"><label for="target_route">Target page</label><?= admin_select('target_route', admin_route_options(), (string) $form['target_route'], 'target_route') ?></div>
      <div class="admin-field"><label for="search_intent">Search intent</label><?= admin_select('search_intent', $intents, (string) $form['search_intent'], 'search_intent') ?></div>
      <div class="admin-field"><label for="priority">Priority</label><?= admin_select('priority', $priorities, (string) $form['priority'], 'priority') ?></div>
      <div class="admin-field"><label for="status">Status</label><?= admin_select('status', $statuses, (string) $form['status'], 'status') ?></div>
      <div class="admin-field"><label for="search_volume">Monthly searches</label><input id="search_volume" name="search_volume" type="number" min="0" value="<?= (int) $form['search_volume'] ?>" /></div>
      <div class="admin-field"><label for="difficulty">Difficulty (0–100)</label><input id="difficulty" name="difficulty" type="number" min="0" max="100" value="<?= (int) $form['difficulty'] ?>" /></div>
      <div class="admin-field"><label for="current_rank">Current rank</label><input id="current_rank" name="current_rank" type="number" min="0" value="<?= (int) $form['current_rank'] ?>" /><span class="hint">0 means not ranking yet.</span></div>
      <div class="admin-field full"><label for="notes">Notes</label><textarea id="notes" name="notes" rows="2"><?= e((string) $form['notes']) ?></textarea></div>
    </div>
    <div class="admin-form-actions">
      <button class="admin-button" type="submit"><?= $editing ? 'Save keyword' : 'Add keyword' ?></button>
      <?php if ($editing): ?><a class="admin-button ghost" href="admin.php?view=keywords">Cancel</a><?php endif; ?>
    </div>
  </form>
</div>

<?php if ($keywords): ?>
  <div class="admin-table-wrap">
    <table class="admin-data-table">
      <thead><tr><th>Keyword</th><th>Target page</th><th>Intent</th><th>Volume</th><th>Difficulty</th><th>Rank</th><th>Priority</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($keywords as $row): ?>
          <tr>
            <td><strong><?= e((string) $row['keyword']) ?></strong><?php if ((string) $row['notes'] !== ''): ?><div class="muted"><?= e(cms_excerpt((string) $row['notes'], 60)) ?></div><?php endif; ?></td>
            <td><code><?= e((string) $row['target_route']) ?></code></td>
            <td><?= e((string) $row['search_intent']) ?></td>
            <td><?= (int) $row['search_volume'] ?></td>
            <td><?= (int) $row['difficulty'] ?></td>
            <td><?= (int) $row['current_rank'] > 0 ? '#' . (int) $row['current_rank'] : '<span class="muted">—</span>' ?></td>
            <td><span class="pill <?= $row['priority'] === 'high' ? 'red' : ($row['priority'] === 'medium' ? 'amber' : 'blue') ?>"><?= e((string) $row['priority']) ?></span></td>
            <td><span class="pill <?= $row['status'] === 'ranking' ? 'green' : '' ?>"><?= e((string) $row['status']) ?></span></td>
            <td class="actions">
              <a class="admin-button ghost small" href="admin.php?view=keywords&amp;id=<?= (int) $row['id'] ?>">Edit</a>
              <form class="admin-inline-form" action="backend/admin_action.php" method="post" data-confirm="Delete this keyword?">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
                <input type="hidden" name="action" value="keyword.delete" />
                <input type="hidden" name="return_view" value="keywords" />
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
  <div class="admin-empty">No keywords tracked yet. Start with five terms a customer would actually type into Google.</div>
<?php endif; ?>
