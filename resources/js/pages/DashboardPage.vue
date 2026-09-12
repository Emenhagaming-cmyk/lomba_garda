<script setup>
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
</script>

<template>
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Dashboard</h2>
            <p class="text-gray-500 mt-1">
                Selamat datang kembali, {{ auth.user?.name }}.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">Stok Kritis</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">—</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">Rekomendasi Beli</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">—</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">Nilai Inventori</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">Rp —</p>
            </div>
        </div>

        <div class="rounded-2xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-400">
            Modul produk, ingredien, resep, penjualan, dan inventori akan hadir di fase berikutnya.
        </div>
    </div>
</template>
