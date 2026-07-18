<?php
use App\Services\Csrf;
$assigned = $subadmin ? (array) ($subadmin->project_ids ?? []) : [];
?>
<div class="modal" id="<?= htmlspecialchars($id) ?>" aria-hidden="true">
  <div class="modal-backdrop" data-modal-close></div>
  <div class="modal-panel wide" role="dialog" aria-modal="true">
    <button class="modal-close" type="button" data-modal-close aria-label="Close">x</button>
    <h2><?= $subadmin ? 'Edit Subadmin' : 'Create Subadmin' ?></h2>
    <form method="post" action="<?= htmlspecialchars($action) ?>" class="form-grid">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
      <label>Name <input name="name" required value="<?= htmlspecialchars($subadmin->name ?? '') ?>"></label>
      <label>Email <input type="email" name="email" required value="<?= htmlspecialchars($subadmin->email ?? '') ?>"></label>
      <label class="span-2">Password <input type="password" name="password" <?= $subadmin ? '' : 'required' ?> minlength="8" placeholder="<?= $subadmin ? 'Leave blank to keep current password' : 'Minimum 8 characters' ?>"></label>
      <fieldset class="span-2 checkbox-panel">
        <legend>Project access</legend>
        <?php foreach ($projects as $project): $projectId = (string) $project->_id; ?>
          <label class="check-row">
            <input type="checkbox" name="project_ids[]" value="<?= $projectId ?>" <?= in_array($projectId, $assigned, true) ? 'checked' : '' ?>>
            <span><?= htmlspecialchars($project->name ?? '') ?></span>
          </label>
        <?php endforeach; ?>
        <?php if (!$projects): ?><p class="muted">Create a project first, then assign it here.</p><?php endif; ?>
      </fieldset>
      <label class="check-row span-2">
        <input type="checkbox" name="active" value="1" <?= !$subadmin || !isset($subadmin->active) || $subadmin->active ? 'checked' : '' ?>>
        <span>Active login</span>
      </label>
      <button class="primary-button span-2" type="submit"><?= $subadmin ? 'Save Subadmin' : 'Create Subadmin' ?></button>
    </form>
  </div>
</div>
