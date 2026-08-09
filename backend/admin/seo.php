<?php
/** Per-page SEO manager for the fixed routes of the website. */
declare(strict_types=1);

$records = cms_seo_records();
$coreRoutes = cms_seo_core_routes();

/* Every route that can be tuned here: the fixed pages plus anything already stored. */
$routes = $coreRoutes;
foreach ($records as $route => $record) {
    if (!isset($routes[$route])) {
        $routes[$route] = (string) ($record['route_label'] ?: $route);
    }
}

$activeRoute = (string) ($_GET['route'] ?? array_key_first($routes));
if (!isset($routes[$activeRoute])) {
    $activeRoute = (string) array_key_first($routes);
}

$defaults = [
    'id' => 0,
    'route' => $activeRoute,
    'route_label' => $routes[$activeRoute] ?? '',
    'meta_title' => '',
    'meta_description' => '',
    'meta_keywords' => '',
    'canonical_url' => '',
    'robots_index' => 'index',
    'robots_follow' => 'follow',
    'robots_extra' => cms_setting('default_robots_extra'),
    'og_type' => 'website',
    'og_title' => '',
    'og_description' => '',
    'og_image' => '',
    'twitter_card' => 'summary_large_image',
    'twitter_title' => '',
    'twitter_description' => '',
    'twitter_image' => '',
    'schema_json' => '',
    'hreflang_json' => '',
    'sitemap_include' => 1,
    'sitemap_priority' => '0.6',
    'sitemap_changefreq' => 'monthly',
    'head_extra' => '',
];
$form = array_merge($defaults, $records[$activeRoute] ?? []);
?>
<div class="admin-page-head">
  <div>
    <h1>Page SEO</h1>
    <p>Titles, descriptions, keywords, canonical URLs, robots directives, social cards, structured data and sitemap behaviour — one record per page. Pages and blog articles carry their own SEO fields in their own editors.</p>
  </div>
  <a class="admin-button ghost" href="sitemap.php" target="_blank" rel="noopener">Open sitemap.xml ↗</a>
</div>

<div class="admin-tabs">
  <?php foreach ($routes as $route => $label): ?>
    <a href="admin.php?view=seo&amp;route=<?= rawurlencode((string) $route) ?>"<?= $route === $activeRoute ? ' class="is-current"' : '' ?>><?= e((string) $label) ?></a>
  <?php endforeach; ?>
</div>

<form action="backend/admin_action.php" method="post" enctype="multipart/form-data">
  <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
  <input type="hidden" name="action" value="seo.save" />
  <input type="hidden" name="return_view" value="seo" />

  <div class="admin-card">
    <h2>Search result appearance</h2>
    <p>Editing <code><?= e($activeRoute) ?></code> — live at <?= e(cms_site_url(ltrim($activeRoute, '/'))) ?></p>
    <div class="admin-form-grid">
      <div class="admin-field"><label for="route">Route</label><input id="route" name="route" type="text" value="<?= e((string) $form['route']) ?>" required<?= isset($coreRoutes[$activeRoute]) ? ' readonly' : '' ?> /></div>
      <div class="admin-field"><label for="route_label">Label used in this panel</label><input id="route_label" name="route_label" type="text" value="<?= e((string) $form['route_label']) ?>" /></div>
      <div class="admin-field full"><label for="meta_title">Meta title</label><input id="meta_title" name="meta_title" type="text" data-counter="60" value="<?= e((string) $form['meta_title']) ?>" /></div>
      <div class="admin-field full"><label for="meta_description">Meta description</label><textarea id="meta_description" name="meta_description" rows="2" data-counter="160"><?= e((string) $form['meta_description']) ?></textarea></div>
      <div class="admin-field full"><label for="meta_keywords">Keywords</label><input id="meta_keywords" name="meta_keywords" type="text" value="<?= e((string) $form['meta_keywords']) ?>" placeholder="comma, separated, keywords" /><span class="hint">Track and plan these in the <a href="admin.php?view=keywords">keyword manager</a>.</span></div>
      <div class="admin-field full"><label for="canonical_url">Canonical URL</label><input id="canonical_url" name="canonical_url" type="url" value="<?= e((string) $form['canonical_url']) ?>" placeholder="<?= e(cms_site_url(ltrim($activeRoute, '/'))) ?>" /></div>
    </div>
  </div>

  <div class="admin-card">
    <h2>Crawling and indexing</h2>
    <div class="admin-form-grid">
      <div class="admin-field"><label for="robots_index">Search indexing</label><?= admin_select('robots_index', admin_robots_options(), (string) $form['robots_index'], 'robots_index') ?></div>
      <div class="admin-field"><label for="robots_follow">Link following</label><?= admin_select('robots_follow', admin_follow_options(), (string) $form['robots_follow'], 'robots_follow') ?></div>
      <div class="admin-field full"><label for="robots_extra">Extra robots directives</label><input id="robots_extra" name="robots_extra" type="text" value="<?= e((string) $form['robots_extra']) ?>" placeholder="max-image-preview:large, max-snippet:-1" /></div>
      <div class="admin-field"><label class="admin-check"><input type="checkbox" name="sitemap_include" value="1"<?= (int) $form['sitemap_include'] === 1 ? ' checked' : '' ?> /> Include in sitemap.xml</label></div>
      <div class="admin-field"><label for="sitemap_priority">Sitemap priority</label><?= admin_select('sitemap_priority', admin_priority_options(), (string) $form['sitemap_priority'], 'sitemap_priority') ?></div>
      <div class="admin-field"><label for="sitemap_changefreq">Change frequency</label><?= admin_select('sitemap_changefreq', admin_changefreq_options(), (string) $form['sitemap_changefreq'], 'sitemap_changefreq') ?></div>
      <div class="admin-field full"><label for="hreflang_json">Language alternates (hreflang)</label><input id="hreflang_json" name="hreflang_json" class="code" type="text" value="<?= e((string) $form['hreflang_json']) ?>" placeholder='{"en-in":"https://example.com/","hi-in":"https://example.com/hi/"}' /><span class="hint">JSON object of language code → URL. Leave empty for a single-language site.</span></div>
    </div>
  </div>

  <div class="admin-card">
    <h2>Social sharing cards</h2>
    <p>How the page looks when it is shared on LinkedIn, Facebook, WhatsApp or X. Empty fields fall back to the meta title, description and default share image.</p>
    <div class="admin-form-grid">
      <div class="admin-field"><label for="og_type">Open Graph type</label><?= admin_select('og_type', ['website' => 'website', 'article' => 'article', 'profile' => 'profile', 'product' => 'product'], (string) $form['og_type'], 'og_type') ?></div>
      <div class="admin-field"><label for="og_title">Open Graph title</label><input id="og_title" name="og_title" type="text" value="<?= e((string) $form['og_title']) ?>" /></div>
      <div class="admin-field full"><label for="og_description">Open Graph description</label><textarea id="og_description" name="og_description" rows="2"><?= e((string) $form['og_description']) ?></textarea></div>
      <div class="admin-field"><label for="og_upload">Share image — upload</label><input id="og_upload" name="og_upload" type="file" accept="image/*" /></div>
      <div class="admin-field"><label for="og_image">Share image — path</label><input id="og_image" name="og_image" type="text" value="<?= e((string) $form['og_image']) ?>" /></div>
      <div class="admin-field"><label for="twitter_card">X / Twitter card</label><?= admin_select('twitter_card', ['summary_large_image' => 'Large image', 'summary' => 'Summary'], (string) $form['twitter_card'], 'twitter_card') ?></div>
      <div class="admin-field"><label for="twitter_title">X / Twitter title</label><input id="twitter_title" name="twitter_title" type="text" value="<?= e((string) $form['twitter_title']) ?>" /></div>
      <div class="admin-field full"><label for="twitter_description">X / Twitter description</label><textarea id="twitter_description" name="twitter_description" rows="2"><?= e((string) $form['twitter_description']) ?></textarea></div>
      <div class="admin-field full"><label for="twitter_image">X / Twitter image</label><input id="twitter_image" name="twitter_image" type="text" value="<?= e((string) $form['twitter_image']) ?>" /></div>
    </div>
  </div>

  <div class="admin-card">
    <h2>Structured data and custom head tags</h2>
    <div class="admin-form-grid">
      <div class="admin-field full"><label for="schema_json">JSON-LD schema for this page</label><textarea id="schema_json" name="schema_json" class="code" rows="6" placeholder="Leave empty to use the site-wide organisation schema"><?= e((string) $form['schema_json']) ?></textarea><span class="hint">Invalid JSON is ignored rather than printed, so a mistake here cannot break the page.</span></div>
      <div class="admin-field full"><label for="head_extra">Extra tags for &lt;head&gt;</label><textarea id="head_extra" name="head_extra" class="code" rows="4" placeholder="&lt;link rel=&quot;alternate&quot; type=&quot;application/rss+xml&quot; href=&quot;…&quot; /&gt;"><?= e((string) $form['head_extra']) ?></textarea></div>
    </div>
  </div>

  <div class="admin-form-actions">
    <button class="admin-button" type="submit">Save SEO for <?= e($activeRoute) ?></button>
    <a class="admin-button ghost" href="<?= e(cms_site_url(ltrim($activeRoute, '/'))) ?>" target="_blank" rel="noopener">Open page ↗</a>
  </div>
</form>

<div class="admin-card">
  <h2>Add another route</h2>
  <p>Create an SEO record for any other URL on this domain, for example <code>/offline.html</code> or a landing page you host separately.</p>
  <form action="backend/admin_action.php" method="post">
    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
    <input type="hidden" name="action" value="seo.save" />
    <input type="hidden" name="return_view" value="seo" />
    <div class="admin-form-grid">
      <div class="admin-field"><label for="new_route">Route</label><input id="new_route" name="route" type="text" placeholder="/example.php" required /></div>
      <div class="admin-field"><label for="new_route_label">Label</label><input id="new_route_label" name="route_label" type="text" placeholder="Example page" /></div>
      <div class="admin-field"><label for="new_meta_title">Meta title</label><input id="new_meta_title" name="meta_title" type="text" /></div>
      <div class="admin-field full"><label for="new_meta_description">Meta description</label><textarea id="new_meta_description" name="meta_description" rows="2"></textarea></div>
    </div>
    <div class="admin-form-actions"><button class="admin-button" type="submit">Add route</button></div>
  </form>
</div>

<?php $extraRecords = array_diff_key($records, $coreRoutes); ?>
<?php if ($extraRecords): ?>
  <div class="admin-card">
    <h2>Custom routes</h2>
    <div class="admin-table-wrap">
      <table class="admin-data-table">
        <thead><tr><th>Route</th><th>Meta title</th><th>Indexing</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($extraRecords as $route => $record): ?>
            <tr>
              <td><code><?= e((string) $route) ?></code></td>
              <td><?= e((string) $record['meta_title']) ?></td>
              <td><span class="pill <?= $record['robots_index'] === 'index' ? 'blue' : 'red' ?>"><?= e((string) $record['robots_index']) ?></span></td>
              <td class="actions">
                <a class="admin-button ghost small" href="admin.php?view=seo&amp;route=<?= rawurlencode((string) $route) ?>">Edit</a>
                <form class="admin-inline-form" action="backend/admin_action.php" method="post" data-confirm="Delete this SEO record?">
                  <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
                  <input type="hidden" name="action" value="seo.delete" />
                  <input type="hidden" name="return_view" value="seo" />
                  <input type="hidden" name="id" value="<?= (int) $record['id'] ?>" />
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
