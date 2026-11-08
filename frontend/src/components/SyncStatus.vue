<script setup>
import { useSyncStore } from '../stores/sync';
import { computed } from 'vue';

const sync = useSyncStore();

const statusClasses = computed(() => {
    const base = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium';
    const colors = {
        offline: 'bg-gray-100 text-gray-600',
        syncing: 'bg-blue-50 text-blue-700',
        pending: 'bg-yellow-50 text-yellow-700',
        error: 'bg-red-50 text-red-700',
        synced: 'bg-green-50 text-green-700'
    };
    return `${base} ${colors[sync.syncStatus]}`;
});

function formatTime(timestamp) {
    if (!timestamp) return '';
    const date = new Date(timestamp);
    return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}

const statusLabels = {
    offline: 'Offline',
    syncing: 'Menyinkronkan...',
    pending: 'Menunggu Sinkronisasi',
    error: 'Sinkronisasi Gagal',
    synced: 'Tersinkron'
};
</script>

<template>
    <div :class="statusClasses" role="status" aria-live="polite" :title="statusLabels[sync.syncStatus]">
        <svg v-if="sync.syncStatus === 'offline'" class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <svg v-else-if="sync.syncStatus === 'syncing'" class="h-3.5 w-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <svg v-else-if="sync.syncStatus === 'pending'" class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <svg v-else-if="sync.syncStatus === 'error'" class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <svg v-else class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="hidden sm:inline">{{ statusLabels[sync.syncStatus] }}</span>
        <span v-if="sync.lastSyncTime" class="hidden text-[10px] opacity-75 sm:inline">
            ({{ formatTime(sync.lastSyncTime) }})
        </span>
    </div>
</template>