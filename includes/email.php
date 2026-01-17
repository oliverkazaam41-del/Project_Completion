<?php

require_once __DIR__ . '/helpers.php';

function send_completion_email(array $contact, array $project, string $approvalUrl): bool
{
    $config = require __DIR__ . '/config.php';

    $subject = sprintf('Project completed: %s', $project['name']);
    $message = sprintf(
        "Hi %s,\n\nThe project '%s' for %s has been marked as completed.\n" .
        "Please review and approve the completion here: %s\n\nThank you.",
        $contact['name'],
        $project['name'],
        $project['client_name'],
        $approvalUrl
    );

    $headers = sprintf("From: %s\r\nReply-To: %s", $config['mail_from'], $config['mail_from']);

    return mail($contact['email'], $subject, $message, $headers);
}
