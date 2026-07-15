# Spec: Tablar Module Optimization

## Overview
This feature optimizes the existing Tablar (warehouse / material) module by enriching
the material **show** view with a richer detail card (image, status, key fields,
inline quantity updater, recent supplier, and a deep-link anchor back to the
material in the index list), and by adding three new quick-toggle search filters
on the **index** view: `Niedriger Bestand` (low stock), `Leere Materialien` (zero
stock), and `Status` (existing `order_status` enum). It also adds a dedicated
supplier list view (a sibling of the existing index — `?supplier=…` or
`/admin/lager/{lager_id}/tablar/{id}/supplier-list`) so admins can pivot from
the show page straight to "all materials from this supplier in this lager".

The work is purely additive: it does not change the public API of
`TablarController` (no new routes to register beyond what the supplier list
requires), does not introduce new tables, and uses the existing
`material_suppliers` pivot already present in the schema.

## Module flag
- `tablar` — already `true` in `config/modules.php`. No flag change required.

## Depends on
- Existing `materials`, `material_suppliers`, `material_consumption` tables.
- Existing `Material` model (with `suppliers()` and `lager()` relations).
- Existing admin tablar routes registered under
  `Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')`.
- Existing public tablar routes for the workshop view
  (`App\Http\Controllers\TablarController`).

## Routes
All new routes sit inside the existing `if (config('modules.tablar')) { … }`
admin block, scoped to `Route::prefix('/lager/{lager_id}/tablar')->name('tablar.')`.

- `GET /admin/lager/{lager_id}/tablar` — already exists. Extended with new
  filter parameters: `low_stock=1`, `empty=1`, `status={notified|ordered|blocked|delivered}`,
  `page`, `name` (anchor target). Access: `admin`.
- `GET /admin/lager/{lager_id}/tablar/{id}/supplier-list` — new. Lists every
  material in this lager that is attached to a given supplier, with the
  supplier's most-recent pivot timestamp. Accepts optional `?supplier={id}`
  query string (required). Access: `admin`.
- `PATCH /admin/lager/{lager_id}/tablar/{id}/quantity` — new. Updates only the
  `quantity` field, used by the inline editor on the show page. Access: `admin`.
- `GET /admin/lager/{lager_id}/tablar/{id}` — already exists. Re-renders the
  show view with the new fields. Access: `admin`.

No new public routes.

## Database changes
No new tables. The required fields (`image`, `order_status`, `threshold`,
`quantity`, `is_active`, `is_werkzeug`, `code`, `description`, `lager_id`)
already exist on `materials` from migrations
`2026_04_14_090352`, `2026_06_25_140904`, `2026_07_01_120000`.

## Eloquent models
- **Modify:** `App\Models\Material`
  - Add a `Scope::lowStock()` that returns materials where
    `quantity <= threshold`.
  - Add a `Scope::empty()` that returns materials where `quantity = 0`.
  - Add a `Scope::forStatus(string $status)` that filters by `order_status`.
  - Add an accessor `Material::getStatusLabelAttribute(): ?string` returning
    the German label for `order_status` (uses the same map already in
    `TablarController::index`).
  - Add a `Material::mostRecentSupplier(): ?Supplier` helper that returns the
    supplier attached to this material with the latest `material_suppliers.created_at`,
    or `null` if none.

## Controllers
- **Modify:** `App\Http\Controllers\Admin\TablarController`
  - `index(Request, int $lager_id)`:
    - Read new query parameters `low_stock`, `empty`, `status`,
      `page`, and `name` (for the anchor).
    - When `low_stock=1` is set, apply the new local scope
      `->lowStock()`.
    - When `empty=1` is set, apply `->empty()`.
    - When `status` is one of the four enums, apply `->forStatus($status)`.
    - When `name` is set AND `page` is set, build the result list and
      compute the page index where the matching material lives, then redirect
      to that page with `?highlight={id}#material-{id}` so the index.js
      can scroll to and highlight it.
    - Pass the active filter state to the view so the UI can reflect which
      quick-toggles are on.
  - `show(int $lager_id, int $id)`:
    - Eager-load `suppliers` (sorted by `material_suppliers.created_at DESC`).
    - Pass `recentSupplier` (first of the eager-loaded list) and
      `supplierListUrl` (new route with `?supplier={id}`) to the view.
  - **New:** `updateQuantity(Request, int $lager_id, int $id)` — validates
    `{ quantity: 'required|integer|min:0' }` and writes only that field. Returns
    the updated material as JSON.
  - **New:** `supplierList(Request, int $lager_id)` — validates
    `{ supplier: 'required|integer|exists:suppliers,id' }`, returns
    `admin.tablar.supplier-list` with: the supplier, all materials in the
    given lager that are attached to it, and the most recent pivot timestamp
    per material.

## Middleware / Policies
No new middleware or policies. All admin routes are already wrapped in
`auth + role:admin`.

## Views
- **Modify:** `resources/views/admin/tablar/index.blade.php`
  - Add three quick-toggle filter chips above the existing filter row:
    `Niedrigerbestand` (toggle bound to `?low_stock=1`),
    `Leere Materialien` (`?empty=1`), and
    `Status` (a small Bootstrap dropdown listing the four enum values; selecting
    one navigates to `?status=…`).
  - Each existing `<tr>` gains `id="material-{{ $material->id }}"` and a
    `data-highlight` attribute so the index.js can locate it.
  - The "Status" column already exists; add a tooltip/aria-label for clarity.
  - Add a small `Filter zurücksetzen` chip is already present; ensure it
    also clears the new quick-toggles.
- **Modify:** `resources/views/admin/tablar/show.blade.php`
  - Layout becomes a two-column card on `md+`:
    - **Left column** (col-md-5): large material image (fallback icon), code,
      description, type, tablar/shelf, threshold, is_werkzeug and is_active
      badges.
    - **Right column** (col-md-7):
      - Status card with the `order_status` translated label + a
        "Bisheriger Lieferant" card (most-recent supplier with name, company,
        email, phone) and a `Alle Materialien von diesem Lieferanten` link
        to `supplier-list`.
      - Inline quantity editor card:
        - Shows current `quantity` (read-only display).
        - "Hinzufügen" number input (default `0`).
        - "Speichern" button POSTs to `updateQuantity` via fetch, refreshes the
          page on success.
        - "Mindestbestand" displayed underneath with a warning chip when the
          material is in the low-stock state.
      - "Lieferanten" quick-link to the supplier modal (deep link to the
        existing supplier modal on the index, or simply a link back to
        `index?supplier={id}` if we choose the URL anchor approach — see
        Rules below).
      - A "back to list" anchor that includes the page number and material
        name in the URL: `?page={page}&name={name}#material-{id}`. The
        controller in step 1 handles the page redirect when both are set.
- **Create:** `resources/views/admin/tablar/supplier-list.blade.php`
  - Extends `admin.layouts.index`.
  - Title: `Materialien — Lieferant: {supplier->name}`.
  - Table of materials (image, code, name, qty, shelf, status, last
    attached `pivot->created_at` formatted in German).
  - Empty state if the supplier has no materials in this lager.

## Files to change
- `app/Http/Controllers/Admin/TablarController.php` — new filter logic, new
  `updateQuantity`, new `supplierList`, expanded `show`.
- `app/Models/Material.php` — scopes + `mostRecentSupplier()` + status label
  accessor.
- `resources/views/admin/tablar/index.blade.php` — quick-toggle chips, row
  anchors, `data-highlight` attribute.
- `resources/views/admin/tablar/show.blade.php` — richer layout described above.
- `public/js/admin/tablar/index.js` — handle new filter UI, scroll/highlight
  the `?name=…&page=…` target on load, wire the inline quantity editor on
  the show page.
- `routes/web.php` — register `tablar.update-quantity` and `tablar.supplier-list`
  inside the existing `if (config('modules.tablar'))` block.
- `lang/de/tablar.php` (or existing translation file under `lang/de/`) — add
  new keys for "Niedrigerbestand", "Leere Materialien", "Status",
  "Bisheriger Lieferant", "Alle Materialien von diesem Lieferanten",
  "Menge aktualisieren", "Hinzufügen", "Speichern", "Aktueller Bestand".
- `lang/en/tablar.php` (or fallback) — matching English keys.

## Files to create
- `resources/views/admin/tablar/supplier-list.blade.php`

## New composer packages
No new dependencies.

## Rules for implementation
- Follow MVC: routes → controllers (Form Requests for validation where more
  than one field is involved; the inline `updateQuantity` is small enough to
  validate inline) → Eloquent models → Blade views.
- Use parameterised Eloquent queries only — no raw SQL strings with user
  input. The `lowStock` and `empty` scopes use `whereColumn` and `where` only.
- The `name` filter must continue to support the existing URL contract
  (`?name=…`) so deep links from the show page resolve to the same page
  where the material currently sits. The implementation must first run
  the filtered query un-paginated, locate the matching material's
  `firstItem()` offset, compute the page number, then redirect
  (`301`/`302`) to `?name=…&page=N#material-{id}`. Cache-friendly: do
  not redirect when the requested `page` already matches.
- The status dropdown must be a real `<form>` (GET) — no JavaScript-only
  filter — so the URL is shareable and the back button works.
- Quick-toggles (`low_stock`, `empty`) are simple GET parameters that
  collapse into the existing query; they share the `Filter zurücksetzen`
  reset link.
- `mostRecentSupplier()` must reuse the same sort direction as
  `getSuppliers()` (`pivot->created_at DESC`); the show view labels this
  row "Bisheriger Lieferant".
- German UI strings first; add English translations to `lang/en/`.
- Use Bootstrap 5 + Tailwind v4 utilities already in the project; do not
  introduce a new CSS framework.
- The inline quantity editor on the show page must use the existing
  CSRF token meta tag and the same `showAlert()` helper as the index.
- All admin routes wrapped in `role:admin` middleware (already true).
- Check `config('modules.tablar')` before registering the new
  `updateQuantity` and `supplierList` routes (already inside the existing
  block in `routes/web.php`).
- Use Laravel Pint style (`./vendor/bin/pint`) before commit.
- No new service providers unless absolutely required.

## Definition of done
- [ ] `php artisan route:list` shows the two new admin routes.
- [ ] `php artisan migrate` runs cleanly (no schema changes expected).
- [ ] `index?low_stock=1` returns only materials with `quantity <= threshold` and
      `threshold > 0`.
- [ ] `index?empty=1` returns only materials with `quantity = 0`.
- [ ] `index?status=ordered` returns only materials with `order_status = ordered`.
- [ ] `index?name=Schraube&page=2#material-12` scrolls to and visually highlights
      the material with id `12` after the page loads.
- [ ] `show` page renders the new layout (image card, status, recent supplier,
      inline quantity editor, "Alle Materialien von diesem Lieferanten" link).
- [ ] PATCH on the inline quantity editor persists and the show page reflects
      the new value after reload.
- [ ] `supplierList?supplier={id}` lists every material in the lager attached to
      that supplier, ordered by most-recent pivot.
- [ ] At least one Pest test in `tests/Feature/TablarFilterTest.php` covering the
      three new filters and the supplier list.
- [ ] `./vendor/bin/pint` runs clean on every changed file.
- [ ] Both `lang/de/` and `lang/en/` translation files have keys for every
      new UI string.
