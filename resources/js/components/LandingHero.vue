<template>
    <section class="relative">
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 left-1/2 h-[34rem] w-[54rem] -translate-x-1/2 rounded-full bg-emerald-100 blur-3xl"></div>
        </div>

        <div class="relative mx-auto max-w-6xl px-4 py-20 sm:py-24 lg:pt-32 lg:pb-40">
            <div class="relative mx-auto max-w-3xl text-center">
                <RegistryBadge
                    text="Predictive Operations untuk UMKM"
                    class="relative"
                />

                <h1 class="relative mt-6 text-4xl leading-tight font-bold text-gray-900 sm:text-5xl lg:text-6xl">
                    Kelola stok, <span class="text-emerald-600">prediksi</span> permintaan,
                    <br class="hidden sm:block" />
                    beli <span class="text-emerald-600">tepat waktu</span>.
                </h1>

                <p class="relative mx-auto mt-6 max-w-xl text-lg text-gray-500">
                    BelanjaYuk! membaca penjualanmu, memprediksi kebutuhan minggu depan, lalu memberi rekomendasi pembelian — jadi kamu tidak pernah kekurangan stok saat ramai, atau menumpuk saat sepi.
                </p>

                <div class="relative mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <RouterLink
                        to="/register"
                        class="w-full rounded-xl bg-emerald-600 px-7 py-3 text-center font-semibold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-500 sm:w-auto"
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

                <p class="relative mt-6 text-sm text-gray-400">
                    Tanpa kartu kredit · Reset data otomatis · Siap untuk skala produksi
                </p>

                <div class="mt-16 flex justify-center xl:hidden">
                    <AppMockCard class="w-full max-w-md rotate-1 shadow-2xl shadow-gray-200" />
                </div>
            </div>

            <div
                v-if="showFloating"
                aria-hidden="true"
                class="pointer-events-none absolute inset-0 hidden select-none xl:block"
            >
                <div class="absolute top-16 -left-40 -rotate-6">
                    <div class="anim-float">
                        <StockCard />
                    </div>
                </div>

                <div class="absolute top-10 -right-44 rotate-6">
                    <div class="anim-float anim-float-delay-1">
                        <ForecastCard />
                    </div>
                </div>

                <div class="absolute -left-52 bottom-14 -rotate-3">
                    <div class="anim-float anim-float-delay-2">
                        <PurchaseCard />
                    </div>
                </div>

                <div class="absolute -right-36 bottom-20 rotate-3">
                    <div class="anim-float anim-float-delay-3">
                        <ValueCard />
                    </div>
                </div>

                <div class="absolute -top-10 left-1/2 -translate-x-1/2 rotate-2">
                    <div class="anim-float anim-float-delay-4">
                        <ForecastToast />
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import RegistryBadge from './landing/RegistryBadge.vue';
import AppMockCard from './landing/AppMockCard.vue';
import StockCard from './landing/StockCard.vue';
import ForecastCard from './landing/ForecastCard.vue';
import PurchaseCard from './landing/PurchaseCard.vue';
import ValueCard from './landing/ValueCard.vue';
import ForecastToast from './landing/ForecastToast.vue';

const showFloating = ref(false);

let media;
function updateVisibility() {
    showFloating.value = window.matchMedia('(min-width: 1280px)').matches;
}

onMounted(() => {
    media = window.matchMedia('(min-width: 1280px)');
    showFloating.value = media.matches;
    media.addEventListener('change', updateVisibility);
});

onUnmounted(() => {
    media?.removeEventListener('change', updateVisibility);
});
</script>

<style scoped>
.anim-float {
    animation: float-y 7s ease-in-out infinite;
}
.anim-float-delay-1 {
    animation-delay: 1.4s;
}
.anim-float-delay-2 {
    animation-delay: 2.8s;
}
.anim-float-delay-3 {
    animation-delay: 4.2s;
}
.anim-float-delay-4 {
    animation-delay: 5s;
}
@keyframes float-y {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-14px);
    }
}
@media (prefers-reduced-motion: reduce) {
    .anim-float {
        animation: none;
    }
}
</style>
