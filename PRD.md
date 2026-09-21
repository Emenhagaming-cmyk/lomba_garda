# TokoKu — UMKM Business OS

Ringkasan produk & roadmap. Sumber kebenaran lengkap: **`alur.docx`** (PRD detail,
file terbaru dari owner) dan `PRD_TokoKu_UMKM_Business_OS_Lengkap.docx`. Dokumen
`PRD_UMKM_Business_OS.docx` lama sudah tergantikan oleh `alur.docx`.

## Konsep

**Input satu kali → data terhubung otomatis → dashboard berubah → intelligence
menemukan kondisi → sistem memberi rekomendasi → user melakukan action.**

UMKM Business OS adalah platform manajemen bisnis untuk berbagai jenis UMKM
(retail, F&B, fashion, reseller, jasa, produksi). Menggabungkan:

- **ERP** — fondasi operasional (produk, penjualan, inventori, pembelian, keuangan).
- **CRM** — hubungan pelanggan (database, leads, pipeline, follow-up).
- **Dashboard** — pusat monitoring & action center.
- **Business Intelligence** — mengubah data menjadi insight & rekomendasi tindakan.

Nama tampilan: **TokoKu**. Seluruh konsep produk era sebelumnya sudah dihapus —
rujukan satu-satunya: **TokoKu / UMKM Business OS**.

## Masalah yang Dipecahkan

1. **Pencatatan berulang** — satu transaksi tidak menjadi sumber data bagi modul
   lain: stok, laporan penjualan, riwayat customer, dan keuangan dihitung/dicatat
   ulang secara terpisah → risiko lupa, salah input, data tidak sinkron.
2. **Data bisnis terfragmentasi** — penjualan, stok, pembelian, pelanggan, keuangan
   di catatan/aplikasi berbeda; pemilik sulit melihat kondisi bisnis secara cepat.
3. **Data tidak menjadi tindakan** — angka tampil, tapi tidak menjawab apa yang
   harus dilakukan hari ini (restock? follow-up? review produk?).

## Solusi Inti

- **Single-entry transaction**: satu input → stok, revenue, customer history,
  finance, laporan, dashboard, dan insight ter-update otomatis, tanpa input ulang.
- **Quick Action "New Sale"**: input singkat (customer → produk → qty → discount →
  payment), plus shortcut "Repeat Order".
- **Offline-first** (wajib di MVP): transaksi tetap tercatat saat offline, antrean
  sync, retry, dan indikator status ("saved locally", "waiting to sync", "synced").
  Prototype **single-device**; conflict resolution multi-device tahap lanjutan.
- **Insight Engine**: tiap insight punya data pendukung + recommended action
  (Auto Restock, Demand Forecast, Dead Stock, HPP & Margin, Product Performance,
  Customer Insight, Channel Analysis), dikirim via **Notification Center**
  (Critical/Attention/Success/Insight, semua menunjuk ke halaman/action terkait).

## Information Architecture (Sidebar)

```
DASHBOARD
  Overview · Alerts · Insights · Recent Transactions

BISNIS
  Penjualan · Produk

INVENTORI
  Stok · Pembelian

PELANGGAN
  Pelanggan · Leads

KEUANGAN & ANALITIK
  Keuangan · Laporan

PENGATURAN
```

## Data Model (dari alur.docx §11 Phase 1)

`User, Business, Product, Customer, Lead, Supplier, Sale, SaleItem,
StockMovement, Purchase, Expense`

## Fitur Kunci (dari alur.docx §7)

- **Onboarding / Business Setup**: login → pilih tipe bisnis (Retail, F&B,
  Fashion, Reseller, Jasa, Produksi) → profil bisnis + mata uang + metode bayar →
  produk & stok awal + supplier → dashboard.
- **Produk**: nama, SKU, kategori, varian, harga jual/beli, HPP & margin, min
  stock, supplier, barcode opsional, status aktif/nonaktif.
- **Penjualan**: satu input → stok −, revenue +, customer history +, finance +
  (cash in), dashboard update; repeat order; channel attribution; invoice lanjutan.
- **Stok**: stock in/out, adjustment/opname, low-stock, movement bisa dilacak
  sampai sumber transaksi (+Purchase / −Sale / −Adjustment / −Production).
- **Pembelian**: supplier, Purchase Order dengan status Draft → Ordered →
  Partially Received → Received, supplier price history, lead time, rekomendasi
  qty dari Auto Restock.
- **Pelanggan / CRM**: profil (total spending, order count, AOV, last purchase,
  riwayat beli), **segmentation (VIP / Loyal / New / At Risk / Inactive)**.
- **Leads**: pipeline NEW → CONTACTED → QUALIFIED → OFFER/NEGOTIATION →
  CONVERTED → CUSTOMER; source, notes, next follow-up, conversion rate.
- **Follow-up**: daftar follow-up hari ini, reminder, notes, contact/create
  sale/mark completed → notifikasi dashboard.
- **Keuangan**: Revenue − COGS = Gross Profit − Expenses = Net Profit; cash
  in/out terhubung transaksi; perbandingan periode.
- **Laporan**: Sales, Inventory, Product Performance, Customer, Purchase,
  Finance, Profit — filter periode/produk/customer/channel/kategori.
- **Intelligence**: Auto Restock (stok + avg daily sales + lead time → stockout
  risk → rekomendasi PO), Dead Stock (durasi tidak laku + modal terikat →
  rekomendasi), Product Performance (best seller/highest profit/growing/slow
  moving/dead stock), Customer Insight. Forecast = **estimasi berbasis histori,
  bukan kepastian**.

## MVP Scope (kompetisi)

1. Auth + Business Setup
2. Main Dashboard (KPI + alerts + quick actions)
3. New Sale / single-entry
4. Produk
5. Inventori
6. Pembelian + supplier dasar
7. Pelanggan + riwayat transaksi
8. Leads dasar
9. Keuangan dasar (revenue − COGS − expense)
10. Laporan
11. Notification center
12. 3–5 intelligence insights
13. Offline transaction + sinkronisasi dasar

## Design Direction

Clean, calm, professional. Teks gelap di background terang/off-white, **satu warna
utama muted (blue/indigo)**; warna lain hanya untuk status. SVG icons,
touch-friendly, quick actions terlihat, dashboard memprioritaskan keputusan.
Satu Main Dashboard; ERP/CRM memiliki sub-view, bukan dua homepage yang bersaing.
Referensi visual di dokumen: CRM Dashboard & Business/Sales Dashboard.

## Roadmap

| Phase | Isi | Status |
| --- | --- | --- |
| Phase 0 | Scaffold, auth session, SPA login/register/dashboard, deploy Vercel | ✓ |
| Phase 1 | Rebrand TokoKu + sidebar layout + dashboard skeleton (blue/indigo) | ✓ |
| Phase 2 | Core ERP: data model, produk, penjualan, inventori + stock movement, pembelian | mulai di sini |
| Phase 3 | CRM: pelanggan, segmentasi, leads pipeline, follow-up | |
| Phase 4 | Intelligence: auto restock, dead stock, margin, insight cards + notification center | |
| Phase 5 | Offline-first (wajib MVP): local storage, sync queue, retry, status UI | |
| Phase 6 | Polish & demo: seed data realistis, empty/loading/error states, e2e + narasi demo | |

## Demo Flow (kompetisi, dari alur.docx §12)

Masalah pencatatan manual → Login TokoKu → Main Dashboard → New Sale → tunjukkan
Sales/Stock/Customer/Finance/Dashboard berubah otomatis → **matikan internet →
transaksi kedua "Saved Locally" → nyalakan internet → Sync** → Auto Restock/Dead
Stock → klik rekomendasi → Purchase Order → Customer Detail (CRM) → penutup:
satu input → seluruh sistem bergerak → data menjadi tindakan.