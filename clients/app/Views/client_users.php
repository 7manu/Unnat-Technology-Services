<?php use App\Services\Csrf; ?>
<section class="page-heading">
  <div>
    <p class="eyebrow">Client Portal</p>
    <h1>Client Access</h1>
  </div>
  <button class="primary-button" type="button" data-modal-open="client-user-create">Add Client Access</button>
</section>

<section class="table-panel">
  <table>
    <thead><tr><th>Name</th><th>Contact</th><th>Address</th><th>Projects</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($clientUsers as $clientUser): $id = (string) $clientUser->_id; ?>
        <?php $assigned = (array) ($clientUser->project_ids ?? []); ?>
        <tr>
          <td><strong><?= htmlspecialchars($clientUser->name ?? '') ?></strong></td>
          <td>
            <?= htmlspecialchars($clientUser->email ?? '') ?>
            <small><?= htmlspecialchars($clientUser->mobile_phone ?? '') ?></small>
          </td>
          <td><?= htmlspecialchars($clientUser->address ?? '') ?></td>
          <td>
            <?php foreach ($projects as $project): ?>
              <?php if (in_array((string) $project->_id, $assigned, true)): ?>
                <span class="status-pill"><?= htmlspecialchars($project->name ?? '') ?></span>
              <?php endif; ?>
            <?php endforeach; ?>
            <?php if (!$assigned): ?><span class="muted">No project access</span><?php endif; ?>
          </td>
          <td><span class="status-pill"><?= !isset($clientUser->active) || $clientUser->active ? 'Active' : 'Inactive' ?></span></td>
          <td class="actions">
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
<?php foreach ($clientUsers as $clientUser): App\Services\View::partial('client_user_modal', ['id' => 'client-user-edit-' . (string) $clientUser->_id, 'action' => '/client-users/' . (string) $clientUser->_id, 'clientUser' => $clientUser, 'projects' => $projects]); endforeach; ?>
