# NADI — UMKM Predictive Operations

Repo monolith Laravel 12 + Vue 3 (SPA same-origin) untuk aplikasi manajemen
operasional UMKM: inventori → forecast → rekomendasi pembelian.

Spesifikasi produk & aturan bisnis: **`PRD.md`** (sumber kebenaran, disalin dari
`UMKM-Predictive-Operations-AGENTS-PRD.md`).

## Stack

- Laravel 12 (PHP 8.2 lokal XAMPP; produksi PHP 8.4 FrankenPHP di Vercel).
- Vue 3 + Vite SPA di-serve FROM Laravel (same-origin, bukan split API).
- Auth: **Sanctum session (cookie), same-origin SPA**. `EnsureFrontendRequestsAreStateful`
  ke-middleware `api` (`bootstrap/app.php`) — stateful hanya jika `Referer`/`Origin`
  cocok dengan `sanctum.stateful`.
- DB: SQLite lokal; produksi TiDB serverless (MySQL-compatible). SEE `docs/tidb-setup.md`.

## Perintah

- Dev: `npm run dev` (Vite) + `php artisan serve` (default `http://localhost:8000`).
- Test: `php artisan test` (phpunit, in-memory sqlite + database session).
- Format: `vendor/bin/pint` (harus hijau sebelum commit).
- Build SPA: `npm run build`.
- Deploy: Vercel git-deploy via `Dockerfile.vercel` (container, root `Caddyfile`,
  `vercel.json` services.app). docker **tidak** dibutuhkan lokal (builds jarak jauh);
  `vercel dev` butuh Docker jika ingin local preview.

## Konvensi

- Semua respons API berbentuk envelope: sukses `{"data": ...}`, error
  `{"error": {"code", "message", "details"}}` (PRD §24.2). Handler error sudah
  disetup di `bootstrap/app.php` untuk ValidationException (422) & AuthenticationException (401).
- Role: `OWNER` (default saat register), `MANAGER`, `STAFF` (PRD §25). Middleware
  alias `role` sudah ada (`EnsureUserRole`); operasional role > OWNER belum dibuat.
- AuthController: register/login/logout/user (auth:sanctum + session).
- Frontend di `resources/js`, tailwind v4, pinia store di `resources/js/stores`.
- `php artisan test` mewajibkan header `Origin`/`Referer` saat memanggil `/api/*`
  yang butuh session (lihat `statefulHeaders()` di AuthTest).
- Jangan commit secrets; `.env` jangan di-commit (sudah di .gitignore).

## Status (F0 selesai)

Scaffold + auth session + SPA login/register/dashboard + pipeline deploy Vercel +
test hijau. TiDB serverless LIVE (cluster ap-southeast-1, DB `nadi`, semua
migration "Ran"; lihat `docs/tidb-setup.md`). Belum: deploy ke Vercel, CRUD
produk/ingredien/inventori/penjualan, forecast, rekomendasi, role > OWNER, seed.

## Catatan penting

- Container Vercel **stateless**: session/cache Wajib `database`, queue `sync`,
  log `stderr` (lihat Dockerfile.vercel env default). Migrate & seed dijalankan
  manual terhadap TiDB, jangan di build.
- `SANCTUM_STATEFUL_DOMAINS` lokal pakai wildcard port; produksi set domain
  Vercel (mis. `*.vercel.app`) di env project Vercel.