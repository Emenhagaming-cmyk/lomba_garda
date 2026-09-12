# Setup TiDB Serverless (MySQL-compatible) — NADI

Produksi memakai TiDB Cloud **serverless** sebagai database eksternal karena
container Vercel bersifat stateless. Gratis tier cukup untuk F0.

## Status

**TERKONFIGURASI + TERDEPLOY (2026-09-12).**
- Cluster dibuat di `ap-southeast-1`; database `nadi` dibuat.
- Semua migration telah dijalankan terhadap TiDB (`php artisan migrate:status` → 5 migration "Ran").
- TLS wajib: diisi via `MYSQL_ATTR_SSL_CA` (Laravel 12 default config mysql sudah membaca env ini).
- App produksi live di `https://lomba3.vercel.app`; alur auth (register/login/`/api/user`) terverifikasi langsung terhadap TiDB.
- Deployment Protection (Vercel Authentication) dimatikan via `PATCH /v9/projects` `{"ssoProtection": null}` agar `*.vercel.app` tidak kena login-wall.

## Env produksi (set di Vercel project → Settings → Environment Variables)

```
DB_CONNECTION=mysql
DB_HOST=<host>.tidbcloud.com
DB_PORT=4000
DB_DATABASE=nadi
DB_USERNAME=<user>.root
DB_PASSWORD=<password>
MYSQL_ATTR_SSL_CA=/etc/ssl/certs/ca-certificates.crt
APP_KEY=<php artisan key:generate --show>
APP_URL=https://<domain>.vercel.app
SANCTUM_STATEFUL_DOMAINS=<domain>.vercel.app,*.vercel.app,localhost,localhost:*,127.0.0.1,127.0.0.1:*
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync
LOG_CHANNEL=stderr
APP_ENV=production
APP_DEBUG=false
```

Catatan:
- `MYSQL_ATTR_SSL_CA` menunjuk ke CA bundle OS. Lokal: `C:\xampp\apache\bin\curl-ca-bundle.crt`.
- `DB_SSLMODE` TIDAK dipakai — connector mysql Laravel 12 mengabaikannya; TLS dikendalikan `MYSQL_ATTR_SSL_CA`.
- `*.vercel.app` membuat preview deployment ikut stateful (login cookie berfungsi di URL preview).
- JANGAN letakkan password di file repo; kelola hanya via env Vercel / secret manager.

## Migrate + seed (sekali saja, manual)

Container build/start TIDAK menjalankan migrate (stateless; DB belum tentu
terhubung saat build dan APP_KEY generik). Jalankan dari lokal terhadap TiDB:

```powershell
$env:DB_CONNECTION='mysql'
$env:DB_HOST='<host>.tidbcloud.com'
$env:DB_PORT='4000'
$env:DB_DATABASE='nadi'
$env:DB_USERNAME='<user>.root'
$env:DB_PASSWORD='<password>'
$env:MYSQL_ATTR_SSL_CA='C:\xampp\apache\bin\curl-ca-bundle.crt'
$env:SESSION_DRIVER='array'
$env:CACHE_STORE='array'
php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction
```

Variabel env dari proses menimpa `.env` (Dotenv immutable). `SESSION_DRIVER`/
`CACHE_STORE` di-set array agar tidak menyentuh tabel sebelum ada.

Versi produksi DB `schema_migrations` harus sinkron dengan kode yang di-deploy;
lakukan saat rilis fitur yang menambah migration.

## Verifikasi

`php artisan migrate:status` (dengan env di atas) menampilkan semua "Ran".
Healthcheck `/up` juga akan 200 setelah deploy.

## Pitfall

- TiDB serverless idle ~1s pada koneksi pertama — normal, bukan bug.
- Driver `pdo_mysql` sudah ada di `Dockerfile.vercel`.
- Lokal dev TETAP SQLite; env TiDB hanya dipakai saat migrate/deploy.