<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

$projectId = (int) ($_GET['id'] ?? 0);
if ($projectId <= 0) {
    http_response_code(404);
    echo 'Project not found.';
    exit;
}

$statement = db()->prepare(
    'SELECT p.*, c.name AS client_name, ct.name AS contact_name, ct.email AS contact_email, ct.phone AS contact_phone,
            l.name AS location_name, l.address AS location_address, l.city AS location_city, l.state AS location_state, l.country AS location_country
     FROM projects p
     JOIN clients c ON p.client_id = c.id
     JOIN contacts ct ON p.contact_id = ct.id
     LEFT JOIN locations l ON p.location_id = l.id
     WHERE p.id = :id'
);
$statement->execute(['id' => $projectId]);
$project = $statement->fetch();

if (!$project) {
    http_response_code(404);
    echo 'Project not found.';
    exit;
}

require __DIR__ . '/partials/header.php';
?>
<section class="card">
  <div class="detail-header">
    <div>
      <h2><?php echo e($project['name']); ?></h2>
      <p class="meta">Client: <?php echo e($project['client_name']); ?></p>
      <p class="meta">Status: <span class="status <?php echo e($project['status']); ?>"><?php echo e($project['status']); ?></span></p>
      <?php if (!empty($project['description'])): ?>
        <p><?php echo e($project['description']); ?></p>
      <?php endif; ?>
    </div>
    <div>
      <?php if ($project['status'] !== 'approved'): ?>
        <form method="post" action="/project_complete.php">
          <input type="hidden" name="id" value="<?php echo e($project['id']); ?>" />
          <button class="button" type="submit">
            <?php echo $project['status'] === 'completed' || $project['status'] === 'declined' ? 'Resend approval email' : 'Mark completed + send email'; ?>
          </button>
        </form>
      <?php else: ?>
        <p class="muted">Approved on <?php echo e(date('M d, Y', strtotime($project['approved_at']))); ?></p>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="card">
  <h3>Contact</h3>
  <p><strong><?php echo e($project['contact_name']); ?></strong></p>
  <p class="meta"><?php echo e($project['contact_email']); ?></p>
  <?php if (!empty($project['contact_phone'])): ?>
    <p class="meta"><?php echo e($project['contact_phone']); ?></p>
  <?php endif; ?>
</section>

<section class="card">
  <h3>Location</h3>
  <?php if (!empty($project['location_name']) || !empty($project['location_address'])): ?>
    <p><?php echo e($project['location_name']); ?></p>
    <p class="meta"><?php echo e($project['location_address']); ?></p>
    <p class="meta">
      <?php echo e(trim($project['location_city'] . ' ' . $project['location_state'] . ' ' . $project['location_country'])); ?>
    </p>
  <?php else: ?>
    <p>No location details recorded.</p>
  <?php endif; ?>
</section>

<section class="card">
  <h3>Timeline</h3>
  <ul class="list">
    <li>Created: <?php echo e(date('M d, Y', strtotime($project['created_at']))); ?></li>
    <li>Completed: <?php echo e($project['completed_at'] ? date('M d, Y', strtotime($project['completed_at'])) : 'Not completed'); ?></li>
    <li>Approved: <?php echo e($project['approved_at'] ? date('M d, Y', strtotime($project['approved_at'])) : 'Not approved'); ?></li>
    <li>Declined: <?php echo e($project['declined_at'] ? date('M d, Y', strtotime($project['declined_at'])) : 'Not declined'); ?></li>
  </ul>
  <?php if (!empty($project['decline_reason'])): ?>
    <p class="meta"><strong>Decline reason:</strong> <?php echo e($project['decline_reason']); ?></p>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
