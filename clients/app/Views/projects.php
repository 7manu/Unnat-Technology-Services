<?php use App\Services\Auth; ?>
<?php use App\Services\Csrf; ?>
<section class="page-heading">
  <div>
    <p class="eyebrow">Projects Dashboard</p>
    <h1>Projects</h1>
  </div>
  <?php if (Auth::isAdmin()): ?><button class="primary-button" type="button" data-modal-open="project-create">Add Project</button><?php endif; ?>
</section>

<section class="toolbar">
  <form class="filters" method="get" action="/projects">
    <input type="search" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search projects">
    <select name="status">
      <option value="">All statuses</option>
      <?php foreach (['Active', 'Paused', 'Completed'] as $item): ?>
        <option value="<?= $item ?>" <?= $status === $item ? 'selected' : '' ?>><?= $item ?></option>
      <?php endforeach; ?>
    </select>
    <button class="ghost-button" type="submit">Filter</button>
  </form>
</section>

<?php if (!Auth::isClientUser()): ?>
<section class="stat-grid">
  <article class="stat-card"><span>Total Projects</span><strong><?= count($projects) ?></strong></article>
  <article class="stat-card"><span>Active</span><strong><?= count(array_filter($projects, fn($p) => ($p->status ?? '') === 'Active')) ?></strong></article>
  <article class="stat-card"><span>Completed</span><strong><?= count(array_filter($projects, fn($p) => ($p->status ?? '') === 'Completed')) ?></strong></article>
</section>
<?php endif; ?>

<section class="table-panel">
  <table>
    <thead>
      <tr>
        <th>Project</th><th>Description</th><th>Progress</th><th>Status</th><th>Project URL</th>
        <?php if (Auth::isAdmin() || Auth::isClientUser()): ?><th>Payment</th><th>Renewal</th><?php endif; ?>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($projects as $project): $id = (string) $project->_id; ?>
        <?php
          $paidTotal = 0;
          foreach ((array) ($project->part_payments ?? []) as $payment) {
              $paidTotal += (float) (is_object($payment) ? ($payment->amount ?? 0) : ($payment['amount'] ?? 0));
          }
          $renewalText = '-';
          if (isset($project->renewal_date) && $project->renewal_date) {
              $renewalText = $project->renewal_date->toDateTime()->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('d M Y');
          }
        ?>
        <tr>
          <td><a class="row-title" href="<?= Auth::isClientUser() ? '/projects/' . $id . '/progress' : '/projects/' . $id . '/clients' ?>"><?= htmlspecialchars($project->name ?? '') ?></a></td>
          <td><?= htmlspecialchars($project->description ?? '') ?></td>
          <td>
            <div class="progress-meter"><span style="width: <?= (int) ($project->completion_percent ?? 0) ?>%"></span></div>
            <small><?= (int) ($project->completion_percent ?? 0) ?>% completed</small>
          </td>
          <td><span class="status-pill"><?= htmlspecialchars($project->status ?? 'Active') ?></span></td>
          <td>
            <?php if (!empty($project->project_url)): ?>
              <a class="row-title" href="<?= htmlspecialchars($project->project_url) ?>" target="_blank" rel="noopener">Open URL</a>
            <?php else: ?>-<?php endif; ?>
          </td>
          <?php if (Auth::isAdmin() || Auth::isClientUser()): ?>
            <td>
              <strong><?= number_format((float) ($project->total_payment ?? 0), 2) ?></strong>
              <small>Paid <?= number_format($paidTotal, 2) ?></small>
            </td>
            <td><?= htmlspecialchars($renewalText) ?></td>
          <?php endif; ?>
          <td class="actions">
            <a class="link-button" href="<?= Auth::isClientUser() ? '/projects/' . $id . '/progress' : '/projects/' . $id . '/clients' ?>">Open</a>
            <?php if (Auth::isAdmin()): ?>
              <button class="link-button" type="button" data-modal-open="project-edit-<?= $id ?>">Edit</button>
              <form method="post" action="/projects/<?= $id ?>/delete" data-confirm="Delete this project and its clients?">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
                <button class="danger-button" type="submit">Delete</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$projects): ?><tr><td colspan="<?= (Auth::isAdmin() || Auth::isClientUser()) ? 8 : 6 ?>" class="empty">No projects found.</td></tr><?php endif; ?>
    </tbody>
  </table>
</section>

<?php if (Auth::isAdmin()): ?>
  <?php App\Services\View::partial('project_modal', ['id' => 'project-create', 'action' => '/projects', 'project' => null]); ?>
  <?php foreach ($projects as $project): App\Services\View::partial('project_modal', ['id' => 'project-edit-' . (string) $project->_id, 'action' => '/projects/' . (string) $project->_id, 'project' => $project]); endforeach; ?>
<?php endif; ?>
