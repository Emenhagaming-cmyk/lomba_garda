<script setup>
import { onMounted, ref, watch } from 'vue';
import { http, formatApiError } from '../api/client';
import { useBusinessStore } from '../stores/business';
import PageHeader from '../components/PageHeader.vue';
import Modal from '../components/Modal.vue';
import { formatRupiah, badgeClasses, stockStatusLabel } from '../utils/format';

const props = defineProps({
    autoOpenNew: { type: Boolean, default: false },
});
const business = useBusinessStore();

const products = ref([]);
const loading = ref(true);
const error = ref('');
const search = ref('');
const showForm = ref(false);
const editing = ref(null);
const saving = ref(false);
const formError = ref('');
const suppliers = ref([]);
const categories = ref(business.categories || ['Umum']);

const form = ref({
    name: '',
    sku: '',
    category: '',
    unit: 'pcs',
    buy_price: '',
    sell_price: '',
    hpp: '',
    min_stock: '',
    supplier_id: '',
    initial_stock: '',
});

async function loadProducts() {
    loading.value = true;
    error.value = '';

    try {
        const { data } = await http.get('/products', { params: { search: search.value || undefined } });
        products.value = data.data.products;
    } catch (err) {
        error.value = formatApiError(err, 'Gagal memuat produk.');
    } finally {
        loading.value = false;
    }
}

function openCreate() {
    editing.value = null;
    Object.assign(form.value, {
        name: '',
        sku: '',
        category: '',
        unit: 'pcs',
        buy_price: '',
        sell_price: '',
        hpp: '',
        min_stock: '',
        supplier_id: '',
        initial_stock: '',
    });
    formError.value = '';
    showForm.value = true;
}

function openEdit(product) {
    editing.value = product;
    Object.assign(form.value, {
        name: product.name,
        sku: product.sku || '',
        category: product.category || '',
        unit: product.unit || 'pcs',
        buy_price: product.buy_price,
        sell_price: product.sell_price,
        hpp: product.hpp,
        min_stock: product.min_stock,
        supplier_id: product.supplier_id || '',
        initial_stock: '',
    });
    formError.value = '';
    showForm.value = true;
}

async function saveProduct() {
    formError.value = '';

    const payload = {
        name: form.value.name,
        sku: form.value.sku || undefined,
        category: form.value.category || undefined,
        unit: form.value.unit || 'pcs',
        buy_price: Number(form.value.buy_price) || 0,
        sell_price: Number(form.value.sell_price) || 0,
        hpp: form.value.hpp === '' ? undefined : Number(form.value.hpp),
        min_stock: Number(form.value.min_stock) || 0,
        supplier_id: form.value.supplier_id || undefined,
    };

    saving.value = true;

    try {
        if (editing.value) {
            await http.put(`/products/${editing.value.id}`, payload);
        } else {
            await http.post('/products', {
                ...payload,
                initial_stock: Number(form.value.initial_stock) || 0,
            });
        }

        showForm.value = false;
        await loadProducts();
    } catch (err) {
        formError.value = formatApiError(err, 'Gagal menyimpan produk.');
    } finally {
        saving.value = false;
    }
}

async function deactivate(product) {
    if (!window.confirm(`Nonaktifkan "${product.name}"? Produk tetap tersimpan untuk riwayat.`)) {
        return;
    }

    try {
        await http.delete(`/products/${product.id}`);
        await loadProducts();
    } catch (err) {
        error.value = formatApiError(err, 'Gagal menonaktifkan produk.');
    }
}

watch(search, () => {
    if (loading.value) {
        return;
    }

    loadProducts();
});

onMounted(async () => {
    if (!business.hasBusiness) {
        await business.fetchBusiness();
    }

    if (business.categories.length) {
        categories.value = [...new Set([...business.categories, ...categories.value])];
    }

    try {
        const { data } = await http.get('/purchases/suppliers');
        suppliers.value = data.data.suppliers;
    } catch {
        suppliers.value = [];
    }

    await loadProducts();

    if (props.autoOpenNew) {
        openCreate();
    }
});
</script>

<template>
    <div class="space-y-6">
        <PageHeader title="Produk" description="Katalog produk, harga, dan margin.">
            <template #actions>
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <svg viewBox="0 0 24 24" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="7"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari produk…"
                            class="rounded-lg border border-gray-300 py-2 pl-9 pr-3 text-sm outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/30"
                        />
                    </div>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700"
                        @click="openCreate"
                    >
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Tambah Produk
                    </button>
                </div>
            </template>
        </PageHeader>

        <div v-if="loading" class="flex items-center justify-center rounded-xl bg-white py-20">
            <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary-600 border-t-transparent"></div>
        </div>

        <p v-else-if="error" class="rounded-xl bg-red-50 px-5 py-4 text-sm text-red-600">{{ error }}</p>

        <div v-else class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div v-if="products.length" class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-100 bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-5 py-3">Produk</th>
                            <th class="px-5 py-3">Kategori</th>
                            <th class="px-5 py-3 text-right">Stok</th>
                            <th class="px-5 py-3 text-right">Harga Beli</th>
                            <th class="px-5 py-3 text-right">Harga Jual</th>
                            <th class="px-5 py-3 text-right">Margin</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50/60">
                            <td class="px-5 py-3">
                                <p class="font-semibold text-gray-900" :class="!product.is_active ? 'text-gray-400 line-through' : ''">
                                    {{ product.name }}
                                </p>
                                <p class="text-xs text-gray-400">{{ product.sku || '—' }} {{ product.supplier ? '· ' + product.supplier : '' }}</p>
                            </td>
                            <td class="px-5 py-3 text-gray-600">{{ product.category || '—' }}</td>
                            <td class="px-5 py-3 text-right">
                                <span class="inline-flex items-center gap-2">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="badgeClasses(product.stock, product.min_stock)">
                                        {{ product.stock }} {{ product.unit }}
                                    </span>
                                    <span class="text-xs text-gray-400">{{ stockStatusLabel(product.stock, product.min_stock) }}</span>
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right text-gray-600">{{ formatRupiah(product.buy_price) }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-gray-900">{{ formatRupiah(product.sell_price) }}</td>
                            <td class="px-5 py-3 text-right text-emerald-600">
                                {{ product.sell_price ? Math.round(((product.sell_price - product.hpp) / product.sell_price) * 100) + '%' : '—' }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <button
                                        type="button"
                                        class="rounded-lg px-2 py-1 text-xs font-semibold text-primary-600 hover:bg-primary-50"
                                        @click="openEdit(product)"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        v-if="product.is_active"
                                        type="button"
                                        class="rounded-lg px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50"
                                        @click="deactivate(product)"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="px-5 py-16 text-center">
                <p class="text-sm font-medium text-gray-700">Belum ada produk</p>
                <p class="mt-1 text-sm text-gray-400">Tambahkan produk pertama ke katalog usaha kamu.</p>
                <button type="button" class="mt-4 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700" @click="openCreate">
                    Tambah Produk
                </button>
            </div>
        </div>

        <!-- Modal form -->
        <Modal
            v-if="showForm"
            :title="editing ? 'Edit Produk' : 'Produk Baru'"
            @close="showForm = false"
        >
            <form class="space-y-4" @submit.prevent="saveProduct">
                <div>
                    <label for="p-name" class="block text-sm font-medium text-gray-700">Nama produk *</label>
                    <input id="p-name" v-model="form.name" type="text" required class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/30" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="p-sku" class="block text-sm font-medium text-gray-700">SKU / Kode</label>
                        <input id="p-sku" v-model="form.sku" type="text" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none" />
                    </div>
                    <div>
                        <label for="p-category" class="block text-sm font-medium text-gray-700">Kategori</label>
                        <input id="p-category" v-model="form.category" type="text" list="category-list" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none" />
                        <datalist id="category-list">
                            <option v-for="category in categories" :key="category" :value="category" />
                        </datalist>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="p-unit" class="block text-sm font-medium text-gray-700">Satuan</label>
                        <select id="p-unit" v-model="form.unit" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none">
                            <option value="pcs">pcs</option>
                            <option value="cup">cup</option>
                            <option value="porsi">porsi</option>
                            <option value="kotak">kotak</option>
                            <option value="kg">kg</option>
                            <option value="liter">liter</option>
                            <option value="pack">pack</option>
                        </select>
                    </div>
                    <div>
                        <label for="p-supplier" class="block text-sm font-medium text-gray-700">Supplier</label>
                        <select id="p-supplier" v-model="form.supplier_id" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none">
                            <option value="">—</option>
                            <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.name }}</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="p-buy" class="block text-sm font-medium text-gray-700">Harga beli (Rp) *</label>
                        <input id="p-buy" v-model="form.buy_price" type="number" min="0" required class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none" />
                    </div>
                    <div>
                        <label for="p-sell" class="block text-sm font-medium text-gray-700">Harga jual (Rp) *</label>
                        <input id="p-sell" v-model="form.sell_price" type="number" min="0" required class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="p-hpp" class="block text-sm font-medium text-gray-700">HPP (Rp)</label>
                        <input id="p-hpp" v-model="form.hpp" type="number" min="0" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none" />
                    </div>
                    <div>
                        <label for="p-min" class="block text-sm font-medium text-gray-700">Stok minimum</label>
                        <input id="p-min" v-model="form.min_stock" type="number" min="0" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none" />
                    </div>
                </div>

                <div v-if="!editing">
                    <label for="p-initial" class="block text-sm font-medium text-gray-700">Stok awal</label>
                    <input id="p-initial" v-model="form.initial_stock" type="number" min="0" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none" />
                    <p class="mt-1 text-xs text-gray-400">Stok awal mencatat pergerakan "Stok awal".</p>
                </div>

                <p v-if="formError" class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600">{{ formError }}</p>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50" @click="showForm = false">
                        Batal
                    </button>
                    <button type="submit" :disabled="saving" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-60">
                        {{ saving ? 'Menyimpan…' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </Modal>
    </div>
</template>