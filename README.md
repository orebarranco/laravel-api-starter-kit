# Laravel API Starter Kit

A production-ready, API-only starter built with Laravel 12 and PHP 8.4.
Designed for scalable backends, mobile apps, SPAs, SaaS platforms, and microservices.

No frontend scaffolding. No Blade. Pure headless API.

[![PHP Version](https://img.shields.io/badge/PHP-8.4%2B-blue)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-12.x-red)](https://laravel.com)
[![License](https://img.shields.io/badge/License-MIT-green)](https://opensource.org/licenses/MIT)

---

## Core Philosophy

* Thin Controllers
* Business logic inside **Actions**
* Custom lightweight DTOs (no external DTO packages)
* Strict typing
* Consistent JSON responses
* Versioned APIs

---

## Features

* **API-Only Architecture**
* **Action Pattern (Application Layer)**
* Token authentication via Laravel Sanctum
* Email verification & password reset flows
* API Versioning (URI-based)
* Query filtering & sorting via spatie/laravel-query-builder
* Custom typed DTOs (readonly PHP classes)
* Automatic OpenAPI generation via dedoc/scramble
* Rate limiting configuration
* Standardized JSON response format
* Reusable middleware
* Modern testing with Pest
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
* Email verification
* Password reset
* Token revocation on password reset

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

Older versions may include deprecation headers following RFC standards.

---

## Response Format

All responses follow a consistent structure.

### Success

```json
{
  "success": true,
  "message": "Operation successful",
  "data": {}
}
```

### Error

```json
{
  "success": false,
  "message": "Error description",
  "errors": {}
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
│   ├── Requests/Api/       # Validation layer
│   └── Resources/Api/      # API Resources
├── Models/
├── Providers/
├── Traits/                 # ApiResponse, helpers
└── Exceptions/

routes/
├── api.php                 # Main entry point, version grouping
└── api/
    └── v1.php

tests/
└── Feature/Api/V1/
```

---

## Custom DTO Strategy

DTOs are simple, immutable (`readonly`) PHP classes.

No external packages required.

Example:

```php
declare(strict_types=1);

final readonly class CreateUserData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
        );
    }
}
```

Controller usage:

```php
public function store(RegisterRequest $request, CreateUserAction $action)
{
    $dto = CreateUserData::fromArray($request->validated());

    $user = $action->handle($dto);

    return $this->created(new UserResource($user));
}
```

Benefits:

* Zero external coupling
* Full control over data structure
* Predictable typing
* Easier refactoring
* Lightweight architecture

---

## Action Pattern

Every business operation lives in an Action.

```php
final class CreateUserAction
{
    public function handle(CreateUserData $data): User
    {
        return User::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => bcrypt($data->password),
        ]);
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

* `force.json`
* `log.api`
* `auth:sanctum`
* `verified`

Reusable and composable per route group.

---

## Testing

Powered by Pest.

```bash
composer test
```

Recommended structure:

```
tests/
└── Feature/Api/V1/
└── Unit/Actions/
```

---

## Code Quality

Tools included:

* PHPStan (max level)
* Rector
* Laravel Pint

Composer scripts:

```bash
composer lint
composer test
composer test:types
```

Strict rules applied:

* declare(strict_types=1)
* Final classes by default
* Typed properties
* Early returns
* Strict comparisons

---

## License

MIT License

---
