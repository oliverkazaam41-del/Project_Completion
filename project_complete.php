<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/email.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed.';
    exit;
}

$projectId = (int) ($_POST['id'] ?? 0);
if ($projectId <= 0) {
    flash('Project not found.');
    header('Location: /index.php');
    exit;
}

$pdo = db();
$statement = $pdo->prepare(
    'SELECT p.*, c.name AS client_name, ct.name AS contact_name, ct.email AS contact_email
     FROM projects p
     JOIN clients c ON p.client_id = c.id
     JOIN contacts ct ON p.contact_id = ct.id
     WHERE p.id = :id'
);
$statement->execute(['id' => $projectId]);
$project = $statement->fetch();

if (!$project) {
    flash('Project not found.');
    header('Location: /index.php');
    exit;
}

if ($project['status'] === 'approved') {
    flash('Project is already approved.');
    header('Location: /project_detail.php?id=' . $projectId);
    exit;
}

$approvalToken = $project['approval_token'] ?: bin2hex(random_bytes(24));

$update = $pdo->prepare(
    'UPDATE projects
     SET status = :status,
         completed_at = NOW(),
         approval_token = :token,
         declined_at = NULL,
         decline_reason = NULL,
         approval_name = NULL,
         approval_signed_at = NULL,
         approved_at = NULL
     WHERE id = :id'
);
$update->execute([
    'status' => 'completed',
    'token' => $approvalToken,
    'id' => $projectId,
]);

$approvalUrl = base_url('approve.php?token=' . urlencode($approvalToken));
$sent = send_completion_email(
    ['name' => $project['contact_name'], 'email' => $project['contact_email']],
    ['name' => $project['name'], 'client_name' => $project['client_name']],
    $approvalUrl
);

flash($sent ? 'Project marked as completed. Approval email sent.' : 'Project completed. Email could not be sent.');
header('Location: /project_detail.php?id=' . $projectId);
exit;
