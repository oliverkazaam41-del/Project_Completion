<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $industry = trim($_POST['industry'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if ($name === '') {
        flash('Client name is required.');
    } else {
        $statement = db()->prepare('INSERT INTO clients (name, industry, notes) VALUES (:name, :industry, :notes)');
        $statement->execute([
            'name' => $name,
            'industry' => $industry ?: null,
            'notes' => $notes ?: null,
        ]);
        flash('Client added.');
        header('Location: /clients.php');
        exit;
    }
}

$clients = db()->query(
    'SELECT c.*, COUNT(p.id) AS project_count
     FROM clients c
     LEFT JOIN projects p ON p.client_id = c.id
     GROUP BY c.id
     ORDER BY c.name'
)->fetchAll();

require __DIR__ . '/partials/header.php';
?>
<section class="card">
  <h2>Clients</h2>
  <form class="form" method="post">
    <div class="grid">
      <label>
        Client name
        <input type="text" name="name" required />
      </label>
      <label>
        Industry
        <input type="text" name="industry" />
      </label>
    </div>
    <label>
      Notes
      <textarea name="notes" rows="3"></textarea>
    </label>
    <button class="button" type="submit">Add client</button>
  </form>
</section>

<section class="card">
  <h3>Client list</h3>
  <?php if ($clients): ?>
    <ul class="list">
      <?php foreach ($clients as $client): ?>
        <li>
          <strong><?php echo e($client['name']); ?></strong>
          <?php if (!empty($client['industry'])): ?>
            <span class="muted">(<?php echo e($client['industry']); ?>)</span>
          <?php endif; ?>
          <p class="muted"><?php echo e((string) $client['project_count']); ?> project(s)</p>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p>No clients yet.</p>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
