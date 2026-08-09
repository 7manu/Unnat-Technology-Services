<?php use App\Services\Csrf; ?>
<section class="page-heading">
  <div>
    <p class="eyebrow">Client Portal</p>
    <h1>Client Access</h1>
  </div>
  <button class="primary-button" type="button" data-modal-open="client-user-create">Add Client Access</button>
</section>

<section class="table-panel">
  <table class="stacked-table">
    <thead><tr><th>Name</th><th>Contact</th><th>Address</th><th>Projects</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($clientUsers as $clientUser): $id = (string) $clientUser->_id; ?>
        <?php $assigned = (array) ($clientUser->project_ids ?? []); ?>
        <tr>
          <td data-label="Name"><strong><?= htmlspecialchars($clientUser->name ?? '') ?></strong></td>
          <td data-label="Contact">
            <?= htmlspecialchars($clientUser->email ?? '') ?>
            <small><?= htmlspecialchars($clientUser->mobile_phone ?? '') ?></small>
          </td>
          <td data-label="Address"><?= htmlspecialchars($clientUser->address ?? '') ?></td>
          <td data-label="Projects">
            <?php foreach ($projects as $project): ?>
              <?php if (in_array((string) $project->_id, $assigned, true)): ?>
                <span class="status-pill"><?= htmlspecialchars($project->name ?? '') ?></span>
              <?php endif; ?>
            <?php endforeach; ?>
            <?php if (!$assigned): ?><span class="muted">No project access</span><?php endif; ?>
          </td>
          <td data-label="Status"><span class="status-pill <?= !isset($clientUser->active) || $clientUser->active ? 'is-paid' : 'is-due' ?>"><?= !isset($clientUser->active) || $clientUser->active ? 'Active' : 'Inactive' ?></span></td>
          <td data-label="Actions" class="actions">
            <button class="link-button" type="button" data-modal-open="client-user-share-<?= $id ?>">Share login</button>
            <a class="link-button" href="/client-users/<?= $id ?>/preview">Preview</a>
            <button class="link-button" type="button" data-modal-open="client-user-edit-<?= $id ?>">Edit</button>
            <form method="post" action="/client-users/<?= $id ?>/delete" data-confirm="Delete this client access?">
              <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
              <button class="danger-button" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$clientUsers): ?><tr><td colspan="6" class="empty">No client access users yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</section>

<?php App\Services\View::partial('client_user_modal', ['id' => 'client-user-create', 'action' => '/client-users', 'clientUser' => null, 'projects' => $projects]); ?>
<?php foreach ($clientUsers as $clientUser): ?>
  <?php $clientId = (string) $clientUser->_id; ?>
  <?php App\Services\View::partial('client_user_modal', ['id' => 'client-user-edit-' . $clientId, 'action' => '/client-users/' . $clientId, 'clientUser' => $clientUser, 'projects' => $projects]); ?>
  <?php App\Services\View::partial('share_modal', [
      'id' => 'client-user-share-' . $clientId,
      'clientUser' => $clientUser,
      'portalUrl' => $portalUrl,
      'sharedPassword' => ($sharedCredentials['id'] ?? '') === $clientId ? (string) $sharedCredentials['password'] : '',
  ]); ?>
<?php endforeach; ?>

<?php if ($openShareFor !== ''): ?>
  <div hidden data-modal-autoopen="client-user-share-<?= htmlspecialchars($openShareFor) ?>"></div>
<?php endif; ?>
