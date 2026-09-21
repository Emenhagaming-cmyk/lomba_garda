# TokoKu — UMKM Business OS

Repo monolith Laravel 12 + Vue 3 (SPA same-origin) untuk aplikasi manajemen
operasional UMKM: satu input transaksi → stok, keuangan, pelanggan, dan laporan
ter-update otomatis (ERP + CRM + Dashboard + Business Intelligence).

Spesifikasi produk & aturan bisnis: **`alur.docx`** (PRD terbaru dari owner;
sumber kebenaran) + **`PRD_TokoKu_UMKM_Business_OS_Lengkap.docx`**.
`PRD_UMKM_Business_OS.docx` lama sudah tergantikan. Ringkasan produk & roadmap
ada di **`PRD.md`**.

## Nama produk

- **TokoKu** — nama tampilan (brand UI, sidebar, landing, auth).
- **UMKM Business OS** — nama konsep/produk dalam PRD.
- Konsep produk era sebelumnya **sudah dihapus** — jangan dipakai lagi. Produk selalu disebut **TokoKu / UMKM Business OS**.

## Stack

- Laravel 12 (PHP 8.2 lokal XAMPP; produksi PHP 8.4 FrankenPHP di Vercel).
- Vue 3 + Vite SPA di-serve FROM Laravel (same-origin, bukan split API).
- Auth: **Sanctum session (cookie), same-origin SPA**. `EnsureFrontendRequestsAreStateful`
  ke-middleware `api` (`bootstrap/app.php`) — stateful hanya jika `Referer`/`Origin`
  cocok dengan `sanctum.stateful`.
- DB: SQLite lokal; produksi TiDB serverless (MySQL-compatible). SEE `docs/tidb-setup.md`.
- Tema: Tailwind v4, token semantik `primary-*` (blue/indigo) di `resources/css/app.css`
  — SSOT warna. UI pasca-login pakai sidebar layout (lihat `resources/js/components/Sidebar.vue`).

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
  `{"error": {"code", "message", "details"}}`. Handler error sudah
  disetup di `bootstrap/app.php` untuk ValidationException (422) & AuthenticationException (401).
- Role: `OWNER` (default saat register), `MANAGER`, `STAFF`. Middleware
  alias `role` sudah ada (`EnsureUserRole`); operasional role > OWNER belum dibuat.
- AuthController: register/login/logout/user (auth:sanctum + session).
- Frontend di `resources/js`, tailwind v4, pinia store di `resources/js/stores`.
- Struktur navigasi sidebar mengikuti Information Architecture PRD
  (`Sidebar.vue`): **DASHBOARD · BISNIS (Penjualan, Produk) · INVENTORI (Stok,
  Pembelian) · PELANGGAN (Pelanggan, Leads) · KEUANGAN & ANALITIK (Keuangan,
  Laporan) · PENGATURAN**. Halaman modul yang belum dibangun memakai
  `PlaceholderPage.vue` (route meta/props di `resources/js/router/index.js`).
- **Offline-first wajib di MVP**: transaksi harus bisa dicatat offline, masuk
  sync queue, retry, dan ada indikator status (single-device; conflict
  resolution multi-device tahap lanjutan).
- `php artisan test` mewajibkan header `Origin`/`Referer` saat memanggil `/api/*`
  yang butuh session (lihat `statefulHeaders()` di AuthTest).
- Jangan commit secrets; `.env` jangan di-commit (sudah di .gitignore).

## Status

F0 selesai: scaffold + auth session + SPA login/register/dashboard + pipeline
deploy Vercel + test hijau. Vercel project `lomba-garda` live di
`https://lomba-garda-xi.vercel.app` (TiDB serverless DB `tokoku`; migration
"Ran"; lihat `docs/tidb-setup.md`). **Rebrand & layout baru selesai**: konsep →
TokoKu / UMKM Business OS, tema blue/indigo, sidebar layout responsif, dashboard
skeleton dengan KPI cards (Penjualan, Order, Produk, Pelanggan + status stok +
insight).
Blok B backend ERP/CRM selesai: 12 tabel (Business, Supplier, Product, Customer,
Lead, Sale, SaleItem, StockMovement, Purchase, PurchaseItem, Expense) + model +
API (produk, penjualan single-entry w/ stock & movement, stok/adjust, pembelian
+ receive, pengeluaran, leads pipeline, segmentasi customer, insight dashboard)
+ seeder realistis "Kopi Tertial" (demo@tokoku.app). Semua migration termigrasi
ke TiDB `tokoku` (batch 2). 21 test hijau. **UI Blok B selesai**: onboarding
pilih tipe usaha + starter kategori, dashboard live dari /api/dashboard, halaman
Produk (CRUD+nonaktifkan), Penjualan (riwayat+pagination+POS /sales/new),
Stok (filter+adjust+riwayat movement), Pembelian (PO+receive), Pelanggan
(list+detail segments+riwayat), Leads (pipeline+convert), Keuangan (expenses),
Pengaturan bisnis — semua ter-deploy.
Belum: modul CRM pipeline UI (advanced multi-step), keuangan & laporan lanjutan,
insight engine + notification center, offline sync, role > OWNER.

## Keputusan (diambil bersama owner)

- **Offline-first wajib di MVP**: transaksi bisa dicatat offline → sync queue →
  retry → indikator status (single-device untuk prototype).
- **Seeder realistis** (tema kafe/kopi) diperlukan agar demo kompetisi hidup:
  dashboard KPI, low-stock, auto restock, dan CRM langsung terlihat isinya.
- Sumber kebenaran terbaru = **`alur.docx`** (baca file ini saat merubah
  perilaku produk; jangan mengandalkan PRD lama).

## Catatan penting

- Container Vercel **stateless**: session/cache Wajib `database`, queue `sync`,
  log `stderr` (lihat Dockerfile.vercel env default). Migrate & seed dijalankan
  manual terhadap TiDB, jangan di build.
- `SANCTUM_STATEFUL_DOMAINS` lokal pakai wildcard port; produksi set domain
  Vercel (mis. `*.vercel.app`) di env project Vercel.