<script setup>
import { computed } from 'vue';

const props = defineProps({
    rating: { type: Number, default: 0 },
    size: { type: String, default: 'md' },
});

const sizeClasses = {
    sm: 'h-3.5 w-3.5',
    md: 'h-4 w-4',
    lg: 'h-5 w-5',
};

const starClass = computed(() => sizeClasses[props.size] || sizeClasses.md);
const percent = computed(() => {
    const value = Number(props.rating);

    if (!Number.isFinite(value)) {
        return 0;
    }

    return Math.min(100, Math.max(0, (value / 5) * 100));
});
</script>

<template>
    <span
        class="relative inline-flex shrink-0"
        role="img"
        :aria-label="`Rating ${rating} dari 5`"
    >
        <span class="flex gap-0.5 text-gray-200">
            <svg
                v-for="star in 5"
                :key="`bg-${star}`"
                :class="starClass"
                viewBox="0 0 20 20"
                fill="currentColor"
                aria-hidden="true"
            >
                <path d="M10 1.6l2.6 5.3 5.8.8-4.2 4.1 1 5.8L10 14.9l-5.2 2.7 1-5.8L1.6 7.7l5.8-.8z" />
            </svg>
        </span>
        <span
            class="absolute inset-y-0 left-0 flex gap-0.5 overflow-hidden text-amber-400"
            :style="{ width: `${percent}%` }"
        >
            <svg
                v-for="star in 5"
                :key="`fill-${star}`"
                :class="starClass"
                viewBox="0 0 20 20"
                fill="currentColor"
                aria-hidden="true"
            >
                <path d="M10 1.6l2.6 5.3 5.8.8-4.2 4.1 1 5.8L10 14.9l-5.2 2.7 1-5.8L1.6 7.7l5.8-.8z" />
            </svg>
        </span>
    </span>
</template>
