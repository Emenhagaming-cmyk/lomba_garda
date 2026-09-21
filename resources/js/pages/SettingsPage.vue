<script setup>
import { onMounted, ref } from 'vue';
import { http, formatApiError } from '../api/client';
import { useBusinessStore } from '../stores/business';
import PageHeader from '../components/PageHeader.vue';
import { BUSINESS_TEMPLATES, businessTemplate, businessTypeLabel } from '../utils/businessTypes';

const business = useBusinessStore();

const form = ref({ name: '', type: 'retail', phone: '', email: '', address: '', currency: 'IDR' });
const loading = ref(true);
const saving = ref(false);
const error = ref('');
const success = ref('');

function fillForm() {
    const current = business.business || {};

    form.value = {
        name: current.name || '',
        type: current.type || 'retail',
        phone: current.phone || '',
        email: current.email || '',
        address: current.address || '',
        currency: current.currency || 'IDR',
    };
}

async function save() {
    error.value = '';
    success.value = '';

    if (!form.value.name.trim()) {
        error.value = 'Nama usaha wajib diisi.';

        return;
    }

    saving.value = true;

    try {
        await business.saveBusiness({
            name: form.value.name,
            type: form.value.type,
            phone: form.value.phone || undefined,
            email: form.value.email || undefined,
            address: form.value.address || undefined,
            currency: form.value.currency,
        });

        success.value = 'Pengaturan bisnis berhasil disimpan.';
    } catch (err) {
        error.value = formatApiError(err, 'Gagal menyimpan pengaturan.');
    } finally {
        saving.value = false;
    }
}

onMounted(async () => {
    if (!business.business) {
        await business.fetchBusiness();
    }

    fillForm();
    loading.value = false;
});
</script>

<template>
    <div class="space-y-6">
        <PageHeader title="Pengaturan Bisnis" description="Informasi profil usaha yang tampil di seluruh aplikasi." />

        <div v-if="loading" class="flex items-center justify-center rounded-xl bg-white py-20">
            <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary-600 border-t-transparent"></div>
        </div>

        <div v-else class="max-w-2xl space-y-6">
            <p v-if="success" class="rounded-xl bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-700">{{ success }}</p>
            <p v-if="error" class="rounded-xl bg-red-50 px-5 py-4 text-sm text-red-600">{{ error }}</p>

            <form class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm" @submit.prevent="save">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Nama usaha *</label>
                        <input v-model="form.name" type="text" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none" placeholder="contoh: Kopi Tertial" />
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Jenis usaha</label>
                        <div class="mt-2 grid grid-cols-2 gap-2">
                            <button
                                v-for="type in Object.keys(BUSINESS_TEMPLATES)"
                                :key="type"
                                type="button"
                                class="flex items-center gap-2 rounded-lg border px-3 py-2.5 text-left text-sm transition"
                                :class="form.type === type ? 'border-primary-500 bg-primary-50 text-primary-800' : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                                @click="form.type = type"
                            >
                                <span class="text-base">{{ businessTemplate(type).emoji }}</span>
                                <span class="font-medium">{{ businessTypeLabel(type) }}</span>
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-gray-400">
                            Kategori produk mengikuti template: {{ businessTemplate(form.type).categories.join(', ') }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Telepon</label>
                        <input v-model="form.phone" type="text" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input v-model="form.email" type="email" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Alamat</label>
                        <input v-model="form.address" type="text" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Mata uang</label>
                        <select v-model="form.currency" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none">
                            <option value="IDR">IDR — Rupiah</option>
                            <option value="USD">USD — Dolar</option>
                            <option value="MYR">MYR — Ringgit</option>
                        </select>
                    </div>
                </div>

                <div class="pt-5">
                    <button type="submit" :disabled="saving" class="rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-60">
                        {{ saving ? 'Menyimpan…' : 'Simpan Pengaturan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>