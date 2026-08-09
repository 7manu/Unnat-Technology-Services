<?php
/**
 * Website content editor.
 *
 * Every visible string on the public site is one row in `cms_content`, keyed by
 * its place on the website (for example `home.hero.headline`). Rows are grouped
 * by page, then by the section of that page they appear in.
 */
declare(strict_types=1);

$rows = cms_content_rows();

$groups = [];
foreach ($rows as $row) {
    $groups[(string) $row['page_label']][(string) $row['section_label']][] = $row;
}
$groupNames = array_keys($groups);

$activeGroup = (string) ($_GET['group'] ?? '');
if (!in_array($activeGroup, $groupNames, true)) {
    $activeGroup = $groupNames[0] ?? '';
}
$activeSections = $groups[$activeGroup] ?? [];
$fieldCount = 0;
foreach ($activeSections as $sectionRows) {
    $fieldCount += count($sectionRows);
}
?>
<div class="admin-page-head">
  <div>
    <h1>Website content</h1>
    <p>Every word, link and image on the public website — <?= count($rows) ?> editable fields in total. Keys are named after the place they appear, for example <code>home.hero.headline_prefix</code>.</p>
  </div>
  <a class="admin-button ghost" href="index.php" target="_blank" rel="noopener">Preview site ↗</a>
</div>

<div class="admin-tabs">
  <?php foreach ($groupNames as $name): ?>
    <a href="admin.php?view=content&amp;group=<?= rawurlencode($name) ?>"<?= $name === $activeGroup ? ' class="is-current"' : '' ?>><?= e($name) ?></a>
  <?php endforeach; ?>
</div>

<div class="admin-toolbar">
  <input type="search" data-content-filter placeholder="Search within <?= e($activeGroup) ?> — by label, key or current text" aria-label="Filter content fields" />
  <span class="muted"><?= $fieldCount ?> fields in this group</span>
</div>

<?php if ($activeSections === []): ?>
  <div class="admin-empty">No content fields were found. Use “Re-check database &amp; content keys” on the dashboard to load them.</div>
<?php else: ?>
  <form action="backend/admin_action.php" method="post">
    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
    <input type="hidden" name="action" value="content.save" />
    <input type="hidden" name="return_view" value="content" />
    <input type="hidden" name="group" value="<?= e($activeGroup) ?>" />

    <?php foreach ($activeSections as $sectionName => $sectionRows): ?>
      <details class="content-section" open>
        <summary><?= e($activeGroup) ?> › <?= e($sectionName) ?> <span class="muted">(<?= count($sectionRows) ?>)</span></summary>
        <div class="content-rows">
          <?php foreach ($sectionRows as $row): ?>
            <?php
              $key = (string) $row['content_key'];
              $value = (string) ($row['content_value'] ?? '');
              $type = (string) $row['field_type'];
              $inputId = 'field-' . preg_replace('/[^a-z0-9]+/i', '-', $key);
              $searchIndex = strtolower($row['field_label'] . ' ' . $key . ' ' . $value);
            ?>
            <div class="content-row" data-search="<?= e($searchIndex) ?>">
              <div class="content-row-head">
                <label for="<?= e($inputId) ?>"><?= e((string) $row['field_label']) ?></label>
                <span class="content-key"><?= e($key) ?></span>
                <?php if ((int) $row['is_custom'] === 1): ?><span class="pill amber">custom</span><?php endif; ?>
              </div>

              <?php if ($type === 'textarea' || $type === 'html'): ?>
                <textarea id="<?= e($inputId) ?>" name="content[<?= e($key) ?>]" rows="3"><?= e($value) ?></textarea>
              <?php elseif ($type === 'image'): ?>
                <input id="<?= e($inputId) ?>" name="content[<?= e($key) ?>]" type="text" value="<?= e($value) ?>" placeholder="assets/images/example.webp" />
                <?php if ($value !== ''): ?><img class="content-image-preview" src="<?= e($value) ?>" alt="" loading="lazy" /><?php endif; ?>
                <p class="hint">Upload files in the <a href="admin.php?view=media">media library</a>, then paste the path here.</p>
              <?php elseif ($type === 'url'): ?>
                <input id="<?= e($inputId) ?>" name="content[<?= e($key) ?>]" type="text" value="<?= e($value) ?>" placeholder="https://example.com or #section or page.php" />
              <?php elseif ($type === 'email'): ?>
                <input id="<?= e($inputId) ?>" name="content[<?= e($key) ?>]" type="email" value="<?= e($value) ?>" />
              <?php elseif ($type === 'tel'): ?>
                <input id="<?= e($inputId) ?>" name="content[<?= e($key) ?>]" type="tel" value="<?= e($value) ?>" />
              <?php elseif ($type === 'number'): ?>
                <input id="<?= e($inputId) ?>" name="content[<?= e($key) ?>]" type="number" value="<?= e($value) ?>" />
              <?php else: ?>
                <input id="<?= e($inputId) ?>" name="content[<?= e($key) ?>]" type="text" value="<?= e($value) ?>" />
              <?php endif; ?>

              <?php if ((int) $row['is_custom'] === 1): ?>
                <p class="hint">Custom field — delete it from the list at the bottom of this page.</p>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </details>
    <?php endforeach; ?>

    <div class="sticky-save">
      <button class="admin-button" type="submit">Save all changes in <?= e($activeGroup) ?></button>
      <span class="muted">Changes appear on the public website immediately.</span>
    </div>
  </form>
<?php endif; ?>

<div class="admin-card" style="margin-top:24px">
  <h2>Add a custom content field</h2>
  <p>Create your own editable text anywhere on the site. Use a key that describes the place it belongs to, for example <code>home.hero.offer_note</code>, then output it in the template with <code>cms_text('your.key')</code>.</p>
  <form action="backend/admin_action.php" method="post">
    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
    <input type="hidden" name="action" value="content.add" />
    <input type="hidden" name="return_view" value="content" />
    <div class="admin-form-grid">
      <div class="admin-field"><label for="content_key">Content key</label><input id="content_key" name="content_key" type="text" placeholder="home.hero.offer_note" required /><span class="hint">Lower case, dots between page, section and element.</span></div>
      <div class="admin-field"><label for="page_label">Page</label><input id="page_label" name="page_label" type="text" value="<?= e($activeGroup) ?>" list="page-labels" required /><datalist id="page-labels"><?php foreach ($groupNames as $name): ?><option value="<?= e($name) ?>"></option><?php endforeach; ?></datalist></div>
      <div class="admin-field"><label for="section_label">Section</label><input id="section_label" name="section_label" type="text" placeholder="Hero" required /></div>
      <div class="admin-field"><label for="field_label">Field name shown here</label><input id="field_label" name="field_label" type="text" placeholder="Offer note" required /></div>
      <div class="admin-field"><label for="field_type">Field type</label><?= admin_select('field_type', ['text' => 'Single line text', 'textarea' => 'Paragraph', 'html' => 'Rich HTML', 'url' => 'Link / URL', 'email' => 'Email', 'tel' => 'Phone', 'image' => 'Image path', 'number' => 'Number'], 'text', 'field_type') ?></div>
      <div class="admin-field full"><label for="content_value">Value</label><textarea id="content_value" name="content_value" rows="2"></textarea></div>
    </div>
    <div class="admin-form-actions"><button class="admin-button" type="submit">Add field</button></div>
  </form>
</div>

<?php $customRows = array_filter($rows, static fn(array $row): bool => (int) $row['is_custom'] === 1); ?>
<?php if ($customRows): ?>
  <div class="admin-card">
    <h2>Custom fields</h2>
    <p>Fields you created. Built-in fields cannot be deleted so the website always has its copy.</p>
    <div class="admin-table-wrap">
      <table class="admin-data-table">
        <thead><tr><th>Key</th><th>Place</th><th>Value</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach ($customRows as $row): ?>
            <tr>
              <td><code><?= e((string) $row['content_key']) ?></code></td>
              <td><?= e((string) $row['page_label']) ?> › <?= e((string) $row['section_label']) ?></td>
              <td class="muted"><?= e(cms_excerpt((string) $row['content_value'], 70)) ?></td>
              <td class="actions">
                <form class="admin-inline-form" action="backend/admin_action.php" method="post" data-confirm="Delete this custom field permanently?">
                  <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
                  <input type="hidden" name="action" value="content.delete" />
                  <input type="hidden" name="return_view" value="content" />
                  <input type="hidden" name="id" value="<?= (int) $row['id'] ?>" />
                  <button class="admin-button danger small" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>
