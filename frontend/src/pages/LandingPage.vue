<script setup>
import LandingHero from '../components/LandingHero.vue';
import FloatingNav from '../components/FloatingNav.vue';
import Footer from '../components/Footer.vue';
import { useAuthStore } from '../stores/auth';
import { useRouter, useRoute } from 'vue-router';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

if (auth.isAuthenticated && route.name === 'landing') {
  router.replace({ name: 'dashboard' });
}
</script>

<template>
    <div class="min-h-screen bg-white text-gray-900 antialiased">
        <FloatingNav />

        <main class="mx-auto max-w-7xl pt-24 pb-8 px-4 sm:pt-32 lg:pt-32">
            <LandingHero />

            <section id="harga" class="mx-auto mt-12 max-w-3xl sm:mt-16">
                <div class="rounded-3xl border border-gray-200 bg-white p-6 text-center shadow-sm sm:p-10">
                    <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">Kenapa memilih TokoKu?</h2>
                    <p class="mt-4 text-lg text-gray-500">Dirancang khusus untuk UMKM Indonesia yang ingin mengelola bisnis tanpa ribet.</p>

                    <ul class="mt-10 space-y-4 text-left max-w-md mx-auto">
                        <li class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-primary-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 16 16"><path d="M3 8.5l3.2 3.2L13 4.5"></path></svg>
                            <span class="text-sm text-gray-600"><strong class="text-gray-900">Satu input, semua ter-update</strong><br> transaksi langsung memperbarui stok, keuangan, pelanggan, dan laporan</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-primary-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 16 16"><path d="M3 8.5l3.2 3.2L13 4.5"></path></svg>
                            <span class="text-sm text-gray-600"><strong class="text-gray-900">Kelola pelanggan & CRM</strong><br> riwayat belanja, pelanggan tetap, leads, dan follow-up dalam satu tempat</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-primary-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 16 16"><path d="M3 8.5l3.2 3.2L13 4.5"></path></svg>
                            <span class="text-sm text-gray-600"><strong class="text-gray-900">Insight yang bisa ditindak</strong><br> auto restock, prediksi permintaan, deteksi stok lambat, dan analisis margin</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-primary-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 16 16"><path d="M3 8.5l3.2 3.2L13 4.5"></path></svg>
                            <span class="text-sm text-gray-600"><strong class="text-gray-900">Bisa dipakai offline</strong><br> tetap catat transaksi saat internet bermasalah, tersinkron otomatis saat kembali online</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-primary-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 16 16"><path d="M3 8.5l3.2 3.2L13 4.5"></path></svg>
                            <span class="text-sm text-gray-600"><strong class="text-gray-900">Mudah digunakan</strong><br> Tutorial yang mudah dipahami dijamin langsung bisa pakai!</span>
                        </li>
                    </ul>

                    <RouterLink
                        to="/register"
                        class="mt-10 inline-block rounded-xl bg-primary-600 px-8 py-3 font-semibold text-white shadow-lg shadow-primary-600/20 transition hover:bg-primary-700"
                    >
                        Coba Sekarang, Gratis
                    </RouterLink>
                </div>
            </section>

            <section id="cara-kerja" class="-mx-4 mt-12 border-y border-gray-200 bg-gray-50 px-4 sm:mt-16">
                <div class="mx-auto grid max-w-6xl gap-8 py-14 sm:grid-cols-3 sm:gap-10 sm:py-20">
                    <div class="flex gap-4">
                        <span class="text-4xl font-bold text-primary-200">01</span>
                        <div>
                            <h3 class="font-semibold text-gray-900">Catat transaksi</h3>
                            <p class="mt-1.5 text-sm text-gray-500">Input penjualan singkat tanpa perlu mengetik ulang laporan. Repeat order untuk pelanggan langganan.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <span class="text-4xl font-bold text-primary-200">02</span>
                        <div>
                            <h3 class="font-semibold text-gray-900">TokoKu memproses</h3>
                            <p class="mt-1.5 text-sm text-gray-500">Stok, keuangan, riwayat pelanggan, dan laporan diperbarui otomatis dari data transaksi.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <span class="text-4xl font-bold text-primary-200">03</span>
                        <div>
                            <h3 class="font-semibold text-gray-900">Ambil keputusan</h3>
                            <p class="mt-1.5 text-sm text-gray-500">Terima rekomendasi: apa yang harus di-restock, pelanggan mana yang perlu di-follow-up, dan masalah yang perlu diperhatikan hari ini.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="cta" class="mx-auto max-w-6xl py-14 text-center sm:py-20">
                <img
                    :src="'/logo.png'"
                    alt="TokoKu"
                    class="mx-auto mb-6 h-20 w-20 object-contain"
                >
                <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">Siap mengelola bisnismu lebih mudah?</h2>
                <p class="mx-auto mt-4 max-w-xl text-lg text-gray-500">Satu platform untuk operasional, pelanggan, keuangan, dan keputusan bisnis. Gratis untuk memulai.</p>
                <RouterLink
                    to="/register"
                    class="mt-8 inline-block rounded-xl bg-primary-600 px-8 py-3 font-semibold text-white shadow-lg shadow-primary-600/20 transition hover:bg-primary-700"
                >
                    Daftar TokoKu Gratis
                </RouterLink>
            </section>
        </main>

        <Footer />
    </div>
</template>