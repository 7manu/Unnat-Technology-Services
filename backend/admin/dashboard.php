<?php
/** Admin dashboard — counts, health checks and shortcuts. */
declare(strict_types=1);

$pendingInquiries = (int) (admin_row("SELECT COUNT(*) AS `total` FROM `query` WHERE `status` <> '1'")['total'] ?? 0);
$publishedPosts = (int) (admin_row("SELECT COUNT(*) AS `total` FROM `cms_posts` WHERE `status` = 'published'")['total'] ?? 0);
$publishedPages = (int) (admin_row("SELECT COUNT(*) AS `total` FROM `cms_pages` WHERE `status` = 'published'")['total'] ?? 0);
$liveBacklinks = (int) (admin_row("SELECT COUNT(*) AS `total` FROM `cms_backlinks` WHERE `status` = 'live'")['total'] ?? 0);

$stats = [
    ['value' => admin_table_count('query'), 'label' => 'Total inquiries'],
    ['value' => $pendingInquiries, 'label' => 'Pending inquiries'],
    ['value' => admin_table_count('products'), 'label' => 'Products'],
    ['value' => admin_table_count('cms_content'), 'label' => 'Editable content fields'],
    ['value' => $publishedPages . ' / ' . admin_table_count('cms_pages'), 'label' => 'Pages published'],
    ['value' => $publishedPosts . ' / ' . admin_table_count('cms_posts'), 'label' => 'Blog posts published'],
    ['value' => admin_table_count('cms_keywords'), 'label' => 'Tracked keywords'],
    ['value' => $liveBacklinks . ' / ' . admin_table_count('cms_backlinks'), 'label' => 'Live backlinks'],
];

$checks = [
    ['label' => 'Google Analytics measurement ID', 'ok' => cms_setting('google_analytics_id') !== '', 'view' => 'settings'],
    ['label' => 'Google Search Console verification', 'ok' => cms_setting('google_site_verification') !== '', 'view' => 'settings'],
    ['label' => 'Default meta description', 'ok' => cms_setting('default_meta_description') !== '', 'view' => 'settings'],
    ['label' => 'Default social share image', 'ok' => cms_setting('default_og_image') !== '', 'view' => 'settings'],
    ['label' => 'Structured data (schema.org)', 'ok' => cms_setting('schema_enabled') === '1', 'view' => 'settings'],
    ['label' => 'Home page SEO record', 'ok' => isset(cms_seo_records()['/']), 'view' => 'seo'],
    ['label' => 'At least one published blog post', 'ok' => $publishedPosts > 0, 'view' => 'blogs'],
    ['label' => 'At least one tracked keyword', 'ok' => admin_table_count('cms_keywords') > 0, 'view' => 'keywords'],
];

$recentActivity = admin_rows('SELECT * FROM `cms_audit` ORDER BY `id` DESC LIMIT 8');
?>
<div class="admin-page-head">
  <div>
    <h1>Dashboard</h1>
    <p>Signed in as <?= e(adminName()) ?>. Everything on the public website can be edited from the sections on the left.</p>
  </div>
  <form action="backend/admin_action.php" method="post">
    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
    <input type="hidden" name="action" value="system.resync" />
    <input type="hidden" name="return_view" value="dashboard" />
    <button class="admin-button ghost" type="submit">Re-check database &amp; content keys</button>
  </form>
</div>

<div class="admin-stat-grid">
  <?php foreach ($stats as $stat): ?>
    <div class="admin-stat-card"><strong><?= e((string) $stat['value']) ?></strong><span><?= e($stat['label']) ?></span></div>
  <?php endforeach; ?>
</div>

<div class="admin-card">
  <h2>Quick actions</h2>
  <p>The tasks used most often.</p>
  <div class="admin-quick-links">
    <a href="admin.php?view=content">Edit website text</a>
    <a href="admin.php?view=pages&amp;id=new">Create a new page</a>
    <a href="admin.php?view=blogs&amp;id=new">Write a blog post</a>
    <a href="admin.php?view=seo">Tune page SEO</a>
    <a href="admin.php?view=keywords">Add keywords</a>
    <a href="admin.php?view=backlinks">Log a backlink</a>
    <a href="admin.php?view=navigation">Edit menu links</a>
    <a href="sitemap.php" target="_blank" rel="noopener">View sitemap ↗</a>
    <a href="robots.php" target="_blank" rel="noopener">View robots.txt ↗</a>
  </div>
</div>

<div class="admin-card">
  <h2>SEO readiness</h2>
  <p>A quick checklist of the settings search engines look for.</p>
  <div class="admin-table-wrap">
    <table class="admin-data-table">
      <thead><tr><th>Check</th><th>Status</th><th>Fix</th></tr></thead>
      <tbody>
        <?php foreach ($checks as $check): ?>
          <tr>
            <td><?= e($check['label']) ?></td>
            <td><span class="pill <?= $check['ok'] ? 'green' : 'amber' ?>"><?= $check['ok'] ? 'Configured' : 'Not set' ?></span></td>
            <td><?= $check['ok'] ? '<span class="muted">—</span>' : '<a href="admin.php?view=' . e($check['view']) . '">Open settings</a>' ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="admin-card">
  <h2>Recent changes</h2>
  <p>The last few edits made from this panel.</p>
  <?php if ($recentActivity): ?>
    <div class="admin-table-wrap">
      <table class="admin-data-table">
        <thead><tr><th>When</th><th>Who</th><th>Action</th><th>Item</th></tr></thead>
        <tbody>
          <?php foreach ($recentActivity as $entry): ?>
            <tr>
              <td class="muted"><?= e(date('j M Y, H:i', strtotime((string) $entry['created_at']) ?: time())) ?></td>
              <td><?= e((string) $entry['admin_name']) ?></td>
              <td><span class="pill <?= $entry['action'] === 'delete' ? 'red' : 'blue' ?>"><?= e((string) $entry['action']) ?></span></td>
              <td><?= e((string) $entry['entity']) ?> <code><?= e((string) $entry['entity_ref']) ?></code></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="admin-empty">No changes have been recorded yet.</div>
  <?php endif; ?>
</div>
