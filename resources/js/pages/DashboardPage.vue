<script setup>
import { useAuthStore } from '../stores/auth';
import StatCard from '../components/StatCard.vue';

const auth = useAuthStore();

const firstName = () => (auth.user?.name || '').split(' ')[0];

const insights = [
    { severity: 'info', title: 'Data transaksi belum tersedia', body: 'Catat penjualan pertama agar dashboard mulai menampilkan analitik.' },
];
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900 sm:text-2xl">
                    Selamat datang kembali, {{ firstName() || 'sahabat UMKM' }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">Ini ringkasan kondisi bisnismu hari ini.</p>
            </div>
            <RouterLink
                to="/sales/new"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
            >
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Transaksi Baru
            </RouterLink>
        </div>

        <!-- KPI Cards -->
        <section aria-label="Metrik utama" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <StatCard label="Penjualan Hari Ini" value="Rp —" trend="0%" trend-direction="up" hint="vs kemarin" icon="wallet" accent />
            <StatCard label="Total Order" value="—" trend="0%" trend-direction="up" hint="vs bulan lalu" icon="cart" />
            <StatCard label="Produk Terjual" value="—" trend="0%" hint="vs bulan lalu" icon="box" />
            <StatCard label="Pelanggan Baru" value="—" hint="bulan ini" icon="users" />
        </section>

        <!-- Main grid: status + insights -->
        <section aria-label="Konten dashboard" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Left: inventory health -->
            <div class="space-y-6 lg:col-span-2">
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                        <h3 class="font-semibold text-gray-900">Status Stok</h3>
                        <div class="flex items-center gap-1.5 text-xs text-gray-400">
                            <span class="inline-flex items-center gap-1">
                                <span class="h-2 w-2 rounded-full bg-emerald-500" aria-hidden="true"></span> Sehat
                            </span>
                            <span class="mx-1">·</span>
                            <span class="inline-flex items-center gap-1">
                                <span class="h-2 w-2 rounded-full bg-amber-500" aria-hidden="true"></span> Menipis
                            </span>
                            <span class="mx-1">·</span>
                            <span class="inline-flex items-center gap-1">
                                <span class="h-2 w-2 rounded-full bg-red-500" aria-hidden="true"></span> Kritis
                            </span>
                        </div>
                    </div>

                    <div role="status" class="px-5 py-14 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-50" aria-hidden="true">
                            <svg viewBox="0 0 24 24" class="h-6 w-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M20 7h-9"></path>
                                <path d="M14 17H5"></path>
                                <circle cx="17" cy="17" r="3"></circle>
                                <circle cx="7" cy="7" r="3"></circle>
                            </svg>
                        </div>
                        <p class="mt-4 text-sm font-medium text-gray-700">Belum ada data stok</p>
                        <p class="mt-1 text-sm text-gray-400">
                            Tambahkan produk dan kelola inventaris untuk memantau kondisi stok secara real-time.
                        </p>
                        <RouterLink
                            to="/products/new"
                            class="mt-5 inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                        >
                            Tambah Produk
                        </RouterLink>
                    </div>
                </div>

                <!-- Sales trend -->
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                        <h3 class="font-semibold text-gray-900">Tren Penjualan</h3>
                        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-500">7 hari terakhir</span>
                    </div>
                    <div role="status" class="px-5 py-14 text-center">
                        <p class="text-sm font-medium text-gray-700">Grafik penjualan muncul setelah ada transaksi</p>
                        <p class="mt-1 text-sm text-gray-400">Semua transaksi otomatis memperbarui stok, keuangan, dan analitik.</p>
                    </div>
                </div>
            </div>

            <!-- Right: insight center -->
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <h3 class="font-semibold text-gray-900">Insight</h3>
                    <RouterLink to="/analytics" class="text-xs font-semibold text-primary-600 hover:text-primary-700">
                        Lihat analitik
                    </RouterLink>
                </div>
                <ul class="divide-y divide-gray-100">
                    <li v-for="insight in insights" :key="insight.title" class="flex gap-3 px-5 py-4">
                        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary-50" aria-hidden="true">
                            <svg viewBox="0 0 24 24" class="h-4 w-4 text-primary-600" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12.01" y2="8"></line>
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ insight.title }}</p>
                            <p class="mt-0.5 text-sm text-gray-500">{{ insight.body }}</p>
                        </div>
                    </li>
                </ul>
            </div>
        </section>
    </div>
</template>