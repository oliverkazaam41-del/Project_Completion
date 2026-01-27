<?php

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function base_url(string $path = ''): string
{
    $config = require __DIR__ . '/config.php';
    $path = ltrim($path, '/');

    return $path ? $config['app_url'] . '/' . $path : $config['app_url'];
}

function flash(?string $message = null): ?string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if ($message !== null) {
        $_SESSION['flash'] = $message;
        return null;
    }

    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $stored = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $stored;
}
