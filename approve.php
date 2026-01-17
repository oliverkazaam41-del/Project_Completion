<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

$token = $_GET['token'] ?? '';
if ($token === '') {
    http_response_code(404);
    echo 'Approval token not found.';
    exit;
}

$statement = db()->prepare(
    'SELECT p.*, c.name AS client_name, ct.name AS contact_name, ct.email AS contact_email,
            l.name AS location_name, l.address AS location_address, l.city AS location_city,
            l.state AS location_state, l.country AS location_country
     FROM projects p
     JOIN clients c ON p.client_id = c.id
     JOIN contacts ct ON p.contact_id = ct.id
     LEFT JOIN locations l ON p.location_id = l.id
     WHERE p.approval_token = :token'
);
$statement->execute(['token' => $token]);
$project = $statement->fetch();

if (!$project) {
    http_response_code(404);
    echo 'Approval token not found.';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'approve') {
        $signature = trim($_POST['signature'] ?? '');
        if ($signature === '') {
            flash('Please provide your name to sign the approval.');
            header('Location: /approve.php?token=' . urlencode($token));
            exit;
        }

        $update = db()->prepare(
            'UPDATE projects
             SET status = :status,
                 approved_at = NOW(),
                 approval_name = :approval_name,
                 approval_signed_at = NOW(),
                 declined_at = NULL,
                 decline_reason = NULL
             WHERE id = :id'
        );
        $update->execute([
            'status' => 'approved',
            'approval_name' => $signature,
            'id' => $project['id'],
        ]);

        flash('Thank you! The project has been approved and closed.');
        header('Location: /approve.php?token=' . urlencode($token));
        exit;
    }

    if ($action === 'decline') {
        $comment = trim($_POST['decline_reason'] ?? '');
        if ($comment === '') {
            flash('Please include a comment when declining the project.');
            header('Location: /approve.php?token=' . urlencode($token));
            exit;
        }

        $update = db()->prepare(
            'UPDATE projects
             SET status = :status,
                 declined_at = NOW(),
                 decline_reason = :decline_reason,
                 approved_at = NULL,
                 approval_name = NULL,
                 approval_signed_at = NULL
             WHERE id = :id'
        );
        $update->execute([
            'status' => 'declined',
            'decline_reason' => $comment,
            'id' => $project['id'],
        ]);

        flash('Thank you. Your feedback has been recorded.');
        header('Location: /approve.php?token=' . urlencode($token));
        exit;
    }
}

$flash = flash();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Project Approval</title>
    <link rel="stylesheet" href="/assets/styles.css" />
  </head>
  <body>
    <header class="site-header">
      <h1>Project Approval</h1>
    </header>
    <main class="container">
      <?php if ($flash): ?>
        <div class="flash-list"><?php echo e($flash); ?></div>
      <?php endif; ?>

      <section class="card">
        <h2><?php echo e($project['name']); ?></h2>
        <p class="meta">Client: <?php echo e($project['client_name']); ?></p>
        <p class="meta">Contact: <?php echo e($project['contact_name']); ?> (<?php echo e($project['contact_email']); ?>)</p>
        <p class="meta">Status: <span class="status <?php echo e($project['status']); ?>"><?php echo e($project['status']); ?></span></p>
        <?php if (!empty($project['description'])): ?>
          <p><?php echo e($project['description']); ?></p>
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
        <h3>Approval</h3>
        <?php if ($project['status'] === 'approved'): ?>
          <p class="meta">Approved by <?php echo e($project['approval_name'] ?: 'Unknown'); ?> on <?php echo e(date('M d, Y', strtotime($project['approved_at']))); ?></p>
        <?php else: ?>
          <form class="form" method="post">
            <input type="hidden" name="action" value="approve" />
            <label>
              Signature (full name)
              <input type="text" name="signature" required />
            </label>
            <button class="button" type="submit">Approve project</button>
          </form>
        <?php endif; ?>
      </section>

      <section class="card">
        <h3>Decline</h3>
        <?php if ($project['status'] === 'declined'): ?>
          <p class="meta">Declined on <?php echo e(date('M d, Y', strtotime($project['declined_at']))); ?></p>
          <p><?php echo e($project['decline_reason']); ?></p>
        <?php else: ?>
          <form class="form" method="post">
            <input type="hidden" name="action" value="decline" />
            <label>
              Decline reason
              <textarea name="decline_reason" rows="4" required></textarea>
            </label>
            <button class="button" type="submit">Decline project</button>
          </form>
        <?php endif; ?>
      </section>
    </main>
  </body>
</html>
