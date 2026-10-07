<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: String, default: '—' },
    hint: { type: String, default: '' },
    trend: { type: String, default: '' },
    trendDirection: { type: String, default: 'up' },
    icon: { type: String, default: '' },
    accent: { type: Boolean, default: false },
});
</script>

<template>
    <div
        class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm"
        :class="accent ? 'border-primary-600 bg-primary-600 text-white' : ''"
    >
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium" :class="accent ? 'text-primary-100' : 'text-gray-500'">{{ label }}</p>
            <svg
                v-if="icon"
                viewBox="0 0 24 24"
                class="h-5 w-5"
                :class="accent ? 'text-white/80' : 'text-gray-400'"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                aria-hidden="true"
            >
                <template v-if="icon === 'wallet'">
                    <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"></path>
                    <path d="M3 5v14a2 2 0 0 0 2 2h16v-5"></path>
                    <path d="M18 12a2 2 0 0 0 0 4h4v-4Z"></path>
                </template>
                <template v-else-if="icon === 'cart'">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </template>
                <template v-else-if="icon === 'box'">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </template>
                <template v-else-if="icon === 'users'">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </template>
            </svg>
        </div>

        <p class="mt-3 text-2xl font-bold sm:text-3xl" :class="accent ? 'text-white' : 'text-gray-900'">
            {{ value }}
        </p>

        <div v-if="trend || hint" class="mt-2 flex items-center gap-2 text-xs">
            <span
                v-if="trend"
                class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 font-semibold"
                :class="accent
                    ? 'bg-white/15 text-white'
                    : trendDirection === 'up'
                        ? 'bg-emerald-50 text-emerald-600'
                        : 'bg-red-50 text-red-600'"
            >
                <svg v-if="trendDirection === 'up'" viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <polyline points="18 15 12 9 6 15"></polyline>
                </svg>
                <svg v-else viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
                {{ trend }}
            </span>
            <span v-if="hint" :class="accent ? 'text-primary-100' : 'text-gray-400'">{{ hint }}</span>
        </div>
    </div>
</template>