<?php
/** Media library — upload images once, reuse their paths anywhere in the panel. */
declare(strict_types=1);

$media = admin_rows('SELECT * FROM `cms_media` ORDER BY `id` DESC');
?>
<div class="admin-page-head">
  <div>
    <h1>Media library</h1>
    <p>Upload images here, then paste the path into a content field, page cover, blog cover or social share image.</p>
  </div>
</div>

<div class="admin-card">
  <h2>Upload an image</h2>
  <p>JPG, PNG, WebP, AVIF, GIF or SVG, up to 5 MB. Files are stored in <code>assets/uploads/</code>.</p>
  <form action="backend/admin_action.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
    <input type="hidden" name="action" value="media.upload" />
    <input type="hidden" name="return_view" value="media" />
    <div class="admin-form-grid">
      <div class="admin-field"><label for="file">Image file</label><input id="file" name="file" type="file" accept="image/*" required /></div>
      <div class="admin-field"><label for="alt_text">Alt text</label><input id="alt_text" name="alt_text" type="text" placeholder="Describe the image for screen readers and search engines" /></div>
    </div>
    <div class="admin-form-actions"><button class="admin-button" type="submit">Upload</button></div>
  </form>
</div>

<?php if ($media): ?>
  <div class="media-grid">
    <?php foreach ($media as $item): ?>
      <div class="media-item">
        <img src="<?= e((string) $item['file_path']) ?>" alt="<?= e((string) $item['alt_text']) ?>" loading="lazy" />
        <code><?= e((string) $item['file_path']) ?></code>
        <p class="muted"><?= e(number_format((int) $item['file_size'] / 1024, 0)) ?> KB</p>
        <form action="backend/admin_action.php" method="post" data-confirm="Delete this image? Pages still using it will show a broken image.">
          <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
          <input type="hidden" name="action" value="media.delete" />
          <input type="hidden" name="return_view" value="media" />
          <input type="hidden" name="id" value="<?= (int) $item['id'] ?>" />
          <button class="admin-button danger small" type="submit">Delete</button>
        </form>
      </div>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <div class="admin-empty">No images uploaded yet.</div>
<?php endif; ?>
