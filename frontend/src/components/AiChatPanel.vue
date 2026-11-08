<script setup>
import { nextTick, ref } from 'vue';
import { formatApiError, http } from '../api/client';

const props = defineProps({
    period: {
        type: Object,
        default: () => ({}),
    },
});

const messages = ref([]);
const input = ref('');
const loading = ref(false);
const error = ref('');
const scrollBox = ref(null);

const quickQuestions = [
    'Berapa laba bulan ini?',
    'Kenapa penjualan turun?',
    'Produk apa yang perlu restock?',
    'Pelanggan mana yang berisiko hilang?',
    'Bagaimana performa leads saya?',
    'Ke mana pengeluaran terbesar?',
];

async function scrollToBottom() {
    await nextTick();

    if (scrollBox.value) {
        scrollBox.value.scrollTop = scrollBox.value.scrollHeight;
    }
}

async function send(text) {
    const content = (text ?? input.value).trim();

    if (!content || loading.value) {
        return;
    }

    error.value = '';
    messages.value.push({ role: 'user', content });
    input.value = '';
    loading.value = true;
    scrollToBottom();

    try {
        const payload = {
            messages: messages.value.map(({ role, content: chatContent }) => ({ role, content: chatContent })),
        };

        if (props.period?.from && props.period?.to) {
            payload.period = { from: props.period.from, to: props.period.to };
        }

        const { data } = await http.post('/reports/ai/chat', payload);
        messages.value.push({ role: 'assistant', content: data.data.reply });
    } catch (e) {
        error.value = formatApiError(e, 'Gagal mendapat jawaban AI.');
    } finally {
        loading.value = false;
        scrollToBottom();
    }
}

function analyzeCurrentPeriod() {
    const periodLabel = props.period?.from && props.period?.to
        ? 'periode '.concat(props.period.from, ' sampai ', props.period.to)
        : 'bulan berjalan';

    send('Analisa laporan keuangan '.concat(
        periodLabel,
        ': omzet, laba, produk, pelanggan, dan leads. Beri rekomendasi ke depan.',
    ));
}
</script>

<template>
    <aside class="flex flex-col rounded-2xl border border-gray-200 bg-white shadow-sm">
        <header class="flex items-center justify-between gap-2 border-b border-gray-200 px-4 py-3">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Fabesky</h3>
                <p class="text-xs text-gray-500">Tanya apa saja soal data bisnis kamu</p>
            </div>
            <span v-if="loading" class="text-xs text-primary-600">menganalisis…</span>
        </header>

        <div ref="scrollBox" class="max-h-[45vh] space-y-3 overflow-y-auto sm:max-h-[380px] px-4 py-3">
            <div v-if="!messages.length" class="space-y-3">
                <button
                    @click="analyzeCurrentPeriod"
                    class="w-full rounded-xl bg-primary-600 px-3 py-2 text-sm font-medium text-white transition hover:bg-primary-700"
                >
                    Analisis laporan saat ini
                </button>
                <div class="space-y-2">
                    <p class="text-xs font-medium text-gray-500">Pertanyaan cepat</p>
                    <button
                        v-for="question in quickQuestions"
                        :key="question"
                        @click="send(question)"
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-left text-xs text-gray-700 transition hover:border-primary-300 hover:bg-primary-50"
                    >
                        {{ question }}
                    </button>
                </div>
            </div>

            <template v-else>
                <div
                    v-for="(message, index) in messages"
                    :key="index"
                    class="flex"
                    :class="message.role === 'user' ? 'justify-end' : 'justify-start'"
                >
                    <p
                        class="max-w-[85%] whitespace-pre-line break-words rounded-2xl px-3 py-2 text-sm"
                        :class="message.role === 'user' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-900'"
                    >
                        {{ message.content }}
                    </p>
                </div>

                <p v-if="loading" class="text-xs text-gray-500">AI sedang menganalisis data…</p>
            </template>
        </div>

        <div class="space-y-2 border-t border-gray-200 px-4 py-3">
            <p v-if="error" class="rounded-lg bg-red-50 px-2 py-1 text-xs text-red-600">{{ error }}</p>

            <div v-if="messages.length" class="flex flex-wrap gap-1.5">
                <button
                    v-for="question in quickQuestions.slice(0, 3)"
                    :key="question"
                    @click="send(question)"
                    class="rounded-full border border-gray-200 px-2.5 py-2 text-[11px] text-gray-600 transition hover:border-primary-300 hover:text-primary-700"
                >
                    {{ question }}
                </button>
            </div>

            <div class="flex items-end gap-2">
                <textarea
                    v-model="input"
                    rows="1"
                    placeholder="Tanya soal penjualan, laba, leads…"
                    class="min-h-[40px] flex-1 resize-none rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    @keydown.enter.exact.prevent="send()"
                ></textarea>
                <button
                    @click="send()"
                    :disabled="loading || !input.trim()"
                    class="rounded-xl bg-primary-600 px-3 py-2 text-sm font-medium text-white transition hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Kirim
                </button>
            </div>
        </div>
    </aside>
</template>
