<script setup>
import PageHeader from '../components/PageHeader.vue';
import PublicLayout from '../components/PublicLayout.vue';
</script>

<template>
    <PublicLayout>
    <div class="space-y-6">
        <PageHeader title="Kebijakan Privasi" description="Kami berkomitmen melindungi privasi data Anda." />

        <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6 shadow-sm max-w-3xl">
            <p class="text-gray-600 mb-4">Halaman ini sedang dalam pengembangan. Kebijakan privasi lengkap akan segera dipublikasikan.</p>
            <p class="text-gray-600">Kami berkomitmen melindungi data pribadi Anda sesuai dengan regulasi yang berlaku di Indonesia (UU PDP).</p>
        </div>
    </div>
</PublicLayout>
</template>
