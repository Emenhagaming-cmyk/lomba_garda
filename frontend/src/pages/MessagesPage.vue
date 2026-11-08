<script setup>
import { computed, nextTick, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { http, formatApiError } from '../api/client';
import PageHeader from '../components/PageHeader.vue';
import { avatarClass, initials } from '../utils/avatar';

const route = useRoute();
const router = useRouter();

const threads = ref([]);
const messages = ref([]);
const selectedId = ref(null);
const search = ref('');
const draft = ref('');
const loadingThreads = ref(true);
const loadingMessages = ref(false);
const sending = ref(false);
const error = ref('');
const scroller = ref(null);

const selectedThread = computed(() => threads.value.find((thread) => thread.id === selectedId.value) ?? null);
const unreadTotal = computed(() => threads.value.reduce((total, thread) => total + thread.unread_count, 0));
const filteredThreads = computed(() => {
    const keyword = search.value.trim().toLowerCase();

    if (!keyword) {
        return threads.value;
    }

    return threads.value.filter((thread) =>
        thread.subject.toLowerCase().includes(keyword)
        || thread.shop.name.toLowerCase().includes(keyword)
        || (thread.last_message ?? '').toLowerCase().includes(keyword));
});

function timeLabel(iso) {
    if (!iso) {
        return '';
    }

    return new Date(iso).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}

function listTime(iso) {
    if (!iso) {
        return '';
    }

    const date = new Date(iso);

    if (date.toDateString() === new Date().toDateString()) {
        return timeLabel(iso);
    }

    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
}

async function scrollToBottom() {
    await nextTick();
    scroller.value?.scrollTo({ top: scroller.value.scrollHeight });
}

async function loadThreads() {
    loadingThreads.value = true;
    error.value = '';

    try {
        const { data } = await http.get('/threads');
        threads.value = data.data.threads;

        const fromQuery = Number(route.query.thread);
        const target = threads.value.find((thread) => thread.id === fromQuery) ?? threads.value[0];

        if (target) {
            await openThread(target);
        }
    } catch (err) {
        error.value = formatApiError(err, 'Gagal memuat pesan.');
    } finally {
        loadingThreads.value = false;
    }
}

async function openThread(thread) {
    selectedId.value = thread.id;
    loadingMessages.value = true;
    messages.value = [];
    router.replace({ query: { thread: thread.id } });

    try {
        const { data } = await http.get(`/threads/${thread.id}/messages`);
        messages.value = data.data.messages;
        thread.unread_count = 0;
        await scrollToBottom();
    } catch (err) {
        error.value = formatApiError(err, 'Gagal memuat percakapan.');
    } finally {
        loadingMessages.value = false;
    }
}

function closeThread() {
    selectedId.value = null;
    messages.value = [];
    router.replace({ query: {} });
}

async function send() {
    const body = draft.value.trim();

    if (!body || !selectedId.value || sending.value) {
        return;
    }

    sending.value = true;

    try {
        const { data } = await http.post(`/threads/${selectedId.value}/messages`, { message: body });
        messages.value.push(data.data.message);
        draft.value = '';

        const thread = selectedThread.value;

        if (thread) {
            thread.last_message = body;
            thread.last_sender = 'buyer';
            thread.last_message_at = data.data.message.created_at;
        }

        await scrollToBottom();
    } catch (err) {
        error.value = formatApiError(err, 'Gagal mengirim pesan.');
    } finally {
        sending.value = false;
    }
}

onMounted(loadThreads);
</script>

<template>
    <div class="space-y-5">
        <PageHeader
            title="Pesan"
            description="Balasan pemasok dan percakapan pemesanan ada di sini."
        />

        <p v-if="error" class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-600">{{ error }}</p>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <div class="grid h-[calc(100vh-13rem)] min-h-[480px] lg:grid-cols-[22rem_minmax(0,1fr)]">
                <div :class="selectedId ? 'hidden lg:flex' : 'flex'" class="min-h-0 flex-col border-r border-gray-200">
                    <div class="space-y-3 border-b border-gray-100 p-4">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-bold text-gray-900">Inbox</p>
                            <span
                                v-if="unreadTotal"
                                class="rounded-full bg-primary-600 px-2 py-0.5 text-[11px] font-semibold text-white"
                            >
                                {{ unreadTotal }}
                            </span>
                            <span class="ml-auto text-gray-400" aria-hidden="true">
                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 6h16M7 12h13M10 18h10"></path>
                                </svg>
                            </span>
                        </div>

                        <input
                            v-model="search"
                            type="search"
                            placeholder="Cari percakapan"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none"
                        >
                    </div>

                    <div class="min-h-0 flex-1 overflow-y-auto">
                        <template v-if="loadingThreads">
                            <div v-for="n in 4" :key="n" class="space-y-2 border-b border-gray-100 p-4">
                                <div class="h-3 w-1/3 animate-pulse rounded bg-gray-100"></div>
                                <div class="h-3 w-2/3 animate-pulse rounded bg-gray-100"></div>
                            </div>
                        </template>

                        <template v-else-if="filteredThreads.length">
                            <button
                                v-for="thread in filteredThreads"
                                :key="thread.id"
                                type="button"
                                class="flex w-full items-start gap-3 border-b border-gray-100 p-4 text-left transition"
                                :class="thread.id === selectedId ? 'bg-primary-50/70' : 'hover:bg-gray-50'"
                                @click="openThread(thread)"
                            >
                                <span
                                    class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-semibold text-white"
                                    :class="avatarClass(thread.shop.name)"
                                >
                                    {{ initials(thread.shop.name) }}
                                </span>

                                <span class="min-w-0 flex-1">
                                    <span class="flex items-center gap-2">
                                        <span class="truncate text-sm font-semibold text-gray-900">{{ thread.shop.name }}</span>
                                        <span class="ml-auto shrink-0 text-[11px] text-gray-400">{{ listTime(thread.last_message_at) }}</span>
                                    </span>
                                    <span class="mt-0.5 flex items-center gap-2">
                                        <span class="truncate text-xs font-medium text-gray-700">{{ thread.subject }}</span>
                                        <span
                                            v-if="thread.unread_count"
                                            class="ml-auto h-2 w-2 shrink-0 rounded-full bg-primary-600"
                                            :aria-label="`${thread.unread_count} pesan belum dibaca`"
                                        ></span>
                                    </span>
                                    <span class="mt-0.5 block truncate text-[11px] text-gray-500">
                                        {{ thread.last_sender === 'buyer' ? 'Kamu: ' : '' }}{{ thread.last_message }}
                                    </span>
                                </span>
                            </button>
                        </template>

                        <div v-else class="px-4 py-12 text-center">
                            <p class="text-sm font-medium text-gray-700">Belum ada percakapan</p>
                            <p class="mt-1 text-xs text-gray-400">Mulai pemesanan dari halaman Pemasok.</p>
                        </div>
                    </div>
                </div>

                <div :class="selectedId ? 'flex' : 'hidden lg:flex'" class="min-h-0 flex-col">
                    <template v-if="selectedThread">
                        <div class="flex items-center gap-3 border-b border-gray-100 px-4 py-3">
                            <button
                                type="button"
                                class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-50 lg:hidden"
                                aria-label="Kembali ke daftar pesan"
                                @click="closeThread"
                            >
                                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path d="M15 18l-6-6 6-6"></path>
                                </svg>
                            </button>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-gray-900">{{ selectedThread.subject }}</p>
                                <p class="truncate text-xs text-gray-500">{{ selectedThread.shop.name }} · {{ selectedThread.shop.city }}</p>
                            </div>
                            <div class="ml-auto flex items-center gap-1 text-gray-400">
                                <span class="rounded-lg p-1.5" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M21 8v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8"></path>
                                        <path d="M1 3h22v5H1z"></path>
                                    </svg>
                                </span>
                                <span class="rounded-lg p-1.5" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <circle cx="12" cy="12" r="9"></circle>
                                        <path d="M12 7v5l3 3"></path>
                                    </svg>
                                </span>
                                <span class="rounded-lg p-1.5" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <circle cx="5" cy="12" r="1"></circle>
                                        <circle cx="12" cy="12" r="1"></circle>
                                        <circle cx="19" cy="12" r="1"></circle>
                                    </svg>
                                </span>
                            </div>
                        </div>

                        <div ref="scroller" class="min-h-0 flex-1 space-y-3 overflow-y-auto bg-gray-50/60 p-4">
                            <template v-if="loadingMessages">
                                <div class="h-12 w-2/3 animate-pulse rounded-2xl bg-white"></div>
                                <div class="ml-auto h-12 w-1/2 animate-pulse rounded-2xl bg-gray-200"></div>
                            </template>

                            <template v-else>
                                <div
                                    v-for="message in messages"
                                    :key="message.id"
                                    class="flex"
                                    :class="message.sender_type === 'buyer' ? 'justify-end' : 'justify-start'"
                                >
                                    <div class="max-w-[80%]">
                                        <p
                                            class="whitespace-pre-line break-words rounded-2xl px-3 py-2 text-sm"
                                            :class="message.sender_type === 'buyer'
                                                ? 'rounded-br-sm bg-neutral-900 text-white'
                                                : 'rounded-bl-sm bg-white text-gray-900 ring-1 ring-gray-200'"
                                        >
                                            {{ message.body }}
                                        </p>
                                        <p
                                            class="mt-1 text-[10px] text-gray-400"
                                            :class="message.sender_type === 'buyer' ? 'text-right' : 'text-left'"
                                        >
                                            {{ message.sender_type === 'buyer' ? 'Kamu' : message.sender_name }} · {{ timeLabel(message.created_at) }}
                                        </p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="border-t border-gray-100 p-3">
                            <div class="flex items-end gap-2">
                                <textarea
                                    v-model="draft"
                                    rows="2"
                                    placeholder="Tulis balasan..."
                                    class="min-h-[2.75rem] flex-1 resize-none rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none"
                                    @keydown.enter.exact.prevent="send"
                                ></textarea>
                                <button
                                    type="button"
                                    class="rounded-xl bg-neutral-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-neutral-800 disabled:opacity-60"
                                    :disabled="sending || !draft.trim()"
                                    @click="send"
                                >
                                    {{ sending ? '...' : 'Kirim' }}
                                </button>
                            </div>
                        </div>
                    </template>

                    <div v-else class="flex flex-1 flex-col items-center justify-center px-6 text-center">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400" aria-hidden="true">
                            <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                        </span>
                        <p class="mt-3 text-sm font-medium text-gray-700">Pilih percakapan</p>
                        <p class="mt-1 max-w-xs text-xs text-gray-400">Pesan pemasok akan muncul di sini begitu kamu memilih salah satu percakapan.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
