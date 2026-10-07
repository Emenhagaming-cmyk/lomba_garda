<script setup>
import { onMounted, ref } from 'vue';
import { http, formatApiError } from '../api/client';
import PageHeader from '../components/PageHeader.vue';
import { formatRupiah, formatDateTime, paymentMethodLabel } from '../utils/format';

const sales = ref([]);
const pagination = ref({ total: 0, per_page: 15, current_page: 1, last_page: 1 });
const loading = ref(true);
const error = ref('');
const from = ref('');
const to = ref('');

async function loadSales() {
    loading.value = true;
    error.value = '';

    try {
        const params = {
            page: pagination.value.current_page,
            from: from.value || undefined,
            to: to.value || undefined,
        };
        const { data } = await http.get('/sales', { params });
        sales.value = data.data.sales;
        pagination.value = data.data.pagination;
    } catch (err) {
        error.value = formatApiError(err, 'Gagal memuat penjualan.');
    } finally {
        loading.value = false;
    }
}

function goToPage(page) {
    if (page < 1 || page > pagination.value.last_page || page === pagination.value.current_page) {
        return;
    }

    pagination.value.current_page = page;
    loadSales();
}

onMounted(loadSales);
</script>

<template>
    <div class="space-y-6">
        <PageHeader title="Penjualan" description="Riwayat transaksi dan performa penjualan.">
            <template #actions>
                <RouterLink
                    to="/sales/new"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700"
                >
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Transaksi Baru
                </RouterLink>
            </template>
        </PageHeader>

        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-500">Dari</label>
                <input v-model="from" type="date" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm" @change="goToPage(1)" />
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-500">Sampai</label>
                <input v-model="to" type="date" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm" @change="goToPage(1)" />
            </div>
            <span class="text-sm text-gray-400">{{ pagination.total.toLocaleString('id-ID') }} transaksi</span>
        </div>

        <div v-if="loading" class="flex items-center justify-center rounded-xl bg-white py-20">
            <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary-600 border-t-transparent"></div>
        </div>

        <p v-else-if="error" class="rounded-xl bg-red-50 px-5 py-4 text-sm text-red-600">{{ error }}</p>

        <div v-else class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div v-if="sales.length" class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-100 bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-5 py-3">Invoice</th>
                            <th class="px-5 py-3">Waktu</th>
                            <th class="px-5 py-3">Pelanggan</th>
                            <th class="px-5 py-3">Metode</th>
                            <th class="px-5 py-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="sale in sales" :key="sale.id" class="hover:bg-gray-50/60">
                            <td class="px-5 py-3 font-medium text-gray-900">{{ sale.invoice_no }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ formatDateTime(sale.created_at) }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ sale.customer?.name || 'Walk-in' }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">
                                    {{ paymentMethodLabel(sale.payment_method) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right font-semibold text-gray-900">{{ formatRupiah(sale.total) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="px-5 py-16 text-center">
                <p class="text-sm font-medium text-gray-700">Belum ada penjualan</p>
                <p class="mt-1 text-sm text-gray-400">Catat transaksi pertama untuk mulai mengisi riwayat.</p>
                <RouterLink to="/sales/new" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">
                    Transaksi Baru
                </RouterLink>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="flex items-center justify-center gap-2">
            <button
                type="button"
                class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-semibold text-gray-600 disabled:opacity-40 disabled:cursor-not-allowed"
                :disabled="pagination.current_page <= 1"
                @click="goToPage(pagination.current_page - 1)"
            >
                Sebelumnya
            </button>
            <span class="text-sm text-gray-500">
                Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}
            </span>
            <button
                type="button"
                class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-semibold text-gray-600 disabled:opacity-40 disabled:cursor-not-allowed"
                :disabled="pagination.current_page >= pagination.last_page"
                @click="goToPage(pagination.current_page + 1)"
            >
                Berikutnya
            </button>
        </div>
    </div>
</template>