<?php
/** Blog manager — write, publish and optimise articles. */
declare(strict_types=1);

$editing = isset($_GET['id']);
$postId = (string) ($_GET['id'] ?? '');
$post = null;

if ($editing && $postId !== 'new') {
    $post = admin_row('SELECT * FROM `cms_posts` WHERE `id` = ? LIMIT 1', 'i', [(int) $postId]);
    if ($post === null) {
        $editing = false;
    }
}

$defaults = [
    'id' => 0,
    'slug' => '',
    'title' => '',
    'cover_image' => '',
    'excerpt' => '',
    'body' => "<h2>Start with the problem</h2>\n<p>Open with the situation your reader recognises.</p>\n<h2>Explain the approach</h2>\n<p>Describe what you would do and why it works.</p>\n<ul>\n  <li>Key point one</li>\n  <li>Key point two</li>\n</ul>\n<h2>What to do next</h2>\n<p>Finish with a clear, useful next step.</p>",
    'author' => cms_setting('blog_default_author'),
    'category' => '',
    'tags' => '',
    'status' => 'draft',
    'is_featured' => 0,
    'published_at' => '',
    'views' => 0,
    'meta_title' => '',
    'meta_description' => '',
    'meta_keywords' => '',
    'canonical_url' => '',
    'robots_index' => 'index',
    'robots_follow' => 'follow',
    'og_image' => '',
    'schema_json' => '',
    'sitemap_include' => 1,
    'sitemap_priority' => '0.7',
    'sitemap_changefreq' => 'monthly',
];
$form = $post !== null ? array_merge($defaults, $post) : $defaults;
$publishedValue = (string) $form['published_at'] !== '' ? date('Y-m-d\TH:i', strtotime((string) $form['published_at']) ?: time()) : '';
?>

<?php if ($editing): ?>
  <div class="admin-page-head">
    <div>
      <h1><?= $post ? 'Edit article' : 'New article' ?></h1>
      <p>Articles appear on the blog page, on the homepage insights strip and in sitemap.xml once published.</p>
    </div>
    <a class="admin-button ghost" href="admin.php?view=blogs">← All articles</a>
  </div>

  <form action="backend/admin_action.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
    <input type="hidden" name="action" value="post.save" />
    <input type="hidden" name="return_view" value="blogs" />
    <input type="hidden" name="id" value="<?= (int) $form['id'] ?>" />

    <div class="admin-card">
      <h2>Article</h2>
      <div class="admin-form-grid">
        <div class="admin-field full"><label for="title">Title</label><input id="title" name="title" type="text" data-slug-source value="<?= e((string) $form['title']) ?>" required /></div>
        <div class="admin-field"><label for="slug">URL slug</label><input id="slug" name="slug" type="text" data-slug-target value="<?= e((string) $form['slug']) ?>" /></div>
        <div class="admin-field"><label for="category">Category</label><input id="category" name="category" type="text" value="<?= e((string) $form['category']) ?>" placeholder="Web platforms" /></div>
        <div class="admin-field"><label for="author">Author</label><input id="author" name="author" type="text" value="<?= e((string) $form['author']) ?>" /></div>
        <div class="admin-field"><label for="tags">Tags</label><input id="tags" name="tags" type="text" value="<?= e((string) $form['tags']) ?>" placeholder="automation, workflow, india" /></div>
        <div class="admin-field"><label for="cover_upload">Cover image — upload</label><input id="cover_upload" name="cover_upload" type="file" accept="image/*" /></div>
        <div class="admin-field"><label for="cover_image">Cover image — path</label><input id="cover_image" name="cover_image" type="text" value="<?= e((string) $form['cover_image']) ?>" placeholder="assets/uploads/cover.webp" /><?php if ((string) $form['cover_image'] !== ''): ?><img class="content-image-preview" src="<?= e((string) $form['cover_image']) ?>" alt="Current cover image" loading="lazy" /><?php endif; ?><span class="hint">If no thumbnail appears here, the path is wrong — copy it again from the <a href="admin.php?view=media">media library</a>.</span></div>
        <div class="admin-field full"><label for="excerpt">Summary</label><textarea id="excerpt" name="excerpt" rows="2" data-counter="160"><?= e((string) $form['excerpt']) ?></textarea><span class="hint">Shown on article cards and used as the fallback meta description.</span></div>
        <div class="admin-field full"><label for="body">Article body</label><textarea id="body" name="body" class="tall"><?= e((string) $form['body']) ?></textarea><span class="hint">HTML is allowed. Reading time is calculated automatically when you save.</span></div>
      </div>
    </div>

    <div class="admin-card">
      <h2>Publishing</h2>
      <div class="admin-form-grid">
        <div class="admin-field"><label for="status">Status</label><?= admin_select('status', ['draft' => 'Draft (hidden)', 'published' => 'Published (live)'], (string) $form['status'], 'status') ?></div>
        <div class="admin-field"><label for="published_at">Publish date &amp; time</label><input id="published_at" name="published_at" type="datetime-local" value="<?= e($publishedValue) ?>" /><span class="hint">Leave empty to use the moment you publish.</span></div>
        <div class="admin-field"><label class="admin-check"><input type="checkbox" name="is_featured" value="1"<?= (int) $form['is_featured'] === 1 ? ' checked' : '' ?> /> Feature this article</label></div>
        <?php if ($post): ?><div class="admin-field"><label>Views</label><p class="muted"><?= (int) $form['views'] ?> page views recorded</p></div><?php endif; ?>
      </div>
    </div>

    <div class="admin-card">
      <h2>Search engine settings</h2>
      <div class="admin-form-grid">
        <div class="admin-field full"><label for="meta_title">Meta title</label><input id="meta_title" name="meta_title" type="text" data-counter="60" value="<?= e((string) $form['meta_title']) ?>" /></div>
        <div class="admin-field full"><label for="meta_description">Meta description</label><textarea id="meta_description" name="meta_description" rows="2" data-counter="160"><?= e((string) $form['meta_description']) ?></textarea></div>
        <div class="admin-field full"><label for="meta_keywords">Focus keywords</label><input id="meta_keywords" name="meta_keywords" type="text" value="<?= e((string) $form['meta_keywords']) ?>" /></div>
        <div class="admin-field"><label for="canonical_url">Canonical URL</label><input id="canonical_url" name="canonical_url" type="url" value="<?= e((string) $form['canonical_url']) ?>" /></div>
        <div class="admin-field"><label for="og_image">Social share image</label><input id="og_image" name="og_image" type="text" value="<?= e((string) $form['og_image']) ?>" placeholder="Falls back to the cover image" /></div>
        <div class="admin-field"><label for="robots_index">Search indexing</label><?= admin_select('robots_index', admin_robots_options(), (string) $form['robots_index'], 'robots_index') ?></div>
        <div class="admin-field"><label for="robots_follow">Link following</label><?= admin_select('robots_follow', admin_follow_options(), (string) $form['robots_follow'], 'robots_follow') ?></div>
        <div class="admin-field"><label class="admin-check"><input type="checkbox" name="sitemap_include" value="1"<?= (int) $form['sitemap_include'] === 1 ? ' checked' : '' ?> /> Include in sitemap.xml</label></div>
        <div class="admin-field"><label for="sitemap_priority">Sitemap priority</label><?= admin_select('sitemap_priority', admin_priority_options(), (string) $form['sitemap_priority'], 'sitemap_priority') ?></div>
        <div class="admin-field"><label for="sitemap_changefreq">Sitemap change frequency</label><?= admin_select('sitemap_changefreq', admin_changefreq_options(), (string) $form['sitemap_changefreq'], 'sitemap_changefreq') ?></div>
        <div class="admin-field full"><label for="schema_json">Structured data (JSON-LD)</label><textarea id="schema_json" name="schema_json" class="code" rows="4" placeholder="Leave empty — a BlogPosting schema is generated automatically"><?= e((string) $form['schema_json']) ?></textarea></div>
      </div>
    </div>

    <div class="admin-form-actions">
      <button class="admin-button" type="submit"><?= $post ? 'Save article' : 'Create article' ?></button>
      <?php if ($post): ?><a class="admin-button ghost" href="<?= e(cms_post_url($post)) ?>" target="_blank" rel="noopener">View article ↗</a><?php endif; ?>
      <a class="admin-button ghost" href="admin.php?view=blogs">Cancel</a>
    </div>
  </form>

<?php else: ?>
  <?php $posts = admin_rows('SELECT * FROM `cms_posts` ORDER BY COALESCE(`published_at`, `created_at`) DESC, `id` DESC'); ?>
  <div class="admin-page-head">
    <div>
      <h1>Blog</h1>
      <p>Publishing articles is the most reliable way to grow search traffic and earn backlinks. The blog is <?= cms_setting('blog_enabled') === '1' ? 'enabled' : 'disabled — turn it on in settings' ?>.</p>
    </div>
    <a class="admin-button" href="admin.php?view=blogs&amp;id=new">+ New article</a>
  </div>

  <?php if ($posts): ?>
    <div class="admin-table-wrap">
      <table class="admin-data-table">
        <thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Published</th><th>Views</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($posts as $row): ?>
            <tr>
              <td><strong><?= e((string) $row['title']) ?></strong><div class="muted"><code><?= e(cms_post_url($row)) ?></code></div></td>
              <td><?= (string) $row['category'] !== '' ? e((string) $row['category']) : '<span class="muted">—</span>' ?></td>
              <td><span class="pill <?= $row['status'] === 'published' ? 'green' : 'amber' ?>"><?= e((string) $row['status']) ?></span></td>
              <td class="muted"><?= (string) $row['published_at'] !== '' && $row['published_at'] !== null ? e(date('j M Y', strtotime((string) $row['published_at']) ?: time())) : '—' ?></td>
              <td><?= (int) $row['views'] ?></td>
              <td class="actions">
                <a class="admin-button ghost small" href="admin.php?view=blogs&amp;id=<?= (int) $row['id'] ?>">Edit</a>
                <a class="admin-button ghost small" href="<?= e(cms_post_url($row)) ?>" target="_blank" rel="noopener">View</a>
                <form class="admin-inline-form" action="backend/admin_action.php" method="post" data-confirm="Delete this article permanently?">
                  <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
                  <input type="hidden" name="action" value="post.delete" />
                  <input type="hidden" name="return_view" value="blogs" />
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
    <div class="admin-empty">No articles yet. Use “New article” to publish the first one.</div>
  <?php endif; ?>
<?php endif; ?>
