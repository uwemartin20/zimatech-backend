# Spec: Material Module Fixes

## Overview
The Material (Tablar) module currently lets admins store materials with a stock threshold, but the threshold is treated as optional with a hard-coded fallback of `20` (consume) / `5` (return) inside `App\Http\Controllers\TablarController`, which means new materials always get a phantom low-stock warning. The admin table (`admin.tablar.index`) is also missing useful columns (material code, description, threshold), and the Add/Edit modal does not surface those fields, has a few small UX bugs (off-screen filter button, hidden apply control, hard-coded warehouse value, no clear separation of `current stock` vs `added stock`), and the supplier modal does not refresh after detach. This spec adds `code` and `description` columns to `materials`, switches the default low-stock logic to a 0 threshold (no warning unless the admin explicitly sets a positive value), fixes the table/modal UI bugs, and ships a stronger admin-side table view.

## Module flag
`tablar` (already enabled in `config/modules.php`). No flag change.

## Depends on
- `feature/material-multi-warehouse` (the existing `lager_id` column on `materials`, introduced in `2026_06_25_140904_add_image_order_status_lager_id_to_material_table.php`)
- `feature/material-suppliers` (the `material_suppliers` pivot table from `2026_06_08_153747_create_material_suppliers_table.php`)

## Routes
No new routes. Existing routes are reused:
- `POST /tablar` (`admin.tablar.store`) — `admin`
- `PUT /tablar/{id}` (`admin.tablar.update`) — `admin`
- `GET /tablar` (`admin.tablar.index`) — `admin`
- `DELETE /tablar/{id}` (`admin.tablar.destroy`) — `admin`
- `GET /tablar/overview` (`admin.tablar.overview`) — `admin`
- `POST /tablar/{material}/suppliers` (`admin.tablar.suppliers.attach`) — `admin`
- `DELETE /tablar/{material}/suppliers/{supplier}` (`admin.tablar.suppliers.detach`) — `admin`
- `POST /tablar/consume` (`tablar.consume`) — `auth`
- `POST /tablar/return` (`tablar.return`) — `auth`
- `POST /tablar/order-request/{materialId}` (`tablar.order-request`) — `auth`

## Database changes
One migration to add `code` and `description` to `materials`:
- `2026_07_01_120000_add_code_and_description_to_materials_table.php`
  - `string('code', 64)->nullable()->after('name')` — material/article number (SKU). Nullable so existing rows keep working; uniqueness is enforced case-insensitively in the application layer (the column is nullable, so a unique DB index would block multiple NULLs which is fine, but we do not need a hard DB unique constraint at this stage).
  - `text('description')->nullable()->after('code')` — free-text description / notes.
  - Add an index on `code` for the new admin search field.

No existing migration touches these columns (verified by reading every migration under `database/migrations/` and grepping for `code` / `description` on the `materials` table — neither exists today).

## Eloquent models
- **Modify: `App\Models\Material`**
  - Add `'code'` and `'description'` to `$fillable`.
  - Update `getStatusAttribute()` so it returns `'ok'` whenever the threshold is `null` OR `0` (only treat threshold as active when it is a positive integer). The current implementation already returns `'ok'` when `threshold` is `null`; we extend that to `threshold <= 0`.
  - Update the magic-helper `werkzeug()`, `active()`, `orderStatus()` to keep their current behaviour (no change needed; just confirming they remain).
- **No change to `App\Models\MaterialConsumption`** — the consume/return logic moves its `?? 20` / `?? 5` fallback to the controller (see below).

## Controllers
- **Modify: `App\Http\Controllers\Admin\TablarController`**
  - `store()` / `update()` validation: add `'code' => 'nullable|string|max:64'` and `'description' => 'nullable|string|max:2000'`.
  - Fix the typo `public $leger_id;` → `public $lager_id;` (currently exposed but inconsistently named; the spec also aligns all internal reads).
  - `overview()`: change the low-stock query from `whereColumn('quantity', '<=', DB::raw('COALESCE(threshold, 20)'))` to `where(function ($q) { $q->whereNotNull('threshold')->where('threshold', '>', 0)->whereColumn('quantity', '<=', 'threshold'); })`. The metric card "Geringer Bestand" should only count materials the admin has explicitly flagged.
  - Make `overview()` pass a new `lager` model (the Hochregal record, `Lager::find($this->lager_id)`) so the view can render the warehouse name dynamically instead of the hard-coded "Hochregal" string.
- **Modify: `App\Http\Controllers\TablarController` (public)**
  - `consume()`: replace `$threshold = $material->threshold ?? 20;` with `$threshold = (int) ($material->threshold ?? 0);` and guard the low-stock check with `$threshold > 0 && $material->quantity <= $threshold`. The notification copy stays in German.
  - `return()`: same treatment — replace `$threshold = $material->threshold ?? 5;` with `$threshold = (int) ($material->threshold ?? 0);` and guard with `$threshold > 0`. The "delete the day's low-stock notification" path also moves inside that guard.
  - `index()`: include `code` and `description` in the `flatList` mapping so the user-facing `user.tablar.index` view can show them.

## Middleware / Policies
No middleware or policy changes. All admin endpoints stay behind `auth` + `role:admin`.

## Views
- **Modify: `resources/views/admin/tablar/index.blade.php`**
  - Add columns to the table: `Code` (small monospaced), `Beschreibung` (truncated, 1 line), `Mindestbestand` (small badge — render `0`/`null` as the literal `—` so the default-zero behaviour is obvious). Keep `Name`, `Menge`, `Fach`, `Bestellstatus`, `Aktionen`.
  - Add a "Code" filter input next to the existing name/shelf filters; submit the form via the existing `#filterForm` (currently the submit button is hidden inside `.d-none`; surface it as a normal `btn btn-primary` next to the reset button).
  - Add Bootstrap tooltip on the `Mindestbestand` column to explain "0 = keine Warnung".
  - Update the Add/Edit modal:
    - Add a `Code` field (`<input type="text" id="code" class="form-control" maxlength="64">`).
    - Add a `Beschreibung` field (`<textarea id="description" class="form-control" maxlength="2000" rows="3">`).
    - Show the `Mindestbestand` help text under the threshold field: `0 (Standard) = keine Niedrigbestands-Warnung`.
    - Default the `addQuantity` field to `0` (already is) and the `isActive` checkbox to `checked` (already is). No regressions.
    - Replace the disabled `<input class="form-control bg-light" value="Hochregal" disabled>` with a dynamic value rendered from `$lager->name` so multi-warehouse deployments don't show the wrong name.
  - Push the new `code` / `description` values into the `data-*` attributes on each row so the existing `openEditModal` JS can populate the modal.
- **Modify: `resources/views/admin/tablar/overview.blade.php`**
  - Update the low-stock card to only render materials that have `threshold > 0`. Add a small note: "Materialien ohne Mindestbestand werden nicht als kritisch gewertet."
- **Modify: `resources/views/user/tablar/index.blade.php`**
  - Show `code` (small monospaced, optional — only if set) and `description` (truncated, only if set) on each card.

## Files to change
- `app/Http/Controllers/Admin/TablarController.php` — validation, low-stock query, typo, lager view variable
- `app/Http/Controllers/TablarController.php` — flatList mapping, default-threshold guard
- `app/Models/Material.php` — fillable, status accessor
- `database/migrations/2026_07_01_120000_add_code_and_description_to_materials_table.php` — new
- `resources/views/admin/tablar/index.blade.php` — table columns, filters, modal fields, dynamic warehouse
- `resources/views/admin/tablar/overview.blade.php` — low-stock card copy & filter
- `resources/views/user/tablar/index.blade.php` — show code/description on cards
- `public/js/admin/tablar/index.js` (or whichever assets the admin page imports via `<script src="...">`) — extend `openEditModal` to populate `code` and `description`; extend `saveMaterial` to include them in the AJAX payload. Existing filename is `resources/js/admin/tablar/index.js` (compiled into `public/js/admin/tablar/index.js` via Vite).
- `tests/Feature/MaterialModuleTest.php` (new) — Pest feature tests for the new threshold behaviour, code/description persistence, and admin update path.

## Files to create
- `database/migrations/2026_07_01_120000_add_code_and_description_to_materials_table.php`
- `tests/Feature/MaterialModuleTest.php`

## New composer packages
No new dependencies.

## Rules for implementation
- Follow MVC: routes → controllers (Form Requests for validation) → Eloquent models → Blade views.
- Use route model binding where possible; the existing `Material $material` binding for `attach`/`detach` is left alone.
- All admin routes are wrapped in `role:admin` middleware (unchanged from current state).
- The `tablar` module flag is `true`; do not gate the new behaviour behind another flag.
- Use Laravel Pint style (`./vendor/bin/pint`) before commit.
- Parameterised Eloquent queries only — no raw SQL strings with user input. The existing `whereColumn` and `DB::raw('COALESCE(...)')` calls are being replaced with parameterised query-builder expressions.
- German UI strings first; English translations go in `lang/en.json`. (Note: `lang/de/` does not currently exist in the repo; this spec keeps UI strings inline in the Blade templates as the rest of the project does, and adds any new short keys to `lang/en.json` only if they are reused.)
- Use Bootstrap 5 + Tailwind v4 utilities already in the project; do not introduce a new CSS framework.
- No new service providers unless absolutely required.
- All new threshold comparisons use `>` 0, not `>=` 0 — `0` means "no warning at all", which is the new default.
- The `code` column is nullable, and uniqueness is enforced in the application layer (Form Request) rather than a DB unique index, so existing rows without a code keep working.

## Definition of done
1. `php artisan migrate` runs cleanly and adds the `code`/`description` columns plus the `code` index.
2. Creating a new material through the admin modal persists `code` and `description`; the row is visible in the admin table.
3. Editing an existing material updates `code` and `description` and the changes round-trip into the user-facing `user.tablar.index` card.
4. A new material created without a threshold does **not** generate a `low_stock` notification when consumed, and is **not** counted in the "Geringer Bestand" overview card.
5. A material with `threshold = 0` behaves identically to a material with `threshold = null` (no low-stock warning, not in critical-stock list).
6. A material with `threshold = 5` and `quantity = 3` generates a `low_stock` notification the first time it is consumed below 5, exactly once per day.
7. The admin tablar index renders `Code`, `Beschreibung`, and `Mindestbestand` columns; the `Code` filter input filters the table when submitted; the previously hidden apply button is now visible.
8. `php artisan test --filter=MaterialModuleTest` passes — at minimum:
   - `test_default_threshold_is_zero_and_does_not_trigger_low_stock_notification`
   - `test_explicit_threshold_triggers_low_stock_notification_when_below`
   - `test_store_material_persists_code_and_description`
   - `test_update_material_persists_code_and_description`
   - `test_overview_excludes_materials_with_zero_threshold_from_low_stock`
9. `./vendor/bin/pint` reports no diff on changed files.
10. Manual smoke: load `/admin/tablar`, open the Add modal, save a new material with no threshold; consume it from the user-side; confirm no notification is created and the overview card does not increment.
