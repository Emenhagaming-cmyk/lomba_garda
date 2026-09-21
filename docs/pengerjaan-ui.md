# Catatan Pengerjaan UI — TokoKu (UMKM Business OS)

> **Dokumen historis.** Catatan ini merekam pengerjaan UI pada konsep lama
> (NADI → BelanjaYuk!). Sejak rebrand final, produk bernama **TokoKu** dengan
> tema blue/indigo dan sidebar layout. Pernyataan di bawah (nama, temas, brand)
> hanya berlaku untuk periode yang dicatat, bukan kondisi saat ini.

Dokumen ini mencatat kronologi pengerjaan UI aplikasi (dahulu **BelanjaYuk!**,
sebelumnya **NADI**), UMKM Predictive Operations: landing page, auth form, dan
perubahan-perubahan desain yang sudah dilakukan.

---

## 1. Ringkasan Perubahan

| Tanggal | Perubahan |
| --- | --- |
| 2026-09-12 | Perbaikan floating card hero terpotong |
| 2026-09-12 | Rename **NADI** → **BelanjaYuk!** |
| 2026-09-12 | Tema gelap → **putih minimalis** |
| 2026-09-12 | Halaman login **split-screen** (form + visual panel) |
| 2026-09-12 | Halaman register **split-screen** (form + visual panel) |
| 2026-09-12 | Dokumentasi pengerjaan ini dibuat |

---

## 2. Rename Nama Aplikasi: NADI → BelanjaYuk!

Semua referensi "NADI" di UI diubah menjadi "BelanjaYuk!".

### File yang diubah

| File | Keterangan |
| --- | --- |
| `resources/js/components/FloatingNav.vue` | Logo nav |
| `resources/js/components/Footer.vue` | Logo + hak cipta footer |
| `resources/js/components/landing/AppMockCard.vue` | Header mock dashboard |
| `resources/js/components/LandingHero.vue` | Teks hero |
| `resources/js/pages/LandingPage.vue` | Teks section & CTA |
| `resources/js/layouts/AppLayout.vue` | Navbar area login |
| `resources/js/pages/LoginPage.vue` | Judul halaman login |

### Catatan internal
- `AGENTS.md`, `PRD.md`, `docs/tidb-setup.md` **tidak diubah** — nama internal
  proyek tetap "NADI" untuk referensi teknis.
- Rename di dashboard Vercel (`lomba3` → `UMKM`) belum dilakukan (butuh aksi
  manual di dashboard Vercel).

---

## 3. Pergantian Tema: Gelap → Putih Minimalis

### Sebelum
- Background hitam (zinc-950 / zinc-900)
- Text putih (text-white / text-zinc-300)
- Border gelap (border-zinc-800)
- Shadow hitam (shadow-black/50)
- Accent emerald terang

### Sesudah
- Background **putih** (`bg-white`) / abu sangat muda (`bg-gray-50`)
- Text **gelap** (`text-gray-900` / `text-gray-600` / `text-gray-500`)
- Border abu muda (`border-gray-200`)
- Shadow abu muda (`shadow-gray-200/50`)
- Accent emerald lebih dalam (`text-emerald-600`, `bg-emerald-600`)

### File yang diubah ke tema putih
- `LandingHero.vue`
- `FloatingNav.vue`
- `Footer.vue`
- `LandingPage.vue`
- `LoginPage.vue`
- `RegisterPage.vue`
- `DashboardPage.vue`
- `AppLayout.vue`
- Semua komponen di `resources/js/components/landing/`:
  - `RegistryBadge.vue`
  - `StockCard.vue`
  - `ForecastCard.vue`
  - `PurchaseCard.vue`
  - `ValueCard.vue`
  - `ForecastToast.vue`
  - `AppMockCard.vue`

---

## 4. Perbaikan Hero: Floating Cards Terpotong

### Masalah
Card-card floating di hero (Stok kritis, Forecast 7 hari, Rekomendasi beli,
Nilai inventori) terpotong. Penyebabnya `overflow-hidden` pada `<section class="relative overflow-hidden">`
yang memotong card yang diposisikan negatif (`-left-52`, `-right-44`, dst).

### Solusi
- `overflow-hidden` **dipindahkan** dari `<section>` ke `<div>` khusus background glow.
- Card floating sekarang tampil penuh tanpa terpotong.

```html
<!-- Sebelum -->
<section class="relative overflow-hidden">
    <!-- bg glow + cards -->

<!-- Sesudah -->
<section class="relative">
    <div aria-hidden="true" class="pointer-events-none absolute inset-0 overflow-hidden">
        <!-- bg glow hanya -->
    </div>
    <!-- cards bebas keluar section -->
```

---

## 5. Section "Kenapa memilih BelanjaYuk!?"

### Evolusi section (setelah fitur cards)
1. **Fitur cards (3 kolom)** → diganti jadi
2. **Pricing 3 tier (Starter/Pro/Business)** → atas permintaan user, **dihapus harga**
   (jangan cantumkan nominal paket) → diganti jadi
3. **Satu kartu besar tengah "Kenapa memilih BelanjaYuk!?"** (final)

### Isi kartu "Kenapa memilih BelanjaYuk!?"
- Judul: "Kenapa memilih BelanjaYuk!?"
- Subjudul: "Dirancang khusus untuk UMKM Indonesia yang ingin kelola stok tanpa ribet."
- 5 poin fitur dengan ikon centang:
  1. **Gratis selamanya** — tanpa kartu kredit, tanpa batas waktu
  2. **Prediksi otomatis** — forecast permintaan 7 hari ke depan
  3. **Rekomendasi beli** — daftar belanja siap pakai + perkiraan nominal
  4. **Stok kritis real-time** — peringatan saat bahan baku menipis
  5. **Mudah digunakan** — tanpa pelatihan, langsung bisa pakai
- CTA: "Coba Sekarang, Gratis" → `/register`

### Navigasi
- Link nav "Harga" → **"Kenapa kami"** (`#harga` tetap dipakai sebagai anchor id).

---

## 6. Halaman Auth Split-Screen (Login)

### Deskripsi
Form login diubah dari layout tengah satu kolom menjadi **split-screen**:
kiri form, kanan panel visual bergaya branded.

### Kiri — Form
- Tombol "Kembali" ke `/`
- Judul "BelanjaYuk!" + tagline
- Field **Email**, **Password**
- Checkbox "Ingat saya" + link "Daftar"
- CTA primer "Masuk" (emerald)
- Link bawah "Daftar gratis" → `/register`
- Tangani error API (envelope `{"error": {...}}`)

### Kanan — Visual Panel
- Background gradient **emerald → teal** (`bg-gradient-to-br from-emerald-500 via-emerald-600 to-teal-700`)
- Bola-bola glow dekoratif (`bg-white/10`, `bg-teal-300/20`, `blur-3xl`)
- Mock dashboard (`AppMockCard.vue`) dalam posisi miring (`rotate-1`) dengan
  shadow besar
- Heading + subheading branding produk
- Responsif: panel kanan **sembunyi** di bawah `lg` (mobile hanya form)

### File yang diubah
| File | Keterangan |
| --- | --- |
| `resources/js/pages/LoginPage.vue` | Layout split-screen, import `AppMockCard` |

---

## 7. Halaman Auth Split-Screen (Register)

### Deskripsi
Form register diubah dari layout tengah satu kolom menjadi **split-screen**:
kiri form, kanan panel visual — desain seragam dengan halaman login.

### Kiri — Form
- Tombol "Kembali" ke `/`
- Judul "BelanjaYuk!" + tagline "Buat akun baru dan mulai kelola stok"
- Field **Nama**, **Email**, **Password**, **Konfirmasi Password**
- CTA primer "Daftar" (emerald)
- Link bawah "Sudah punya akun? Masuk" → `/login`
- Tangani error API (envelope `{"error": {...}}`)

### Kanan — Visual Panel
- Background gradient emerald → teal (sama dengan login)
- Bola-bola glow dekoratif (sama dengan login)
- Mock dashboard (`AppMockCard.vue`) dalam posisi miring (`-rotate-1`) —
  rotasi berlawanan arah dengan login untuk variasi
- Heading: "Gratis selamanya, tanpa kartu kredit."
- Subheading: "Daftar sekarang, catat stok pertamamu dalam hitungan menit."
- Responsif: panel kanan sembunyi di bawah `lg`

### Perbedaan dengan login
| Aspek | Login | Register |
| --- | --- | --- |
| Rotasi mock card | `rotate-1` | `-rotate-1` |
| Heading kanan | "Kelola stok, prediksi permintaan..." | "Gratis selamanya, tanpa kartu kredit." |
| Subheading kanan | "Forecast 7 hari..." | "Daftar sekarang, catat stok pertamamu..." |
| Fields | 2 (email, password) | 4 (nama, email, password, konfirmasi) |

### File yang diubah
| File | Keterangan |
| --- | --- |
| `resources/js/pages/RegisterPage.vue` | Layout split-screen, import `AppMockCard` |

---

## 8. Verifikasi & Status

- Build SPA: `npm run build` — **hijau** (96 modules).
- Test: `php artisan test` — **9/9 PASS**.
- Format: `vendor/bin/pint` — **bersih**.
- Teks welcome area pasca-login belum diubah (belum wajib; bisa jadi iterasi berikutnya).

---

## 9. Iterasi Berikutnya (opsional)

- Rename Vercel project `lomba3` → `UMKM` (dashboard Vercel).
- Rotasi TiDB password (kredensial pernah ter-posting di chat).