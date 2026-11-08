<script setup>
import { onMounted, ref } from 'vue';
import { http } from '../../api/client';
import { formatRupiah } from '../../utils/format';

const products = ref([]);
const loading = ref(true);

onMounted(async () => {
    try {
        const { data } = await http.get('/showcase');
        products.value = data.data.products ?? [];
    } catch {
        products.value = [];
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <section v-if="loading || products.length" class="mx-auto mt-12 max-w-6xl sm:mt-16">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-gray-900 sm:text-4xl">Produk UMKM</h2>
            <p class="mx-auto mt-3 max-w-xl text-base text-gray-500 sm:mt-4">
                Etalase dari penjual yang memakai TokoKu. Temukan produk, lalu hubungi penjualnya langsung.
            </p>
        </div>

        <div v-if="loading" class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="n in 3" :key="n" class="h-40 animate-pulse rounded-2xl border border-gray-200 bg-gray-50"></div>
        </div>

        <div v-else class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <article
                v-for="product in products"
                :key="product.id"
                class="flex flex-col rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-gray-300 hover:shadow-md"
            >
                <span class="inline-flex w-fit rounded-full bg-primary-50 px-2.5 py-0.5 text-[11px] font-semibold text-primary-700">
                    {{ product.category || 'Produk' }}
                </span>
                <h3 class="mt-2.5 text-base font-semibold text-gray-900">{{ product.name }}</h3>
                <p v-if="product.description" class="mt-1 line-clamp-2 text-sm text-gray-500">{{ product.description }}</p>

                <div class="mt-4 flex items-end justify-between gap-3 border-t border-gray-100 pt-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-gray-900">{{ product.business.name }}</p>
                        <p class="truncate text-xs text-gray-500">{{ product.business.address || 'Lokasi belum diisi' }}</p>
                    </div>
                    <p class="shrink-0 text-sm font-bold text-primary-700">{{ formatRupiah(product.sell_price) }}</p>
                </div>
            </article>
        </div>

        <div class="mt-6 text-center">
            <RouterLink
                to="/login"
                class="inline-block rounded-xl border border-gray-300 px-6 py-2.5 text-sm font-semibold text-gray-700 transition hover:border-gray-400 hover:bg-gray-50"
            >
                Hubungi penjual
            </RouterLink>
        </div>
    </section>
</template>
