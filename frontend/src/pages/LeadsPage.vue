<script setup>
import { computed, onMounted, ref } from 'vue';
import { http, formatApiError } from '../api/client';
import PageHeader from '../components/PageHeader.vue';
import Modal from '../components/Modal.vue';
import { LEAD_STAGES, leadStageLabel, leadStageClasses, formatDate } from '../utils/format';

const leads = ref([]);
const loading = ref(true);
const error = ref('');
const showCreate = ref(false);
const creating = ref(false);
const createError = ref('');
const createForm = ref({ name: '', phone: '', email: '', source: '', stage: 'new', notes: '' });
const updating = ref(false);

const columns = computed(() =>
    LEAD_STAGES.map((stage) => ({
        ...stage,
        items: leads.value.filter((lead) => lead.stage === stage.value),
    })),
);

const counts = computed(() =>
    LEAD_STAGES.reduce((acc, stage) => {
        acc[stage.value] = leads.value.filter((lead) => lead.stage === stage.value).length;

        return acc;
    }, {}),
);

async function loadLeads() {
    loading.value = true;
    error.value = '';

    try {
        const { data } = await http.get('/leads');
        leads.value = data.data.leads;
    } catch (err) {
        error.value = formatApiError(err, 'Gagal memuat leads.');
    } finally {
        loading.value = false;
    }
}

function resetCreate() {
    createForm.value = { name: '', phone: '', email: '', source: '', stage: 'new', notes: '' };
    createError.value = '';
}

async function createLead() {
    createError.value = '';

    if (!createForm.value.name.trim()) {
        createError.value = 'Nama lead wajib diisi.';

        return;
    }

    creating.value = true;

    try {
        await http.post('/leads', createForm.value);
        showCreate.value = false;
        await loadLeads();
    } catch (err) {
        createError.value = formatApiError(err, 'Gagal menambah lead.');
    } finally {
        creating.value = false;
    }
}

function nextStage(stage) {
    const index = LEAD_STAGES.findIndex((item) => item.value === stage);

    if (index === -1) {
        return null;
    }

    return LEAD_STAGES[index + 1]?.value ?? null;
}

async function moveStage(lead, stage) {
    updating.value = true;

    try {
        await http.patch(`/leads/${lead.id}`, { stage });
        await loadLeads();
    } catch (err) {
        error.value = formatApiError(err, 'Gagal memperbarui stage.');
    } finally {
        updating.value = false;
    }
}

onMounted(loadLeads);
</script>

<template>
    <div class="space-y-6">
        <PageHeader title="Leads" description="Pipeline pelacakan prospek hingga menjadi pelanggan.">
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
                    Lead Baru
                </button>
            </template>
        </PageHeader>

        <p v-if="error" class="rounded-xl bg-red-50 px-5 py-4 text-sm text-red-600">{{ error }}</p>

        <div v-if="loading" class="flex items-center justify-center rounded-xl bg-white py-20">
            <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary-600 border-t-transparent"></div>
        </div>

        <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-3 xl:grid-cols-5">
            <div v-for="column in columns" :key="column.value" class="flex flex-col rounded-xl border border-gray-200 bg-gray-50/80 p-3">
                <div class="mb-3 flex items-center justify-between px-1">
                    <span class="rounded-full px-2.5 py-1 text-xs font-bold" :class="leadStageClasses(column.value)">
                        {{ column.label }}
                    </span>
                    <span class="text-xs font-semibold text-gray-400">{{ counts[column.value] }}</span>
                </div>

                <div class="flex flex-1 flex-col gap-2">
                    <div v-for="lead in column.items" :key="lead.id" class="group rounded-lg border border-gray-200 bg-white p-3 shadow-sm">
                        <p class="text-sm font-semibold text-gray-900">{{ lead.name }}</p>
                        <div class="mt-1 space-y-0.5 text-xs text-gray-500">
                            <p v-if="lead.source">Sumber: {{ lead.source }}</p>
                            <p v-if="lead.phone">{{ lead.phone }}</p>
                            <p v-if="lead.next_follow_up_at">Follow-up: {{ formatDate(lead.next_follow_up_at) }}</p>
                            <p v-if="lead.notes" class="italic text-gray-400">{{ lead.notes }}</p>
                            <p v-if="lead.converted_customer_id" class="font-medium text-emerald-600">✓ Pelanggan dibuat otomatis</p>
                        </div>

                        <div class="mt-2.5 flex items-center gap-1">
                            <button
                                v-if="nextStage(column.value)"
                                type="button"
                                :disabled="updating"
                                class="flex-1 rounded-lg border border-primary-200 px-2 py-1 text-xs font-semibold text-primary-700 transition hover:bg-primary-50 disabled:opacity-50"
                                @click="moveStage(lead, nextStage(column.value))"
                            >
                                → {{ leadStageLabel(nextStage(column.value)) }}
                            </button>
                            <button
                                v-if="column.value !== 'converted'"
                                type="button"
                                :disabled="updating"
                                class="flex-1 rounded-lg bg-violet-50 px-2 py-1 text-xs font-semibold text-violet-700 transition hover:bg-violet-100 disabled:opacity-50"
                                @click="moveStage(lead, 'converted')"
                            >
                                Konversi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create modal -->
        <Modal v-if="showCreate" title="Lead Baru" @close="showCreate = false">
            <form class="space-y-4" @submit.prevent="createLead">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama *</label>
                    <input v-model="createForm.name" type="text" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none" />
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
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Sumber</label>
                        <input v-model="createForm.source" type="text" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none" placeholder="IG, walk-in, rekomendasi…" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Stage awal</label>
                        <select v-model="createForm.stage" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none">
                            <option v-for="stage in LEAD_STAGES" :key="stage.value" :value="stage.value">{{ stage.label }}</option>
                        </select>
                    </div>
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