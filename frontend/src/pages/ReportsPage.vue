<script setup>
import { onMounted, ref } from 'vue';
import { http } from '../api/client';
import PageHeader from '../components/PageHeader.vue';

const loading = ref(false);
const error = ref('');
const report = ref(null);
const from = ref('');
const to = ref('');

function setThisMonth() {
    const now = new Date();
    const f = new Date(now.getFullYear(), now.getMonth(), 1);
    const t = new Date(now.getFullYear(), now.getMonth() + 1, 0);
    from.value = f.toISOString().split('T')[0];
    to.value = t.toISOString().split('T')[0];
}

async function load() {
    loading.value = true;
    error.value = '';
    try {
        const { data } = await http.get('/reports/summary', {
            params: { from: from.value || undefined, to: to.value || undefined },
        });
        report.value = data.data;
    } catch (e) {
        error.value = 'Gagal memuat laporan';
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    setThisMonth();
    load();
});

function formatRupiah(n) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(n);
}
</script>

<template>
    <div class="space-y-6">
        <PageHeader
            title="Laporan"
            subtitle="Ringkasan penjualan, COGS, beban, dan produk terlaris"
            :back="{ to: { name: 'dashboard' }, label: 'Dashboard' }"
        />

        <div class="flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center">
            <div class="flex flex-col sm:flex-row sm:gap-3">
                <input v-model="from" type="date" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500" />
                <input v-model="to" type="date" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500" />
            </div>
            <div class="flex gap-2">
                <button @click="setThisMonth(); load()" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-900 transition hover:bg-gray-200">Bulan ini</button>
                <button @click="load" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-primary-700">Tampilkan</button>
            </div>
        </div>

        <div v-if="error" class="rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-500">{{ error }}</div>

        <div v-if="loading" class="rounded-2xl border border-gray-200 bg-white p-8 text-center text-sm text-gray-500">Memuat laporan…</div>

        <template v-else-if="report">
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
                <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-xs text-gray-500">Pendapatan</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900">{{ formatRupiah(report.summary.revenue) }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-xs text-gray-500">HPP/COGS</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900">{{ formatRupiah(report.summary.cogs) }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-xs text-gray-500">Beban (Expense)</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900">{{ formatRupiah(report.summary.expenses) }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-xs text-gray-500">Laba Kotor</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900">{{ formatRupiah(report.summary.gross) }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm col-span-2 lg:col-span-1">
                    <p class="text-xs text-gray-500">Laba Bersih</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900">{{ formatRupiah(report.summary.net) }}</p>
                    <p class="mt-1 text-xs text-gray-400">{{ report.summary.orderCount }} transaksi</p>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-4 py-3">
                        <h3 class="text-sm font-semibold text-gray-900">Produk Terlaris</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Produk</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-500">Qty</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-500">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <tr v-for="item in report.topProducts" :key="item.name">
                                    <td class="px-4 py-2 text-gray-900">{{ item.name }}</td>
                                    <td class="px-4 py-2 text-right text-gray-600">{{ item.qty }}</td>
                                    <td class="px-4 py-2 text-right text-gray-600">{{ formatRupiah(item.total) }}</td>
                                </tr>
                                <tr v-if="!report.topProducts.length">
                                    <td colspan="3" class="px-4 py-4 text-center text-gray-400">Belum ada data</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-4 py-3">
                        <h3 class="text-sm font-semibold text-gray-900">Penjualan per Hari</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Tanggal</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-500">Transaksi</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-500">Pendapatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <tr v-for="item in report.salesByDay" :key="item.date">
                                    <td class="px-4 py-2 text-gray-900">{{ item.date }}</td>
                                    <td class="px-4 py-2 text-right text-gray-600">{{ item.orders }}</td>
                                    <td class="px-4 py-2 text-right text-gray-600">{{ formatRupiah(item.revenue) }}</td>
                                </tr>
                                <tr v-if="!report.salesByDay.length">
                                    <td colspan="3" class="px-4 py-4 text-center text-gray-400">Belum ada data</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
