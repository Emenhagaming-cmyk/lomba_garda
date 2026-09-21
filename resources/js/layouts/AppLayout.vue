<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import Sidebar from '../components/Sidebar.vue';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();
const mobileOpen = ref(false);

async function logout() {
    await auth.logout();
    await router.replace({ name: 'landing' });
}

const pageTitles = {
    dashboard: 'Dashboard',
    sales: 'Penjualan',
    products: 'Produk',
    inventory: 'Stok',
    purchase: 'Pembelian',
    customers: 'Pelanggan',
    leads: 'Leads',
    finance: 'Keuangan',
    analytics: 'Laporan',
    settings: 'Pengaturan',
};

const title = () => pageTitles[route.name] || 'Dashboard';

const today = () =>
    new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).format(new Date());
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Desktop sidebar -->
        <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 border-r border-gray-200 lg:block">
            <Sidebar />
        </aside>

        <!-- Mobile drawer -->
        <div v-if="mobileOpen" class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="Menu navigasi">
            <div class="absolute inset-0 bg-gray-900/50" @click="mobileOpen = false"></div>
            <div class="absolute inset-y-0 left-0 w-64 bg-white shadow-xl">
                <button
                    type="button"
                    class="absolute right-3 top-3 rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                    @click="mobileOpen = false"
                    aria-label="Tutup menu"
                >
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6 6 18M6 6l12 12"></path>
                    </svg>
                </button>
                <Sidebar />
            </div>
        </div>

        <div class="lg:pl-64">
            <!-- Top bar -->
            <header class="sticky top-0 z-30 border-b border-gray-200 bg-white/90 backdrop-blur">
                <div class="flex items-center justify-between gap-4 px-4 py-3 sm:px-6">
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 lg:hidden"
                            @click="mobileOpen = true"
                            aria-label="Buka menu"
                        >
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                        <div>
                            <h1 class="text-base font-bold text-gray-900 sm:text-lg">{{ title() }}</h1>
                            <p class="hidden text-xs text-gray-400 sm:block">{{ today() }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-3">
                        <button
                            type="button"
                            class="relative rounded-lg p-2 text-gray-500 transition hover:bg-gray-100"
                            aria-label="Notifikasi"
                        >
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-red-500"></span>
                        </button>

                        <div class="hidden items-center gap-2.5 border-l border-gray-200 pl-3 sm:flex">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-600 text-sm font-semibold text-white"
                                aria-hidden="true"
                            >
                                {{ (auth.user?.name || 'U').charAt(0).toUpperCase() }}
                            </div>
                            <div class="hidden md:block">
                                <p class="text-sm font-medium text-gray-900">{{ auth.user?.name }}</p>
                                <p class="text-[11px] text-gray-400 uppercase">{{ auth.user?.role }}</p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-red-500"
                            @click="logout"
                            aria-label="Keluar"
                        >
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                        </button>
                    </div>
                </div>
            </header>

            <main class="px-4 py-6 sm:px-6 lg:py-8">
                <div class="mx-auto max-w-7xl">
                    <router-view />
                </div>
            </main>
        </div>
    </div>
</template>