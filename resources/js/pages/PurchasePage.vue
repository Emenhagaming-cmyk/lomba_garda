<script setup>
import { computed, onMounted, ref } from 'vue';
import { http, formatApiError } from '../api/client';
import PageHeader from '../components/PageHeader.vue';
import Modal from '../components/Modal.vue';
import { formatRupiah, formatDate } from '../utils/format';

const purchases = ref([]);
const pagination = ref({ total: 0, per_page: 15, current_page: 1, last_page: 1 });
const suppliers = ref([]);
const products = ref([]);
const loading = ref(true);
const error = ref('');
const showForm = ref(false);
const creating = ref(false);
const formError = ref('');
const formSuccess = ref('');

const form = ref({
    supplier_id: '',
    order_date: '',
    status: 'ordered',
    items: [],
    note: '',
});

const newItemForm = ref({ product_id: '', quantity: 1, unit_cost: '' });

const statusBadges = {
    draft: 'bg-gray-100 text-gray-600',
    ordered: 'bg-amber-50 text-amber-700',
    partially_received: 'bg-blue-50 text-blue-700',
    received: 'bg-emerald-50 text-emerald-700',
};

const statusLabels = {
    draft: 'Draft',
    ordered: 'Dipesan',
    partially_received: 'Sebagian',
    received: 'Diterima',
};

const purchaseTotal = computed(() =>
    form.value.items.reduce((sum, item) => sum + (Number(item.unit_cost) || 0) * item.quantity, 0),
);

async function loadPurchases() {
    loading.value = true;
    error.value = '';

    try {
        const { data } = await http.get('/purchases', { params: { page: pagination.value.current_page } });
        purchases.value = data.data.purchases;
        pagination.value = data.data.pagination;
    } catch (err) {
        error.value = formatApiError(err, 'Gagal memuat pembelian.');
    } finally {
        loading.value = false;
    }
}

async function loadOptions() {
    const [suppliersResponse, productsResponse] = await Promise.all([
        http.get('/purchases/suppliers'),
        http.get('/products'),
    ]);
    suppliers.value = suppliersResponse.data.data.suppliers;
    products.value = productsResponse.data.data.products;
}

function openCreate() {
    form.value = {
        supplier_id: '',
        order_date: new Date().toISOString().slice(0, 10),
        status: 'ordered',
        items: [],
        note: '',
    };
    newItemForm.value = { product_id: '', quantity: 1, unit_cost: '' };
    formError.value = '';
    formSuccess.value = '';
    showForm.value = true;
}

function addItem() {
    const product = products.value.find((item) => item.id === Number(newItemForm.value.product_id));

    if (!product) {
        formError.value = 'Pilih produk dulu.';

        return;
    }

    const existing = form.value.items.find((item) => item.product_id === product.id);

    if (existing) {
        existing.quantity += Number(newItemForm.value.quantity) || 0;
        existing.unit_cost = Number(newItemForm.value.unit_cost) || product.buy_price;
        existing.product = product.name;
    } else {
        form.value.items.push({
            product_id: product.id,
            product: product.name,
            quantity: Number(newItemForm.value.quantity) || 1,
            unit_cost: Number(newItemForm.value.unit_cost) || product.buy_price,
        });
    }

    newItemForm.value = { product_id: '', quantity: 1, unit_cost: '' };
    formError.value = '';
}

function removeItem(index) {
    form.value.items.splice(index, 1);
}

function itemPrice(productId) {
    return products.value.find((item) => item.id === productId)?.buy_price || 0;
}

async function submitPurchase() {
    formError.value = '';
    formSuccess.value = '';

    if (!form.value.supplier_id || !form.value.items.length) {
        formError.value = 'Pilih supplier dan minimal satu produk.';

        return;
    }

    creating.value = true;

    try {
        await http.post('/purchases', {
            supplier_id: Number(form.value.supplier_id),
            order_date: form.value.order_date || undefined,
            status: form.value.status,
            note: form.value.note || undefined,
            items: form.value.items.map((item) => ({
                product_id: item.product_id,
                quantity: item.quantity,
                unit_cost: Number(item.unit_cost) || itemPrice(item.product_id) || 0,
            })),
        });

        showForm.value = false;
        formSuccess.value = 'Purchase order berhasil dibuat.';
        await loadPurchases();
    } catch (err) {
        formError.value = formatApiError(err, 'Gagal membuat pembelian.');
    } finally {
        creating.value = false;
    }
}

async function receive(purchase) {
    if (!window.confirm(`Terima PO ${purchase.po_number}? Stok barang akan bertambah otomatis.`)) {
        return;
    }

    try {
        await http.post(`/purchases/${purchase.id}/receive`);
        await loadPurchases();
    } catch (err) {
        error.value = formatApiError(err, 'Gagal menerima barang.');
    }
}

onMounted(async () => {
    const [optionsStub] = await Promise.allSettled([loadOptions()]);
    await loadPurchases();
});
</script>

<template>
    <div class="space-y-6">
        <PageHeader title="Pembelian" description="Purchase order dari supplier dan terima barang masuk.">
            <template #actions>
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700"
                    @click="openCreate"
                >
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Purchase Order
                </button>
            </template>
        </PageHeader>

        <p v-if="formSuccess" class="rounded-xl bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-700">{{ formSuccess }}</p>
        <p v-else-if="error" class="rounded-xl bg-red-50 px-5 py-4 text-sm text-red-600">{{ error }}</p>

        <div v-if="loading" class="flex items-center justify-center rounded-xl bg-white py-20">
            <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary-600 border-t-transparent"></div>
        </div>

        <div v-else class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div v-if="purchases.length" class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-100 bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-5 py-3">PO No.</th>
                            <th class="px-5 py-3">Supplier</th>
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Total</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="purchase in purchases" :key="purchase.id" class="hover:bg-gray-50/60">
                            <td class="px-5 py-3 font-semibold text-gray-900">{{ purchase.po_number }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ purchase.supplier?.name || '—' }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ formatDate(purchase.order_date) }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="statusBadges[purchase.status] || 'bg-gray-100 text-gray-600'">
                                    {{ statusLabels[purchase.status] || purchase.status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right font-semibold text-gray-900">{{ formatRupiah(purchase.total) }}</td>
                            <td class="px-5 py-3 text-right">
                                <button
                                    v-if="purchase.status !== 'received'"
                                    type="button"
                                    class="rounded-lg bg-primary-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-primary-700"
                                    @click="receive(purchase)"
                                >
                                    Terima Barang
                                </button>
                                <span v-else class="text-xs text-gray-300">—</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="px-5 py-16 text-center">
                <p class="text-sm font-medium text-gray-700">Belum ada purchase order</p>
                <p class="mt-1 text-sm text-gray-400">Buat PO pertama ke supplier untuk mengisi stok.</p>
            </div>
        </div>

        <!-- Create PO modal -->
        <Modal v-if="showForm" title="Purchase Order Baru" max-width="max-w-2xl" @close="showForm = false">
            <form class="space-y-4" @submit.prevent="submitPurchase">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Supplier *</label>
                        <select v-model="form.supplier_id" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none">
                            <option value="">— pilih supplier —</option>
                            <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                                {{ supplier.name }} ({{ supplier.lead_time_days }} hari)
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal order</label>
                        <input v-model="form.order_date" type="date" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Item pesanan</label>
                    <div class="mt-1.5 flex flex-col gap-2 sm:flex-row">
                        <select v-model="newItemForm.product_id" class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none">
                            <option value="">— pilih produk —</option>
                            <option v-for="product in products" :key="product.id" :value="product.id">
                                {{ product.name }} · stok {{ product.stock }}
                            </option>
                        </select>
                        <input v-model="newItemForm.quantity" type="number" min="1" placeholder="Qty" class="w-20 rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                        <input v-model="newItemForm.unit_cost" type="number" min="0" placeholder="Harga" class="w-28 rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                        <button type="button" class="rounded-lg bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200" @click="addItem">
                            Tambah
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-400">Kosongkan harga untuk memakai harga beli produk.</p>
                </div>

                <div v-if="form.items.length" class="divide-y divide-gray-100 rounded-lg border border-gray-200">
                    <div v-for="(item, index) in form.items" :key="item.product_id" class="flex items-center justify-between px-4 py-2.5">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ item.product }}</p>
                            <p class="text-xs text-gray-400">{{ item.quantity }} × {{ formatRupiah(item.unit_cost) }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold text-gray-900">{{ formatRupiah(item.quantity * item.unit_cost) }}</span>
                            <button type="button" class="text-gray-300 hover:text-red-500" aria-label="Hapus" @click="removeItem(index)">
                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6h14"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex justify-between border-t border-gray-100 bg-gray-50 px-4 py-2.5 text-sm font-bold text-gray-900">
                        <span>Total PO</span>
                        <span>{{ formatRupiah(purchaseTotal) }}</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Catatan</label>
                    <input v-model="form.note" type="text" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none" />
                </div>

                <p v-if="formError" class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600">{{ formError }}</p>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50" @click="showForm = false">
                        Batal
                    </button>
                    <button type="submit" :disabled="creating" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-60">
                        {{ creating ? 'Menyimpan…' : 'Buat PO' }}
                    </button>
                </div>
            </form>
        </Modal>
    </div>
</template>