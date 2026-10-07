# TokoKu — UMKM Business OS

Monolit **Laravel 12 + Vue 3 SPA (same-origin, di-serve dari Laravel)** untuk
manajemen operasional UMKM: satu input transaksi → stok, keuangan, pelanggan,
dan dashboard ter-update otomatis (ERP + CRM + BI).

Ringkasan produk, IA, dan roadmap: **`PRD.md`** (baca ini sebelum ngubah
perilaku/fitur). Detail produk dari owner ada di `alur.docx` — **file itu tidak
ada di repo** (tidak pernah di-commit); kalau butuh spec perilaku, tanyakan ke
owner, jangan karang dari ingatan. `PRD_UMKM_Business_OS.docx` = versi lama,
sudah digantikan. Dokumen operasional DB ada di **`docs/tidb-setup.md`**.

## Stack

- PHP 8.2 lokal (XAMPP), produksi FrankenPHP 8.4 di container Vercel. `php -v` lokal.
- Vue 3 + Vite 7 + Pinia + vue-router (`createWebHistory`), Tailwind **v4**.
- Auth: **Sanctum session cookie** (bukan token). DB lokal SQLite, produksi TiDB
  serverless (MySQL).
- Tanpa CI, tanpa ESLint/Prettier, tanpa typecheck. Verifikasi = `pint` + `artisan test`.

## Setup (wajib sebelum dev/serve)

`.env` **tidak** ada di repo (gitignored) tapi WAJIB ada untuk menjalankan app —
`config/app.php` default `APP_ENV=production` + `APP_DEBUG=false` dan
`APP_KEY=null`, jadi tanpa `.env` semua route balas **500 polos tanpa pesan**
(`MissingAppKeyException` di `storage/logs/laravel.log`). Sekali saja:

```powershell
copy .env.example .env
php artisan key:generate
New-Item -ItemType File database\database.sqlite   # tidak di-commit, wajib ada
php artisan migrate --seed
```

Verifikasi cepat: `php artisan serve` → `/up` 200, `/` 200, `/api/dashboard` 401.
Halaman kosong (bukan 500) = aset Vite tidak termuat; `npm run dev` harus jalan.

## Perintah

```powershell
composer dev        # 4 proses: serve + queue:listen + pail + vite (port 5173)
composer setup      # install + .env + key + migrate + npm build (sekali saja)
php artisan serve   # backend saja (default http://localhost:8000)
npm run dev         # Vite saja
npm run build       # WAJIB sebelum deploy / test lewat server

# verifikasi — urutan ini
vendor/bin/pint              # format PHP (preset laravel, tanpa pint.json)
vendor/bin/pint --test       # cek tanpa tulis
php artisan test
php artisan test --filter=test_sale_records_stock_movement_and_updates_kpi
php artisan test tests/Feature/BusinessFlowTest.php
```

Suite = **21 test** (`tests/Feature/AuthTest.php`, `BusinessFlowTest.php`, 2 Example).
Test DB = SQLite `:memory:` + `RefreshDatabase`, jadi seed tidak ikut.

## Arsitektur

```
routes/api.php        semua endpoint JSON (auth:sanctum group) — SATU-SATUNYA API
routes/web.php        / dan /{any} → view('app') (SPA fallback; jangan dihapus,
                      deep-link vue-router bergantung padanya)
resources/views/app.blade.php   shell: #app + @vite(css/app.css, js/app.js)
resources/js/{pages,layouts,components,stores,router,api,utils}
app/Http/Controllers  logika bisnis inline (tanpa FormRequest, tanpa Service layer)
app/Models            Eloquent + business_id scoping
database/migrations/2026_09_21_000001_create_business_erp_tables.php  = 11 tabel ERP/CRM
```

- `dt_env.php` di root itu **script debug sisa** (dump APP_KEY), bukan entrypoint.
- `resources/js/pages/*` = 1 file per halaman; `AppLayout.vue` + `Sidebar.vue`
  membungkus semua route `requiresAuth`.
- Route yang belum dibangun pakai `PlaceholderPage.vue` (props di
  `resources/js/router/index.js`, mis. `/analytics` → Laporan).

## Konvensi API (WAJIB konsisten)

Envelope: sukses `{"data": ...}` (bisa di-key, mis. `data.product`), error
`{"error": {"code", "message", "details"}}`. Handler global ada di
`bootstrap/app.php`. Kode error konstan yang **sudah dipakai frontend/tes**:

| HTTP | code | pemicu |
| --- | --- | --- |
| 401 | `UNAUTHENTICATED` | belum login |
| 403 | `FORBIDDEN` | middleware `role` |
| 409 | `BUSINESS_REQUIRED` | belum onboarding |
| 422 | `VALIDATION_ERROR` | `validate()` / `ValidationException::withMessages` |
| 422 | `STOCK_NEGATIVE` | adjustment stok melebihi stok |

- Validasi inline di controller (`$request->validate([...])`), pesan user-facing
  dalam **Bahasa Indonesia**.
- `role` middleware (`EnsureUserRole`) sudah terdaftar tapi **belum dipakai**
  route mana pun. Role: `OWNER` / `MANAGER` / `STAFF`.
- Listberpaginasi tidak pakai bentuk default Laravel; tangani di controller:
  `paginate(15)` lalu serialize `{items..., pagination: {total, per_page,
  current_page, last_page}}` (lihat `SaleController::index`).

## Konvensi domain

- **Uang = integer rupiah tanpa koma** (`unsignedInteger`), tanggal = `unsignedInteger`
  (`hpp`, `buy_price`, `sell_price`). Format ke "Rp 1.234.567" hanya di frontend
  (`resources/js/utils/format.js`).
- **Multi-tenancy by `business_id`**, tapi praktis 1 user = 1 business. Ambil via
  `$this->businessId($request->user())` (`app/Http/Controllers/Controller.php:13`).
  Jangan diam-diam menambah konsep multi-business.
- Route model binding wajib di-scope:
  `abort_unless($model->business_id === $this->businessId($request->user()), 404);`
- **Single-entry**: `POST /sales` dalam satu `DB::transaction` → buat Sale+SaleItem,
  `decrement('stock')`, catat `StockMovement` (`reference_type`/`reference_id` =
  class model + id). `POST /purchases/{id}/receive` melakukan hal yang sama dengan
  arah `in`. Adjustment lewat `POST /stock/adjust`.
- Nomor dokumen dihitung dari auto-increment, bukan sequence:
  `'INV-'.str_pad((string) (Sale::max('id') + 1), 6, '0', STR_PAD_LEFT)`.
- Enum-ish string: lead stage `new|contacted|qualified|offer|converted`,
  segment `new|loyal|vip|at_risk|inactive`, PO status `ordered|received`,
  payment `cash|qris|transfer|edc|other`, tipe bisnis = key di
  `resources/js/utils/businessTypes.js`.
- Replikasi enum baru **harus dua sisi**: validasi backend + label/warna di
  `resources/js/utils/format.js` (`LEAD_STAGES`, `SEGMENTS`, `PAYMENT_METHODS`).

## Konvensi frontend

- Token warna SSOT = `--color-primary-*` di `resources/css/app.css` (`@theme`).
  Pakai `bg-primary-600`/`text-primary-700`; warna lain hanya untuk status.
  Tidak ada `tailwind.config.js` — konten dideteksi lewat `@source` di `app.css`.
- Reuse komponen yang sudah ada sebelum bikin baru: `PageHeader.vue` (9 pemakaian),
  `Modal.vue`, `StatCard.vue`, `FloatingNav.vue`.
- API hanya lewat `resources/js/api/client.js` (axios, `withCredentials`,
  `baseURL: '/api'`, header `X-Requested-With`). Login/register/logout **wajib**
  `fetchCsrfCookie()` dulu. Error ditampilkan dengan `formatApiError()`.
- Guard di `resources/js/router/index.js`: `meta.public` / `meta.guest` /
  `meta.requiresAuth`; setelah auth, user tanpa business dipaksa ke `/onboarding`.
- Gaya: 4 spasi, single quote, semicolon (lihat `.editorconfig` + file ada).

## Testing quirks

- Panggil `/api/*` yang butuh session **harus** mengirim header
  `Origin: http://localhost` + `Referer: http://localhost/login` (helper
  `statefulHeaders()` di `AuthTest.php`) — tanpa itu `EnsureFrontendRequestsAreStateful`
  tidak mengaktifkan session dan login gagal.
- Kalau memanggil API **manual** (curl/Postman/PowerShell, bukan browser),
  `POST /api/login` tetap balas **419** walau cookie sudah diambil, karena header
  `X-XSRF-TOKEN` (nilai cookie `XSRF-TOKEN` yang di-URL-decode) tidak ikut.
  Di browser axios mengirimnya otomatis; test PHPUnit tidak perlu karena `actingAs()`.
- Después `$this->actingAs($user)` header tetap dipakai di test yang sama.
- Hanya ada factory `UserFactory`; objek bisnis dibuat manual lewat relasi
  (`$user->businesses()->create([...])`, `$business->products()->create([...])`).
- `phpunit.xml` menyetel `APP_KEY` khusus test (`base64:MDEyMzQ1Njc4OWFiY2Rl...`,
  32 byte). Jadi suite **tidak** bergantung pada `.env` lokal — jangan hapus entri
  itu atau test kembali gagal `MissingAppKeyException`. `.env` (kalau ada) tidak
  menimpanya karena Dotenv immutable.
- Refresh DB saat menambah migration; tidak ada migration rollback di produksi
  (lihat bagian Deploy).

## Deploy (Vercel, container)

`vercel.json` → service `app` dari `Dockerfile.vercel` (multi-stage: composer
`--no-dev` → vite build → FrankenPHP + Caddyfile). **Deploy = git push**;
tidak ada Docker lokal.

- Container **stateless** → default env di Dockerfile wajib: `LOG_CHANNEL=stderr`,
  `CACHE_STORE=array`, `SESSION_DRIVER=database`, `QUEUE_CONNECTION=sync`.
  Kalau butuh queue asynchronous, itu PR baru.
- **Build tidak menjalankan `migrate`/`db:seed`.** Jalankan manual dari lokal
  terhadap TiDB dengan env proses + `SESSION_DRIVER=array` `CACHE_STORE=array`
  (panduan lengkap + env produksi: `docs/tidb-setup.md`).
- JANGAN set env Vercel ke string kosong — env kosong menimpa default config dan
  memicu crash `Manager::createDriver()` / 502 di `/`. Var yang tak perlu
  di-set sebaiknya dihapus dari Vercel.
- HTTPS dijamin `URL::forceScheme('https')` di `AppServiceProvider` saat
  production — bukan lewat `APP_URL`.
- `laravel/pail` ada di `extra.laravel.dont-discover` karena bikin build
  `--no-dev` crash. Jangan dihapus tanpa tested.

## Gotcha

- Gejala "500 polos tanpa pesan di browser" hampir selalu = `APP_KEY` kosong
  (`.env` hilang) → cek `storage/logs/laravel.log` dulu, jangan menebak.
- `public/hot` = marker Vite dev server. Kalau file ini ada, `@vite` mengarahkan
  browser ke `localhost:5173` — termasuk di image Docker kalau ikut ter-copy.
  Hapus sebelum `npm run build` lokal yang dipakai untuk uji produksi.
- `axios` sengaja di `devDependencies`, bukan `dependencies` (frontend build
  memang pakai `npm ci`). Jangan ganti ke `npm ci --omit=dev`.
- `DashboardController` pakai `CAST(... AS SIGNED)` (flavor MySQL/TiDB) di
  `orderRaw` — diterima SQLite & TiDB, tapi hati-hati menulis SQL portabel baru.
- `composer.json` masih `name: laravel/laravel` dan `README.md` masih template
  Laravel — bukan dokumentasi repo ini.
- `businessTypes.js` memakai key `fnb` dan `food-drink` (dua key untuk konsep
  sama-ish). Jangan "rapikan" nilainya tanpa cek data demo.

## Status

Selesai: auth session, onboarding, dashboard live (KPI + low/dead stock + insight),
POS & riwayat penjualan, produk, stok + movement, pembelian + receive,
pelanggan + segmentasi, leads pipeline + convert, keuangan (expenses), pengaturan
bisnis. Semua ter-deploy.

Belum: **offline-first / sync queue (WAJIB MVP, belum ada sama sekali)**,
modul Laporan (`/analytics` masih placeholder), notification center, follow-up
harian, role > OWNER, advanced multi-step CRM. Roadmap detail + demo flow:
`PRD.md`. Ide tertunda (belum resmi): `docs/ideas/harga-kelengahan.md`.

Demo lokal: `php artisan db:seed` → `demo@tokoku.app` / `Rahasia123!`
(bisnis "Kopi Tertial", F&B).
