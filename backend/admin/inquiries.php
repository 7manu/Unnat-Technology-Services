<?php
/** Client inquiries submitted through the website form and the AI assistant. */
declare(strict_types=1);

$inquiries = admin_rows('SELECT `id`, `name`, `mobile`, `email`, `question`, `details`, `status` FROM `query` ORDER BY `id` DESC');
$pending = count(array_filter($inquiries, static fn(array $row): bool => (string) $row['status'] !== '1'));

/** AI assistant submissions are tagged inside the details column. */
$source = static fn(array $inquiry): string => strpos((string) ($inquiry['details'] ?? ''), 'UTS AI Assistant') !== false ? 'AI Assistant' : 'Contact form';
?>
<div class="admin-page-head">
  <div>
    <h1>Inquiries</h1>
    <p><?= count($inquiries) ?> total, <?= $pending ?> still pending.</p>
  </div>
</div>

<?php if ($inquiries): ?>
  <div class="admin-table-wrap">
    <table class="admin-data-table">
      <thead><tr><th>Name</th><th>Contact</th><th>Source</th><th>Request</th><th>Details</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($inquiries as $inquiry): ?>
          <tr>
            <td><strong><?= e((string) $inquiry['name']) ?></strong></td>
            <td>
              <a href="tel:<?= e((string) $inquiry['mobile']) ?>"><?= e((string) $inquiry['mobile']) ?></a>
              <?php if ((string) $inquiry['email'] !== ''): ?><br /><a href="mailto:<?= e((string) $inquiry['email']) ?>"><?= e((string) $inquiry['email']) ?></a><?php endif; ?>
            </td>
            <td><span class="pill <?= $source($inquiry) === 'AI Assistant' ? 'blue' : '' ?>"><?= e($source($inquiry)) ?></span></td>
            <td><?= nl2br(e((string) $inquiry['question'])) ?></td>
            <td class="muted"><?= nl2br(e((string) $inquiry['details'])) ?></td>
            <td><span class="pill <?= (string) $inquiry['status'] === '1' ? 'green' : 'amber' ?>"><?= (string) $inquiry['status'] === '1' ? 'Completed' : 'Pending' ?></span></td>
            <td class="actions">
              <?php if ((string) $inquiry['status'] !== '1'): ?>
                <form class="admin-inline-form" action="backend/confirmQuery.php" method="post">
                  <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
                  <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>" />
                  <button class="admin-button ghost small" type="submit">Mark done</button>
                </form>
              <?php endif; ?>
              <form class="admin-inline-form" action="backend/deleteQuery.php" method="post" data-confirm="Delete this inquiry permanently?">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />
                <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>" />
                <button class="admin-button danger small" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php else: ?>
  <div class="admin-empty">No inquiries have been submitted yet.</div>
<?php endif; ?>
