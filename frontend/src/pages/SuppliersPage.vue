<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { http, formatApiError } from '../api/client';
import PageHeader from '../components/PageHeader.vue';
import { formatRupiah } from '../utils/format';

const router = useRouter();

const shops = ref([]);
const closedShop = ref(null);
const loading = ref(true);
const error = ref('');
const search = ref('');
const filters = ref({ open: false, shipping: false, cod: false });
const hovered = ref(null);
const selected = ref(null);
const booking = ref(null);

const chips = [
    { key: 'open', label: 'Buka sekarang' },
    { key: 'shipping', label: 'Siap kirim' },
    { key: 'cod', label: 'Bisa COD' },
];

const activeShop = computed(() => selected.value ?? hovered.value);

async function loadShops() {
    loading.value = true;
    error.value = '';

    try {
        const { data } = await http.get('/shops', {
            params: {
                search: search.value || undefined,
                open: filters.value.open ? 1 : undefined,
                shipping: filters.value.shipping ? 1 : undefined,
                cod: filters.value.cod ? 1 : undefined,
            },
        });

        shops.value = data.data.shops;
        closedShop.value = data.data.temporarily_closed;

        if (!shops.value.some((shop) => shop.id === selected.value)) {
            selected.value = shops.value[0]?.id ?? null;
        }
    } catch (err) {
        error.value = formatApiError(err, 'Gagal memuat daftar pemasok.');
    } finally {
        loading.value = false;
    }
}

function toggleFilter(key) {
    filters.value[key] = !filters.value[key];
    loadShops();
}

function useMyLocation() {
    search.value = 'Yogyakarta';
    loadShops();
}

async function book(shop) {
    if (booking.value) {
        return;
    }

    booking.value = shop.id;
    const item = shop.items?.[0];

    try {
        const { data } = await http.post('/threads', {
            shop_id: shop.id,
            message: `Halo ${shop.name}, saya tertarik dengan ${item ? item.name : 'produk yang dijual'}. Apakah stok tersedia untuk pemesanan?`,
        });

        router.push({ name: 'messages', query: { thread: data.data.thread.id } });
    } catch (err) {
        error.value = formatApiError(err, 'Gagal memulai percakapan.');
    } finally {
        booking.value = null;
    }
}

function formatReopens(date) {
    if (!date) {
        return '';
    }

    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
}

onMounted(loadShops);
</script>

<template>
    <div class="space-y-5">
        <PageHeader
            title="Pemasok"
            description="Temukan toko bahan baku dan kemasan di sekitar usahamu, lalu pesan langsung lewat chat."
        />

        <p v-if="error" class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-600">{{ error }}</p>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <div class="grid lg:grid-cols-[minmax(0,25rem)_minmax(0,1fr)]">
                <div class="flex min-h-0 flex-col border-b border-gray-200 lg:border-b-0 lg:border-r">
                    <div class="space-y-3 border-b border-gray-100 p-4">
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <svg viewBox="0 0 24 24" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path d="M21 10c0 7-9 12-9 12S3 17 3 10a9 9 0 0 1 18 0Z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                <input
                                    v-model="search"
                                    type="search"
                                    placeholder="Cari kota atau nama toko"
                                    class="w-full rounded-lg border border-gray-200 py-2 pl-9 pr-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none"
                                    @keyup.enter="loadShops"
                                >
                            </div>
                            <button
                                type="button"
                                class="shrink-0 rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50"
                                @click="useMyLocation"
                            >
                                Gunakan lokasi saya
                            </button>
                        </div>

                        <div class="flex flex-wrap gap-1.5">
                            <button
                                v-for="chip in chips"
                                :key="chip.key"
                                type="button"
                                class="rounded-full border px-3 py-1 text-xs font-semibold transition"
                                :class="filters[chip.key]
                                    ? 'border-neutral-900 bg-neutral-900 text-white'
                                    : 'border-gray-200 text-gray-600 hover:bg-gray-50'"
                                @click="toggleFilter(chip.key)"
                            >
                                {{ chip.label }}
                            </button>
                        </div>
                    </div>

                    <div class="max-h-[30rem] flex-1 overflow-y-auto">
                        <template v-if="loading">
                            <div v-for="n in 4" :key="n" class="space-y-2 border-b border-gray-100 p-4">
                                <div class="h-3 w-1/2 animate-pulse rounded bg-gray-100"></div>
                                <div class="h-3 w-3/4 animate-pulse rounded bg-gray-100"></div>
                                <div class="h-3 w-1/3 animate-pulse rounded bg-gray-100"></div>
                            </div>
                        </template>

                        <template v-else-if="shops.length">
                            <div
                                v-for="shop in shops"
                                :key="shop.id"
                                class="cursor-pointer border-b border-gray-100 p-4 transition last:border-b-0"
                                :class="selected === shop.id ? 'bg-primary-50/60 ring-1 ring-inset ring-neutral-900' : 'hover:bg-gray-50'"
                                @mouseenter="hovered = shop.id"
                                @mouseleave="hovered = null"
                                @click="selected = shop.id"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-gray-900">{{ shop.name }}</p>
                                        <p class="mt-0.5 line-clamp-2 text-xs leading-relaxed text-gray-500">{{ shop.address }}</p>
                                    </div>
                                    <span class="shrink-0 font-mono text-xs text-gray-500">{{ shop.distance_km }} km</span>
                                </div>

                                <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <span
                                        class="rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                        :class="shop.status === 'open' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"
                                    >
                                        {{ shop.status === 'open' ? 'Buka' : 'Tutup' }}
                                    </span>
                                    <span class="text-xs text-gray-500">{{ shop.status === 'open' ? 'Hari ini' : 'Buka' }} {{ shop.open_hours }}</span>
                                    <span class="ml-auto text-xs text-gray-600">★ {{ shop.rating }} ({{ shop.reviews_count }})</span>
                                </div>

                                <p v-if="shop.items?.length" class="mt-2 text-[11px] text-gray-500">
                                    {{ shop.items.slice(0, 2).map((item) => item.name).join(' · ') }}
                                    <span v-if="shop.items.length > 2"> · +{{ shop.items.length - 2 }} lagi</span>
                                </p>
                                <p v-if="shop.items?.[0]" class="mt-0.5 font-mono text-[11px] text-gray-500">
                                    mulai {{ formatRupiah(shop.items[0].price) }} / {{ shop.items[0].unit }}
                                </p>

                                <div class="mt-3 flex items-center gap-3 text-xs">
                                    <span class="font-semibold text-gray-700">Rute</span>
                                    <span class="font-semibold text-gray-700">Telepon</span>
                                    <button
                                        type="button"
                                        class="ml-auto rounded-lg bg-neutral-900 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-neutral-800 disabled:opacity-60"
                                        :disabled="booking === shop.id"
                                        @click.stop="book(shop)"
                                    >
                                        {{ booking === shop.id ? 'Membuka...' : 'Pesan' }}
                                    </button>
                                </div>
                            </div>
                        </template>

                        <div v-else class="px-4 py-12 text-center">
                            <p class="text-sm font-medium text-gray-700">Toko tidak ditemukan</p>
                            <p class="mt-1 text-xs text-gray-400">Coba kata kunci lain atau matikan filter.</p>
                        </div>
                    </div>
                </div>

                <div
                    class="relative min-h-[280px] bg-primary-50/40 lg:min-h-[34rem]"
                    :style="{
                        backgroundImage: 'linear-gradient(to right, rgba(120,113,108,0.12) 1px, transparent 1px), linear-gradient(to bottom, rgba(120,113,108,0.12) 1px, transparent 1px)',
                        backgroundSize: '36px 36px',
                    }"
                >
                    <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-white/60 to-transparent"></div>

                    <button
                        v-for="(shop, index) in shops"
                        :key="shop.id"
                        type="button"
                        class="absolute -translate-x-1/2 -translate-y-1/2 rounded-full border border-white font-semibold shadow transition-all"
                        :class="activeShop === shop.id
                            ? 'h-9 w-9 bg-neutral-900 text-[12px] text-white'
                            : 'h-7 w-7 bg-white text-[11px] text-gray-700 hover:bg-gray-100'"
                        :style="{ left: `${shop.map_x}%`, top: `${shop.map_y}%` }"
                        :title="shop.name"
                        @mouseenter="hovered = shop.id"
                        @mouseleave="hovered = null"
                        @click="selected = shop.id"
                    >
                        {{ index + 1 }}
                    </button>

                    <div class="absolute bottom-4 left-4 rounded-lg border border-gray-200 bg-white/95 px-3 py-2 text-[11px] text-gray-500 shadow-sm">
                        <span class="font-semibold text-gray-700">{{ shops.length }} toko</span> di sekitar lokasimu
                    </div>
                </div>
            </div>

            <div v-if="closedShop" class="flex flex-wrap items-center gap-3 border-t border-gray-200 px-4 py-3">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-50 text-amber-600">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="M12 7v5l3 3"></path>
                    </svg>
                </span>
                <p class="text-xs text-gray-600">
                    <span class="font-semibold text-gray-900">{{ closedShop.name }}</span>
                    tutup sementara sampai {{ formatReopens(closedShop.reopens_on) }}.
                </p>
                <button type="button" class="ml-auto rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                    Ingatkan saya
                </button>
            </div>
        </div>
    </div>
</template>
