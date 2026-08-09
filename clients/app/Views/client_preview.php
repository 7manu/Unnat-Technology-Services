<?php
use App\Services\Billing;

$clientId = (string) $clientUser->_id;
$assigned = (array) ($clientUser->project_ids ?? []);
$isActive = !isset($clientUser->active) || $clientUser->active;

/* Totals across every project this client can see. */
$totalBillable = 0.0;
$totalPaid = 0.0;
foreach ($projects as $project) {
    $projectSummary = Billing::summary($project);
    $totalBillable += $projectSummary['grand_total'];
    $totalPaid += $projectSummary['paid'];
}
$totalBalance = max(0, $totalBillable - $totalPaid);
?>
<section class="preview-banner">
  <div>
    <strong>Client preview</strong>
    <p>You are viewing the portal exactly as <?= htmlspecialchars($clientUser->name ?? 'this client') ?> sees it after signing in. Nothing here can be edited.</p>
  </div>
  <div class="heading-actions">
    <button class="primary-button" type="button" data-modal-open="client-user-share-<?= $clientId ?>">Share login</button>
    <button class="ghost-button" type="button" data-modal-open="client-user-edit-<?= $clientId ?>">Edit access</button>
    <a class="ghost-button" href="/client-users">Back to client access</a>
  </div>
</section>

<section class="profile-card">
  <div class="profile-avatar" aria-hidden="true"><?= htmlspecialchars(strtoupper(substr((string) ($clientUser->name ?? 'C'), 0, 1))) ?></div>
  <div class="profile-body">
    <p class="eyebrow">Client profile</p>
    <h1><?= htmlspecialchars($clientUser->name ?? '') ?></h1>
    <dl class="profile-facts">
      <div><dt>Email</dt><dd><a href="mailto:<?= htmlspecialchars($clientUser->email ?? '') ?>"><?= htmlspecialchars($clientUser->email ?? '—') ?></a></dd></div>
      <div><dt>Mobile</dt><dd><a href="tel:<?= htmlspecialchars($clientUser->mobile_phone ?? '') ?>"><?= htmlspecialchars($clientUser->mobile_phone ?? '—') ?></a></dd></div>
      <div><dt>Address</dt><dd><?= $clientUser->address ? nl2br(htmlspecialchars($clientUser->address)) : '—' ?></dd></div>
      <div><dt>Login</dt><dd><span class="status-pill <?= $isActive ? 'is-paid' : 'is-due' ?>"><?= $isActive ? 'Active' : 'Disabled' ?></span></dd></div>
      <div><dt>Projects visible</dt><dd><?= count($assigned) ?></dd></div>
    </dl>
  </div>
</section>

<section class="payment-summary">
  <article class="stat-card"><span>Projects</span><strong><?= count($projects) ?></strong></article>
  <article class="stat-card"><span>Total billed</span><strong><?= Billing::money($totalBillable) ?></strong></article>
  <article class="stat-card"><span>Received</span><strong class="positive"><?= Billing::money($totalPaid) ?></strong></article>
  <article class="stat-card"><span>Balance</span><strong class="<?= $totalBalance > 0 ? 'due' : 'positive' ?>"><?= Billing::money($totalBalance) ?></strong></article>
</section>

<section class="section-heading">
  <h2>What the client sees</h2>
  <p class="muted">The project list below is generated from this client's project access, with the same columns and links their login shows.</p>
</section>

<div class="project-cards">
  <?php foreach ($projects as $project): ?>
    <?php
      $projectId = (string) $project->_id;
      $projectSummary = Billing::summary($project);
      $percent = (int) ($project->completion_percent ?? 0);
      $renewalText = '—';
      if (isset($project->renewal_date) && $project->renewal_date) {
          $renewalText = $project->renewal_date->toDateTime()->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('d M Y');
      }
    ?>
    <article class="project-card">
      <header>
        <div>
          <h3><?= htmlspecialchars($project->name ?? '') ?></h3>
          <p class="muted"><?= htmlspecialchars($project->description ?? '') ?></p>
        </div>
        <span class="status-pill"><?= htmlspecialchars($project->status ?? 'Active') ?></span>
      </header>
      <div class="project-card-progress">
        <div class="progress-meter"><span style="width: <?= $percent ?>%"></span></div>
        <small><?= $percent ?>% completed</small>
      </div>
      <dl class="project-card-facts">
        <div><dt>Billed</dt><dd><?= Billing::money($projectSummary['grand_total']) ?></dd></div>
        <div><dt>Received</dt><dd><?= Billing::money($projectSummary['paid']) ?></dd></div>
        <div><dt>Balance</dt><dd><?= Billing::money($projectSummary['balance']) ?></dd></div>
        <div><dt>Renewal</dt><dd><?= htmlspecialchars($renewalText) ?></dd></div>
      </dl>
      <div class="actions">
        <a class="link-button" href="/projects/<?= $projectId ?>/progress">Progress</a>
        <a class="link-button" href="/projects/<?= $projectId ?>/billing">Billing</a>
        <a class="link-button" href="/projects/<?= $projectId ?>/invoice">Bill</a>
      </div>
    </article>
  <?php endforeach; ?>
  <?php if (!$projects): ?>
    <div class="empty-panel">
      <strong>No projects are visible to this client.</strong>
      <p class="muted">Assign at least one project in “Edit access” — until then their dashboard is empty after login.</p>
    </div>
  <?php endif; ?>
</div>

<?php App\Services\View::partial('client_user_modal', [
    'id' => 'client-user-edit-' . $clientId,
    'action' => '/client-users/' . $clientId,
    'clientUser' => $clientUser,
    'projects' => $allProjects,
]); ?>

<?php App\Services\View::partial('share_modal', [
    'id' => 'client-user-share-' . $clientId,
    'clientUser' => $clientUser,
    'portalUrl' => $portalUrl,
    'sharedPassword' => '',
]); ?>
