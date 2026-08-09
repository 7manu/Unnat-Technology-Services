<?php
/** Activity log — a record of every change made from the admin panel. */
declare(strict_types=1);

$entries = admin_rows('SELECT * FROM `cms_audit` ORDER BY `id` DESC LIMIT 200');
?>
<div class="admin-page-head">
  <div>
    <h1>Activity log</h1>
    <p>The 200 most recent changes made from this panel.</p>
  </div>
</div>

<?php if ($entries): ?>
  <div class="admin-table-wrap">
    <table class="admin-data-table">
      <thead><tr><th>When</th><th>Who</th><th>Action</th><th>Area</th><th>Item</th><th>Details</th></tr></thead>
      <tbody>
        <?php foreach ($entries as $entry): ?>
          <tr>
            <td class="muted"><?= e(date('j M Y, H:i', strtotime((string) $entry['created_at']) ?: time())) ?></td>
            <td><?= e((string) $entry['admin_name']) ?></td>
            <td><span class="pill <?= $entry['action'] === 'delete' ? 'red' : ($entry['action'] === 'create' ? 'green' : 'blue') ?>"><?= e((string) $entry['action']) ?></span></td>
            <td><?= e((string) $entry['entity']) ?></td>
            <td><code><?= e((string) $entry['entity_ref']) ?></code></td>
            <td class="muted"><?= e((string) $entry['details']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php else: ?>
  <div class="admin-empty">No activity recorded yet.</div>
<?php endif; ?>
