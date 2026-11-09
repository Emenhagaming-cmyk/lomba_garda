<script setup>
import { onMounted, ref } from 'vue';
import { http } from '../../api/client';
import { formatNumber, formatRupiah } from '../../utils/format';
import StarRating from '../StarRating.vue';
import ProductPreviewModal from './ProductPreviewModal.vue';

const products = ref([]);
const loading = ref(true);
const selected = ref(null);
const failedImages = ref({});

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

function openPreview(product) {
    selected.value = product;
}

function imageFailed(product) {
    failedImages.value = { ...failedImages.value, [product.id]: true };
}
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
            <div v-for="n in 3" :key="n" class="h-64 animate-pulse rounded-2xl border border-gray-200 bg-gray-50"></div>
        </div>

        <div v-else class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <article
                v-for="product in products"
                :key="product.id"
                class="group flex cursor-pointer flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-gray-300 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/40"
                role="button"
                tabindex="0"
                :aria-label="`Lihat detail ${product.name}`"
                @click="openPreview(product)"
                @keydown.enter.prevent="openPreview(product)"
                @keydown.space.prevent="openPreview(product)"
            >
                <div class="relative aspect-[16/10] w-full overflow-hidden bg-gray-100">
                    <img
                        v-if="product.image_url && !failedImages[product.id]"
                        :src="product.image_url"
                        :alt="product.name"
                        class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
                        loading="lazy"
                        @error="imageFailed(product)"
                    />
                    <div
                        v-else
                        class="flex h-full w-full items-center justify-center bg-gradient-to-br from-primary-100 via-primary-50 to-amber-50"
                    >
                        <span class="text-3xl font-bold text-primary-300">{{ product.name.charAt(0).toUpperCase() }}</span>
                    </div>
                    <span class="absolute left-3 top-3 inline-flex rounded-full bg-white/90 px-2.5 py-0.5 text-[11px] font-semibold text-primary-700 backdrop-blur">
                        {{ product.category || 'Produk' }}
                    </span>
                </div>

                <div class="flex flex-1 flex-col p-5">
                    <h3 class="text-base font-semibold text-gray-900">{{ product.name }}</h3>

                    <div v-if="product.review_count" class="mt-1.5 flex items-center gap-1.5">
                        <StarRating :rating="product.rating_avg || 0" size="sm" />
                        <span class="text-xs font-semibold text-gray-700">
                            {{ Number(product.rating_avg).toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 }) }}
                        </span>
                        <span class="text-xs text-gray-400">({{ formatNumber(product.review_count) }})</span>
                    </div>

                    <p v-if="product.description" class="mt-1.5 line-clamp-2 text-sm text-gray-500">{{ product.description }}</p>

                    <div class="mt-auto flex items-end justify-between gap-3 border-t border-gray-100 pt-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-gray-900">{{ product.business.name }}</p>
                            <p class="truncate text-xs text-gray-500">{{ product.business.address || 'Lokasi belum diisi' }}</p>
                        </div>
                        <p class="shrink-0 text-sm font-bold text-primary-700">{{ formatRupiah(product.sell_price) }}</p>
                    </div>
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

        <ProductPreviewModal v-if="selected" :product="selected" @close="selected = null" />
    </section>
</template>
