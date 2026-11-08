<script setup>
import PageHeader from '../components/PageHeader.vue';
import PublicLayout from '../components/PublicLayout.vue';
</script>

<template>
    <PublicLayout>
    <div class="space-y-6">
        <PageHeader title="Insight & Laporan" description="Business intelligence untuk keputusan yang lebih baik." />

        <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6 shadow-sm max-w-3xl">
            <p class="text-gray-600 mb-4">Halaman fitur ini sedang dalam pengembangan.</p>
            <p class="text-gray-600">Fitur insight mencakup: auto restock, prediksi permintaan, deteksi stok lambat, analisis margin, laporan keuangan, dan laporan performa produk.</p>
        </div>
    </div>
</PublicLayout>
</template>
