# Habits Tracker API

REST API for a Habits Tracker application built with Laravel 12, PHP 8.2, MySQL, and Laravel Sanctum personal access tokens.

## Stack

- Laravel 12
- PHP 8.2+
- MySQL
- Laravel Sanctum
- PHPUnit feature tests

## Implemented Features

- `POST /api/register`
- `POST /api/login`
- `POST /api/logout`
- `GET /api/me`
- Full habits CRUD under `/api/habits`
- Habit logs management under `/api/habits/{id}/logs`
- Habit stats under `/api/habits/{id}/stats`
- Dashboard overview under `/api/stats/overview`
- Unified JSON success and error responses

## Unified Response Format

Success:

```json
{
  "success": true,
  "data": {},
  "message": "Operation reussie"
}
```

Error:

```json
{
  "success": false,
  "errors": {},
  "message": "Erreur"
}
```

## Installation

### 1. Create a Laravel 12 project

```bash
composer create-project laravel/laravel habits-tracker-api
cd habits-tracker-api
```

### 2. Install Sanctum

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

### 3. Configure environment

Update `.env` for MySQL:

```env
APP_NAME="Habits Tracker API"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=habits_tracker
DB_USERNAME=root
DB_PASSWORD=
```

Generate the app key:

```bash
php artisan key:generate
```

### 4. Run migrations

```bash
php artisan migrate
```

### 5. Start the server

```bash
php artisan serve
```

API base URL:

```text
http://127.0.0.1:8000/api
```

## Sanctum Notes

- Protected routes use `auth:sanctum`
- Send tokens with `Authorization: Bearer {token}`
- `bootstrap/app.php` is already configured for API exception JSON formatting

## Testing

Run the automated API tests:

```bash
php artisan test
```

## Postman Guide

Detailed endpoint examples, headers, and request bodies are in [docs/postman-examples.md](/c:/Users/pc/Desktop/Janus/docs/postman-examples.md).

## Main API Routes

| Method | Endpoint | Auth | Description |
| --- | --- | --- | --- |
| POST | `/api/register` | No | Register a new user |
| POST | `/api/login` | No | Login and get token |
| POST | `/api/logout` | Yes | Revoke current token |
| GET | `/api/me` | Yes | Return authenticated user |
| GET | `/api/habits` | Yes | List habits, optional `?active=false` |
| POST | `/api/habits` | Yes | Create habit |
| GET | `/api/habits/{id}` | Yes | Show habit |
| PUT | `/api/habits/{id}` | Yes | Update habit |
| DELETE | `/api/habits/{id}` | Yes | Delete habit |
| POST | `/api/habits/{id}/logs` | Yes | Create today's log |
| GET | `/api/habits/{id}/logs` | Yes | List habit logs |
| DELETE | `/api/habits/{id}/logs/{logId}` | Yes | Delete a log |
| GET | `/api/habits/{id}/stats` | Yes | Habit statistics |
| GET | `/api/stats/overview` | Yes | Dashboard overview |
