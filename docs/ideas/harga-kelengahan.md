# Ide Diferensiasi: "Harga Kelengahan" (Cost of Inaction)

Draft dari sesi idea-refine — belum dikunci sebagai fitur resmi PRD. Diuji
konsepnya dulu, lalu ditetapkan (atau dimodifikasi) sebelum implementasi.

## Masalah

Rekomendasi pembelian standar bilang "beli 12 unit" atau "stok akan habis 5 hari
lagi". Pemilik UMKM tidak merasa urgensi karena: (a) angkanya abstrak, (b) tidak
ada konsekuensi nyata jika ditunda, (c) tidak ada perkiraan profit.

## Gagasan inti

Ubah output rekomendasi menjadi **rupiah yang hilang per hari menunda pembelian**:

- Jika stok diperkirakan habis dalam 5 hari dan dalam 3 hari "
  tidak akan ada produk untuk dijual", maka setiap hari keterlambatan order =
  estimasi unit terjual terjebak × margin → **"Rp 48.000/hari"**.
- Rekomendasi ditampilkan sebagai peringkat berdasarkan *cost of inaction*,
  bukan sekadar tanggal habis.

## Komponen

1. **Cost of inaction engine** — dari forecast harian + margin produk + harga
   pokok, hitung nilai terbuang per hari delay (dorong sampai restock tiba).
2. **Pola mingguan (weekly pulse)** — dekomposisi pola 7 hari (weekday vs
   weekend); forecast lebih akurat untuk makanan/minuman musiman.
3. **Honest forecast guard** — jika data penjualan < ambang minimal, tampilkan
   label `LOW confidence` jelas (bukan angka palsu yang meyakinkan).

## Kenapa beda dari PRD lain

- Fokus pada **kerugian ripta nyata (rupiah)** daripada metrik stok teknis —
  bahasa pemilik UMKM.
- Anti-polusi AI: semuanya dihitung deterministik dari data lokal, tidak ada
  "jawaban keren" tanpa dasar.
- Kejujuran model (confidence guard) memperkuat kepercayaan vs asisten yang
  overclaim.

## Keputusan terbuka

- Satuan: rupiah/hari vs rupiah per "minggu delay".
- Apakah cost of inaction juga dihitung untuk bahan baku di level ingredien (bukan
  hanya produk jadi)?
- Batas minimum data untuk confidence (mis. 21 hari transaksi).

## Referensi

- PRD §23 (forecast), §24 (rekomendasi pembelian).
- Bagian "Rekomendasi yang Berbicara Uang" dari sesi idea-refine.