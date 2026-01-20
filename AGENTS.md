# AGENTS.md

This file guides agentic coding tools working in this repository.
If other rules exist (Cursor/Copilot), follow them first; none are present here.

## Project Snapshot
- App: Hospital Queue Management System (HQMS)
- Stack: Laravel 12, Livewire 4, Flux UI (free), Tailwind CSS v4, Vite
- Auth: Fortify + Sanctum
- Realtime: Laravel Reverb
- Tests: Pest v4
- Runtime: PHP 8.4.x (per project rules)

## Build, Lint, Test Commands

### Setup
- `composer setup` (install deps, migrate, build)

### Dev server
- `composer dev` (Laravel server + queue listener + Vite)
- `npm run dev` (Vite only)

### Lint / format
- `composer lint` (Pint auto-fix)
- `composer test:lint` (Pint check only, CI mode)
- `vendor/bin/pint --dirty` (required before finalizing changes)

### Tests (Pest)
- `composer test` (config clear + lint + test)
- `php artisan test --compact` (full suite, compact output)
- `php artisan test --compact tests/Feature/SomeTest.php`
- `php artisan test --compact --filter=test_name`
- `./vendor/bin/pest` (Pest-only run)
- `./vendor/bin/pest tests/Feature/Auth`
- `./vendor/bin/pest --filter="test name"`

## Codebase Structure
- Routes: `routes/web.php`, `routes/settings.php`
- Livewire: `app/Livewire/`
- Models: `app/Models/` and relationship traits in `app/Traits/Models/`
- Views: `resources/views/` (Flux UI components, Blade)
- Docs: `documents/` (PROJECT.md, DATABASE.md, WORKFLOW.md)

## Core Conventions

### PHP / Laravel
- Always use curly braces for control structures.
- Use explicit return types and parameter types for all methods.
- Use constructor property promotion when possible; avoid empty constructors.
- Prefer Eloquent relationships over raw queries; avoid `DB::`.
- Use `Model::query()` for complex queries; eager-load to avoid N+1.
- Use `casts()` method on models (not `$casts`) when present in peers.
- Use named routes (`route()`) for URLs.
- Avoid `env()` outside config; use `config()` helpers.
- Use Form Request classes for validation in controllers.
- Use queued jobs (`ShouldQueue`) for long operations.

### Livewire
- One root element per component.
- Keep state on the server; always validate and authorize in actions.
- Use lifecycle hooks (`mount`, `updatedFoo`) for side effects.
- Use `wire:key` in loops.
- Use `wire:loading` / `wire:dirty` for UX.

### Blade / Flux UI
- Prefer Flux UI components when available (free set only).
- Fallback to standard Blade components when Flux is unavailable.
- Ensure layouts keep a single root and include `@fluxScripts`.

### Tailwind CSS v4
- Use Tailwind v4 utilities (no deprecated v3 classes).
- Use `@import "tailwindcss"` in CSS (no `@tailwind` directives).
- Prefer `gap-*` utilities over margins for spacing lists.
- Support `dark:` variants when existing UI supports dark mode.

### Testing (Pest)
- Tests must be written in Pest.
- Use model factories for data setup; use existing factory states when possible.
- Prefer `assertForbidden`, `assertNotFound`, `assertSuccessful` over `assertStatus`.
- Use datasets to reduce duplicated data where appropriate.
- Browser tests go in `tests/Browser/` (Pest 4).

## Naming & Types
- Use descriptive method/variable names (e.g., `isRegisteredForDiscounts`).
- Methods in models and traits should use type hints and return types.
- Relationships should be declared on the model/trait with correct return types.
- Enums: use TitleCase for enum keys.

## Imports & Formatting
- One `use` per line; no grouped imports.
- Prefer logical grouping: App, Illuminate, third-party, then Livewire.
- Keep import order consistent with nearby files.
- Run Pint before finalizing changes.

## Error Handling & Auth
- Use `abort(403)` / `abort(404)` for authorization/ownership failures.
- Gate checks and policies are preferred for authorization.
- Validate all Livewire actions and controller inputs.

## Data Model Notes (Critical)
- `users` is auth-only.
- `personal_information` holds account owner profile data.
- `medical_records` stores patient info per visit (self-contained).
- Parent booking for a child: patient fields belong in `medical_records`.

## Tooling Guidance (Laravel Boost)
- Use `search-docs` for Laravel/Livewire/Flux/Tailwind guidance.
- Use `list-artisan-commands` before running Artisan if unsure.
- Use `tinker` for PHP debugging and `database-query` for read-only DB checks.
- Use `get-absolute-url` when sharing app URLs (Herd: `https://[repo].test`).

## Repo-specific CI Notes
- GitHub Actions run Pint and full test suite on PHP 8.4/8.5.
- Flux UI credentials are required in CI secrets.

## Files & Docs
- Do not add documentation files unless explicitly requested.
- Prefer updating existing files over adding new folders.

## Quick Checklist for Agents
- Use existing structure and naming conventions.
- Add/adjust tests for changes.
- Run Pint and the minimal test set.
- Ask before changing dependencies or adding new base folders.
