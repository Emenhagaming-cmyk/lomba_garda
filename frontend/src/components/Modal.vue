<script setup>
defineProps({
    title: { type: String, default: '' },
    maxWidth: { type: String, default: 'max-w-lg' },
});

const emit = defineEmits(['close']);
</script>

<template>
    <Teleport to="body">
        <div class="fixed inset-0 z-50 flex items-end justify-center sm:items-center" role="dialog" aria-modal="true" :aria-label="title">
            <div class="absolute inset-0 bg-gray-900/50" @click="emit('close')"></div>
            <div
                class="relative flex max-h-[90vh] w-full flex-col overflow-hidden rounded-t-2xl bg-white shadow-xl sm:m-4 sm:rounded-2xl"
                :class="maxWidth"
            >
                <div v-if="title" class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <h3 class="text-base font-bold text-gray-900">{{ title }}</h3>
                    <button
                        type="button"
                        class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                        aria-label="Tutup"
                        @click="emit('close')"
                    >
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6 6 18M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="overflow-y-auto px-5 py-5">
                    <slot />
                </div>
            </div>
        </div>
    </Teleport>
</template>