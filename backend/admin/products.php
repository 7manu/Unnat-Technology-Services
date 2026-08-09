<?php
/** Product portfolio shown on products.php — add, edit and remove entries. */
declare(strict_types=1);

$products = cms_products();

$editId = (int) ($_GET['id'] ?? 0);
$editing = null;
foreach ($products as $product) {
    if ((int) $product['id'] === $editId) {
        $editing = $product;
        break;
    }
}

$form = $editing ?? ['id' => 0, 'name' => '', 'url' => '', 'description' => '', 'image' => ''];
?>
<div class="admin-page-head">
  <div>
    <h1>Products</h1>
    <p>Items shown on the public product portfolio. The surrounding headings and button labels are in <a href="admin.php?view=content&amp;group=<?= rawurlencode('Products page') ?>">Website content → Products page</a>.</p>
  </div>
  <a class="admin-button ghost" href="products.php" target="_blank" rel="noopener">Open portfolio ↗</a>
</div>

<div class="admin-card">
  <h2><?= $editing ? 'Edit product' : 'Add a product' ?></h2>
  <p><?= $editing ? 'Leave the image field empty to keep the current picture.' : 'All four fields are required.' ?></p>
  <form action="backend/admin_action.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
    <input type="hidden" name="action" value="product.save" />
    <input type="hidden" name="return_view" value="products" />
    <input type="hidden" name="id" value="<?= (int) $form['id'] ?>" />
    <div class="admin-form-grid">
      <div class="admin-field"><label for="product-name">Product name</label><input id="product-name" name="name" type="text" maxlength="25" value="<?= e((string) $form['name']) ?>" required /><span class="hint">Up to 25 characters.</span></div>
      <div class="admin-field"><label for="product-url">Product URL</label><input id="product-url" name="url" type="url" maxlength="50" value="<?= e((string) $form['url']) ?>" placeholder="https://example.com" required /><span class="hint">Up to 50 characters.</span></div>
      <div class="admin-field full"><label for="product-description">Short description</label><input id="product-description" name="description" type="text" maxlength="50" value="<?= e((string) $form['description']) ?>" required /><span class="hint">Up to 50 characters.</span></div>
      <div class="admin-field full">
        <label for="product-image">Product image</label>
        <input id="product-image" name="image" type="file" accept="image/jpeg,image/png,image/webp,image/avif"<?= $editing ? '' : ' required' ?> />
        <span class="hint">JPG, PNG, WebP or AVIF, up to 3 MB.<?= $editing ? ' Optional when editing.' : '' ?></span>
        <?php if ($editing && (string) $editing['image'] !== ''): ?>
          <img class="content-image-preview" src="assets/productimages/<?= e(rawurlencode(cms_product_image($editing))) ?>" alt="Current image for <?= e((string) $editing['name']) ?>" loading="lazy" />
        <?php endif; ?>
      </div>
    </div>
    <div class="admin-form-actions">
      <button class="admin-button" type="submit"><?= $editing ? 'Save product' : 'Add product' ?></button>
      <?php if ($editing): ?><a class="admin-button ghost" href="admin.php?view=products">Cancel</a><?php endif; ?>
    </div>
  </form>
</div>

<?php if ($products): ?>
  <div class="admin-table-wrap">
    <table class="admin-data-table">
      <thead><tr><th>Image</th><th>Name</th><th>Description</th><th>URL</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($products as $product): ?>
          <tr<?= (int) $product['id'] === $editId ? ' class="is-editing"' : '' ?>>
            <td><img src="assets/productimages/<?= e(rawurlencode(cms_product_image($product))) ?>" alt="" width="80" loading="lazy" /></td>
            <td><strong><?= e((string) $product['name']) ?></strong></td>
            <td><?= e((string) $product['description']) ?></td>
            <td><a href="<?= e(cms_safe_external_url((string) $product['url'])) ?>" target="_blank" rel="noopener noreferrer"><?= e((string) $product['url']) ?> ↗</a></td>
            <td class="actions">
              <a class="admin-button ghost small" href="admin.php?view=products&amp;id=<?= (int) $product['id'] ?>">Edit</a>
              <form class="admin-inline-form" action="backend/admin_action.php" method="post" data-confirm="Delete this product permanently?">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
                <input type="hidden" name="action" value="product.delete" />
                <input type="hidden" name="return_view" value="products" />
                <input type="hidden" name="id" value="<?= (int) $product['id'] ?>" />
                <button class="admin-button danger small" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php else: ?>
  <div class="admin-empty">No products are currently published.</div>
<?php endif; ?>
