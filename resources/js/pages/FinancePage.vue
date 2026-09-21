<script setup>
import { onMounted, ref } from 'vue';
import { http, formatApiError } from '../api/client';
import PageHeader from '../components/PageHeader.vue';
import Modal from '../components/Modal.vue';
import { formatRupiah, formatDate } from '../utils/format';

const expenses = ref([]);
const salesCount = ref(0);
const total = ref(0);
const pagination = ref({ total: 0, per_page: 15, current_page: 1, last_page: 1 });
const loading = ref(true);
const error = ref('');
const from = ref('');
const to = ref('');
const showCreate = ref(false);
const creating = ref(false);
const createError = ref('');
const createForm = ref({ category: 'Operasional', amount: '', note: '', expense_date: '' });

const categories = ['Operasional', 'Gaji', 'Sewa', 'Listrik & Air', 'Marketing', 'Bahan Baku', 'Lainnya'];

async function loadExpenses() {
    loading.value = true;
    error.value = '';

    try {
        const { data } = await http.get('/expenses', {
            params: {
                page: pagination.value.current_page,
                from: from.value || undefined,
                to: to.value || undefined,
            },
        });

        expenses.value = data.data.expenses;
        total.value = Number(data.data.total) || 0;
        pagination.value = data.data.pagination;

        const salesResponse = await http.get('/sales', { params: { per_page: 1 } });
        salesCount.value = salesResponse.data.data.pagination.total;
    } catch (err) {
        error.value = formatApiError(err, 'Gagal memuat data keuangan.');
    } finally {
        loading.value = false;
    }
}

function resetCreate() {
    createForm.value = { category: 'Operasional', amount: '', note: '', expense_date: new Date().toISOString().slice(0, 10) };
    createError.value = '';
}

async function createExpense() {
    createError.value = '';

    const amount = Number(createForm.value.amount);

    if (!createForm.value.amount || amount < 0) {
        createError.value = 'Masukkan nominal yang valid.';

        return;
    }

    creating.value = true;

    try {
        await http.post('/expenses', {
            category: createForm.value.category || undefined,
            amount,
            note: createForm.value.note || undefined,
            expense_date: createForm.value.expense_date || undefined,
        });

        showCreate.value = false;
        await loadExpenses();
    } catch (err) {
        createError.value = formatApiError(err, 'Gagal mencatat pengeluaran.');
    } finally {
        creating.value = false;
    }
}

function goToPage(page) {
    if (page < 1 || page > pagination.value.last_page || page === pagination.value.current_page) {
        return;
    }

    pagination.value.current_page = page;
    loadExpenses();
}

onMounted(loadExpenses);
</script>

<template>
    <div class="space-y-6">
        <PageHeader title="Keuangan" description="Arus kas: pengeluaran tercatat dan total transaksi penjualan.">
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
                    Catat Pengeluaran
                </button>
            </template>
        </PageHeader>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Total Pengeluaran</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ formatRupiah(total) }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Jumlah Transaksi</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ salesCount.toLocaleString('id-ID') }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Jumlah Pengeluaran</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ pagination.total.toLocaleString('id-ID') }}</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-500">Dari</label>
                <input v-model="from" type="date" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm" @change="goToPage(1)" />
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-500">Sampai</label>
                <input v-model="to" type="date" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm" @change="goToPage(1)" />
            </div>
        </div>

        <div v-if="loading" class="flex items-center justify-center rounded-xl bg-white py-20">
            <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary-600 border-t-transparent"></div>
        </div>

        <p v-else-if="error" class="rounded-xl bg-red-50 px-5 py-4 text-sm text-red-600">{{ error }}</p>

        <div v-else class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div v-if="expenses.length" class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-100 bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-5 py-3">Kategori</th>
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3">Catatan</th>
                            <th class="px-5 py-3 text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="expense in expenses" :key="expense.id" class="hover:bg-gray-50/60">
                            <td class="px-5 py-3">
                                <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600">{{ expense.category || 'Lainnya' }}</span>
                            </td>
                            <td class="px-5 py-3 text-gray-600">{{ formatDate(expense.expense_date) }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ expense.note || '—' }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-red-600">− {{ formatRupiah(expense.amount) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="px-5 py-16 text-center">
                <p class="text-sm font-medium text-gray-700">Belum ada pengeluaran</p>
                <p class="mt-1 text-sm text-gray-400">Catat pengeluaran untuk melengkapi laporan arus kas.</p>
            </div>
        </div>

        <div v-if="pagination.last_page > 1" class="flex items-center justify-center gap-2">
            <button
                type="button"
                class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-semibold text-gray-600 disabled:opacity-40 disabled:cursor-not-allowed"
                :disabled="pagination.current_page <= 1"
                @click="goToPage(pagination.current_page - 1)"
            >
                Sebelumnya
            </button>
            <span class="text-sm text-gray-500">Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}</span>
            <button
                type="button"
                class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-semibold text-gray-600 disabled:opacity-40 disabled:cursor-not-allowed"
                :disabled="pagination.current_page >= pagination.last_page"
                @click="goToPage(pagination.current_page + 1)"
            >
                Berikutnya
            </button>
        </div>

        <!-- Create expense modal -->
        <Modal v-if="showCreate" title="Catat Pengeluaran" @close="showCreate = false">
            <form class="space-y-4" @submit.prevent="createExpense">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kategori</label>
                        <select v-model="createForm.category" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none">
                            <option v-for="category in categories" :key="category" :value="category">{{ category }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal</label>
                        <input v-model="createForm.expense_date" type="date" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Jumlah (Rp) *</label>
                    <input v-model="createForm.amount" type="number" min="0" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none" placeholder="contoh: 150000" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Catatan</label>
                    <input v-model="createForm.note" type="text" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none" placeholder="contoh: listrik bulan ini" />
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