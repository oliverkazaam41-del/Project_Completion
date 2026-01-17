<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/db.php';

$projects = db()->query(
    'SELECT p.*, c.name AS client_name, ct.name AS contact_name, ct.email AS contact_email
     FROM projects p
     JOIN clients c ON p.client_id = c.id
     JOIN contacts ct ON p.contact_id = ct.id
     ORDER BY p.created_at DESC'
)->fetchAll();

require __DIR__ . '/partials/header.php';
?>
<section class="card">
  <h2>Active Projects</h2>
  <?php if ($projects): ?>
    <div class="project-grid">
      <?php foreach ($projects as $project): ?>
        <article class="project-card">
          <div>
            <h3><?php echo e($project['name']); ?></h3>
            <p class="meta">Client: <?php echo e($project['client_name']); ?></p>
            <p class="meta">Contact: <?php echo e($project['contact_name']); ?> (<?php echo e($project['contact_email']); ?>)</p>
            <p class="meta">
              Status: <span class="status <?php echo e($project['status']); ?>"><?php echo e($project['status']); ?></span>
            </p>
          </div>
          <div class="project-actions">
            <a class="button" href="/project_detail.php?id=<?php echo e($project['id']); ?>">View</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p>No projects yet. Create one to get started.</p>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
