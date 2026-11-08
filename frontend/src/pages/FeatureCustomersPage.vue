<script setup>
import PageHeader from '../components/PageHeader.vue';
import PublicLayout from '../components/PublicLayout.vue';
</script>

<template>
    <PublicLayout>
    <div class="space-y-6">
        <PageHeader title="Pelanggan" description="Manajemen pelanggan dan CRM terintegrasi." />

        <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6 shadow-sm max-w-3xl">
            <p class="text-gray-600 mb-4">Halaman fitur ini sedang dalam pengembangan.</p>
            <p class="text-gray-600">Fitur pelanggan mencakup: profil pelanggan, riwayat belanja, segmentasi otomatis (VIP/Loyal/Baru/Melemah/Tidak Aktif), dan follow-up reminder.</p>
        </div>
    </div>
</PublicLayout>
</template>
