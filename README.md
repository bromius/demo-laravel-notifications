# Notification Service

REST API service for creating, delivering and reporting user notifications.

## Stack

- PHP 8.3
- Laravel 12
- MySQL 8.4
- Docker Compose
- Database queue
- PHPUnit
- Larastan, based on PHPStan, for static code analysis
- Laravel Pint, based on PHP-CS-Fixer, for PHP code style

## Installation

Clone the repository:

```bash
git clone https://github.com/bromius/demo-laravel-notifications.git
cd demo-laravel-notifications
```

Copy the environment file:

```bash
cp .env.example .env
```

Recommended local Docker values:

```env
APP_URL=http://localhost:8088
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=notifications
DB_USERNAME=notifications
DB_PASSWORD=password

QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

# Docker
DOCKER_DB_PORT=33061
DOCKER_DB_ROOT_PASSWORD=root
DOCKER_PHPMYADMIN_PORT=8089
DOCKER_APP_PORT=8088
DOCKER_UID=1000
DOCKER_GID=1000

```

Set `DOCKER_UID` and `DOCKER_GID` to your host user values so files created in mounted directories such as `storage/` are not owned by `root`:

```bash
id -u
id -g
```

For production, set:

```env
APP_ENV=production
APP_DEBUG=false
```

## Docker

Build and start the containers:

```bash
docker compose up -d --build
```

Install PHP dependencies:

```bash
docker compose exec app composer install
```

Generate the application key and run migrations:

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

The API is available at:

```text
http://localhost:8088/api
```

phpMyAdmin is available at:

```text
http://localhost:8089
```

Use the MySQL credentials from `.env`.

## Queue Worker

The queue worker is started by Docker Compose as the `queue` service. It processes notification delivery jobs and report generation jobs.

Check the worker status and logs:

```bash
docker compose ps
docker compose logs -f queue
```

## Seed Data

Seeders are optional and are intended only for manual API testing. They are not required for automated tests.

Run them when you need demo users, notifications and reports:

```bash
docker compose exec app php artisan db:seed
```

## Test Database

Automated tests use a separate MySQL database named `notifications_test`.

Create `.env.testing`:

```bash
cp .env.example .env.testing
```

Recommended `.env.testing` values:

```env
APP_ENV=testing
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=notifications_test
DB_USERNAME=notifications
DB_PASSWORD=password

QUEUE_CONNECTION=sync
CACHE_STORE=array
SESSION_DRIVER=array
FILESYSTEM_DISK=local
```

Create the test database:

```bash
docker compose exec mysql mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS notifications_test; GRANT ALL PRIVILEGES ON notifications_test.* TO 'notifications'@'%';"
```

The test suite uses `RefreshDatabase`, so migrations are applied to the test database during tests.

## Quality Checks

Run the full project check:

```bash
docker compose exec app composer check
```

This command runs:

- `composer format:test`
- `composer analyse`
- `composer test`

Run tests only:

```bash
docker compose exec app composer test
```

Run Larastan:

```bash
docker compose exec app composer analyse
```

Run Pint style check:

```bash
docker compose exec app composer format:test
```

Format code with Pint:

```bash
docker compose exec app composer format
```

## API

Create notification:

```http
POST /api/notifications
Content-Type: application/json

{
  "user_id": 1,
  "channel": "email",
  "message": "Your order was shipped."
}
```

Get notification status:

```http
GET /api/notifications/1
```

Get user notification history:

```http
GET /api/users/1/notifications?status=sent&channel=email
```

Request user report generation:

```http
POST /api/users/1/notification-reports
Content-Type: application/json

{
  "period_from": "2026-05-01T00:00:00Z",
  "period_to": "2026-05-06T23:59:59Z"
}
```

Get report status:

```http
GET /api/notification-reports/1
```

Download completed report:

```http
GET /api/notification-reports/1/download
```

## Architecture

Notifications are stored with `processing`, `sent` or `failed` status. Creating a notification saves it first and then dispatches `SendNotificationJob`. The job calls a channel implementation through `NotificationDeliveryService`; successful delivery marks the notification as `sent`, and exhausted retries mark it as `failed`.

Channels are resolved through `NotificationChannelRegistry`. The application code depends on the `NotificationChannel` contract, not on concrete implementations like email or telegram. To add a new channel, create a new class implementing the contract and add it to `config/notifications.php`.

Reports are generated asynchronously by `GenerateNotificationReportJob`. A report starts as `processing`; the generator writes a temporary CSV file and moves it to the final path only after successful generation.

## Report Failure Handling

If report generation fails in the middle of the process, Laravel queue retries the job according to its retry policy. The report file is first written to a temporary `.tmp` path and moved to the final path only after successful generation. This prevents users from downloading a partially generated report.

If all retry attempts are exhausted, the report status is changed to `failed`, the error message is stored in the database, and the download endpoint returns an error instead of a file.

## Adding A New Channel

To add a new delivery channel:

1. Create a class that implements `App\Contracts\NotificationChannel`.
2. Return a unique channel name from `name()`.
3. Implement the delivery logic in `send()`.
4. Add the class to `config/notifications.php`.

No existing service, job or controller needs to be changed.

## Production Improvements

- Add API authentication with an API key.
- Add rate limiting for public API endpoints.
- Add API versioning, for example `/api/v1/notifications`.
- Add idempotency keys for `POST /api/notifications` to avoid duplicate notifications on repeated requests.
- Add OpenAPI / Swagger documentation.
