# AGENTS.md

Laravel 12 + PHP 8.2 POS app (FACTUS — Esperanza Veliz). PostgreSQL in dev, Bootstrap 5 + jQuery/DataTables 2.x + Alpine.js + Chart.js on the front. UI strings, views and test method names are in **Spanish** — keep new code consistent.

## Commands

- Tests: `php artisan test` (runs on in-memory SQLite, no DB needed). Single: `php artisan test --filter=NomeDoTeste`
- Dev server: `php artisan serve` (port 8000). Full stack (`server + queue + pail + vite`): `composer dev`
- Assets: `npm run dev` / `npm run build` (Vite)
- Setup DB: `php artisan migrate --seed`
- Style: `vendor\bin\pint` (run on touched files)

## Testing quirks

- `phpunit.xml` forces `DB_CONNECTION=sqlite :memory:` — the pgsql `.env` is ignored by tests.
- Almost every route is guarded by the `permiso:slug` middleware, so Feature tests must seed `\Database\Seeders\PermisoSeeder::class` (typically in `setUp`) or requests return 403. Existing pattern: `User::factory()->create(['rol' => 'admin'])` + `$this->actingAs($user)`.

## Auth & permissions (non-default)

- Login is by `usuario` field, **not email** (`User::username()` returns `'usuario'`, see `app/Http/Requests/Auth/LoginRequest.php`).
- Custom middleware aliases `rol` (CheckRole) and `permiso` (CheckPermiso) are registered in `bootstrap/app.php`. CheckPermiso calls `User::hasPermiso($slug)` and aborts 403.
- **Roles are dynamic** (`roles` table): the admin manages them from `/roles` (RolController + `resources/views/roles/*`). A user's permissions come **only from its role** — there is no per-user grant (no `permiso_user` table in the rebuilt schema). `users.rol` is a plain string holding `roles.slug` (convention + `exists:roles,slug` validation, no DB FK); display name via `User::role()` (belongsTo Rol, FK `rol`, owner key `slug`).
- **Only the `admin` role is preloaded** (`PermisoSeeder`): slug `admin`, `protegido = true`, always synced with all permissions. Protected roles cannot be edited or deleted. Other roles are created by the admin; `store()` auto-generates `slug` from `nombre` via `Str::slug` (with `_2` suffix on collision, same pattern as `tasa_cambios.tipo`); `slug` is immutable. `permiso_rol` is keyed by the `rol` slug string; `Rol::permisos()` = `belongsToMany(Permiso::class, 'permiso_rol', 'rol', 'permiso_id', 'slug', 'id')`. Deleting a role is blocked while users reference it.
- `User::esAdmin()` returns `rol === 'admin'`; `HerramientasController::importar()` also checks the raw `rol` string. `CheckRole`/`rol` middleware is registered but unused.
- Seeded credentials: `admin`/`admin123` (only).

## Tasas de cambio (tasa_cambios)

- The table is an **append-only history**: `TasaCambioController::actualizar()` never updates a row, it inserts a new one per change (with `user_id` and `origen = 'manual'`). The "current" rate for a `tipo` is the **latest row by `id`** — see `TasaCambio::ultimaDe()`, `activade()`, `ultimasPorTipo()`, `mapaMontos()`. Never assume one row per tipo (no unique constraint on `tipo` in the rebuilt schema).
- `tipo` is an internal system code (`[a-z0-9_]`, unique). In `store()` it's **auto-generated from the `nombre`** via `Str::slug` (with `_2` `_3`... suffix on collision) — the UI never asks for it. The user-facing name is `nombre`; fallback display is `ucfirst($tipo)`.
- Reference rate: `Configuracion` key `tasa_referencia` (default `'bcv'`), settable only on an active rate (`fijarReferencia`). `toggleEstado()` flips `activo` on the whole series of a tipo and is **blocked when the rate is the current reference**.
- Historial (`historial()`): ordered `orderByDesc('created_at')->orderByDesc('id')` — the `id` tie-break matters since tests (and real edits) can insert in the same second. Paginated 20. `variacion` is computed per tipo against the **previous row of the same tipo** (skip other tipos) and attached to the newest row; null when not computable.
- Import: `HerramientasController::importar()` inserts a new row with `origen='importado'` (badge in historial) instead of updating.
- Invoices save a **snapshot** `tasa_cambio` at creation — later rate changes don't affect existing facturas. Products have `fuente_tasa` (source-type selector); price helpers use `ultimasPorTipo`/`mapaMontos`.

## Créditos (facturas a crédito)

- A credit invoice is **100% USD**: debt is fixed in `total_usd` at sale (`total_bs / tasa_cambio` snapshot). All money columns on the ticket, POS confirm modal and `facturas.show` render USD for `estado === 'credito'` (items too, via `precio_unitario_bs / tasa_cambio`).
- Payment method is **not chosen at sale**: `store()` forces `metodo_pago = 'credito'` as a sentinel. At collection time `pagarCredito()` **updates the same row** (no extra columns): sets `metodo_pago` to the real single method (validation `in:efectivo,punto,biopago,divisas,pago_movil,transferencia` — mixto is rejected), `estado_credito = 'cancelado'`, `pago_bs` (= `total_usd * tasa vigente` at that moment) and `fecha_pago`. `pago_bs`/`fecha_pago` are the only credit columns on the table; there is **no `pago_usd`** — `total_usd` doubles as amount paid.
- "A pagar hoy (Bs)" is shown **only** in the collection screen (`facturas.creditos`), computed live as `total_usd * tasaVigente`; the cobrar flow uses a Bootstrap modal with a required method select. `anular()` is blocked once `estado_credito === 'cancelado'`.
- `show()`/`creditos()` pass `tasaVigente` from `TasaCambio::ultimaDe()` (id-based), never `latest()`.

## Environment

- Dev `.env`: `APP_ENV=production`, `APP_DEBUG=false`, DB `pgsql` → `factus_esperanza_veliz` on 127.0.0.1:5432 (user `postgres`). This is intentional; don't "fix" it.
- Migration files carry manual date prefixes (e.g. `2026_08_13_000000_...`); follow that pattern for new ones. The schema was **rebuilt from scratch** (squashed) in the `2026_08_13_*` batch — one migration per table with the final schema, no legacy backfills. After pulling, run `php artisan migrate:fresh --seed` on the dev pgsql DB to rebuild it.

## Frontend gotchas

- DataTables is initialized with Spanish via `window.DataTableSpanish` in `resources/views/layouts/app.blade.php` — it passes a **literal Spanish object** (same shape as the defaults in `resources/js/app.js`). Do NOT switch it back to an async `url: '/js/i18n/es-ES.json'` fetch: the async load combined with `order` + `columnDefs:[{targets:'_all',defaultContent:''}]` makes DataTables 2.3.8 wipe all rows ("Ningún dato disponible").
- Blades mix server-rendered HTML with DataTables/Alpine; UI copy is Spanish.

## Misc

- Thermal printing: see `docs/IMPRESORA.md` (mike42/escpos-php).
- Git: `main` is the integration branch; `login-mejorado` is an open feature branch (login redesign). Local feature branches (e.g. `tasas-dinamicas`) get fast-forwarded into `main` after their feature tests pass. Keep commits in Spanish.
