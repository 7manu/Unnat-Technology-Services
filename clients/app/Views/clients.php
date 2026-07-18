<?php use App\Services\Csrf; ?>
<?php $projectId = (string) $project->_id; ?>
<section class="page-heading">
  <div>
    <a class="back-link" href="/projects">Back to projects</a>
    <p class="eyebrow">Clients Section</p>
    <h1><?= htmlspecialchars($project->name ?? 'Project') ?></h1>
  </div>
  <button class="primary-button" type="button" data-modal-open="client-create">Add Client</button>
</section>

<section class="toolbar">
  <form class="filters" method="get" action="/projects/<?= $projectId ?>/clients">
    <input type="search" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search clients">
    <select name="status">
      <option value="">All statuses</option>
      <?php foreach (['New', 'Contacted', 'Meeting Scheduled', 'Won', 'Lost'] as $item): ?>
        <option value="<?= $item ?>" <?= $status === $item ? 'selected' : '' ?>><?= $item ?></option>
      <?php endforeach; ?>
    </select>
    <button class="ghost-button" type="submit">Filter</button>
  </form>
</section>

<section class="table-panel">
  <table>
    <thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>Status</th><th>Meeting</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($clients as $client): $id = (string) $client->_id; ?>
        <?php
          $meetingText = '-';
          $isToday = false;
          if (isset($client->meeting_at) && $client->meeting_at) {
              $meetingDate = $client->meeting_at->toDateTime()->setTimezone(new DateTimeZone(date_default_timezone_get()));
              $meetingText = $meetingDate->format('d M Y, h:i A');
              $isToday = $meetingDate->format('Y-m-d') === (new DateTimeImmutable('today'))->format('Y-m-d');
          }
        ?>
        <tr>
          <td><strong><?= htmlspecialchars($client->name ?? '') ?></strong><small><?= htmlspecialchars($client->address ?? '') ?></small></td>
          <td><?= htmlspecialchars($client->mobile_phone ?? '') ?><small><?= htmlspecialchars($client->alternate_mobile_phone ?? '') ?></small></td>
          <td><?= htmlspecialchars($client->email ?? '') ?></td>
          <td><span class="status-pill"><?= htmlspecialchars($client->status ?? 'New') ?></span></td>
          <td>
            <?= htmlspecialchars($meetingText) ?>
            <?php if ($isToday): ?><span class="today-pill">Today</span><?php endif; ?>
          </td>
          <td class="actions">
            <button class="link-button" type="button" data-modal-open="client-edit-<?= $id ?>">Edit</button>
            <form method="post" action="/projects/<?= $projectId ?>/clients/<?= $id ?>/delete" data-confirm="Delete this client?">
              <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
              <button class="danger-button" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$clients): ?><tr><td colspan="6" class="empty">No clients found. Use Add Client to create one.</td></tr><?php endif; ?>
    </tbody>
  </table>
</section>

<?php App\Services\View::partial('client_modal', ['id' => 'client-create', 'action' => '/projects/' . $projectId . '/clients', 'client' => null]); ?>
<?php foreach ($clients as $client): App\Services\View::partial('client_modal', ['id' => 'client-edit-' . (string) $client->_id, 'action' => '/projects/' . $projectId . '/clients/' . (string) $client->_id, 'client' => $client]); endforeach; ?>
