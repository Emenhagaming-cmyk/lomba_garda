<script setup>
import { useRoute } from 'vue-router';

const route = useRoute();

const isActive = (path) => route.path === path || (path !== '/dashboard' && route.path.startsWith(path));

const navGroups = [
    {
        label: 'BISNIS',
        items: [
            { name: 'Dashboard', path: '/dashboard', icon: 'dashboard' },
            { name: 'Penjualan', path: '/sales', icon: 'cart' },
            { name: 'Produk', path: '/products', icon: 'box' },
        ],
    },
    {
        label: 'INVENTORI',
        items: [
            { name: 'Stok', path: '/inventory', icon: 'warehouse' },
            { name: 'Pembelian', path: '/purchase', icon: 'truck' },
        ],
    },
    {
        label: 'PELANGGAN',
        items: [
            { name: 'Pelanggan', path: '/customers', icon: 'users' },
            { name: 'Leads', path: '/leads', icon: 'target' },
        ],
    },
    {
        label: 'KEUANGAN & ANALITIK',
        items: [
            { name: 'Keuangan', path: '/finance', icon: 'wallet' },
            { name: 'Laporan', path: '/analytics', icon: 'chart' },
        ],
    },
];
</script>

<template>
    <nav aria-label="Navigasi utama" class="flex h-full flex-col overflow-y-auto bg-white">
        <div class="flex items-center gap-2.5 px-5 py-5 border-b border-gray-100">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-600 text-white" aria-hidden="true">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-6 9 6v11a1 1 0 01-1 1h-5v-7h-6v7H4a1 1 0 01-1-1V9z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-gray-900">TokoKu</p>
                <p class="text-[11px] text-gray-500">UMKM Business OS</p>
            </div>
        </div>

        <div class="flex-1 space-y-5 overflow-y-auto px-3 py-4">
            <div v-for="group in navGroups" :key="group.label">
                <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-gray-400">{{ group.label }}</p>
                <ul class="mt-2 space-y-0.5">
                    <li v-for="item in group.items" :key="item.path">
                        <RouterLink
                            :to="item.path"
                            class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition"
                            :class="isActive(item.path)
                                ? 'bg-primary-50 text-primary-700'
                                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                class="h-5 w-5 shrink-0"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                aria-hidden="true"
                            >
                                <template v-if="item.icon === 'dashboard'">
                                    <rect x="3" y="3" width="7" height="9" rx="1"></rect>
                                    <rect x="14" y="3" width="7" height="5" rx="1"></rect>
                                    <rect x="14" y="12" width="7" height="9" rx="1"></rect>
                                    <rect x="3" y="16" width="7" height="5" rx="1"></rect>
                                </template>
                                <template v-else-if="item.icon === 'cart'">
                                    <circle cx="9" cy="21" r="1"></circle>
                                    <circle cx="20" cy="21" r="1"></circle>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                </template>
                                <template v-else-if="item.icon === 'box'">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                </template>
                                <template v-else-if="item.icon === 'warehouse'">
                                    <path d="M22 8.35V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8.35A2 2 0 0 1 3.26 6.5l8-3.2a2 2 0 0 1 1.48 0l8 3.2A2 2 0 0 1 22 8.35Z"></path>
                                    <path d="M6 18h12"></path>
                                    <path d="M6 14h12"></path>
                                    <rect x="6" y="10" width="12" height="12" rx="1"></rect>
                                </template>
                                <template v-else-if="item.icon === 'truck'">
                                    <rect x="1" y="3" width="15" height="13" rx="1"></rect>
                                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                </template>
                                <template v-else-if="item.icon === 'users'">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </template>
                                <template v-else-if="item.icon === 'target'">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <circle cx="12" cy="12" r="6"></circle>
                                    <circle cx="12" cy="12" r="2"></circle>
                                </template>
                                <template v-else-if="item.icon === 'wallet'">
                                    <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"></path>
                                    <path d="M3 5v14a2 2 0 0 0 2 2h16v-5"></path>
                                    <path d="M18 12a2 2 0 0 0 0 4h4v-4Z"></path>
                                </template>
                                <template v-else-if="item.icon === 'chart'">
                                    <line x1="18" y1="20" x2="18" y2="10"></line>
                                    <line x1="12" y1="20" x2="12" y2="4"></line>
                                    <line x1="6" y1="20" x2="6" y2="14"></line>
                                </template>
                            </svg>
                            <span>{{ item.name }}</span>
                        </RouterLink>
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t border-gray-100 px-3 py-3">
            <RouterLink
                to="/settings"
                class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50 hover:text-gray-900"
                :class="route.path.startsWith('/settings') ? 'bg-primary-50 text-primary-700' : ''"
            >
                <svg viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
                <span>Pengaturan</span>
            </RouterLink>
        </div>
    </nav>
</template>