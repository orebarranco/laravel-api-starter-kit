# Laravel API Starter Kit

A production-ready, API-only starter built with Laravel 13 and PHP 8.4.
Designed for scalable backends, mobile apps, SPAs, SaaS platforms, and microservices.

No frontend scaffolding. No Blade. Pure headless API.

[![PHP Version](https://img.shields.io/badge/PHP-8.4%2B-blue)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-13.x-red)](https://laravel.com)
[![License](https://img.shields.io/badge/License-MIT-green)](https://opensource.org/licenses/MIT)

---

## Core Philosophy

* Thin controllers — business logic lives in **Actions**
* Typed DTOs hydrated from Form Requests via `toDto()`
* Strict typing throughout (`declare(strict_types=1)`, `final` classes)
* JSON:API compliant responses
* Versioned APIs from day one

---

## Features

* Token authentication via Laravel Sanctum
* Email verification with signed URLs
* Password reset via email
* Rate limiting (per IP and per user)
* API versioning (URI-based)
* Custom `readonly` DTOs (no external packages)
* JSON:API resource objects via `JsonApiResource`
* Centralized exception handling
* Pest with 100% coverage enforced
* Static analysis via Larastan
* Automated refactoring with Rector
* Code formatting via Laravel Pint

---

## Requirements

* PHP 8.4+
* Composer 2.x
* MySQL / PostgreSQL / SQLite

---

## Quick Start

```bash
git clone https://github.com/orebarranco/laravel-api-starter-kit.git
cd laravel-api-starter-kit

composer setup
```

```bash
composer test
```

---

## Authentication

Protected routes require:

```
Authorization: Bearer {token}
```

| Endpoint | Method | Auth |
|----------|--------|------|
| `/api/v1/auth/register` | POST | — |
| `/api/v1/auth/login` | POST | — |
| `/api/v1/auth/logout` | POST | Bearer |
| `/api/v1/auth/me` | GET | Bearer |
| `/api/v1/auth/forgot-password` | POST | — |
| `/api/v1/auth/reset-password` | POST | — |
| `/api/v1/auth/email/verify/{id}/{hash}` | GET | Signed URL |
| `/api/v1/auth/email/resend` | POST | Bearer |

---

## API Versioning

URI-based versioning. Each version is fully isolated:

```
app/Http/Controllers/Api/V1/
app/Http/Requests/Api/V1/
routes/api/v1.php
```

---

## Response Format

All responses use `Content-Type: application/vnd.api+json`.

**Success**

```json
{
  "data": {
    "id": "01kn38s0cv0edq25et3vyrxd7s",
    "type": "users",
    "attributes": { "name": "Carlos Méndez", "email": "carlos@example.com" }
  },
  "meta": { "request_id": "...", "version": "v1", "timestamp": "..." }
}
```

**Error**

```json
{
  "errors": [{
    "status": "422",
    "code": "VALIDATION_ERROR",
    "title": "The given data was invalid.",
    "detail": "The email field is required.",
    "source": { "pointer": "/data/attributes/email" }
  }],
  "meta": { "request_id": "...", "version": "v1", "timestamp": "..." }
}
```

---

## Project Structure

```
app/
├── Actions/                # Single-purpose use cases
├── DTOs/                   # Immutable readonly DTOs
├── Exceptions/             # Typed exceptions + centralized handler
├── Http/
│   ├── Controllers/Api/    # Versioned, single-action controllers
│   ├── Middleware/         # ForceJsonResponse, EnsureEmailIsVerified
│   ├── Requests/Api/       # Validation + toDto()
│   └── Resources/Api/      # JSON:API resources
├── Models/
├── Providers/              # AppServiceProvider (rate limiting, email verification, password reset)
└── Traits/                 # ApiResponse

routes/
├── api.php                 # Version grouping
└── api/v1.php
```

---

## Action Pattern

Controllers delegate to single-purpose Action classes:

```php
// Controller
public function __invoke(RegisterRequest $request, RegisterUserAction $action): JsonResponse
{
    $result = $action->execute($request->toDto());

    return $this->success(new UserResource($result['user']), Response::HTTP_CREATED, [
        'token' => $result['token'],
    ]);
}

// Action
public function execute(RegisterUserDTO $data): array
{
    $user = User::query()->create([...]);

    event(new Registered($user));

    return ['user' => $user, 'token' => $user->createToken('auth_token')->plainTextToken];
}
```

---

## Rate Limiting

| Limiter | Routes | Limit |
|---------|--------|-------|
| `auth` | register, login, forgot-password, reset-password, email verify | 5 req/min per IP |
| `api` | all authenticated endpoints | 120 req/min per user · 60 req/min per IP |

---

## Email Verification

Sent automatically on registration via the `Registered` event.

```
GET  /auth/email/verify/{id}/{hash}   — no auth required (signed URL)
POST /auth/email/resend               — requires Bearer token
```

---

## Password Reset

```
POST /auth/forgot-password   — sends reset link to email (no auth required)
POST /auth/reset-password    — resets password and invalidates all tokens (no auth required)
```

The reset link points to `FRONTEND_URL/reset-password?token=...&email=...`. Configure `FRONTEND_URL` in your `.env`.

---

## Middleware

* `force.json` — enforces `Accept: application/vnd.api+json`
* `api.version` — sets `X-API-Version` response header
* `verified` — requires verified email → `EMAIL_NOT_VERIFIED` (403)

---

## Testing

```bash
composer test          # lint + static analysis + coverage
composer test:unit     # unit tests only
```

Powered by Pest 4 with 100% coverage enforced. Feature and unit tests for all controllers, actions, middleware, and exception handling.

---

## Code Quality

```bash
composer lint          # Rector + Pint
```

* PHPStan level max via Larastan
* Rector for automated refactoring
* Laravel Pint for code style

---

## License

MIT License
