<script setup>
defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
});
</script>

<template>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900 sm:text-2xl">{{ title }}</h2>
            <p v-if="description" class="mt-1 text-sm text-gray-500">{{ description }}</p>
        </div>
        <slot name="actions" />
    </div>
</template>