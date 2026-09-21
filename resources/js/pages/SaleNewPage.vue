<script setup>
import { computed, onMounted, ref } from 'vue';
import { http, formatApiError } from '../api/client';
import { useBusinessStore } from '../stores/business';
import PageHeader from '../components/PageHeader.vue';
import { formatRupiah, PAYMENT_METHODS } from '../utils/format';

const business = useBusinessStore();

const products = ref([]);
const customers = ref([]);
const loading = ref(true);
const error = ref('');
const search = ref('');
const cart = ref(new Map());
const customerId = ref('');
const paymentMethod = ref('cash');
const discount = ref(0);
const submitting = ref(false);
const formError = ref('');
const success = ref(null);

const filteredProducts = computed(() => {
    const keyword = search.value.trim().toLowerCase();

    if (!keyword) {
        return products.value;
    }

    return products.value.filter((product) => product.name.toLowerCase().includes(keyword) || (product.category || '').toLowerCase().includes(keyword));
});

const cartItems = computed(() => Array.from(cart.value.values()));

const subtotal = computed(() => cartItems.value.reduce((sum, item) => sum + item.quantity * item.sell_price, 0));

const total = computed(() => Math.max(0, subtotal.value - (Number(discount.value) || 0)));

function addToCart(product) {
    const existing = cart.value.get(product.id);

    if (existing) {
        if (existing.quantity >= product.stock) {
            return;
        }

        existing.quantity += 1;

        return;
    }

    cart.value.set(product.id, { ...product, quantity: 1 });
}

function changeQuantity(productId, delta) {
    const item = cart.value.get(productId);

    if (!item) {
        return;
    }

    const next = item.quantity + delta;

    if (next <= 0) {
        cart.value.delete(productId);

        return;
    }

    if (next > item.stock) {
        return;
    }

    item.quantity = next;
}

function removeItem(productId) {
    cart.value.delete(productId);
}

async function submit() {
    formError.value = '';
    success.value = null;

    if (!cartItems.value.length) {
        formError.value = 'Keranjang masih kosong.';

        return;
    }

    submitting.value = true;

    try {
        const { data } = await http.post('/sales', {
            customer_id: customerId.value || undefined,
            payment_method: paymentMethod.value,
            discount: Number(discount.value) || 0,
            items: cartItems.value.map((item) => ({ product_id: item.id, quantity: item.quantity })),
        });

        success.value = data.data.sale;
        cart.value = new Map();
        discount.value = 0;
        await loadProducts();
    } catch (err) {
        formError.value = formatApiError(err, 'Gagal menyimpan transaksi.');
    } finally {
        submitting.value = false;
    }
}

async function loadProducts() {
    const { data } = await http.get('/products', { params: { low: undefined } });
    products.value = data.data.products;
}

onMounted(async () => {
    try {
        const [productsResponse, customersResponse] = await Promise.all([
            http.get('/products'),
            http.get('/customers'),
        ]);
        products.value = productsResponse.data.data.products;
        customers.value = customersResponse.data.data.customers;
    } catch (err) {
        error.value = formatApiError(err, 'Gagal memuat data produk.');
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div class="space-y-6">
        <PageHeader title="Transaksi Baru" description="Catat penjualan — stok, keuangan, dan pelanggan ter-update otomatis.">
            <template #actions>
                <RouterLink to="/sales" class="text-sm font-semibold text-primary-600 hover:text-primary-700">← Riwayat Penjualan</RouterLink>
            </template>
        </PageHeader>

        <p v-if="error" class="rounded-xl bg-red-50 px-5 py-4 text-sm text-red-600">{{ error }}</p>

        <!-- Success -->
        <div v-if="success" class="rounded-xl border border-emerald-200 bg-emerald-50 p-6">
            <div class="flex flex-col items-center gap-3 text-center">
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-500 text-white" aria-hidden="true">
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </span>
                <div>
                    <p class="text-lg font-bold text-emerald-800">Transaksi {{ success.invoice_no }} tersimpan</p>
                    <p class="text-sm text-emerald-600">Total {{ formatRupiah(success.total) }} · stok berhasil diperbarui.</p>
                </div>
                <div class="flex gap-2">
                    <button type="button" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700" @click="success = null">
                        Transaksi Baru
                    </button>
                    <RouterLink to="/sales" class="rounded-lg border border-primary-600 px-4 py-2 text-sm font-semibold text-primary-700 hover:bg-primary-50">
                        Lihat Riwayat
                    </RouterLink>
                </div>
            </div>
        </div>

        <div v-else class="grid grid-cols-1 gap-6 lg:grid-cols-5">
            <!-- Product picker -->
            <div class="lg:col-span-3">
                <div class="relative">
                    <svg viewBox="0 0 24 24" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari produk untuk ditambahkan…"
                        class="w-full rounded-xl border border-gray-300 bg-white py-3 pl-10 pr-4 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/30"
                    />
                </div>

                <div v-if="loading" class="mt-4 flex items-center justify-center rounded-xl bg-white py-16">
                    <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary-600 border-t-transparent"></div>
                </div>

                <div v-else class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <button
                        v-for="product in filteredProducts"
                        :key="product.id"
                        type="button"
                        :disabled="product.stock <= 0 || !product.is_active"
                        class="group flex flex-col rounded-xl border border-gray-200 bg-white p-3 text-left shadow-sm transition hover:border-primary-400 hover:shadow disabled:cursor-not-allowed disabled:opacity-50"
                        @click="addToCart(product)"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <p class="line-clamp-2 text-sm font-semibold text-gray-900 group-hover:text-primary-700">{{ product.name }}</p>
                            <span
                                class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                :class="product.stock <= 0 ? 'bg-red-50 text-red-600' : product.stock <= product.min_stock ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600'"
                            >
                                {{ product.stock }} {{ product.unit }}
                            </span>
                        </div>
                        <p class="mt-2 text-sm font-bold text-gray-900">{{ formatRupiah(product.sell_price) }}</p>
                    </button>
                </div>
                <p v-if="!loading && !filteredProducts.length" class="mt-4 rounded-xl bg-gray-50 px-5 py-8 text-center text-sm text-gray-500">
                    Tidak ada produk yang cocok.
                </p>
            </div>

            <!-- Cart -->
            <div class="lg:col-span-2">
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-5 py-4">
                        <h3 class="font-semibold text-gray-900">Keranjang <span class="text-sm font-normal text-gray-400">({{ cartItems.length }} item)</span></h3>
                    </div>

                    <div class="divide-y divide-gray-100 px-5">
                        <div v-for="item in cartItems" :key="item.id" class="flex items-center gap-3 py-3">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-gray-900">{{ item.name }}</p>
                                <p class="text-xs text-gray-400">{{ formatRupiah(item.sell_price) }}</p>
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" class="h-7 w-7 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-100" @click="changeQuantity(item.id, -1)">−</button>
                                <span class="w-8 text-center text-sm font-semibold text-gray-900">{{ item.quantity }}</span>
                                <button type="button" class="h-7 w-7 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-100" @click="changeQuantity(item.id, 1)">+</button>
                            </div>
                            <p class="w-20 text-right text-sm font-semibold text-gray-900">{{ formatRupiah(item.quantity * item.sell_price) }}</p>
                            <button type="button" class="text-gray-300 hover:text-red-500" aria-label="Hapus" @click="removeItem(item.id)">
                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6h14"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div v-if="!cartItems.length" class="px-5 py-10 text-center text-sm text-gray-400">
                        Klik produk untuk menambahkan ke keranjang.
                    </div>

                    <div v-if="cartItems.length" class="space-y-4 border-t border-gray-100 px-5 py-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Pelanggan</label>
                            <select v-model="customerId" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none">
                                <option value="">Walk-in (tanpa pelanggan)</option>
                                <option v-for="customer in customers" :key="customer.id" :value="customer.id">{{ customer.name }}</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Metode bayar</label>
                                <select v-model="paymentMethod" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none">
                                    <option v-for="method in PAYMENT_METHODS" :key="method.value" :value="method.value">{{ method.label }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Diskon (Rp)</label>
                                <input v-model="discount" type="number" min="0" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none" />
                            </div>
                        </div>

                        <div class="space-y-1.5 rounded-lg bg-gray-50 px-4 py-3 text-sm">
                            <div class="flex justify-between text-gray-500">
                                <span>Subtotal</span>
                                <span>{{ formatRupiah(subtotal) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-500">
                                <span>Diskon</span>
                                <span>− {{ formatRupiah(Number(discount) || 0) }}</span>
                            </div>
                            <div class="flex justify-between border-t border-gray-200 pt-1.5 text-base font-bold text-gray-900">
                                <span>Total</span>
                                <span>{{ formatRupiah(total) }}</span>
                            </div>
                        </div>

                        <p v-if="formError" class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600">{{ formError }}</p>

                        <button
                            type="button"
                            :disabled="submitting"
                            class="w-full rounded-xl bg-primary-600 py-3 text-sm font-bold text-white transition hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-60"
                            @click="submit"
                        >
                            {{ submitting ? 'Menyimpan…' : `Bayar ${formatRupiah(total)}` }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>