# Project Completion Tracker (PHP + MySQL)

A lightweight PHP app to track project details, associate projects with clients, and request client approval when work is completed.

## Features
- Client table with multiple projects per client.
- Project records with contact and location details.
- Mark a project as completed to email the primary contact with an approval link.
- Contact approval closes the project, and contacts can also decline with comments.

## Requirements
- PHP 8.1+
- MySQL 8+
- Docker (optional, for local test environment)

## Setup

1. Create a database and load the schema:

```bash
mysql -u root -p -e "CREATE DATABASE project_completion;"
mysql -u root -p project_completion < schema.sql
```

2. Configure environment variables:

```bash
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_NAME=project_completion
export DB_USER=root
export DB_PASSWORD=secret
export APP_URL=http://localhost:8000
export MAIL_FROM=noreply@example.com
export SMTP_HOST=192.168.1.241
export SMTP_PORT=25
export SMTP_USERNAME=
export SMTP_PASSWORD=
```

3. Serve the app locally:

```bash
php -S localhost:8000
```

Then visit `http://localhost:8000`.

## Local test environment (Docker)

You can spin up the app and a MySQL database together using Docker Compose:

```bash
docker compose up --build
```

This will:
- Start PHP on `http://localhost:8000`.
- Initialize the MySQL database using `schema.sql`.

If you want to override defaults, copy `.env.example` to `.env` and adjust values
before running `docker compose up --build`.

## Email delivery

The app sends email via SMTP when `SMTP_HOST` is set. Leave `SMTP_USERNAME` and
`SMTP_PASSWORD` empty for unauthenticated servers. If `SMTP_HOST` is empty, the app
skips sending and logs a warning so you can configure SMTP or install a mail transfer agent.
If email is not available, the app still marks the project completed and shows a flash message.
