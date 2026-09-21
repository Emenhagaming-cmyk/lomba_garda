<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { http, formatApiError } from '../api/client';
import { useAuthStore } from '../stores/auth';
import { useBusinessStore } from '../stores/business';
import StatCard from '../components/StatCard.vue';
import { formatCompactRupiah, formatRupiah, formatDateTime, badgeClasses, stockStatusLabel } from '../utils/format';

const router = useRouter();
const auth = useAuthStore();
const business = useBusinessStore();

const data = ref(null);
const error = ref('');
const loading = ref(true);

const firstName = () => (auth.user?.name || '').split(' ')[0];

const revenueTrend = computed(() => {
    if (!data.value?.kpi) {
        return null;
    }

    const { revenue_today, revenue_yesterday } = data.value.kpi;

    if (!revenue_yesterday || revenue_yesterday === 0) {
        return null;
    }

    const percent = Math.round(((revenue_today - revenue_yesterday) / revenue_yesterday) * 100);

    return { percent, direction: percent >= 0 ? 'up' : 'down' };
});

const hasContent = computed(() => {
    if (!data.value) {
        return false;
    }

    const { kpi } = data.value;

    return kpi.revenue_today > 0 || kpi.orders_month > 0 || kpi.customers > 0;
});

onMounted(async () => {
    try {
        const { data: response } = await http.get('/dashboard');
        data.value = response.data;
    } catch (err) {
        error.value = formatApiError(err, 'Gagal memuat dashboard.');
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900 sm:text-2xl">
                    Halo, {{ firstName() || 'sahabat UMKM' }} 👋
                </h2>
                <p class="mt-1 text-sm text-gray-500">Berikut ringkasan usaha kamu hari ini.</p>
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

        <!-- Loading -->
        <div v-if="loading" class="flex flex-col items-center justify-center rounded-xl bg-white py-20 text-center">
            <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary-600 border-t-transparent"></div>
            <p class="mt-4 text-sm text-gray-500">Memuat dashboard…</p>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="rounded-xl bg-red-50 px-5 py-4 text-sm text-red-600">
            {{ error }}
        </div>

        <template v-else-if="data">
            <!-- KPI Cards -->
            <section aria-label="Metrik utama" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <StatCard
                    label="Penjualan Hari Ini"
                    :value="formatCompactRupiah(data.kpi.revenue_today)"
                    :trend="revenueTrend ? `${revenueTrend.percent > 0 ? '+' : ''}${revenueTrend.percent}%` : ''"
                    :trend-direction="revenueTrend?.direction || 'up'"
                    hint="vs kemarin"
                    icon="wallet"
                    accent
                />
                <StatCard
                    label="Revenue Bulan Ini"
                    :value="formatCompactRupiah(data.kpi.revenue_month)"
                    icon="cart"
                />
                <StatCard
                    label="Total Order"
                    :value="data.kpi.orders_month.toLocaleString('id-ID')"
                    icon="box"
                />
                <StatCard
                    label="Pelanggan"
                    :value="data.kpi.customers.toLocaleString('id-ID')"
                    hint="total terdaftar"
                    icon="users"
                />
            </section>

            <!-- Profit & Expense KPIs -->
            <section aria-label="Profit" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <StatCard
                    label="Gross Profit Bulan"
                    :value="formatCompactRupiah(data.kpi.gross_profit_month)"
                    hint="sebelum pengeluaran"
                />
                <StatCard
                    label="Pengeluaran Bulan"
                    :value="formatCompactRupiah(data.kpi.expenses_month)"
                />
                <StatCard
                    label="Net Profit Bulan"
                    :value="formatCompactRupiah(data.kpi.net_profit_month)"
                    :accent="data.kpi.net_profit_month > 0"
                />
            </section>

            <!-- Main grid: stok + recent sales + insights -->
            <section aria-label="Detail" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Left -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Low stock -->
                    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                            <h3 class="font-semibold text-gray-900">Stok Menipis</h3>
                            <RouterLink to="/inventory" class="text-xs font-semibold text-primary-600 hover:text-primary-700">
                                Lihat semua
                            </RouterLink>
                        </div>
                        <div v-if="data.low_stock.length" class="divide-y divide-gray-100">
                            <div v-for="item in data.low_stock" :key="item.id" class="flex items-center justify-between px-5 py-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ item.name }}</p>
                                    <p class="text-xs text-gray-400">{{ item.category }} {{ item.supplier ? '· ' + item.supplier : '' }}</p>
                                </div>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="badgeClasses(item.stock, item.min_stock)">
                                    {{ item.stock }} / min {{ item.min_stock }}
                                </span>
                            </div>
                        </div>
                        <div v-else class="px-5 py-10 text-center">
                            <p class="text-sm text-gray-400">Semua stok dalam kondisi aman.</p>
                        </div>
                    </div>

                    <!-- Recent sales -->
                    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                            <h3 class="font-semibold text-gray-900">Penjualan Terbaru</h3>
                            <RouterLink to="/sales" class="text-xs font-semibold text-primary-600 hover:text-primary-700">
                                Riwayat
                            </RouterLink>
                        </div>
                        <div v-if="data.recent_sales.length" class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="border-b border-gray-100 bg-gray-50 text-xs uppercase text-gray-500">
                                    <tr>
                                        <th class="px-5 py-2.5">Invoice</th>
                                        <th class="px-5 py-2.5">Pelanggan</th>
                                        <th class="px-5 py-2.5 text-right">Total</th>
                                        <th class="px-5 py-2.5">Metode</th>
                                        <th class="px-5 py-2.5">Waktu</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="sale in data.recent_sales" :key="sale.id" class="hover:bg-gray-50/60">
                                        <td class="px-5 py-2.5 font-medium text-gray-900">{{ sale.invoice_no }}</td>
                                        <td class="px-5 py-2.5 text-gray-600">{{ sale.customer }}</td>
                                        <td class="px-5 py-2.5 text-right font-semibold text-gray-900">{{ formatRupiah(sale.total) }}</td>
                                        <td class="px-5 py-2.5 text-gray-500 uppercase">{{ sale.payment_method }}</td>
                                        <td class="px-5 py-2.5 text-gray-400">{{ formatDateTime(sale.created_at) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="px-5 py-10 text-center">
                            <p class="text-sm font-medium text-gray-700">Belum ada penjualan</p>
                            <p class="mt-1 text-sm text-gray-400">Mulai catat transaksi pertama di sini.</p>
                            <RouterLink to="/sales/new" class="mt-4 inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                Catat Sekarang
                            </RouterLink>
                        </div>
                    </div>
                </div>

                <!-- Right: insights -->
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-5 py-4">
                        <h3 class="font-semibold text-gray-900">Insight & Rekomendasi</h3>
                    </div>
                    <ul v-if="data.insights.length" class="divide-y divide-gray-100">
                        <li v-for="(insight, index) in data.insights" :key="index" class="flex gap-3 px-5 py-4">
                            <span
                                class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg"
                                :class="insight.severity === 'warning' ? 'bg-amber-50' : 'bg-blue-50'"
                                aria-hidden="true"
                            >
                                <svg viewBox="0 0 24 24" class="h-4 w-4" :class="insight.severity === 'warning' ? 'text-amber-600' : 'text-blue-600'" fill="none" stroke="currentColor" stroke-width="2">
                                    <template v-if="insight.type === 'auto_restock'">
                                        <rect x="1" y="3" width="15" height="13" rx="1"></rect>
                                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                        <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                        <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                    </template>
                                    <template v-else>
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="8" x2="12" y2="12"></line>
                                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                    </template>
                                </svg>
                            </span>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ insight.title }}</p>
                                <p class="mt-0.5 text-xs leading-relaxed text-gray-500">{{ insight.message }}</p>
                                <RouterLink
                                    v-if="insight.type === 'auto_restock'"
                                    to="/purchase"
                                    class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-primary-600 hover:text-primary-700"
                                >
                                    Buat Pesanan →
                                </RouterLink>
                            </div>
                        </li>
                    </ul>
                    <div v-else class="px-5 py-10 text-center">
                        <p class="text-sm text-gray-400">Belum ada insight. Data akan muncul setelah ada transaksi.</p>
                    </div>
                </div>
            </section>
        </template>
    </div>
</template>