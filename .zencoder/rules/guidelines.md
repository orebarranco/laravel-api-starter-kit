---
description: General Laravel and PHP Guidelines
alwaysApply: true
---

# Laravel API Starter Kit Guidelines

## Foundational Context
This is a **Laravel 12** application running on **PHP 8.4**.
- **PHP**: 8.4.x
- **Laravel Framework**: v12
- **Testing**: Pest v4
- **Static Analysis**: Larastan v3
- **Refactoring**: Rector v2
- **Formatting**: Laravel Pint v1

## PHP & Coding Standards
- **Strict Types**: Always use `declare(strict_types=1);`.
- **Constructor Promotion**: Use PHP 8 constructor property promotion.
- **Type Declarations**: Always use explicit return type declarations and method parameters.
- **Control Structures**: Always use curly braces, even for single-line bodies.
- **Enums**: Enum keys should be `TitleCase`.
- **Classes**: Prefer `final` classes by default.

## Laravel Best Practices
- **Artisan**: Use `php artisan make:` commands to create new files (migrations, controllers, models, etc.).
- **Models**:
    - Use `Model::query()` instead of `DB::`.
    - Define casts in a `casts()` method instead of the `$casts` property.
    - Use factories and seeders when creating models.
- **Controllers & Validation**:
    - Use **Thin Controllers**.
    - Always use **Form Request** classes for validation.
- **Business Logic**: Encapsulate business logic within **Actions** (Application Layer).
- **DTOs**: Use custom immutable `readonly` DTO classes for data transfer.
- **API Versioning**: Use URI-based versioning (e.g., `/api/v1/...`).
- **Response Format**: Use a consistent JSON response format via the `ApiResponse` trait.

## Configuration & Environment
- **Env**: Never use `env()` outside of configuration files. Use `config()` instead.

## Code Quality & Formatting
- **Formatting**: Run `vendor/bin/pint --dirty` to ensure code matches the project's style.
- **Analysis**: Ensure code passes PHPStan/Larastan at max level.
- **Comments**: Prefer PHPDoc blocks over inline comments. Avoid comments within code unless logic is exceptionally complex.
