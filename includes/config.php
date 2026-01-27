<?php

return [
    'db_host' => getenv('DB_HOST') ?: '127.0.0.1',
    'db_port' => getenv('DB_PORT') ?: '3306',
    'db_name' => getenv('DB_NAME') ?: 'project_completion',
    'db_user' => getenv('DB_USER') ?: 'root',
    'db_password' => getenv('DB_PASSWORD') ?: '',
    'app_url' => rtrim(getenv('APP_URL') ?: 'http://localhost:8000', '/'),
    'mail_from' => getenv('MAIL_FROM') ?: 'noreply@example.com',
    'smtp_host' => getenv('SMTP_HOST') ?: '',
    'smtp_port' => getenv('SMTP_PORT') ?: '25',
    'smtp_username' => getenv('SMTP_USERNAME') ?: '',
    'smtp_password' => getenv('SMTP_PASSWORD') ?: '',
];
