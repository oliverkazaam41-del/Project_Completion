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

    $headers = [
        'From' => $config['mail_from'],
        'Reply-To' => $config['mail_from'],
    ];

    if (!empty($config['smtp_host'])) {
        return smtp_send_mail(
            $config['smtp_host'],
            (int) $config['smtp_port'],
            $config['smtp_username'],
            $config['smtp_password'],
            $config['mail_from'],
            $contact['email'],
            $subject,
            $message,
            $headers
        );
    }

    error_log('SMTP_HOST not set; email not sent. Configure SMTP_HOST or install sendmail.');
    return false;
}

function smtp_send_mail(
    string $host,
    int $port,
    string $username,
    string $password,
    string $from,
    string $to,
    string $subject,
    string $body,
    array $headers
): bool {
    $socket = fsockopen($host, $port, $errno, $errstr, 10);
    if (!$socket) {
        return false;
    }

    $read = function () use ($socket): string {
        $response = '';
        while (!feof($socket)) {
            $line = fgets($socket, 515);
            if ($line === false) {
                break;
            }
            $response .= $line;
            if (preg_match('/^\d{3} /', $line)) {
                break;
            }
        }
        return $response;
    };

    $write = function (string $command) use ($socket): void {
        fwrite($socket, $command . "\r\n");
    };

    $read();
    $write('EHLO localhost');
    $read();

    if ($username !== '' && $password !== '') {
        $write('AUTH LOGIN');
        $read();
        $write(base64_encode($username));
        $read();
        $write(base64_encode($password));
        $read();
    }

    $write('MAIL FROM:<' . $from . '>');
    $read();
    $write('RCPT TO:<' . $to . '>');
    $read();
    $write('DATA');
    $read();

    $headerLines = [
        'From: ' . $headers['From'],
        'Reply-To: ' . $headers['Reply-To'],
        'To: ' . $to,
        'Subject: ' . $subject,
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
    ];

    $data = implode("\r\n", $headerLines) . "\r\n\r\n" . $body . "\r\n.";
    $write($data);
    $read();
    $write('QUIT');
    fclose($socket);

    return true;
}
