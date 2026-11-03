<script setup>
import PageHeader from '../components/PageHeader.vue';
import { RouterLink } from 'vue-router';

const features = [
    { path: '/features/penjualan', title: 'Penjualan', desc: 'Catat transaksi singkat, repeat order, multi-metode pembayaran, invoice otomatis.' },
    { path: '/features/inventori', title: 'Inventori', desc: 'Stok real-time, adjustment/opname, low-stock alert, riwayat pergerakan stok.' },
    { path: '/features/pelanggan', title: 'Pelanggan', desc: 'Profil pelanggan, riwayat belanja, segmentasi otomatis (VIP/Loyal/Baru/Melemah/Tidak Aktif).' },
    { path: '/features/insight', title: 'Insight & Laporan', desc: 'Auto restock, prediksi permintaan, deteksi stok lambat, analisis margin, laporan keuangan.' },
];
</script>

<template>
    <div class="space-y-6">
        <PageHeader title="Fitur Produk" description="Semua yang dibutuhkan UMKM untuk mengelola bisnis dalam satu platform." />

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div v-for="f in features" :key="f.path" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm hover:shadow-md transition">
                <RouterLink :to="f.path" class="block">
                    <h3 class="font-semibold text-gray-900">{{ f.title }}</h3>
                    <p class="mt-2 text-sm text-gray-500">{{ f.desc }}</p>
                </RouterLink>
            </div>
        </div>
    </div>
</template>