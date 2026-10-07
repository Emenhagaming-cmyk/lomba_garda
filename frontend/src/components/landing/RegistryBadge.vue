<template>
    <span
        class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-gray-50 px-4 py-1.5 text-xs font-medium text-gray-600"
    >
        <span class="h-1.5 w-1.5 rounded-full bg-primary-500"></span>
        {{ text }}
    </span>
</template>

<script setup>
defineProps({
    text: { type: String, required: true },
});
</script>
