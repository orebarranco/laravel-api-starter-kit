---
description: Repository Information Overview
alwaysApply: true
---

# Laravel API Starter Kit Information

## Summary
A production-ready, API-only starter kit built with **Laravel 12** and **PHP 8.4**. It focuses on a headless architecture with no frontend scaffolding, emphasizing strict typing, thin controllers, and the Action pattern for business logic.

## Structure
- [./app/Actions](./app/Actions): Business logic and use cases (Application Layer).
- [./app/DTOs](./app/DTOs): Custom immutable `readonly` Data Transfer Objects.
- [./app/Http/Controllers/Api](./app/Http/Controllers/Api): Versioned API controllers (e.g., V1).
- [./app/Http/Requests](./app/Http/Requests): Validation layer using Form Requests.
- [./app/Http/Resources](./app/Http/Resources): API response transformation layer.
- [./routes/api](./routes/api): Versioned route definitions (e.g., `v1.php`).
- [./tests](./tests): Comprehensive test suite using the **Pest** framework.

## Language & Runtime
**Language**: PHP  
**Version**: 8.4+ (Targeted), ^8.2 (Composer requirement)  
**Build System**: Composer  
**Package Manager**: Composer 2.x

## Dependencies
**Main Dependencies**:
- `laravel/framework`: ^12.0
- `laravel/tinker`: ^2.10.1
- `laravel/sanctum`: Token-based authentication (integrated via framework)
- `spatie/laravel-query-builder`: Query filtering and sorting (mentioned in README)
- `dedoc/scramble`: Automatic OpenAPI documentation

**Development Dependencies**:
- `pestphp/pest`: ^4.4 (Testing framework)
- `larastan/larastan`: ^3.0 (Static analysis)
- `rector/rector`: ^2.3 (Automated refactoring)
- `laravel/pint`: ^1.24 (Code style/linting)
- `laravel/sail`: ^1.41 (Docker development environment)

## Build & Installation
```bash
# Clone and setup the project (installs deps, copies .env, generates key, migrates)
composer setup

# Start the development server and background workers
composer dev
```

## Testing
**Framework**: Pest
**Test Location**: [./tests](./tests)
**Naming Convention**: `*Test.php` and `Pest.php` configuration.
**Configuration**: [./phpunit.xml](./phpunit.xml), [./tests/Pest.php](./tests/Pest.php)

**Run Command**:
```bash
# Run all tests (including coverage, linting, and type checking)
composer test

# Specific test suites
composer test:unit
composer test:types
composer test:type-coverage
```

## Quality & Validation
- **Static Analysis**: [./phpstan.neon](./phpstan.neon) (Max level enabled).
- **Refactoring**: [./rector.php](./rector.php) with strict rules and early returns.
- **Formatting**: [./pint.json](./pint.json) for Laravel Pint.
- **Linting Command**: `composer lint`
