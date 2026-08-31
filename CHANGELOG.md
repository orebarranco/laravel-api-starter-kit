# Changelog

All notable changes to this project are documented here.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and
versioning follows [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Added

- `LICENSE` (MIT). The README advertised MIT since the first release, but the
  file was missing, which legally left the code as all rights reserved for
  anyone who cloned it.
- `CONTRIBUTING.md` with the branch, commit and Pull Request workflow, and the
  local order of `lint → types → test` that mirrors what CI validates.
- `CHANGELOG.md`, backfilled from the existing `v1.0.0` and `v1.1.0` tags.
- `.github/dependabot.yml` for Composer (weekly, grouped, minor and patch) and
  GitHub Actions (monthly). No npm entry: this starter ships no `package.json`.
- `.github/pull_request_template.md`.

### Changed

- CI now runs two jobs instead of one. `Pint + Rector + Larastan` runs with no
  coverage driver, and `Tests` only runs if it passes. A single job meant the
  whole suite booted just to discover a formatting slip, and a branch ruleset
  had one opaque check to require instead of two meaningful ones.
- Bumped `actions/checkout` to v7 and `actions/cache` to v6.

## [1.1.0] - 2026-04-20

### Added

- GitHub Actions workflow running the full suite on push and pull request.

## [1.0.0] - 2026-04-14

First published version. Headless API starter on Laravel 13 and PHP 8.4.

### Added

- Token authentication through Laravel Sanctum, with the login flow raising an
  explicit domain exception instead of an inline `abort()`.
- Email verification with signed URLs, and password reset by email pointing at
  `FRONTEND_URL`.
- Rate limiting for both auth and protected endpoints, per IP and per user.
- URI-based API versioning, with an `api.version` middleware setting the
  `X-API-Version` response header.
- Full JSON:API response format: resource objects through `JsonApiResource`,
  a `force.json` middleware enforcing `Accept: application/vnd.api+json`, and
  a centralized exception handler mapping failures to JSON:API errors.
- Custom `readonly` DTOs hydrated from Form Requests via `toDto()`, with no
  external package.
- Business logic in Actions, keeping controllers thin.
- Users index endpoint with filtering and sorting through
  `spatie/laravel-query-builder`, and pagination customization moved into a
  macro so every paginated response shares one shape.
- Quality gates: Pest with 100% coverage and 100% type coverage enforced,
  Larastan at max level, Rector, and Laravel Pint.

### Changed

- Default database switched from MySQL to SQLite, so a fresh clone runs its
  test suite without provisioning anything.

[Unreleased]: https://github.com/orebarranco/laravel-api-starter-kit/compare/v1.1.0...HEAD
[1.1.0]: https://github.com/orebarranco/laravel-api-starter-kit/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/orebarranco/laravel-api-starter-kit/releases/tag/v1.0.0
