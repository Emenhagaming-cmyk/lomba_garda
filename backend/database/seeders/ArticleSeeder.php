<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $articles = [
            [
                'title' => '5 Tanda Stok Usahamu Mulai Tidak Sehat',
                'category' => 'Operasional',
                'author' => 'Nadia Kusuma',
                'author_role' => 'Pemilik Gayo Roastery',
                'read_minutes' => 4,
                'days_ago' => 1,
                'excerpt' => 'Stok menumpuk bukan selalu tanda laris. Kenali lima sinyal yang perlu kamu cek sebelum modal mengendap terlalu lama.',
                'body' => <<<'MD'
Stok adalah uang yang disimpan dalam bentuk barang. Kalau salah kelola, modalmu berhenti berputar tanpa terasa.

## 1. Barang cepat mati lebih dari 10%
Produk dengan penjualan **nol selama 30 hari** menandakan permintaan yang tidak cocok dengan rak display. Pindahkan ke area depan atau bundling dengan produk terlaris.

## 2. Selalu kehabisan item yang sama
Sering kehabisan justru tanda *cash flow* tersandera di stok yang salah. Catat tiga produk paling sering kosong, lalu naikkan batas minimum stoknya.

## 3. Rasio stok terhadap penjualan di atas 3 bulan
Kalau butuh lebih dari tiga bulan untuk menjual habis seluruh stok, kamu menyimpan terlalu banyak.

- Hitung rata-rata penjualan per produk per bulan
- Bagi jumlah stok saat ini dengan angka itu
- Targetkan di bawah 2 bulan untuk produk cepat saji

## 4. Tidak ada catatan pergerakan stok
Tanpa catatan masuk-keluar, selisih stok hanya muncul saat rugi. Mulai dari pencatatan sederhana: setiap penjualan mengurangi stok, setiap pembelian menambah stok.

## 5. Diskon terus-menerus untuk menghabiskan stok
Diskon bukan strategi, itu gejala. Perbaiki jumlah pembelian dan pilih pemasok dengan *lead time* lebih pendek.

> Stok yang sehat bukan yang paling banyak, tapi yang paling cepat berputar.
MD,
            ],
            [
                'title' => 'Cara Menentukan Harga Jual Tanpa Menebak',
                'category' => 'Keuangan',
                'author' => 'Rangga Pratama',
                'author_role' => 'Konsultan Keuangan UMKM',
                'read_minutes' => 5,
                'days_ago' => 3,
                'excerpt' => 'Harga jual yang benar dimulai dari HPP yang benar. Ini rumus sederhana yang bisa langsung kamu pakai hari ini.',
                'body' => <<<'MD'
Banyak UMKM menetapkan harga dengan cara "lihat harga tetangga". Cara itu cepat, tapi tidak menjelaskan apakah usahamu untung.

## Mulai dari HPP
HPP (Harga Pokok Penjualan) mencakup:

- Harga bahan baku
- Kemasan
- Tenaga kerja langsung
- Biaya operasional per unit (listrik, gas, sewa dibagi volume)

## Tetapkan margin
Setelah HPP diketahui, tentukan margin kotor. Untuk makanan dan minuman, **margin 40–60%** adalah rentang yang umum.

Contoh: HPP Rp 8.000, margin 50% → harga jual **Rp 16.000**.

## Uji dengan penjualan nyata
Kalau dalam dua minggu penjualan turun drastis setelah harga naik, cek apakah masalahnya harga atau cara komunikasi nilai produk.

## Jangan lupa biaya tersembunyi
Biaya platform, ongkos kirim gratis, dan retur bisa memakan 5–10% margin. Masukkan angka itu ke dalam perhitungan sebelum menetapkan harga akhir.

> Harga yang baik membuat pelanggan merasa wajar dan pemilik usaha tetap untung.
MD,
            ],
            [
                'title' => 'Mengubah Pelanggan Sekali Beli Jadi Pelanggan Setia',
                'category' => 'Pelanggan',
                'author' => 'Sinta Maharani',
                'author_role' => 'Pemilik Bakery Harapan',
                'read_minutes' => 4,
                'days_ago' => 6,
                'excerpt' => 'Biaya mencari pelanggan baru jauh lebih mahal daripada mempertahankan yang lama. Mulai dari data yang sederhana.',
                'body' => <<<'MD'
Pelanggan setia tidak lahir dari diskon besar, tapi dari pengalaman yang konsisten.

## Catat tiga hal saja
Nama, nomor telepon, dan produk favorit. Tiga data ini cukup untuk membuat pelanggan merasa diingat.

## Kenali pola kunjungan
Pelanggan yang membeli setiap Jumat sore bisa kamu ingatkan lewat pesan singkat pada Kamis malam. Tanpa spamming, cukup satu pengingat yang relevan.

## Beri alasan untuk kembali
- Stempel digital: beli 5 gratis 1
- Akses awal untuk produk baru
- Ucapan terima kasih personal setelah pembelian besar

## Segmentasi sederhana
Kelompokkan pelanggan menjadi **baru**, **loyal**, dan **berisiko hilang** (tidak membeli lebih dari 60 hari). Perlakuan setiap kelompok harus berbeda.

> Pelanggan yang merasa dihargai akan bercerita. Itu iklan paling murah yang bisa kamu dapatkan.
MD,
            ],
            [
                'title' => 'Membaca Laporan Penjualan dalam 5 Menit',
                'category' => 'Analitik',
                'author' => 'Adi Nugroho',
                'author_role' => 'Analis Data TokoKu',
                'read_minutes' => 3,
                'days_ago' => 9,
                'excerpt' => 'Tidak perlu jadi ahli statistik. Empat angka ini sudah cukup untuk tahu arah usahamu minggu ini.',
                'body' => <<<'MD'
Laporan yang rumit sering berakhir tidak dibaca. Cukup pantau empat angka.

## 1. Omzet
Total penjualan periode ini. Bandingkan dengan periode sebelumnya, bukan dengan target yang tidak realistis.

## 2. Laba kotor
Omzet dikurangi HPP. Kalau omzet naik tapi laba kotor turun, kamu menjual lebih banyak barang dengan margin lebih tipis.

## 3. Rata-rata nilai transaksi
Naikkan angka ini dengan bundling atau upsell. Kenaikan 10% di sini berdampak langsung ke laba.

## 4. Produk penyumbang omzet
Fokus pada 20% produk yang memberi 80% omzet. Pastikan stoknya tidak pernah kosong.

> Konsistensi membaca laporan lebih penting daripada kecanggihan alat.
MD,
            ],
            [
                'title' => 'Kapan Waktu Tepat Menambah Karyawan?',
                'category' => 'Operasional',
                'author' => 'Maya Lestari',
                'author_role' => 'Pemilik Sumber Rejeki Sembako',
                'read_minutes' => 4,
                'days_ago' => 14,
                'excerpt' => 'Menambah karyawan terlalu cepat membebani kas. Terlalu lambat membuat layanan menurun. Ini indikatornya.',
                'body' => <<<'MD'
Pertumbuhan yang sehat terlihat dari antrean pelanggan, bukan dari jumlah kursi kosong.

## Tanda kamu butuh tambahan tenaga
- Waktu tunggu pelanggan meningkat dua minggu berturut-turut
- Kamu mulai menolak pesanan karena kapasitas
- Kesalahan pesanan naik karena kelelahan

## Hitung dulu kemampuannya
Sebelum merekrut, pastikan **laba bersih tiga bulan terakhir** cukup menutup gaji baru tanpa mengurangi kas cadangan minimal satu bulan biaya operasional.

## Mulai dari paruh waktu
Pola permintaan UMKM sering tidak rata. Tenaga paruh waktu pada jam sibuk lebih efisien daripada satu karyawan penuh waktu.

> Rekrut untuk masalah yang berulang, bukan untuk satu hari yang ramai.
MD,
            ],
            [
                'title' => 'Bangun Kehadiran Online Tanpa Budget Iklan',
                'category' => 'Pemasaran',
                'author' => 'Dimas Saputra',
                'author_role' => 'Pemilik Snack Nusantara',
                'read_minutes' => 5,
                'days_ago' => 21,
                'excerpt' => 'Konsistensi konten sederhana mengalahkan iklan mahal yang tidak terukur. Ini urutan yang paling masuk akal.',
                'body' => <<<'MD'
Kehadiran online bukan soal sebanyak apa unggahan, tapi seberapa mudah orang menemukanmu.

## Rapikan informasi dasar
Nama usaha, alamat, jam buka, dan nomor WhatsApp harus sama di semua tempat. Ketidakcocokan data membuat calon pembeli ragu.

## Satu foto produk yang benar
Latar polos, cahaya alami, dan sudut yang konsisten. Foto yang rapi meningkatkan kepercayaan lebih besar daripada caption panjang.

## Konten yang bisa diulang
- Proses pembuatan (30 detik)
- Cerita pelanggan
- Jawaban pertanyaan yang sering muncul

## Ukur yang sederhana
Catat berapa chat masuk per minggu. Kalau naik setelah unggahan tertentu, ulangi pola itu.

> Mulai dari yang bisa kamu kerjakan setiap minggu, bukan yang terlihat sempurna sekali saja.
MD,
            ],
        ];

        foreach ($articles as $data) {
            $daysAgo = $data['days_ago'];
            unset($data['days_ago']);

            Article::updateOrCreate(
                ['title' => $data['title']],
                $data + [
                    'slug' => null,
                    'published_at' => Carbon::now()->subDays($daysAgo),
                    'is_published' => true,
                ],
            );
        }
    }
}
