# Contributing guide

This document defines the repository workflow: how to create branches, how to
write commits and how to open a Pull Request. The goal is that `master` is
always installable and that every change is traceable by reading the history.

This is a **starter kit**: whatever lands here is inherited by every project
generated from it. That rules out anything specific to one client, to a concrete
business domain, or to a single machine's environment.

---

## 1. General flow

1. `master` is always installable. **Nobody pushes directly to `master`.**
2. All work starts on its own branch, created from an up-to-date `master`.
3. It is integrated through a Pull Request targeting `master`.
4. CI must be green before merging.
5. Merges use a **merge commit**, not a squash.

On point 5: the commits of a branch tell different things — the behavior, its
tests, its documentation — and keeping them separate is worth it. A squash melts
them into one message that no longer explains any of them.

### Approval

| Kind of change | What it takes |
|----------------|---------------|
| Touches behavior, data or authorization (`feat`, `fix`, `refactor`, migrations) | Review before merging |
| Trivial and self-explanatory (`docs`, `style`, formatting `chore`) | Self-merge with CI green |

---

## 2. Branches

```
<type>/<short-description-in-kebab-case>
```

```
feat/refresh-token-rotation
fix/email-verification-signature
chore/update-dependencies
docs/contributing-guide
```

The type is one of those in the table in section 3.4. No ticket keys: this
repository does not use them, and a branch or comment referencing an external
identifier stops making sense as soon as that identifier is archived.

Before you start, update `master`:

```bash
git checkout master
git pull origin master
git checkout -b feat/my-change
```

---

## 3. Commits

This repository uses [Conventional Commits](https://www.conventionalcommits.org/).

### 3.1. Header

```
<type>(<scope>): <short description in the imperative>
```

The scope is optional and names the affected part (`auth`, `dtos`, `ci`,
`dependabot`). The description is imperative and stays under 72 characters.

```
feat(auth): rotate sanctum tokens on password reset
```

### 3.2. Body

Explain **which problem it solves and why this way**, not which lines changed:
the diff already shows that. No class or method names unless they are the point.

Wrap every line at **72 characters maximum**. A paragraph on one long line is
unreadable in `git log` and in any narrow terminal.

```
A leaked token stayed valid after the owner reset their password, so the
reset gave a false sense of recovery: the attacker kept the session the
user believed they had just closed.
```

### 3.3. Language

**Whatever travels with the code is in English**: commit messages, class,
method and test names, and code comments.

**Repository documentation is in English**: this file, the README, the
CHANGELOG and Pull Request bodies. This starter ships no UI copy — it is a
headless API — so the API error codes and messages are in English too.

No co-authorship lines for tools or assistants are added to the message.

### 3.4. Types

| Type | What for |
|------|----------|
| `feat` | New functionality |
| `fix` | Defect correction |
| `docs` | Documentation |
| `style` | Formatting, no behavior change |
| `refactor` | Restructuring, no behavior change |
| `perf` | Performance |
| `test` | Adding or changing tests |
| `build` | Build system or dependencies |
| `ci` | Continuous integration |
| `chore` | Configuration and minor tasks |

---

## 4. Pull Request

1. Push the branch and open the PR against `master`.
2. Fill in the template (`.github/pull_request_template.md`).
3. The title follows Conventional Commits, same as a commit.
4. **One PR per feature.** A huge PR gets a shallow review. Size is controlled
   by scoping the change, not by splitting a coherent change to hit a line
   count.
5. Wait for CI to be green.
6. Merge with a merge commit.

---

## 5. Quality

Before asking for a review, run locally the same thing CI validates.

| Action | Command | What it does |
|--------|---------|--------------|
| Fix style | `composer lint` | Rector + Pint, **they modify files** |
| Verify style | `composer test:lint` | Rector in dry-run + Pint in test mode |
| Static analysis | `composer test:types` | PHPStan through Larastan |
| Type coverage | `composer test:type-coverage` | Pest, 100% enforced |
| Tests | `composer test:unit` | Pest in parallel, 100% coverage enforced |
| Everything | `composer test` | Clears config, then all of the above |

The difference that matters: `composer lint` **fixes**, `composer test:lint`
only **verifies**. CI runs the verifying version, so fix locally before pushing.

### 5.1. Order before committing

```
composer lint  →  composer test:types  →  composer test
```

`lint` goes **first** because Rector and Pint modify files. If you commit before
running it, their corrections land in a later commit and pollute both the
history and the PR diff.

If any of the three fails, stop and fix it. Nothing is committed red.

CI (`.github/workflows/ci.yml`) runs two jobs in order: first `composer
test:lint` and `composer test:types`, and only if they pass, the type coverage
and the test suite.

---

## 6. Code conventions

- Strict typing everywhere: `declare(strict_types=1)` and `final` classes.
- Thin controllers. Business logic lives in `app/Actions/`.
- Typed `readonly` DTOs in `app/DTOs/`, hydrated from Form Requests via
  `toDto()`.
- Responses go through `app/Http/Resources/`, JSON:API compliant.
- Domain failures are explicit exceptions in `app/Exceptions/`, never inline
  `abort()` calls.
- Tests mirror the source layout: `app/Actions/Auth/LoginAction.php` is tested
  by `tests/Unit/Actions/Auth/LoginActionTest.php`. HTTP behavior goes to
  `tests/Feature/Api/`.
- Coverage and type coverage are enforced at 100%. A new file without tests
  fails CI.

`CLAUDE.md` carries the same rules in the form agents read.

---

## 7. Checklist before opening a PR

- [ ] The branch came from an up-to-date `master`.
- [ ] `composer lint`, `composer test:types` and `composer test` are green, in that order.
- [ ] There are tests for what I changed, mirroring the source layout.
- [ ] Coverage and type coverage are still at 100%.
- [ ] The PR title follows Conventional Commits.
- [ ] The diff is scoped and reviewable in one sitting.
- [ ] Nothing I added is specific to a client, a business or my machine.
