<script setup>
import { onMounted, ref } from 'vue';
import { http, formatApiError } from '../api/client';
import PageHeader from '../components/PageHeader.vue';
import Modal from '../components/Modal.vue';
import { formatRupiah, formatDateTime, paymentMethodLabel, segmentLabel, segmentClasses } from '../utils/format';

const customers = ref([]);
const loading = ref(true);
const error = ref('');
const search = ref('');
const showCreate = ref(false);
const creating = ref(false);
const createError = ref('');
const createForm = ref({ name: '', phone: '', email: '', address: '', notes: '' });
const showDetail = ref(false);
const detail = ref(null);
const detailLoading = ref(false);

async function loadCustomers() {
    loading.value = true;
    error.value = '';

    try {
        const { data } = await http.get('/customers', { params: { search: search.value || undefined } });
        customers.value = data.data.customers;
    } catch (err) {
        error.value = formatApiError(err, 'Gagal memuat pelanggan.');
    } finally {
        loading.value = false;
    }
}

function resetCreate() {
    createForm.value = { name: '', phone: '', email: '', address: '', notes: '' };
    createError.value = '';
}

async function createCustomer() {
    createError.value = '';

    if (!createForm.value.name.trim()) {
        createError.value = 'Nama pelanggan wajib diisi.';

        return;
    }

    creating.value = true;

    try {
        await http.post('/customers', createForm.value);
        showCreate.value = false;
        await loadCustomers();
    } catch (err) {
        createError.value = formatApiError(err, 'Gagal menambah pelanggan.');
    } finally {
        creating.value = false;
    }
}

async function openDetail(customer) {
    showDetail.value = true;
    detail.value = null;
    detailLoading.value = true;

    try {
        const { data } = await http.get(`/customers/${customer.id}`);
        detail.value = data.data;
    } catch {
        detail.value = null;
    } finally {
        detailLoading.value = false;
    }
}

onMounted(loadCustomers);
</script>

<template>
    <div class="space-y-6">
        <PageHeader title="Pelanggan" description="Kelola pelanggan dan pantau nilai tiap segmen.">
            <template #actions>
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700"
                    @click="resetCreate(); showCreate = true"
                >
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Pelanggan Baru
                </button>
            </template>
        </PageHeader>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="relative w-full max-w-sm">
                <svg viewBox="0 0 24 24" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="7"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input v-model="search" type="text" placeholder="Cari nama atau telepon…" class="w-full rounded-xl border border-gray-300 bg-white py-2.5 pl-10 pr-4 text-sm shadow-sm focus:border-primary-500 focus:outline-none" @keyup.enter="loadCustomers" />
            </div>
            <span class="text-sm text-gray-400">{{ customers.length }} pelanggan</span>
        </div>

        <div v-if="loading" class="flex items-center justify-center rounded-xl bg-white py-20">
            <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary-600 border-t-transparent"></div>
        </div>

        <p v-else-if="error" class="rounded-xl bg-red-50 px-5 py-4 text-sm text-red-600">{{ error }}</p>

        <div v-else class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div v-if="customers.length" class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-100 bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-5 py-3">Nama</th>
                            <th class="px-5 py-3">Kontak</th>
                            <th class="px-5 py-3 text-right">Transaksi</th>
                            <th class="px-5 py-3 text-right">Total Belanja</th>
                            <th class="px-5 py-3 text-right">Segmen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="customer in customers" :key="customer.id" class="cursor-pointer hover:bg-gray-50/60" @click="openDetail(customer)">
                            <td class="px-5 py-3 font-semibold text-gray-900">{{ customer.name }}</td>
                            <td class="px-5 py-3 text-gray-600">
                                <p class="text-sm">{{ customer.phone || '—' }}</p>
                                <p class="text-xs text-gray-400">{{ customer.email || '' }}</p>
                            </td>
                            <td class="px-5 py-3 text-right text-gray-600">{{ customer.order_count }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-gray-900">{{ formatRupiah(customer.total_spending) }}</td>
                            <td class="px-5 py-3 text-right">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="segmentClasses(customer.segment)">
                                    {{ segmentLabel(customer.segment) }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="px-5 py-16 text-center">
                <p class="text-sm font-medium text-gray-700">Belum ada pelanggan</p>
                <p class="mt-1 text-sm text-gray-400">Pelanggan muncul otomatis saat transaksi dicatat dengan nama.</p>
            </div>
        </div>

        <!-- Detail modal -->
        <Modal v-if="showDetail" :title="detail?.customer?.name || 'Detail Pelanggan'" @close="showDetail = false">
            <div v-if="detailLoading" class="flex justify-center py-8">
                <div class="h-6 w-6 animate-spin rounded-full border-4 border-primary-600 border-t-transparent"></div>
            </div>
            <div v-else-if="detail" class="space-y-5">
                <div class="grid grid-cols-3 gap-3">
                    <div class="rounded-lg bg-gray-50 px-3 py-2.5 text-center">
                        <p class="text-lg font-bold text-gray-900">{{ formatRupiah(detail.metrics.total_spending) }}</p>
                        <p class="text-xs text-gray-500">Total Belanja</p>
                    </div>
                    <div class="rounded-lg bg-gray-50 px-3 py-2.5 text-center">
                        <p class="text-lg font-bold text-gray-900">{{ detail.metrics.order_count }}</p>
                        <p class="text-xs text-gray-500">Transaksi</p>
                    </div>
                    <div class="rounded-lg bg-gray-50 px-3 py-2.5 text-center">
                        <p class="text-lg font-bold text-gray-900">{{ formatRupiah(detail.metrics.average_order) }}</p>
                        <p class="text-xs text-gray-500">Rata-rata</p>
                    </div>
                </div>

                <div class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3">
                    <span class="text-sm text-gray-500">Segmen</span>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="segmentClasses(detail.metrics.segment)">
                        {{ segmentLabel(detail.metrics.segment) }}
                    </span>
                </div>

                <div>
                    <h4 class="mb-2 text-sm font-semibold text-gray-900">Riwayat Transaksi</h4>
                    <ul v-if="detail.history.length" class="divide-y divide-gray-100">
                        <li v-for="sale in detail.history" :key="sale.id" class="py-2.5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ sale.invoice_no }} · {{ paymentMethodLabel(sale.payment_method) }}</p>
                                    <p class="text-xs text-gray-400">{{ formatDateTime(sale.created_at) }}</p>
                                </div>
                                <p class="text-sm font-bold text-gray-900">{{ formatRupiah(sale.total) }}</p>
                            </div>
                            <p v-if="sale.items.length" class="mt-1 text-xs text-gray-500">
                                {{ sale.items.map((item) => `${item.quantity}× ${item.product}`).join(', ') }}
                            </p>
                        </li>
                    </ul>
                    <p v-else class="py-3 text-sm text-gray-400">Belum ada transaksi tercatat.</p>
                </div>
            </div>
        </Modal>

        <!-- Create modal -->
        <Modal v-if="showCreate" title="Pelanggan Baru" @close="showCreate = false">
            <form class="space-y-4" @submit.prevent="createCustomer">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama *</label>
                    <input v-model="createForm.name" type="text" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none" placeholder="Nama pelanggan" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Telepon</label>
                        <input v-model="createForm.phone" type="text" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input v-model="createForm.email" type="email" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Alamat</label>
                    <input v-model="createForm.address" type="text" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Catatan</label>
                    <textarea v-model="createForm.notes" rows="2" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"></textarea>
                </div>

                <p v-if="createError" class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600">{{ createError }}</p>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50" @click="showCreate = false">
                        Batal
                    </button>
                    <button type="submit" :disabled="creating" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-60">
                        {{ creating ? 'Menyimpan…' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </Modal>
    </div>
</template>