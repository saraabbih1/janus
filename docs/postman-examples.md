# Postman Examples

Base URL:

```text
http://127.0.0.1:8000/api
```

Default headers for JSON requests:

```text
Accept: application/json
Content-Type: application/json
```

Protected routes also need:

```text
Authorization: Bearer {{token}}
```

## 1. Register

**Request**

```http
POST /api/register
Accept: application/json
Content-Type: application/json
```

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123"
}
```

## 2. Login

**Request**

```http
POST /api/login
Accept: application/json
Content-Type: application/json
```

```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

## 3. Logout

**Request**

```http
POST /api/logout
Accept: application/json
Authorization: Bearer {{token}}
```

No body.

## 4. Me

**Request**

```http
GET /api/me
Accept: application/json
Authorization: Bearer {{token}}
```

## 5. List Habits

**Request**

```http
GET /api/habits
Accept: application/json
Authorization: Bearer {{token}}
```

Filter inactive habits only:

```http
GET /api/habits?active=false
```

## 6. Create Habit

**Request**

```http
POST /api/habits
Accept: application/json
Content-Type: application/json
Authorization: Bearer {{token}}
```

```json
{
  "title": "Morning Workout",
  "description": "30 minutes of exercise",
  "frequency": "daily",
  "target_days": 5,
  "color": "#FF6B35",
  "is_active": true
}
```

## 7. Show Habit

**Request**

```http
GET /api/habits/{{habit_id}}
Accept: application/json
Authorization: Bearer {{token}}
```

## 8. Update Habit

**Request**

```http
PUT /api/habits/{{habit_id}}
Accept: application/json
Content-Type: application/json
Authorization: Bearer {{token}}
```

```json
{
  "title": "Morning Workout Updated",
  "description": "45 minutes of exercise",
  "frequency": "daily",
  "target_days": 6,
  "color": "#1D9BF0",
  "is_active": true
}
```

## 9. Delete Habit

**Request**

```http
DELETE /api/habits/{{habit_id}}
Accept: application/json
Authorization: Bearer {{token}}
```

## 10. Create Habit Log

Creates a log for the current day only. The same habit cannot be logged twice on the same day.

**Request**

```http
POST /api/habits/{{habit_id}}/logs
Accept: application/json
Content-Type: application/json
Authorization: Bearer {{token}}
```

```json
{
  "note": "Completed before breakfast"
}
```

## 11. List Habit Logs

**Request**

```http
GET /api/habits/{{habit_id}}/logs
Accept: application/json
Authorization: Bearer {{token}}
```

## 12. Delete Habit Log

**Request**

```http
DELETE /api/habits/{{habit_id}}/logs/{{log_id}}
Accept: application/json
Authorization: Bearer {{token}}
```

## 13. Habit Statistics

**Request**

```http
GET /api/habits/{{habit_id}}/stats
Accept: application/json
Authorization: Bearer {{token}}
```

**Response payload fields**

- `current_streak`
- `longest_streak`
- `total_completions`
- `completion_rate`

## 14. Overview Statistics

**Request**

```http
GET /api/stats/overview
Accept: application/json
Authorization: Bearer {{token}}
```

**Response payload fields**

- `total_active_habits`
- `completed_today`
- `habit_with_longest_streak`
- `completion_rate_last_7_days`

## Suggested Postman Workflow

1. Call `POST /api/register` or `POST /api/login`.
2. Copy `data.token` into a Postman variable named `token`.
3. Create a habit with `POST /api/habits`.
4. Save the returned habit id into `habit_id`.
5. Create a daily log with `POST /api/habits/{{habit_id}}/logs`.
6. Verify progress with `GET /api/habits/{{habit_id}}/stats`.
7. Check dashboard metrics with `GET /api/stats/overview`.
