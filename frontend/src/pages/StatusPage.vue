<script setup>
import PageHeader from '../components/PageHeader.vue';
</script>

<template>
    <div class="space-y-6">
        <PageHeader title="Status Sistem" description="Monitor status layanan TokoKu secara real-time." />

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm max-w-3xl">
            <p class="text-gray-600 mb-4">Halaman ini sedang dalam pengembangan.</p>
            <p class="text-gray-600">Status uptime, insiden, dan pemeliharaan rutin akan ditampilkan di sini.</p>
            <div class="mt-6 flex items-center gap-3 rounded-lg bg-emerald-50 p-4">
                <span class="flex h-3 w-3 rounded-full bg-emerald-500"></span>
                <span class="text-sm font-medium text-emerald-700">Semua sistem beroperasi normal</span>
            </div>
        </div>
    </div>
</template>