---
description: Create a spec file and feature branch for the next ZiMaTech step
argument-hint: "Step number and feature name e.g. 02 supplier-offers-pdf"
allowed-tools: Read, Write, Glob, Bash(git:*)
---

You are a senior Laravel developer spinning up a new feature for
the ZiMaTech manufacturing management system. Always follow the
rules in `CLAUDE.md`.

User input: $ARGUMENTS

## Step 1 — Check working directory is clean
Run `git status` and check for uncommitted, unstaged, or
untracked files. If any exist, stop immediately and tell the
user to commit or stash changes before proceeding.
DO NOT CONTINUE until the working directory is clean.

## Step 2 — Parse the arguments
From $ARGUMENTS extract:

1. `step_number` — zero-padded to 2 digits: 2 → 02, 11 → 11

2. `feature_title` — human readable title in Title Case
   - Example: "Supplier Offers PDF" or "Lager Stock Movements"

3. `feature_slug` — git and file safe slug
   - Lowercase, kebab-case
   - Only a-z, 0-9 and -
   - Maximum 40 characters
   - Example: `supplier-offers-pdf`, `lager-stock-movements`

4. `branch_name` — format: `feature/<feature_slug>`
   - Example: `feature/supplier-offers-pdf`

If you cannot infer these from $ARGUMENTS, ask the user
to clarify before proceeding.

## Step 3 — Check branch name is not taken
Run `git branch` to list existing branches.
If `branch_name` is already taken, append a number:
`feature/supplier-offers-pdf-01`, `feature/supplier-offers-pdf-02` etc.

## Step 4 — Switch to main and pull latest
Run:
```
git checkout main
git pull origin main
```

## Step 5 — Create and switch to the feature branch
Run:
```
git checkout -b <branch_name>
```

## Step 6 — Research the codebase
Read these files before writing the spec:
- `CLAUDE.md` — architecture, module flags, integrations, conventions
- `config/modules.php` — to know which feature toggles to touch
- `routes/web.php` — to know the existing public/auth/admin route shape
- `app/Models/` — list the directory; confirm names of related Eloquent models
- `app/Http/Controllers/` and `app/Http/Controllers/Admin/` — match
  existing controller conventions
- `database/migrations/` — list to avoid duplicating table names
- All files in `.claude/specs/` — avoid duplicating existing specs
  (create the directory if it does not yet exist)

If the requested feature is gated by a flag in `config/modules.php`,
note it in the spec and decide whether the spec should flip the flag on
or keep it off.

## Step 7 — Write the spec
Generate a spec document with this exact structure:

---
# Spec: <feature_title>

## Overview
One paragraph describing what this feature does and why
it exists at this stage of the ZiMaTech roadmap.

## Module flag
Which entry in `config/modules.php` this feature belongs to
(`teams`, `projects`, `time`, `suppliers`, `settings`,
`feedback`, `tablar`, `scheduler`, `project_offers`, `emails`).
If the flag must be flipped on, state it explicitly.
If no flag applies, state "No module flag".

## Depends on
Which previous steps or existing features this requires to
be complete.

## Routes
Every new route needed:
- `METHOD /path` — description — access level
  (`public`, `auth`, `admin`)

Group them the way `routes/web.php` does (public at top,
then `Route::middleware(['auth'])->group(...)`,
then `Route::middleware(['auth','role:admin'])->group(...)`).
If no new routes: state "No new routes".

## Database changes
Any new tables, columns, or constraints needed.
Always verify against existing files in `database/migrations/`
before writing this — never reuse a table name.
Use the next timestamped migration name in the project's
convention: `YYYY_MM_DD_HHMMSS_descriptive_name.php`.
If none: state "No database changes".

## Eloquent models
- **Create:** new model(s) with relationships and fillable
- **Modify:** existing model(s) and what changes (relations,
  casts, scopes)

## Controllers
- **Create:** `App\Http\Controllers\...` with method list
- **Modify:** existing controller(s) and what changes

## Middleware / Policies
New authorization logic. Admin-only routes must use
`role:admin` (defined by `App\Http\Middleware\RoleMiddleware`).
If none: state "No middleware or policy changes".

## Views
- **Create:** list new Blade templates with their full path
  under `resources/views/`
- **Modify:** list existing templates and what changes
- Each template must extend the right layout:
  - User-facing: `@extends('user.layouts.index')`
  - Admin-facing: `@extends('admin.layouts.index')`
  - Auth pages: `@extends('layouts.app')`
- German-first UI text; add keys to `lang/de/` and `lang/en/`.

## Files to change
Every existing file that will be modified.

## Files to create
Every new file that will be created.

## New composer packages
Any new Composer dependencies (after `composer require`,
also update `composer.json` by hand if not auto-updated).
If none: state "No new dependencies".

## Rules for implementation
Specific constraints Claude must follow. Always include:
- Follow MVC: routes → controllers (Form Requests for
  validation) → Eloquent models → Blade views
- Use route model binding where possible
- All admin routes wrapped in `role:admin` middleware
- Check `config('modules.<flag>')` before registering
  module-scoped routes
- Use Laravel Pint style (`./vendor/bin/pint`) before commit
- Parameterised Eloquent queries only — no raw SQL strings
  with user input
- German UI strings first; add English translations
- Use Bootstrap 5 + Tailwind v4 utilities already in the
  project; do not introduce a new CSS framework
- No new service providers unless absolutely required

## Definition of done
A specific testable checklist. Each item must be
something that can be verified by running the app or the
test suite. Include at least one Pest test for any new
controller action.
---

## Step 8 — Save the spec
Save to: `.claude/specs/<step_number>-<feature_slug>.md`

## Step 9 — Report to the user
Print a short summary in this exact format:
```
Branch:    <branch_name>
Spec file: .claude/specs/<step_number>-<feature_slug>.md
Title:     <feature_title>
```

Then tell the user:
"Review the spec at `.claude/specs/<step_number>-<feature_slug>.md`
then enter Plan Mode with Shift+Tab twice to begin implementation."

Do not print the full spec in chat unless explicitly asked.
