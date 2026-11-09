<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { http } from '../api/client';
import { useBusinessStore } from '../stores/business';

const router = useRouter();
const business = useBusinessStore();

const wrapper = ref(null);
const open = ref(false);
const loading = ref(false);
const error = ref('');
const items = ref([]);
const readIds = ref([]);
let lastFetched = 0;

const storageKey = computed(() => `tokoku.notif.read.${business.business?.id ?? 'default'}`);
const unreadCount = computed(() => items.value.filter((item) => !readIds.value.includes(item.id)).length);
const badge = computed(() => (unreadCount.value > 9 ? '9+' : String(unreadCount.value)));

const severityStyles = {
    warning: 'bg-amber-50 text-amber-600',
    info: 'bg-blue-50 text-blue-600',
    success: 'bg-emerald-50 text-emerald-600',
};

function loadRead() {
    try {
        const raw = localStorage.getItem(storageKey.value);
        const parsed = raw ? JSON.parse(raw) : [];
        readIds.value = Array.isArray(parsed) ? parsed : [];
    } catch {
        readIds.value = [];
    }
}

function persistRead() {
    try {
        localStorage.setItem(storageKey.value, JSON.stringify(readIds.value));
    } catch {
        // storage penuh / diblokir: read state cukup di memori
    }
}

async function fetchNotifications(force = false) {
    if (!force && Date.now() - lastFetched < 30000 && items.value.length > 0) {
        return;
    }

    loading.value = true;
    error.value = '';

    try {
        const { data } = await http.get('/notifications');
        items.value = data.data?.notifications ?? [];
        lastFetched = Date.now();
    } catch {
        error.value = 'Gagal memuat notifikasi.';
    } finally {
        loading.value = false;
    }
}

function toggle() {
    open.value = !open.value;

    if (open.value) {
        fetchNotifications();
    }
}

function markRead(id) {
    if (readIds.value.includes(id)) {
        return;
    }

    readIds.value = [...readIds.value, id];
    persistRead();
}

function markAllRead() {
    readIds.value = items.value.map((item) => item.id);
    persistRead();
}

function openItem(item) {
    markRead(item.id);
    open.value = false;
    router.push(item.action_url);
}

function onDocumentClick(event) {
    if (open.value && wrapper.value && !wrapper.value.contains(event.target)) {
        open.value = false;
    }
}

function onKeydown(event) {
    if (event.key === 'Escape') {
        open.value = false;
    }
}

onMounted(() => {
    loadRead();
    fetchNotifications(true);
    document.addEventListener('click', onDocumentClick);
    document.addEventListener('keydown', onKeydown);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', onDocumentClick);
    document.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <div ref="wrapper" class="relative">
        <button
            type="button"
            class="relative rounded-lg p-2 text-gray-500 transition hover:bg-gray-100"
            aria-label="Notifikasi"
            aria-haspopup="true"
            :aria-expanded="open"
            @click="toggle"
        >
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
            <span
                v-if="unreadCount > 0"
                class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold leading-none text-white"
            >
                {{ badge }}
            </span>
        </button>

        <div
            v-if="open"
            class="absolute right-0 top-full z-50 mt-2 w-80 max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl"
        >
            <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-4 py-3">
                <div class="flex items-center gap-2">
                    <p class="text-sm font-bold text-gray-900">Notifikasi</p>
                    <span
                        v-if="unreadCount > 0"
                        class="rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-semibold text-red-600"
                    >
                        {{ unreadCount }} baru
                    </span>
                </div>
                <button
                    v-if="unreadCount > 0"
                    type="button"
                    class="text-[11px] font-semibold text-primary-600 hover:text-primary-700"
                    @click="markAllRead"
                >
                    Tandai semua dibaca
                </button>
            </div>

            <div class="max-h-[70vh] overflow-y-auto">
                <div v-if="loading && items.length === 0" class="space-y-3 px-4 py-4">
                    <div v-for="n in 3" :key="n" class="flex animate-pulse items-start gap-3">
                        <div class="h-8 w-8 shrink-0 rounded-full bg-gray-100"></div>
                        <div class="flex-1 space-y-2">
                            <div class="h-3 w-3/4 rounded bg-gray-100"></div>
                            <div class="h-2.5 w-full rounded bg-gray-100"></div>
                        </div>
                    </div>
                </div>

                <p v-else-if="error" class="px-4 py-6 text-center text-xs text-red-500">{{ error }}</p>

                <div v-else-if="items.length === 0" class="px-4 py-8 text-center">
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                    </div>
                    <p class="mt-2 text-sm font-semibold text-gray-700">Belum ada notifikasi</p>
                    <p class="mt-0.5 text-xs text-gray-400">Kabar stok, follow-up, dan pesan akan muncul di sini.</p>
                </div>

                <template v-else>
                    <button
                        v-for="item in items"
                        :key="item.id"
                        type="button"
                        class="flex w-full items-start gap-3 border-b border-gray-50 px-4 py-3 text-left transition last:border-0 hover:bg-gray-50"
                        @click="openItem(item)"
                    >
                        <span
                            class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full"
                            :class="severityStyles[item.severity] || severityStyles.info"
                        >
                            <svg
                                v-if="item.severity === 'warning'"
                                viewBox="0 0 24 24"
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M12 9v4"></path>
                                <path d="M12 17h.01"></path>
                                <path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"></path>
                            </svg>
                            <svg
                                v-else-if="item.severity === 'success'"
                                viewBox="0 0 24 24"
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <circle cx="12" cy="12" r="9"></circle>
                                <path d="m8.5 12 2.5 2.5 4.5-5"></path>
                            </svg>
                            <svg v-else viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="9"></circle>
                                <path d="M12 11v5"></path>
                                <path d="M12 8h.01"></path>
                            </svg>
                        </span>

                        <span class="min-w-0 flex-1">
                            <span class="flex items-center gap-1.5">
                                <span class="truncate text-sm font-semibold text-gray-900">{{ item.title }}</span>
                                <span
                                    v-if="!readIds.includes(item.id)"
                                    class="h-1.5 w-1.5 shrink-0 rounded-full bg-primary-600"
                                ></span>
                            </span>
                            <span class="mt-0.5 line-clamp-2 block text-xs leading-relaxed text-gray-500">
                                {{ item.message }}
                            </span>
                        </span>
                    </button>
                </template>
            </div>
        </div>
    </div>
</template>
