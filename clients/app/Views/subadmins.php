<?php use App\Services\Csrf; ?>
<section class="page-heading">
  <div>
    <p class="eyebrow">Access Control</p>
    <h1>Subadmins</h1>
  </div>
  <button class="primary-button" type="button" data-modal-open="subadmin-create">Create Subadmin</button>
</section>

<section class="table-panel">
  <table>
    <thead><tr><th>Name</th><th>Email</th><th>Projects</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($subadmins as $subadmin): $id = (string) $subadmin->_id; ?>
        <?php $assigned = (array) ($subadmin->project_ids ?? []); ?>
        <tr>
          <td><strong><?= htmlspecialchars($subadmin->name ?? '') ?></strong></td>
          <td><?= htmlspecialchars($subadmin->email ?? '') ?></td>
          <td>
            <?php foreach ($projects as $project): ?>
              <?php if (in_array((string) $project->_id, $assigned, true)): ?>
                <span class="status-pill"><?= htmlspecialchars($project->name ?? '') ?></span>
              <?php endif; ?>
            <?php endforeach; ?>
            <?php if (!$assigned): ?><span class="muted">No project access</span><?php endif; ?>
          </td>
          <td><span class="status-pill"><?= !isset($subadmin->active) || $subadmin->active ? 'Active' : 'Inactive' ?></span></td>
          <td class="actions">
            <button class="link-button" type="button" data-modal-open="subadmin-edit-<?= $id ?>">Edit</button>
            <form method="post" action="/subadmins/<?= $id ?>/delete" data-confirm="Delete this subadmin?">
              <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
              <button class="danger-button" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$subadmins): ?><tr><td colspan="5" class="empty">No subadmins yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</section>

<?php App\Services\View::partial('subadmin_modal', ['id' => 'subadmin-create', 'action' => '/subadmins', 'subadmin' => null, 'projects' => $projects]); ?>
<?php foreach ($subadmins as $subadmin): App\Services\View::partial('subadmin_modal', ['id' => 'subadmin-edit-' . (string) $subadmin->_id, 'action' => '/subadmins/' . (string) $subadmin->_id, 'subadmin' => $subadmin, 'projects' => $projects]); endforeach; ?>
