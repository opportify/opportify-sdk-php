# GitHub Copilot Instructions — `opportify/opportify-sdk-php`

> These instructions apply to every AI-assisted contribution in this repository.
> Read them fully before writing or suggesting any code.

---

## 1. Project Overview

`opportify-sdk-php` is the **official, publicly published PHP SDK** for the
[Opportify API](https://opportify.ai). It is distributed on Packagist under an
MIT licence and consumed directly by third-party developers. Every line of
code that ships is part of the public interface of Opportify's developer ecosystem.

**Tech stack:** PHP ≥ 8.1 · Composer · PHPUnit · Laravel Pint (code style)

**Key directories:**
```
src/          Hand-authored wrapper layer (start here for feature work)
lib/          Auto-generated API client (do NOT edit manually)
tests/        PHPUnit test suite
.github/      Workflows, Copilot instructions (this file)
```

---

## 2. Core Priorities (in order)

### 2.1 Security — highest priority
- **Never commit API keys, credentials, or secrets** of any kind.
- The SDK itself handles user-supplied API keys — treat them as untrusted input.
- Run `git diff --cached | grep -iE "(api_key|secret|password|token)"` before every
  commit.

### 2.2 Public API stability
- This is a published package. Breaking changes require a major version bump and
  must be documented in the PR description.
- Deprecate before removing whenever possible.

### 2.3 Code quality
- Follow PSR-12 and the project's Laravel Pint configuration (`pint.json`).
- Do not edit files under `lib/` — they are auto-generated.
- Every public method must have a PHPDoc block.

---

## 3. Branching & PR Rules

- **`main` is protected.** Never push directly to it.
- All work happens in a dedicated git worktree (see section 5).
- Branch naming: `<type>/<short-description>` (e.g. `feat/add-webhooks`,
  `fix/null-response-handling`, `chore/bump-php-version`).
- One logical change per PR.
- Fill in every section of the PR template before opening.

---

## 4. Versioning

This project uses **semantic versioning** (`MAJOR.MINOR.PATCH`).

- `PATCH` — backwards-compatible bug fixes
- `MINOR` — new backwards-compatible functionality
- `MAJOR` — breaking changes

Packagist derives the version from the **git tag** — do not add a `version` field to `composer.json`.
Publishing the GitHub Release triggers `.github/workflows/packagist.yml`,
which notifies Packagist.

---

## 5. Git Workflow — Worktree, Branch, PR

### 5.1 Flow overview

```
Create worktree + branch  →  Work  →  Open PR against main  →  Review & approve  →  Merge  →  Remove worktree
```

### 5.2 Always work in a dedicated worktree

This repository uses **git worktrees** so that multiple contributors and AI agents
can work concurrently without stepping on each other. Each worktree lives under
`../opportify-sdk-php.worktrees/<short-name>` — a sibling directory to the repo root.

This convention is machine-independent: `../` is always relative to wherever the
repository is cloned, so it works for every contributor regardless of their local
path.

```bash
# 1. Create worktree and branch (run from the repo root)
git worktree add ../opportify-sdk-php.worktrees/<short-name> -b <type>/<short-description> origin/main

# 2. Work inside the worktree
cd ../opportify-sdk-php.worktrees/<short-name>

# 3. Install dependencies
composer install

# 4. Make changes, then run checks before pushing
vendor/bin/phpunit     # PHPUnit
./vendor/bin/pint      # code style

# 5. Push and open a PR
git push -u origin <type>/<short-description>
gh pr create --base main

# 6. After merge, clean up
git worktree remove ../opportify-sdk-php.worktrees/<short-name>
git branch -d <type>/<short-description>
```

---

## 6. What to Do Before Opening a PR

```bash
# Run tests
vendor/bin/phpunit

# Check code style
./vendor/bin/pint --test

# Confirm no secrets are staged
git diff --cached | grep -iE "(api_key|secret|password|token)" && echo "STOP - remove secrets" || echo "OK"
```

---

## 7. What NOT to Do

- **Do not commit or push directly to `main`** — ever, for any reason.
- **Do not open a PR against any branch other than `main`.**
- **Do not merge a PR without at least one review and approval.**
- Do not edit files under `lib/` — they are auto-generated.
- Do not commit `vendor/`.
- Do not bypass or ignore failing CI checks.
- Do not introduce breaking changes without a major version bump and explicit
  documentation.
