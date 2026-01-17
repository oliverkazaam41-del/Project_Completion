<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

$token = $_GET['token'] ?? '';
if ($token === '') {
    http_response_code(404);
    echo 'Approval token not found.';
    exit;
}

$statement = db()->prepare('SELECT id, status FROM projects WHERE approval_token = :token');
$statement->execute(['token' => $token]);
$project = $statement->fetch();

if (!$project) {
    http_response_code(404);
    echo 'Approval token not found.';
    exit;
}

if ($project['status'] === 'approved') {
    flash('Project already approved. Thank you!');
    header('Location: /project_detail.php?id=' . $project['id']);
    exit;
}

$update = db()->prepare('UPDATE projects SET status = :status, approved_at = NOW() WHERE id = :id');
$update->execute([
    'status' => 'approved',
    'id' => $project['id'],
]);

flash('Thank you! The project has been approved and closed.');
header('Location: /project_detail.php?id=' . $project['id']);
exit;
