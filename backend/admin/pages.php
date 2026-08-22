<?php
/** Custom page builder — create, edit and delete pages with their own SEO. */
declare(strict_types=1);

$editing = isset($_GET['id']);
$pageId = (string) ($_GET['id'] ?? '');
$page = null;

if ($editing && $pageId !== 'new') {
    $page = admin_row('SELECT * FROM `cms_pages` WHERE `id` = ? LIMIT 1', 'i', [(int) $pageId]);
    if ($page === null) {
        $editing = false;
    }
}

/** Default template values for a brand-new page. */
$defaults = [
    'id' => 0,
    'slug' => '',
    'title' => '',
    'subtitle' => '',
    'cover_image' => '',
    'description' => '',
    'body' => "<h2>Section heading</h2>\n<p>Write the first paragraph of the page here. Use the heading, paragraph and list tags to structure the content — the site styling is applied automatically.</p>\n<ul>\n  <li>First supporting point</li>\n  <li>Second supporting point</li>\n</ul>\n<h2>Second heading</h2>\n<p>Close with what the visitor should do next.</p>",
    'template' => cms_setting('page_default_template', 'standard'),
    'status' => 'draft',
    'show_in_nav' => 0,
    'show_in_header' => 0,
    'show_in_mobile' => 0,
    'show_in_footer' => 0,
    'nav_menu' => 'primary',
    'footer_menu' => 'footer_explore',
    'sort_order' => 100,
    'meta_title' => '',
    'meta_description' => '',
    'meta_keywords' => '',
    'canonical_url' => '',
    'robots_index' => 'index',
    'robots_follow' => 'follow',
    'og_image' => '',
    'schema_json' => '',
    'sitemap_include' => 1,
    'sitemap_priority' => '0.6',
    'sitemap_changefreq' => 'monthly',
];
$form = $page !== null ? array_merge($defaults, $page) : $defaults;
?>

<?php if ($editing): ?>
  <div class="admin-page-head">
    <div>
      <h1><?= $page ? 'Edit page' : 'New page' ?></h1>
      <p>The template gives every page a title, cover image, description and body. SEO fields below control how it appears in search results.</p>
    </div>
    <a class="admin-button ghost" href="admin.php?view=pages">← All pages</a>
  </div>

  <form action="backend/admin_action.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
    <input type="hidden" name="action" value="page.save" />
    <input type="hidden" name="return_view" value="pages" />
    <input type="hidden" name="id" value="<?= (int) $form['id'] ?>" />

    <div class="admin-card">
      <h2>Page template</h2>
      <p>These four fields are all a page needs to go live.</p>
      <div class="admin-form-grid">
        <div class="admin-field full"><label for="title">Page title</label><input id="title" name="title" type="text" data-slug-source value="<?= e((string) $form['title']) ?>" required /></div>
        <div class="admin-field"><label for="slug">URL slug</label><input id="slug" name="slug" type="text" data-slug-target value="<?= e((string) $form['slug']) ?>" placeholder="about-our-team" /><span class="hint">Address: <?= e(cms_site_url(cms_setting('page_base_path', 'page.php') . '?slug=')) ?><strong>slug</strong></span></div>
        <div class="admin-field"><label for="subtitle">Eyebrow / subtitle</label><input id="subtitle" name="subtitle" type="text" value="<?= e((string) $form['subtitle']) ?>" placeholder="About us" /></div>
        <div class="admin-field"><label for="cover_upload">Cover image — upload</label><input id="cover_upload" name="cover_upload" type="file" accept="image/*" /></div>
        <div class="admin-field"><label for="cover_image">Cover image — path</label><input id="cover_image" name="cover_image" type="text" value="<?= e((string) $form['cover_image']) ?>" placeholder="assets/uploads/cover.webp" /><?php if ((string) $form['cover_image'] !== ''): ?><img class="content-image-preview" src="<?= e((string) $form['cover_image']) ?>" alt="Current cover image" loading="lazy" /><?php endif; ?><span class="hint">If no thumbnail appears here, the path is wrong — copy it again from the <a href="admin.php?view=media">media library</a>.</span></div>
        <div class="admin-field full"><label for="description">Short description</label><textarea id="description" name="description" rows="2" data-counter="160"><?= e((string) $form['description']) ?></textarea><span class="hint">Shown under the page title and used as the fallback meta description.</span></div>
        <div class="admin-field"><label for="template">Layout template</label><?= admin_select('template', ['standard' => 'Standard — cover image above the content', 'wide' => 'Wide — full-width content and edge-to-edge cover', 'landing' => 'Landing — cover plus closing call to action'], (string) $form['template'], 'template') ?></div>
        <div class="admin-field"><label for="status">Status</label><?= admin_select('status', ['draft' => 'Draft (hidden)', 'published' => 'Published (live)'], (string) $form['status'], 'status') ?></div>
        <div class="admin-field full"><label for="body">Page content</label><textarea id="body" name="body" class="tall"><?= e((string) $form['body']) ?></textarea><span class="hint">HTML is allowed: headings, paragraphs, lists, links, images, tables and quotes. Scripts and iframes are removed automatically.</span></div>
      </div>
    </div>

    <div class="admin-card">
      <h2>Menu placement</h2>
      <p>Choose where this page appears. Tick any combination — the menu links are created, updated and removed for you.</p>
      <div class="admin-form-grid">
        <div class="admin-field"><label class="admin-check"><input type="checkbox" name="show_in_header" value="1"<?= (int) $form['show_in_header'] === 1 ? ' checked' : '' ?> /> Show in the header menu (desktop)</label><span class="hint">The horizontal menu across the top on wide screens.</span></div>
        <div class="admin-field"><label class="admin-check"><input type="checkbox" name="show_in_mobile" value="1"<?= (int) $form['show_in_mobile'] === 1 ? ' checked' : '' ?> /> Show in the mobile menu bar</label><span class="hint">The drawer that opens from the ☰ button on phones and tablets.</span></div>
        <div class="admin-field"><label class="admin-check"><input type="checkbox" name="show_in_footer" value="1"<?= (int) $form['show_in_footer'] === 1 ? ' checked' : '' ?> /> Show in the footer</label></div>
        <div class="admin-field"><label for="footer_menu">Footer column</label><?= admin_select('footer_menu', ['footer_explore' => 'Column 1 — ' . cms_raw('footer.column_1.title'), 'footer_platforms' => 'Column 2 — ' . cms_raw('footer.column_2.title'), 'footer_contact' => 'Column 3 — ' . cms_raw('footer.column_3.title')], (string) $form['footer_menu'], 'footer_menu') ?></div>
        <div class="admin-field"><label for="sort_order">Menu position</label><input id="sort_order" name="sort_order" type="number" value="<?= (int) $form['sort_order'] ?>" /><span class="hint">Lower numbers appear first.</span></div>
      </div>
      <p class="hint">Ticking header only hides the link on phones; ticking mobile only hides it on desktop. Tick both to show it everywhere. Fine-tune any link later in <a href="admin.php?view=navigation">Links &amp; URLs</a>.</p>
    </div>

    <div class="admin-card">
      <h2>Search engine settings</h2>
      <p>Leave a field empty to fall back to the page title, description and site defaults.</p>
      <div class="admin-form-grid">
        <div class="admin-field full"><label for="meta_title">Meta title</label><input id="meta_title" name="meta_title" type="text" data-counter="60" value="<?= e((string) $form['meta_title']) ?>" /></div>
        <div class="admin-field full"><label for="meta_description">Meta description</label><textarea id="meta_description" name="meta_description" rows="2" data-counter="160"><?= e((string) $form['meta_description']) ?></textarea></div>
        <div class="admin-field full"><label for="meta_keywords">Focus keywords</label><input id="meta_keywords" name="meta_keywords" type="text" value="<?= e((string) $form['meta_keywords']) ?>" placeholder="comma, separated, keywords" /></div>
        <div class="admin-field"><label for="canonical_url">Canonical URL</label><input id="canonical_url" name="canonical_url" type="url" value="<?= e((string) $form['canonical_url']) ?>" placeholder="Leave empty for the default" /></div>
        <div class="admin-field"><label for="og_image">Social share image</label><input id="og_image" name="og_image" type="text" value="<?= e((string) $form['og_image']) ?>" placeholder="Falls back to the cover image" /></div>
        <div class="admin-field"><label for="robots_index">Search indexing</label><?= admin_select('robots_index', admin_robots_options(), (string) $form['robots_index'], 'robots_index') ?></div>
        <div class="admin-field"><label for="robots_follow">Link following</label><?= admin_select('robots_follow', admin_follow_options(), (string) $form['robots_follow'], 'robots_follow') ?></div>
        <div class="admin-field"><label class="admin-check"><input type="checkbox" name="sitemap_include" value="1"<?= (int) $form['sitemap_include'] === 1 ? ' checked' : '' ?> /> Include in sitemap.xml</label></div>
        <div class="admin-field"><label for="sitemap_priority">Sitemap priority</label><?= admin_select('sitemap_priority', admin_priority_options(), (string) $form['sitemap_priority'], 'sitemap_priority') ?></div>
        <div class="admin-field"><label for="sitemap_changefreq">Sitemap change frequency</label><?= admin_select('sitemap_changefreq', admin_changefreq_options(), (string) $form['sitemap_changefreq'], 'sitemap_changefreq') ?></div>
        <div class="admin-field full"><label for="schema_json">Structured data (JSON-LD)</label><textarea id="schema_json" name="schema_json" class="code" rows="4" placeholder="Leave empty to use the site-wide organisation schema"><?= e((string) $form['schema_json']) ?></textarea></div>
      </div>
    </div>

    <div class="admin-form-actions">
      <button class="admin-button" type="submit"><?= $page ? 'Save page' : 'Create page' ?></button>
      <?php if ($page): ?><a class="admin-button ghost" href="<?= e(cms_page_url($page)) ?>" target="_blank" rel="noopener">View page ↗</a><?php endif; ?>
      <a class="admin-button ghost" href="admin.php?view=pages">Cancel</a>
    </div>
  </form>

<?php else: ?>
  <?php $pages = admin_rows('SELECT * FROM `cms_pages` ORDER BY `sort_order`, `id` DESC'); ?>
  <div class="admin-page-head">
    <div>
      <h1>Pages</h1>
      <p>Build new pages from the default template — title, cover image, description and content — and control their SEO individually.</p>
    </div>
    <a class="admin-button" href="admin.php?view=pages&amp;id=new">+ New page</a>
  </div>

  <?php if ($pages): ?>
    <div class="admin-table-wrap">
      <table class="admin-data-table">
        <thead><tr><th>Title</th><th>URL</th><th>Status</th><th>Appears in</th><th>Indexing</th><th>Updated</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($pages as $row): ?>
            <tr>
              <td><strong><?= e((string) $row['title']) ?></strong><?php if ((string) $row['subtitle'] !== ''): ?><div class="muted"><?= e((string) $row['subtitle']) ?></div><?php endif; ?></td>
              <td><code><?= e(cms_page_url($row)) ?></code></td>
              <td><span class="pill <?= $row['status'] === 'published' ? 'green' : 'amber' ?>"><?= e((string) $row['status']) ?></span></td>
              <?php
                $placements = [];
                if ((int) ($row['show_in_header'] ?? 0) === 1) { $placements[] = 'header'; }
                if ((int) ($row['show_in_mobile'] ?? 0) === 1) { $placements[] = 'mobile'; }
                if ((int) ($row['show_in_footer'] ?? 0) === 1) { $placements[] = 'footer'; }
              ?>
              <td><?= $placements ? '<span class="pill blue">' . implode('</span> <span class="pill blue">', array_map('e', $placements)) . '</span>' : '<span class="muted">—</span>' ?></td>
              <td><span class="pill <?= $row['robots_index'] === 'index' ? 'blue' : 'red' ?>"><?= e((string) $row['robots_index']) ?></span></td>
              <td class="muted"><?= e(date('j M Y', strtotime((string) $row['updated_at']) ?: time())) ?></td>
              <td class="actions">
                <a class="admin-button ghost small" href="admin.php?view=pages&amp;id=<?= (int) $row['id'] ?>">Edit</a>
                <a class="admin-button ghost small" href="<?= e(cms_page_url($row)) ?>" target="_blank" rel="noopener">View</a>
                <form class="admin-inline-form" action="backend/admin_action.php" method="post" data-confirm="Delete this page permanently?">
                  <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
                  <input type="hidden" name="action" value="page.delete" />
                  <input type="hidden" name="return_view" value="pages" />
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
    <div class="admin-empty">No custom pages yet. Use “New page” to create one from the default template.</div>
  <?php endif; ?>
<?php endif; ?>
