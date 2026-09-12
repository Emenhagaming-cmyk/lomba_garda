<script setup>
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
</script>

<template>
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-white">Dashboard</h2>
            <p class="text-zinc-400 mt-1">
                Selamat datang kembali, {{ auth.user?.name }}.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-5">
                <p class="text-sm text-zinc-400">Stok Kritis</p>
                <p class="mt-2 text-3xl font-bold text-white">—</p>
            </div>
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-5">
                <p class="text-sm text-zinc-400">Rekomendasi Beli</p>
                <p class="mt-2 text-3xl font-bold text-white">—</p>
            </div>
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-5">
                <p class="text-sm text-zinc-400">Nilai Inventori</p>
                <p class="mt-2 text-3xl font-bold text-white">Rp —</p>
            </div>
        </div>

        <div class="rounded-2xl border border-dashed border-zinc-800 p-8 text-center text-sm text-zinc-500">
            Modul produk, ingredien, resep, penjualan, dan inventori akan hadir di fase berikutnya.
        </div>
    </div>
</template>