<script setup>
import { computed, onMounted, ref } from 'vue';
import { http, formatApiError } from '../../api/client';
import { formatDate, formatNumber, formatRupiah } from '../../utils/format';
import Modal from '../Modal.vue';
import StarRating from '../StarRating.vue';

const props = defineProps({
    product: { type: Object, required: true },
});

defineEmits(['close']);

const detail = ref(null);
const summary = ref(null);
const loading = ref(true);
const error = ref('');
const imageFailed = ref(false);

const avatarPalette = [
    'bg-primary-100 text-primary-700',
    'bg-emerald-100 text-emerald-700',
    'bg-amber-100 text-amber-700',
    'bg-sky-100 text-sky-700',
    'bg-violet-100 text-violet-700',
];

const averageLabel = computed(() => {
    const average = summary.value?.average;

    if (average === null || average === undefined) {
        return '—';
    }

    return Number(average).toLocaleString('id-ID', {
        minimumFractionDigits: 1,
        maximumFractionDigits: 1,
    });
});

function initials(name) {
    return String(name || '?')
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
}

onMounted(async () => {
    try {
        const { data } = await http.get(`/showcase/${props.product.id}`);
        detail.value = data.data.product;
        summary.value = data.data.review_summary;
    } catch (err) {
        error.value = formatApiError(err, 'Gagal memuat detail produk.');
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <Modal :title="product.name" max-width="max-w-4xl" @close="$emit('close')">
        <div v-if="loading" class="space-y-4">
            <div class="aspect-[16/9] animate-pulse rounded-xl bg-gray-100"></div>
            <div class="h-4 w-1/2 animate-pulse rounded bg-gray-100"></div>
            <div class="h-3 w-2/3 animate-pulse rounded bg-gray-100"></div>
            <div class="h-24 animate-pulse rounded-xl bg-gray-100"></div>
        </div>

        <p v-else-if="error" class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-600">{{ error }}</p>

        <div v-else-if="detail" class="space-y-6">
            <div class="overflow-hidden rounded-xl bg-gray-100">
                <img
                    v-if="detail.image_url && !imageFailed"
                    :src="detail.image_url"
                    :alt="detail.name"
                    class="aspect-[16/9] w-full object-cover"
                    loading="lazy"
                    @error="imageFailed = true"
                />
                <div
                    v-else
                    class="flex aspect-[16/9] w-full items-center justify-center bg-gradient-to-br from-primary-100 via-primary-50 to-amber-50"
                >
                    <span class="text-4xl font-bold text-primary-300">{{ detail.name.charAt(0).toUpperCase() }}</span>
                </div>
            </div>

            <p v-if="detail.image_url && detail.image_credit" class="-mt-4 text-[11px] text-gray-400">
                {{ detail.image_credit }}
            </p>

            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex rounded-full bg-primary-50 px-2.5 py-0.5 text-[11px] font-semibold text-primary-700">
                            {{ detail.category || 'Produk' }}
                        </span>
                        <span
                            v-if="summary && summary.total > 0"
                            class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500"
                        >
                            <StarRating :rating="summary.average || 0" size="sm" />
                            {{ averageLabel }} · {{ formatNumber(summary.total) }} ulasan
                        </span>
                    </div>
                    <p class="mt-2 text-sm font-medium text-gray-900">{{ detail.business.name }}</p>
                    <p class="text-xs text-gray-500">{{ detail.business.address || 'Lokasi belum diisi' }}</p>
                </div>
                <div class="text-right">
                    <p class="text-lg font-bold text-primary-700">{{ formatRupiah(detail.sell_price) }}</p>
                    <p class="text-xs text-gray-400">per {{ detail.unit || 'pcs' }}</p>
                </div>
            </div>

            <p v-if="detail.description" class="text-sm leading-relaxed text-gray-600">{{ detail.description }}</p>

            <div class="grid gap-6 border-t border-gray-100 pt-6 lg:grid-cols-[minmax(0,290px)_minmax(0,1fr)] lg:gap-8">
                <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Rating pembeli</p>
                    <div class="mt-2 flex items-end gap-2">
                        <p class="text-5xl font-bold leading-none text-gray-900">{{ averageLabel }}</p>
                        <p class="pb-1 text-sm text-gray-400">/ 5</p>
                    </div>
                    <div class="mt-3">
                        <StarRating :rating="summary?.average || 0" size="lg" />
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                        {{ summary && summary.total > 0 ? `${formatNumber(summary.total)} ulasan pembeli` : 'Belum ada ulasan' }}
                    </p>

                    <div v-if="summary && summary.total > 0" class="mt-5 space-y-2">
                        <div
                            v-for="row in summary.distribution"
                            :key="row.star"
                            class="flex items-center gap-3"
                        >
                            <span class="w-7 text-xs font-medium text-gray-500">{{ row.star }} ★</span>
                            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-gray-200">
                                <div class="h-full rounded-full bg-amber-400" :style="{ width: `${row.percent}%` }"></div>
                            </div>
                            <span class="w-9 text-right text-xs text-gray-500">{{ row.percent }}%</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-bold text-gray-900">Ulasan pembeli</h4>

                    <div v-if="!detail.reviews.length" class="mt-4 rounded-xl border border-dashed border-gray-200 px-4 py-8 text-center">
                        <p class="text-sm font-medium text-gray-600">Belum ada ulasan</p>
                        <p class="mt-1 text-xs text-gray-400">Jadi yang pertama setelah membeli produk ini.</p>
                    </div>

                    <div v-else class="mt-2 divide-y divide-gray-100">
                        <article
                            v-for="(review, index) in detail.reviews"
                            :key="review.id"
                            class="py-4 first:pt-2 last:pb-0"
                        >
                            <div class="flex items-start gap-3">
                                <span
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold"
                                    :class="avatarPalette[index % avatarPalette.length]"
                                >
                                    {{ initials(review.reviewer_name) }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-sm font-semibold text-gray-900">{{ review.reviewer_name }}</p>
                                        <span
                                            v-if="review.is_verified"
                                            class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700"
                                        >
                                            <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3">
                                                <path d="m5 13 4 4L19 7"></path>
                                            </svg>
                                            Pembeli terverifikasi
                                        </span>
                                    </div>
                                    <div class="mt-1 flex items-center gap-2">
                                        <StarRating :rating="review.rating" size="sm" />
                                        <span class="text-xs text-gray-400">{{ formatDate(review.reviewed_at) }}</span>
                                    </div>
                                </div>
                            </div>
                            <p class="mt-2.5 text-sm font-semibold text-gray-900">{{ review.summary }}</p>
                            <p v-if="review.body" class="mt-1 text-sm leading-relaxed text-gray-600">{{ review.body }}</p>
                        </article>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-2 border-t border-gray-100 pt-5 sm:flex-row sm:justify-end">
                <RouterLink
                    to="/login"
                    class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700"
                >
                    Hubungi penjual
                </RouterLink>
            </div>
        </div>
    </Modal>
</template>
