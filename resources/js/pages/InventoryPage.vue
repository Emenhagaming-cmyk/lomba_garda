<script setup>
import { onMounted, ref } from 'vue';
import { http, formatApiError } from '../api/client';
import PageHeader from '../components/PageHeader.vue';
import Modal from '../components/Modal.vue';
import { formatDateTime } from '../utils/format';

const stock = ref([]);
const movements = ref([]);
const loading = ref(true);
const error = ref('');
const status = ref('');
const showAdjust = ref(false);
const adjustProduct = ref(null);
const adjustQuantity = ref(0);
const adjustNote = ref('');
const adjustError = ref('');
const adjusting = ref(false);
const showMovements = ref(false);
const movementsProductName = ref('');
const movementsLoading = ref(false);

const tabs = [
    { value: '', label: 'Semua' },
    { value: 'ok', label: 'Cukup' },
    { value: 'low', label: 'Menipis' },
    { value: 'out', label: 'Habis' },
];

function statusBadge(item) {
    if (item.stock <= 0) {
        return 'bg-red-50 text-red-700';
    }

    if (item.stock <= item.min_stock) {
        return 'bg-amber-50 text-amber-700';
    }

    return 'bg-emerald-50 text-emerald-700';
}

function statusLabel(item) {
    if (item.stock <= 0) {
        return 'Habis';
    }

    if (item.stock <= item.min_stock) {
        return 'Menipis';
    }

    return 'Cukup';
}

async function loadStock() {
    loading.value = true;
    error.value = '';

    try {
        const { data } = await http.get('/stock', { params: { status: status.value || undefined } });
        stock.value = data.data.stock;
    } catch (err) {
        error.value = formatApiError(err, 'Gagal memuat stok.');
    } finally {
        loading.value = false;
    }
}

function openAdjust(item) {
    adjustProduct.value = item;
    adjustQuantity.value = 0;
    adjustNote.value = '';
    adjustError.value = '';
    showAdjust.value = true;
}

async function saveAdjust() {
    adjustError.value = '';

    const quantity = Number(adjustQuantity.value);

    if (!quantity || quantity === 0) {
        adjustError.value = 'Masukkan jumlah perubahan (bukan 0).';

        return;
    }

    adjusting.value = true;

    try {
        await http.post('/stock/adjust', {
            product_id: adjustProduct.value.id,
            quantity,
            note: adjustNote.value || undefined,
        });

        showAdjust.value = false;
        await loadStock();
    } catch (err) {
        adjustError.value = formatApiError(err, 'Gagal menyesuaikan stok.');
    } finally {
        adjusting.value = false;
    }
}

async function openMovements(item) {
    showMovements.value = true;
    movementsProductName.value = item.name;
    movementsLoading.value = true;
    movements.value = [];

    try {
        const { data } = await http.get('/stock/movements', { params: { product_id: item.id } });
        movements.value = data.data.movements;
    } catch {
        movements.value = [];
    } finally {
        movementsLoading.value = false;
    }
}

onMounted(loadStock);
</script>

<template>
    <div class="space-y-6">
        <PageHeader title="Stok" description="Pantau ketersediaan, pergerakan, dan lakukan penyesuaian." />

        <div class="flex gap-2">
            <button
                v-for="tab in tabs"
                :key="tab.value"
                type="button"
                class="rounded-lg px-3.5 py-1.5 text-sm font-semibold transition"
                :class="status === tab.value ? 'bg-primary-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100'"
                @click="status = tab.value; loadStock()"
            >
                {{ tab.label }}
            </button>
        </div>

        <div v-if="loading" class="flex items-center justify-center rounded-xl bg-white py-20">
            <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary-600 border-t-transparent"></div>
        </div>

        <p v-else-if="error" class="rounded-xl bg-red-50 px-5 py-4 text-sm text-red-600">{{ error }}</p>

        <div v-else class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div v-if="stock.length" class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-100 bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-5 py-3">Produk</th>
                            <th class="px-5 py-3">Kategori</th>
                            <th class="px-5 py-3">Supplier</th>
                            <th class="px-5 py-3 text-right">Stok</th>
                            <th class="px-5 py-3 text-right">Minimum</th>
                            <th class="px-5 py-3 text-right">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in stock" :key="item.id" class="hover:bg-gray-50/60">
                            <td class="px-5 py-3">
                                <p class="font-semibold text-gray-900">{{ item.name }}</p>
                                <p v-if="item.last_updated" class="text-xs text-gray-400">update {{ formatDateTime(item.last_updated) }}</p>
                            </td>
                            <td class="px-5 py-3 text-gray-600">{{ item.category || '—' }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ item.supplier || '—' }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-gray-900">{{ item.stock.toLocaleString('id-ID') }}</td>
                            <td class="px-5 py-3 text-right text-gray-500">{{ item.min_stock }}</td>
                            <td class="px-5 py-3 text-right">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="statusBadge(item)">
                                    {{ statusLabel(item) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <button type="button" class="rounded-lg px-2 py-1 text-xs font-semibold text-primary-600 hover:bg-primary-50" @click="openAdjust(item)">
                                        Adjust
                                    </button>
                                    <button type="button" class="rounded-lg px-2 py-1 text-xs font-semibold text-gray-500 hover:bg-gray-100" @click="openMovements(item)">
                                        Riwayat
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="px-5 py-16 text-center text-sm text-gray-500">Tidak ada produk dalam filter ini.</div>
        </div>

        <!-- Adjust modal -->
        <Modal v-if="showAdjust" title="Penyesuaian Stok" @close="showAdjust = false">
            <form class="space-y-4" @submit.prevent="saveAdjust">
                <div class="rounded-lg bg-gray-50 px-4 py-3">
                    <p class="text-sm font-semibold text-gray-900">{{ adjustProduct?.name }}</p>
                    <p class="text-xs text-gray-500">Stok saat ini: <span class="font-semibold">{{ adjustProduct?.stock }}</span></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Perubahan (+ masuk / − keluar)</label>
                    <input v-model="adjustQuantity" type="number" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none" placeholder="contoh: 5 atau -2" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Catatan</label>
                    <input v-model="adjustNote" type="text" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none" placeholder="contoh: stock opname, spill, susut" />
                </div>

                <p v-if="adjustError" class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600">{{ adjustError }}</p>

                <div class="flex justify-end gap-2">
                    <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50" @click="showAdjust = false">
                        Batal
                    </button>
                    <button type="submit" :disabled="adjusting" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-60">
                        {{ adjusting ? 'Menyimpan…' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </Modal>

        <!-- Movements modal -->
        <Modal v-if="showMovements" :title="`Riwayat • ${movementsProductName}`" @close="showMovements = false">
            <div v-if="movementsLoading" class="flex justify-center py-8">
                <div class="h-6 w-6 animate-spin rounded-full border-4 border-primary-600 border-t-transparent"></div>
            </div>
            <ul v-else-if="movements.length" class="divide-y divide-gray-100">
                <li v-for="movement in movements" :key="movement.id" class="flex items-center justify-between py-2.5">
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold"
                            :class="movement.type === 'in' ? 'bg-emerald-50 text-emerald-600' : movement.type === 'out' ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-600'"
                        >
                            {{ movement.type === 'in' ? '+' : movement.type === 'out' ? '−' : '±' }}
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">
                                {{ movement.quantity }} <span class="ml-1 text-xs text-gray-400">{{ movement.reference }} {{ movement.note ? '· ' + movement.note : '' }}</span>
                            </p>
                            <p class="text-xs text-gray-400">{{ formatDateTime(movement.created_at) }}</p>
                        </div>
                    </div>
                </li>
            </ul>
            <p v-else class="py-8 text-center text-sm text-gray-500">Belum ada pergerakan.</p>
        </Modal>
    </div>
</template>