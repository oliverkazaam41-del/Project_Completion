<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/db.php';

$clients = db()->query('SELECT id, name FROM clients ORDER BY name')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $clientId = $_POST['client_id'] ?? '';
    $contactName = trim($_POST['contact_name'] ?? '');
    $contactEmail = trim($_POST['contact_email'] ?? '');
    $contactPhone = trim($_POST['contact_phone'] ?? '');

    if ($name === '' || $clientId === '' || $contactName === '' || $contactEmail === '') {
        flash('Project name, client, and contact details are required.');
    } else {
        $pdo = db();
        $pdo->beginTransaction();

        try {
            $statement = $pdo->prepare(
                'INSERT INTO contacts (name, email, phone) VALUES (:name, :email, :phone)'
            );
            $statement->execute([
                'name' => $contactName,
                'email' => $contactEmail,
                'phone' => $contactPhone ?: null,
            ]);
            $contactId = (int) $pdo->lastInsertId();

            $locationId = null;
            $locationFields = [
                'name' => trim($_POST['location_name'] ?? ''),
                'address' => trim($_POST['location_address'] ?? ''),
                'city' => trim($_POST['location_city'] ?? ''),
                'state' => trim($_POST['location_state'] ?? ''),
                'country' => trim($_POST['location_country'] ?? ''),
            ];

            $hasLocation = false;
            foreach ($locationFields as $value) {
                if ($value !== '') {
                    $hasLocation = true;
                    break;
                }
            }

            if ($hasLocation) {
                $statement = $pdo->prepare(
                    'INSERT INTO locations (name, address, city, state, country)
                     VALUES (:name, :address, :city, :state, :country)'
                );
                $statement->execute([
                    'name' => $locationFields['name'] ?: null,
                    'address' => $locationFields['address'] ?: null,
                    'city' => $locationFields['city'] ?: null,
                    'state' => $locationFields['state'] ?: null,
                    'country' => $locationFields['country'] ?: null,
                ]);
                $locationId = (int) $pdo->lastInsertId();
            }

            $statement = $pdo->prepare(
                'INSERT INTO projects (name, description, status, client_id, contact_id, location_id, created_at)
                 VALUES (:name, :description, :status, :client_id, :contact_id, :location_id, NOW())'
            );
            $statement->execute([
                'name' => $name,
                'description' => $description ?: null,
                'status' => 'active',
                'client_id' => (int) $clientId,
                'contact_id' => $contactId,
                'location_id' => $locationId,
            ]);

            $pdo->commit();
            flash('Project created.');
            header('Location: /index.php');
            exit;
        } catch (PDOException $exception) {
            $pdo->rollBack();
            flash('Unable to create the project.');
        }
    }
}

require __DIR__ . '/partials/header.php';
?>
<section class="card">
  <h2>New Project</h2>
  <?php if (!$clients): ?>
    <p>Please add a client before creating a project.</p>
  <?php endif; ?>
  <form class="form" method="post">
    <label>
      Project name
      <input type="text" name="name" required />
    </label>
    <label>
      Description
      <textarea name="description" rows="4"></textarea>
    </label>
    <label>
      Client
      <select name="client_id" required>
        <option value="" disabled selected>Select a client</option>
        <?php foreach ($clients as $client): ?>
          <option value="<?php echo e($client['id']); ?>"><?php echo e($client['name']); ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <h3>Primary contact</h3>
    <div class="grid">
      <label>
        Name
        <input type="text" name="contact_name" required />
      </label>
      <label>
        Email
        <input type="email" name="contact_email" required />
      </label>
      <label>
        Phone
        <input type="text" name="contact_phone" />
      </label>
    </div>

    <h3>Location</h3>
    <div class="grid">
      <label>
        Location name
        <input type="text" name="location_name" />
      </label>
      <label>
        Address
        <input type="text" name="location_address" />
      </label>
      <label>
        City
        <input type="text" name="location_city" />
      </label>
      <label>
        State/Region
        <input type="text" name="location_state" />
      </label>
      <label>
        Country
        <input type="text" name="location_country" />
      </label>
    </div>

    <button class="button" type="submit" <?php echo !$clients ? 'disabled' : ''; ?>>Create project</button>
  </form>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
