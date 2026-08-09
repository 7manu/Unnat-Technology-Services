<?php
/** Global SEO, analytics, structured data, sitemap and publishing settings. */
declare(strict_types=1);

/**
 * Each tab is a list of fields rendered into one form.
 * type: text | textarea | code | select | checkbox
 */
$tabs = [
    'identity' => [
        'label' => 'Site identity',
        'intro' => 'The address and name used to build canonical URLs, sitemap entries and structured data.',
        'fields' => [
            ['key' => 'site_url', 'label' => 'Website address', 'type' => 'text', 'hint' => 'No trailing slash, for example https://unnattechnologyservices.com'],
            ['key' => 'site_name', 'label' => 'Site name', 'type' => 'text'],
            ['key' => 'site_locale', 'label' => 'Locale', 'type' => 'text', 'hint' => 'Used for og:locale, for example en_IN'],
            ['key' => 'title_suffix', 'label' => 'Title suffix', 'type' => 'text', 'hint' => 'Appended to page titles that do not already contain it.'],
            ['key' => 'title_separator', 'label' => 'Title separator', 'type' => 'text'],
        ],
    ],
    'seo' => [
        'label' => 'Default SEO',
        'intro' => 'Used on any page that does not define its own values.',
        'fields' => [
            ['key' => 'default_meta_title', 'label' => 'Default meta title', 'type' => 'text', 'counter' => 60],
            ['key' => 'default_meta_description', 'label' => 'Default meta description', 'type' => 'textarea', 'counter' => 160],
            ['key' => 'default_meta_keywords', 'label' => 'Default keywords', 'type' => 'textarea', 'hint' => 'Comma separated. Plan these in the keyword manager.'],
            ['key' => 'default_og_image', 'label' => 'Default social share image', 'type' => 'text', 'hint' => 'Path or full URL. 1200×630 pixels works best.'],
            ['key' => 'default_robots_index', 'label' => 'Default indexing', 'type' => 'select', 'options' => 'robots'],
            ['key' => 'default_robots_follow', 'label' => 'Default link following', 'type' => 'select', 'options' => 'follow'],
            ['key' => 'default_robots_extra', 'label' => 'Extra robots directives', 'type' => 'text'],
            ['key' => 'twitter_card', 'label' => 'Default X / Twitter card', 'type' => 'select', 'options' => ['summary_large_image' => 'Large image', 'summary' => 'Summary']],
            ['key' => 'twitter_site', 'label' => 'X / Twitter site handle', 'type' => 'text', 'hint' => 'Including the @'],
            ['key' => 'twitter_creator', 'label' => 'X / Twitter author handle', 'type' => 'text'],
            ['key' => 'facebook_app_id', 'label' => 'Facebook app ID', 'type' => 'text'],
        ],
    ],
    'verification' => [
        'label' => 'Verification & analytics',
        'intro' => 'Paste only the verification code or measurement ID — the meta tags and scripts are written for you.',
        'fields' => [
            ['key' => 'google_site_verification', 'label' => 'Google Search Console verification', 'type' => 'text'],
            ['key' => 'bing_site_verification', 'label' => 'Bing Webmaster verification', 'type' => 'text'],
            ['key' => 'yandex_verification', 'label' => 'Yandex verification', 'type' => 'text'],
            ['key' => 'pinterest_verification', 'label' => 'Pinterest verification', 'type' => 'text'],
            ['key' => 'facebook_domain_verification', 'label' => 'Facebook domain verification', 'type' => 'text'],
            ['key' => 'google_analytics_id', 'label' => 'Google Analytics 4 measurement ID', 'type' => 'text', 'hint' => 'For example G-XXXXXXXXXX'],
            ['key' => 'google_tag_manager_id', 'label' => 'Google Tag Manager container ID', 'type' => 'text', 'hint' => 'For example GTM-XXXXXX'],
            ['key' => 'facebook_pixel_id', 'label' => 'Meta Pixel ID', 'type' => 'text'],
            ['key' => 'microsoft_clarity_id', 'label' => 'Microsoft Clarity ID', 'type' => 'text'],
            ['key' => 'hotjar_id', 'label' => 'Hotjar site ID', 'type' => 'text'],
            ['key' => 'custom_head_html', 'label' => 'Custom code in &lt;head&gt;', 'type' => 'code'],
            ['key' => 'custom_body_start_html', 'label' => 'Custom code after &lt;body&gt; opens', 'type' => 'code', 'hint' => 'Google Tag Manager noscript tag goes here.'],
            ['key' => 'custom_body_end_html', 'label' => 'Custom code before &lt;/body&gt;', 'type' => 'code'],
        ],
    ],
    'schema' => [
        'label' => 'Structured data',
        'intro' => 'The organisation schema that tells Google what the business is, where it is and how to contact it. Address, phone and social links come from Website content → Global.',
        'fields' => [
            ['key' => 'schema_enabled', 'label' => 'Output organisation schema', 'type' => 'checkbox'],
            ['key' => 'schema_type', 'label' => 'Schema type', 'type' => 'select', 'options' => ['ProfessionalService' => 'ProfessionalService', 'Organization' => 'Organization', 'LocalBusiness' => 'LocalBusiness', 'SoftwareCompany' => 'SoftwareCompany', 'Corporation' => 'Corporation']],
            ['key' => 'schema_price_range', 'label' => 'Price range', 'type' => 'text'],
            ['key' => 'schema_opening_hours', 'label' => 'Opening hours', 'type' => 'text', 'hint' => 'For example Mo-Sa 10:00-19:00'],
            ['key' => 'schema_founding_date', 'label' => 'Founding date', 'type' => 'text', 'hint' => 'YYYY-MM-DD'],
            ['key' => 'schema_area_served', 'label' => 'Area served', 'type' => 'text'],
            ['key' => 'schema_geo_lat', 'label' => 'Latitude', 'type' => 'text'],
            ['key' => 'schema_geo_lng', 'label' => 'Longitude', 'type' => 'text'],
        ],
    ],
    'sitemap' => [
        'label' => 'Sitemap & robots',
        'intro' => 'sitemap.xml and robots.txt are generated from these settings every time they are requested.',
        'fields' => [
            ['key' => 'sitemap_include_pages', 'label' => 'Include custom pages in the sitemap', 'type' => 'checkbox'],
            ['key' => 'sitemap_include_posts', 'label' => 'Include blog articles in the sitemap', 'type' => 'checkbox'],
            ['key' => 'sitemap_include_products', 'label' => 'Include the products page in the sitemap', 'type' => 'checkbox'],
            ['key' => 'sitemap_default_changefreq', 'label' => 'Default change frequency', 'type' => 'select', 'options' => 'changefreq'],
            ['key' => 'sitemap_default_priority', 'label' => 'Default priority', 'type' => 'select', 'options' => 'priority'],
            ['key' => 'robots_txt', 'label' => 'robots.txt content', 'type' => 'code', 'hint' => 'The Sitemap: line is appended automatically.'],
        ],
    ],
    'publishing' => [
        'label' => 'Blog & pages',
        'intro' => 'How articles and custom pages behave.',
        'fields' => [
            ['key' => 'blog_enabled', 'label' => 'Enable the blog', 'type' => 'checkbox'],
            ['key' => 'blog_posts_per_page', 'label' => 'Articles per page', 'type' => 'text'],
            ['key' => 'blog_default_author', 'label' => 'Default author name', 'type' => 'text'],
            ['key' => 'blog_base_path', 'label' => 'Blog script path', 'type' => 'text', 'hint' => 'Change only if you rename blog.php.'],
            ['key' => 'page_base_path', 'label' => 'Page script path', 'type' => 'text', 'hint' => 'Change only if you rename page.php.'],
            ['key' => 'page_default_template', 'label' => 'Default page template', 'type' => 'select', 'options' => ['standard' => 'Standard', 'wide' => 'Wide', 'landing' => 'Landing']],
        ],
    ],
];

$activeTab = (string) ($_GET['tab'] ?? 'identity');
if (!isset($tabs[$activeTab])) {
    $activeTab = 'identity';
}
$tab = $tabs[$activeTab];

/** Resolves the shared option lists referenced by name. */
$resolveOptions = static function ($options): array {
    if (is_array($options)) {
        return $options;
    }

    return match ($options) {
        'robots' => admin_robots_options(),
        'follow' => admin_follow_options(),
        'changefreq' => admin_changefreq_options(),
        'priority' => admin_priority_options(),
        default => [],
    };
};
?>
<div class="admin-page-head">
  <div>
    <h1>SEO &amp; site settings</h1>
    <p>Site-wide configuration. Per-page titles and descriptions live in <a href="admin.php?view=seo">Page SEO</a>.</p>
  </div>
</div>

<div class="admin-tabs">
  <?php foreach ($tabs as $key => $item): ?>
    <a href="admin.php?view=settings&amp;tab=<?= e($key) ?>"<?= $key === $activeTab ? ' class="is-current"' : '' ?>><?= e($item['label']) ?></a>
  <?php endforeach; ?>
</div>

<form action="backend/admin_action.php" method="post">
  <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
  <input type="hidden" name="action" value="settings.save" />
  <input type="hidden" name="return_view" value="settings" />
  <input type="hidden" name="tab" value="<?= e($activeTab) ?>" />
  <input type="hidden" name="settings_group" value="<?= e($activeTab) ?>" />

  <div class="admin-card">
    <h2><?= e($tab['label']) ?></h2>
    <p><?= $tab['intro'] ?></p>
    <div class="admin-form-grid">
      <?php foreach ($tab['fields'] as $field): ?>
        <?php
          $key = $field['key'];
          $value = cms_setting($key);
          $type = $field['type'];
          $isFull = in_array($type, ['textarea', 'code'], true);
        ?>
        <div class="admin-field<?= $isFull ? ' full' : '' ?>">
          <?php if ($type === 'checkbox'): ?>
            <input type="hidden" name="settings[<?= e($key) ?>]" value="0" />
            <label class="admin-check"><input type="checkbox" name="settings[<?= e($key) ?>]" value="1"<?= $value === '1' ? ' checked' : '' ?> /> <?= $field['label'] ?></label>
          <?php else: ?>
            <label for="setting-<?= e($key) ?>"><?= $field['label'] ?></label>
            <?php if ($type === 'select'): ?>
              <?= admin_select('settings[' . $key . ']', $resolveOptions($field['options']), $value, 'setting-' . $key) ?>
            <?php elseif ($type === 'textarea'): ?>
              <textarea id="setting-<?= e($key) ?>" name="settings[<?= e($key) ?>]" rows="3"<?= isset($field['counter']) ? ' data-counter="' . (int) $field['counter'] . '"' : '' ?>><?= e($value) ?></textarea>
            <?php elseif ($type === 'code'): ?>
              <textarea id="setting-<?= e($key) ?>" name="settings[<?= e($key) ?>]" class="code" rows="6"><?= e($value) ?></textarea>
            <?php else: ?>
              <input id="setting-<?= e($key) ?>" name="settings[<?= e($key) ?>]" type="text" value="<?= e($value) ?>"<?= isset($field['counter']) ? ' data-counter="' . (int) $field['counter'] . '"' : '' ?> />
            <?php endif; ?>
          <?php endif; ?>
          <?php if (isset($field['hint'])): ?><span class="hint"><?= $field['hint'] ?></span><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="admin-form-actions">
      <button class="admin-button" type="submit">Save <?= e(strtolower($tab['label'])) ?></button>
      <?php if ($activeTab === 'sitemap'): ?>
        <a class="admin-button ghost" href="sitemap.php" target="_blank" rel="noopener">Preview sitemap ↗</a>
        <a class="admin-button ghost" href="robots.php" target="_blank" rel="noopener">Preview robots.txt ↗</a>
      <?php endif; ?>
    </div>
  </div>
</form>
