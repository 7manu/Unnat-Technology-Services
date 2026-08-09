<?php
/** Product portfolio shown on products.php. */
declare(strict_types=1);

$products = cms_products();
?>
<div class="admin-page-head">
  <div>
    <h1>Products</h1>
    <p>Items shown on the public product portfolio. The surrounding headings and button labels are in <a href="admin.php?view=content&amp;group=<?= rawurlencode('Products page') ?>">Website content → Products page</a>.</p>
  </div>
  <a class="admin-button ghost" href="products.php" target="_blank" rel="noopener">Open portfolio ↗</a>
</div>

<div class="admin-card">
  <h2>Add a product</h2>
  <form action="backend/product.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
    <div class="admin-form-grid">
      <div class="admin-field"><label for="product-name">Product name</label><input id="product-name" name="name" type="text" maxlength="25" required /></div>
      <div class="admin-field"><label for="product-url">Product URL</label><input id="product-url" name="url" type="url" maxlength="50" placeholder="https://example.com" required /></div>
      <div class="admin-field full"><label for="product-description">Short description</label><input id="product-description" name="description" type="text" maxlength="50" required /></div>
      <div class="admin-field full"><label for="product-image">Product image</label><input id="product-image" name="image" type="file" accept="image/jpeg,image/png,image/webp,image/avif" required /><span class="hint">JPG, PNG, WebP or AVIF, up to 3 MB.</span></div>
    </div>
    <div class="admin-form-actions"><button class="admin-button" type="submit">Add product</button></div>
  </form>
</div>

<?php if ($products): ?>
  <div class="admin-table-wrap">
    <table class="admin-data-table">
      <thead><tr><th>Image</th><th>Name</th><th>Description</th><th>URL</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach ($products as $product): ?>
          <tr>
            <td><img src="assets/productimages/<?= e(rawurlencode(cms_product_image($product))) ?>" alt="" width="80" loading="lazy" /></td>
            <td><strong><?= e((string) $product['name']) ?></strong></td>
            <td><?= e((string) $product['description']) ?></td>
            <td><a href="<?= e(cms_safe_external_url((string) $product['url'])) ?>" target="_blank" rel="noopener noreferrer"><?= e((string) $product['url']) ?> ↗</a></td>
            <td class="actions">
              <form class="admin-inline-form" action="backend/delete.php" method="post" data-confirm="Delete this product permanently?">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
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
