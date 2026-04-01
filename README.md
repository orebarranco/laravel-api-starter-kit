# Laravel API Starter Kit

A production-ready, API-only starter built with Laravel 13 and PHP 8.4.
Designed for scalable backends, mobile apps, SPAs, SaaS platforms, and microservices.

No frontend scaffolding. No Blade. Pure headless API.

[![PHP Version](https://img.shields.io/badge/PHP-8.4%2B-blue)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-13.x-red)](https://laravel.com)
[![License](https://img.shields.io/badge/License-MIT-green)](https://opensource.org/licenses/MIT)

---

## Core Philosophy

* Thin Controllers
* Business logic inside **Actions**
* Custom lightweight DTOs (no external DTO packages)
* Strict typing
* Strict JSON:API compliant responses
* Versioned APIs

---

## Features

* **API-Only Architecture**
* **Action Pattern (Application Layer)**
* Token authentication via Laravel Sanctum
* API Versioning (URI-based)
* Custom typed DTOs (`readonly` PHP classes)
* JSON:API compliant resource objects via `JsonApiResource`
* Standardized JSON:API response format (`application/vnd.api+json`)
* Reusable middleware
* Modern testing with Pest (100% coverage enforced)
* Static analysis (PHPStan + Larastan)
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

Run tests:

```bash
composer test
```

---

## Authentication

Authentication is powered by Laravel Sanctum using token-based authentication.

Protected routes require:

```
Authorization: Bearer {token}
```

Supports:

* Registration
* Login
* Logout

---

## API Versioning

Default strategy: URI-based

```
/api/v1/...
/api/v2/...
```

Each version contains:

```
app/Http/Controllers/Api/V1/
app/Http/Requests/Api/V1/
routes/api/v1.php
```

---

## Response Format

All responses follow the [JSON:API](https://jsonapi.org) specification (`Content-Type: application/vnd.api+json`).

### Success — single resource

```json
{
  "data": {
    "id": "01kn38s0cv0edq25et3vyrxd7s",
    "type": "users",
    "attributes": {
      "name": "Carlos Méndez",
      "email": "carlos@example.com"
    }
  },
  "meta": {
    "request_id": "01HXYZ123ABC",
    "version": "v1",
    "timestamp": "2025-02-24T10:00:00+00:00"
  }
}
```

### Success — with extra meta (e.g. auth token)

```json
{
  "data": {
    "id": "01kn38s0cv0edq25et3vyrxd7s",
    "type": "users",
    "attributes": {
      "name": "Carlos Méndez",
      "email": "carlos@example.com"
    }
  },
  "meta": {
    "request_id": "01HXYZ123ABC",
    "version": "v1",
    "timestamp": "2025-02-24T10:00:00+00:00",
    "token": "1|abc123..."
  }
}
```

### Success — collection (paginated)

```json
{
  "data": [
    {
      "id": "01kn38s0cv0edq25et3vyrxd7s",
      "type": "users",
      "attributes": {
        "name": "Carlos Méndez",
        "email": "carlos@example.com"
      }
    }
  ],
  "meta": {
    "request_id": "01HXYZ123ABC",
    "version": "v1",
    "timestamp": "2025-02-24T10:00:00+00:00",
    "pagination": {
      "total": 100,
      "per_page": 15,
      "current_page": 1,
      "last_page": 7,
      "from": 1,
      "to": 15
    }
  }
}
```

### Error

```json
{
  "errors": [
    {
      "status": "404",
      "code": "NOT_FOUND",
      "title": "Not Found.",
      "detail": "The requested resource was not found."
    }
  ],
  "meta": {
    "request_id": "01HXYZ123ABC",
    "version": "v1",
    "timestamp": "2025-02-24T10:00:00+00:00"
  }
}
```

### Validation error

```json
{
  "errors": [
    {
      "status": "422",
      "code": "VALIDATION_ERROR",
      "title": "The given data was invalid.",
      "detail": "The email field is required.",
      "source": {
        "pointer": "/data/attributes/email"
      }
    }
  ],
  "meta": {
    "request_id": "01HXYZ123ABC",
    "version": "v1",
    "timestamp": "2025-02-24T10:00:00+00:00"
  }
}
```

---

## Project Structure

```
app/
├── Actions/                # Use cases (application layer)
├── DTOs/                   # Custom typed DTOs
├── Http/
│   ├── Controllers/Api/    # Versioned controllers
│   ├── Requests/Api/       # Validation + DTO hydration
│   └── Resources/Api/      # JSON:API resource classes
├── Models/
├── Providers/
├── Traits/                 # ApiResponse
└── Exceptions/

routes/
├── api.php                 # Main entry point, version grouping
└── api/
    └── v1.php

tests/
├── Feature/Api/V1/
└── Unit/
```

---

## Custom DTO Strategy

DTOs are simple, immutable (`readonly`) PHP classes hydrated directly from Form Requests via `toDto()`.

No external packages required.

Example:

```php
declare(strict_types=1);

final readonly class RegisterUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}
}
```

Form Request hydration:

```php
public function toDto(): RegisterUserDTO
{
    return new RegisterUserDTO(
        name: $this->string('name')->toString(),
        email: $this->string('email')->toString(),
        password: $this->string('password')->toString(),
    );
}
```

Controller usage:

```php
public function __invoke(RegisterRequest $request, RegisterUserAction $action): JsonResponse
{
    $result = $action->execute($request->toDto());

    return $this->success(
        new UserResource($result['user']),
        Response::HTTP_CREATED,
        ['token' => $result['token']],
    );
}
```

---

## Action Pattern

Every business operation lives in an Action.

```php
final class RegisterUserAction
{
    /**
     * @return array{user: User, token: string}
     */
    public function execute(RegisterUserDTO $data): array
    {
        $user = User::query()->create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => Hash::make($data->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }
}
```

Controllers delegate. Actions execute business logic.

---

## Rate Limiting

Defined in `AppServiceProvider`.

Examples:

* 60/min default
* 5/min for authentication
* 120/min for authenticated users

Headers returned:

```
X-RateLimit-Limit
X-RateLimit-Remaining
Retry-After
```

---

## Middleware Included

* `force.json` — forces `Accept: application/vnd.api+json` on every request
* `api.version` — sets the API version in request attributes and `X-API-Version` response header
* `auth:sanctum`

Reusable and composable per route group.

---

## Testing

Powered by Pest with 100% code coverage enforced.

```bash
composer test
```

Structure:

```
tests/
├── Feature/Api/V1/
└── Unit/
    ├── Actions/
    ├── Traits/
    └── Models/
```

---

## Code Quality

Tools included:

* PHPStan (max level via Larastan)
* Rector
* Laravel Pint

Composer scripts:

```bash
composer lint      # Rector + Pint
composer test      # Full suite: lint, types, coverage
```

Strict rules applied:

* `declare(strict_types=1)`
* Final classes by default
* Typed properties
* 100% test coverage

---

## License

MIT License

---
