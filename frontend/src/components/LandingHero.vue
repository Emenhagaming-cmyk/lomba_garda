<template>
    <section class="relative">
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 overflow-hidden">
            <!-- <div class="absolute -top-40 left-1/2 h-[26rem] w-[36rem] -translate-x-1/2 rounded-full bg-primary-100 blur-3xl sm:h-[34rem] sm:w-[54rem]"></div> -->
        </div>

        <div class="relative mx-auto grid max-w-6xl items-center gap-10 pb-14 sm:pb-16 lg:grid-cols-2 lg:gap-16 lg:pb-24">
            <div class="text-center lg:text-left">
                <h1 class="text-3xl leading-tight font-bold text-gray-900 sm:text-4xl lg:text-5xl">
                    Satu aplikasi untuk <span class="text-primary-600">seluruh operasional</span>
                    bisnis <b>UMKM</b> anda.
                </h1>

                <!-- <p class="mx-auto mt-5 max-w-xl text-base text-gray-500 sm:text-lg lg:mx-0 lg:mt-6">
                   di TokoKu menyatukan pencatatan pesanan, stok, pelanggan dan keuangan. Cukup input transaksi satu kali semuanya otomatis ter-update.
                </p> -->

                <div class="mt-8 flex flex-col items-stretch gap-3 sm:flex-row sm:items-center sm:justify-center lg:justify-start">
                    <RouterLink
                        to="/register"
                        class="w-full rounded-xl bg-primary-600 px-7 py-3 text-center font-semibold text-white shadow-lg shadow-primary-600/20 transition hover:bg-primary-700 sm:w-auto"
                    >
                        Daftar Gratis
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

                <div class="mt-12 flex justify-center lg:hidden">
                    <AppMockCard class="w-full max-w-md rotate-1 shadow-2xl shadow-gray-200" />
                </div>
            </div>

            <div v-if="isDesktop" class="relative">
                <ModelViewer src="/models/toystore-storefront.glb" label="toko TokoKu" />
            </div>
        </div>
    </section>
</template>

<script setup>
import { defineAsyncComponent, onMounted, onUnmounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import AppMockCard from './landing/AppMockCard.vue';

const ModelViewer = defineAsyncComponent(() => import('./landing/ModelViewer.vue'));

const QUERY = '(min-width: 1024px)';

const isDesktop = ref(typeof window !== 'undefined' && window.matchMedia(QUERY).matches);

let media;
function update() {
    isDesktop.value = media.matches;
}

onMounted(() => {
    media = window.matchMedia(QUERY);
    isDesktop.value = media.matches;
    media.addEventListener('change', update);
});

onUnmounted(() => {
    media?.removeEventListener('change', update);
});
</script>
