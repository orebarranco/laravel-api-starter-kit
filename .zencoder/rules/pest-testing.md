---
description: Pest Testing Guidelines
alwaysApply: false
---

# Pest Testing Guidelines

## Activation
Apply these rules when writing tests, creating unit or feature tests, adding assertions, debugging test failures, or working with datasets.

## Core Rules
- **Framework**: Use **Pest 4**.
- **Commands**: 
    - Create test: `php artisan make:test --pest {name}`.
    - Run tests: `php artisan test --compact` or `composer test`.
- **Assertions**: Use semantic assertions:
    - `assertSuccessful()` instead of `assertStatus(200)`.
    - `assertNotFound()` instead of `assertStatus(404)`.
    - `assertForbidden()` instead of `assertStatus(403)`.
- **Mocking**: Always import `use function Pest\Laravel\mock;` before use.
- **Datasets**: Use datasets for repetitive tests (e.g., validation rules).
- **Architecture**: Use Pest architecture tests to enforce code conventions (e.g., controllers suffix, directory organization).

## Test Organization
- Feature tests: `tests/Feature/Api/V1/`
- Unit tests: `tests/Unit/Actions/`
- Browser tests (if any): `tests/Browser/`

## Best Practices
- Use model factories for database state.
- Use `RefreshDatabase` for clean state.
- Do NOT delete existing tests without explicit approval.
- Aim for high type coverage and 100% coverage where specified in `composer.json`.
