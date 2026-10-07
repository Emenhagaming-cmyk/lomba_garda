<template>
    <section class="relative">
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 left-1/2 h-[34rem] w-[54rem] -translate-x-1/2 rounded-full bg-primary-100 blur-3xl"></div>
        </div>

        <div class="relative mx-auto grid max-w-6xl items-center gap-10 py-12 sm:py-16 lg:grid-cols-2 lg:gap-16 lg:py-24">
            <div class="text-left">
                <h1 class="text-4xl leading-tight font-bold text-gray-900 sm:text-5xl">
                    Satu aplikasi untuk <span class="text-primary-600">seluruh operasional</span>
                    bisnis UMKM-mu.
                </h1>

                <p class="mt-6 max-w-xl text-lg text-gray-500">
                    TokoKu menyatukan pencatatan pesanan, stok, pelanggan, dan keuangan. Cukup input transaksi satu kali — semuanya otomatis ter-update dan berubah menjadi rekomendasi yang bisa langsung kamu jalankan.
                </p>

                <div class="mt-8 flex flex-col items-stretch gap-3 sm:flex-row sm:items-center">
                    <RouterLink
                        to="/register"
                        class="w-full rounded-xl bg-primary-600 px-7 py-3 text-center font-semibold text-white shadow-lg shadow-primary-600/20 transition hover:bg-primary-700 sm:w-auto"
                    >
                        Mulai Gratis
                    </RouterLink>
                    <RouterLink
                        to="/login"
                        class="w-full rounded-xl border border-gray-300 px-7 py-3 text-center font-semibold text-gray-700 transition hover:border-gray-400 hover:bg-gray-50 sm:w-auto"
                    >
                        Masuk
                    </RouterLink>
                </div>

                <p class="mt-5 text-sm text-gray-400">
                    Tanpa kartu kredit dan Bisa dipakai secara offline.
                </p>
            </div>

            <div class="relative">
                <ModelViewer src="/models/toystore-storefront.glb" label="toko TokoKu" />
            </div>
        </div>
    </section>
</template>

<script setup>
import { defineAsyncComponent } from 'vue';
import { RouterLink } from 'vue-router';

const ModelViewer = defineAsyncComponent(() => import('./landing/ModelViewer.vue'));
</script>
