<script setup>
import { computed, onMounted, ref } from 'vue';
import { http, formatApiError } from '../api/client';
import PageHeader from '../components/PageHeader.vue';
import Modal from '../components/Modal.vue';
import { formatRupiah, formatDateTime, segmentLabel, segmentClasses, leadStageLabel, leadStageClasses, whatsappLink } from '../utils/format';

const loading = ref(true);
const error = ref('');
const data = ref({ leads: { overdue: [], today: [], upcoming: [] }, customers: [], counts: {} });
const updating = ref(false);

const showReschedule = ref(false);
const rescheduleLead = ref(null);
const rescheduleForm = ref({ next_follow_up_at: '' });
const rescheduling = ref(false);
const rescheduleError = ref('');

const groups = computed(() => [
    { key: 'overdue', label: 'Terlambat', classes: 'border-red-200 bg-red-50/60', items: data.value.leads.overdue },
    { key: 'today', label: 'Hari ini', classes: 'border-amber-200 bg-amber-50/60', items: data.value.leads.today },
    { key: 'upcoming', label: '7 hari ke depan', classes: 'border-gray-200 bg-white', items: data.value.leads.upcoming },
]);

const hasAny = computed(
    () => (data.value.counts.overdue || 0) + (data.value.counts.today || 0) + (data.value.counts.upcoming || 0) + data.value.customers.length > 0,
);

async function load() {
    loading.value = true;
    error.value = '';

    try {
        const { data: response } = await http.get('/leads/follow-ups');
        data.value = response.data;
    } catch (err) {
        error.value = formatApiError(err, 'Gagal memuat data follow-up.');
    } finally {
        loading.value = false;
    }
}

async function markDone(lead) {
    updating.value = true;

    try {
        await http.patch(`/leads/${lead.id}`, { next_follow_up_at: null });
        await load();
    } catch (err) {
        error.value = formatApiError(err, 'Gagal menyelesaikan follow-up.');
    } finally {
        updating.value = false;
    }
}

function startReschedule(lead) {
    rescheduleLead.value = lead;
    rescheduleForm.value = { next_follow_up_at: lead.next_follow_up_at ? lead.next_follow_up_at.slice(0, 16) : '' };
    rescheduleError.value = '';
    showReschedule.value = true;
}

async function submitReschedule() {
    rescheduleError.value = '';

    if (!rescheduleForm.value.next_follow_up_at) {
        rescheduleError.value = 'Pilih tanggal follow-up.';

        return;
    }

    rescheduling.value = true;

    try {
        await http.patch(`/leads/${rescheduleLead.value.id}`, { next_follow_up_at: rescheduleForm.value.next_follow_up_at });
        showReschedule.value = false;
        await load();
    } catch (err) {
        rescheduleError.value = formatApiError(err, 'Gagal menjadwalkan follow-up.');
    } finally {
        rescheduling.value = false;
    }
}

function leadMessage(lead) {
    return `Halo ${lead.name}, kami ingin menindaklanjuti. Kapan waktu yang tepat untuk kami hubungi?`;
}

function customerMessage(customer) {
    return `Halo ${customer.name}, sudah lama tidak berbelanja. Ada promo khusus untuk Anda, mau kami bantu pesan lagi?`;
}

onMounted(load);
</script>

<template>
    <div class="space-y-6">
        <PageHeader title="Follow-up" description="Jadwal tindak lanjut lead dan pelanggan yang mulai pasif.">
            <template #actions>
                <button type="button" class="text-sm font-semibold text-primary-600 hover:text-primary-700" @click="load">Muat ulang</button>
            </template>
        </PageHeader>

        <p v-if="error" class="rounded-xl bg-red-50 px-5 py-4 text-sm text-red-600">{{ error }}</p>

        <div v-if="loading" class="flex items-center justify-center rounded-xl bg-white py-20">
            <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary-600 border-t-transparent"></div>
        </div>

        <template v-else>
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div class="rounded-xl border border-red-100 bg-red-50/70 px-5 py-4">
                    <p class="text-sm text-red-600">Terlambat</p>
                    <p class="mt-1 text-2xl font-bold text-red-700">{{ data.counts.overdue || 0 }}</p>
                </div>
                <div class="rounded-xl border border-amber-100 bg-amber-50/70 px-5 py-4">
                    <p class="text-sm text-amber-600">Hari ini</p>
                    <p class="mt-1 text-2xl font-bold text-amber-700">{{ data.counts.today || 0 }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white px-5 py-4">
                    <p class="text-sm text-gray-500">7 hari ke depan</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ data.counts.upcoming || 0 }}</p>
                </div>
                <div class="rounded-xl border border-primary-100 bg-primary-50/70 px-5 py-4">
                    <p class="text-sm text-primary-600">Pelanggan pasif</p>
                    <p class="mt-1 text-2xl font-bold text-primary-700">{{ data.counts.customers || 0 }}</p>
                </div>
            </div>

            <p v-if="!hasAny" class="rounded-xl bg-white px-5 py-12 text-center text-sm text-gray-500">
                Tidak ada follow-up yang perlu dilakukan. Kerja bagus!
            </p>

            <section v-for="group in groups" :key="group.key" class="rounded-xl border p-5" :class="group.classes">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900">{{ group.label }}</h3>
                    <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600">{{ group.items.length }}</span>
                </div>

                <p v-if="!group.items.length" class="text-sm text-gray-400">Tidak ada.</p>

                <div v-else class="space-y-2">
                    <div v-for="lead in group.items" :key="lead.id" class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-gray-200 bg-white px-4 py-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-semibold text-gray-900">{{ lead.name }}</p>
                                <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold" :class="leadStageClasses(lead.stage)">{{ leadStageLabel(lead.stage) }}</span>
                            </div>
                            <p class="mt-0.5 text-xs text-gray-500">
                                <span v-if="lead.phone">{{ lead.phone }} · </span>Jadwal {{ formatDateTime(lead.next_follow_up_at) }}
                            </p>
                            <p v-if="lead.notes" class="mt-0.5 text-xs italic text-gray-400">{{ lead.notes }}</p>
                        </div>

                        <div class="flex items-center gap-2">
                            <a
                                v-if="whatsappLink(lead.phone, leadMessage(lead))"
                                :href="whatsappLink(lead.phone, leadMessage(lead))"
                                target="_blank"
                                rel="noopener"
                                class="rounded-lg border border-emerald-200 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50"
                            >
                                WhatsApp
                            </a>
                            <button
                                type="button"
                                :disabled="updating"
                                class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 disabled:opacity-50"
                                @click="startReschedule(lead)"
                            >
                                Jadwalkan ulang
                            </button>
                            <button
                                type="button"
                                :disabled="updating"
                                class="rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-primary-700 disabled:opacity-50"
                                @click="markDone(lead)"
                            >
                                Selesai
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900">Pelanggan perlu perhatian</h3>
                    <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600">{{ data.customers.length }}</span>
                </div>

                <p v-if="!data.customers.length" class="text-sm text-gray-400">Semua pelanggan aktif.</p>

                <div v-else class="space-y-2">
                    <div v-for="customer in data.customers" :key="customer.id" class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-gray-200 px-4 py-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-semibold text-gray-900">{{ customer.name }}</p>
                                <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold" :class="segmentClasses(customer.segment)">{{ segmentLabel(customer.segment) }}</span>
                            </div>
                            <p class="mt-0.5 text-xs text-gray-500">
                                Terakhir belanja {{ customer.days_since }} hari lalu · {{ customer.order_count }}x · {{ formatRupiah(customer.total_spending) }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <a
                                v-if="customer.phone"
                                :href="whatsappLink(customer.phone, customerMessage(customer))"
                                target="_blank"
                                rel="noopener"
                                class="rounded-lg border border-emerald-200 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50"
                            >
                                WhatsApp
                            </a>
                            <RouterLink :to="{ name: 'sales-new', query: { customer_id: customer.id } }" class="rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-primary-700">
                                Buat penjualan
                            </RouterLink>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <Modal v-if="showReschedule" title="Jadwalkan ulang" @close="showReschedule = false">
            <form class="space-y-4" @submit.prevent="submitReschedule">
                <p class="text-sm text-gray-600">Lead: <span class="font-semibold text-gray-900">{{ rescheduleLead?.name }}</span></p>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tanggal &amp; jam</label>
                    <input v-model="rescheduleForm.next_follow_up_at" type="datetime-local" class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none" />
                </div>

                <p v-if="rescheduleError" class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600">{{ rescheduleError }}</p>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50" @click="showReschedule = false">Batal</button>
                    <button type="submit" :disabled="rescheduling" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-60">
                        {{ rescheduling ? 'Menyimpan…' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </Modal>
    </div>
</template>
