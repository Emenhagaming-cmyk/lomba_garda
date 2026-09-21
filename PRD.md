# TokoKu — UMKM Business OS

Ringkasan produk & roadmap. Sumber kebenaran lengkap: **`PRD_UMKM_Business_OS.docx`**.

## Konsep

**Data → Analysis → Recommendation → Action**

UMKM Business OS adalah platform manajemen bisnis untuk berbagai jenis UMKM
(retail, F&B, fashion, reseller, jasa, produksi). Menggabungkan:

- **ERP** — fondasi operasional (produk, penjualan, inventori, pembelian, keuangan).
- **CRM** — hubungan pelanggan (database, leads, pipeline, follow-up).
- **Dashboard** — pusat monitoring kondisi bisnis.
- **Business Intelligence** — mengubah data menjadi insight & rekomendasi tindakan.

Nama tampilan: **TokoKu**. Seluruh konsep produk era sebelumnya sudah dihapus — rujukan satu-satunya: **TokoKu / UMKM Business OS**.

## Masalah yang Dipecahkan

1. **Pencatatan berulang** — admin mengetik ulang data saat order baru masuk;
   data jadi tidak sinkron dan dashboard tidak mencerminkan kondisi terkini.
2. **Data tersebar** — penjualan di catatan/POS/chat/marketplace, stok di tempat
   lain, pelanggan tak terhubung riwayat beli, keuangan dihitung terpisah.
3. **Data tidak menjadi tindakan** — angka tampil, tapi tidak menjawab apa yang
   harus dilakukan hari ini (restock? follow-up? review produk?).

## Solusi Inti

- **Single-entry transaction**: satu input → stok, revenue, customer history, laporan ter-update otomatis.
- **Quick Action "New Sale"**: input singkat (produk → qty → customer → payment), plus shortcut "Repeat Last Order".
- **Offline-first**: transaksi tetap bisa dicatat saat offline, antrean sinkronisasi, status jelas ("saved locally", "waiting to sync", "synced just now").
- **Insight Engine**: tiap insight punya tindakan yang disarankan (auto restock, demand forecast, dead stock detection, HPP & margin analysis, product performance, customer insight, channel analysis).

## Information Architecture (Sidebar)

```
Dashboard
  Overview · Insights · Notifications

Bisnis
  Penjualan · Produk · Stok · Pembelian · Keuangan

Pelanggan
  Pelanggan · Leads · Pipeline · Follow-ups

Analitik
  Laporan · Product Performance · Customer Analytics

Pengaturan
  Profil Bisnis · Pengguna & Peran · Integrasi · Offline & Sync
```

## MVP Scope (kompetisi)

1. Main Dashboard
2. New Sale / Quick Action
3. Produk
4. Inventori
5. Pelanggan + riwayat transaksi
6. Pembelian/Supplier sederhana
7. Keuangan dasar
8. Laporan
9. 3–5 intelligent insights
10. Offline transaction + sync
11. Notification center

## Design Direction

Clean, calm, professional. Background terang/off-white, teks gelap, **satu warna
utama muted (blue/indigo)** — warna lain hanya untuk status. Card & border
seperlunya, dashboard memprioritaskan action. Referensi visual: gambar CRM
Dashboard (sidebar, KPI, lead source, latest leads) & Business/Sales Dashboard
(KPI cards, product statistics, customer habits) di dokumen PRD.

## Roadmap

| Phase | Isi |
| --- | --- |
| Phase 0 | ✓ Scaffold, auth session, SPA login/register/dashboard, deploy Vercel |
| Phase 1 | ✓ Rebrand TokoKu + sidebar layout + dashboard skeleton (blue/indigo) |
| Phase 2 | Core ERP: produk, penjualan, inventori, stock movement, pembelian |
| Phase 3 | CRM: pelanggan, leads, pipeline, follow-up |
| Phase 4 | Intelligence: forecast, dead stock, margin, insight cards |
| Phase 5 | Offline-first: local cache, sync queue, status UI |
| Phase 6 | Polish & demo: seed data, empty/loading/error states, e2e flow |