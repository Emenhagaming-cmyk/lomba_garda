<script setup>
import { computed, onMounted, ref } from 'vue';
import { http, formatApiError } from '../api/client';
import PageHeader from '../components/PageHeader.vue';
import PublicLayout from '../components/PublicLayout.vue';

const articles = ref([]);
const categories = ref([]);
const loading = ref(true);
const error = ref('');
const activeCategory = ref('Semua');
const search = ref('');

const filtered = computed(() => {
    const keyword = search.value.trim().toLowerCase();

    return articles.value.filter((article) => {
        const matchesCategory = activeCategory.value === 'Semua' || article.category === activeCategory.value;
        const matchesSearch = !keyword
            || article.title.toLowerCase().includes(keyword)
            || article.excerpt.toLowerCase().includes(keyword);

        return matchesCategory && matchesSearch;
    });
});

const featured = computed(() => filtered.value[0] ?? null);
const rest = computed(() => filtered.value.slice(1));

function formatDate(iso) {
    if (!iso) {
        return '';
    }

    return new Date(iso).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

async function loadArticles() {
    loading.value = true;
    error.value = '';

    try {
        const { data } = await http.get('/articles');
        articles.value = data.data.articles;
        categories.value = data.data.categories;
    } catch (err) {
        error.value = formatApiError(err, 'Gagal memuat berita.');
    } finally {
        loading.value = false;
    }
}

onMounted(loadArticles);
</script>

<template>
    <PublicLayout>
        <div class="space-y-8">
            <PageHeader title="Berita" description="Tips operasional, panduan keuangan, dan cerita dari UMKM Indonesia." />

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="flex flex-wrap gap-1.5">
                    <button
                        v-for="category in ['Semua', ...categories]"
                        :key="category"
                        type="button"
                        class="rounded-full border px-3 py-1 text-xs font-semibold transition"
                        :class="activeCategory === category
                            ? 'border-neutral-900 bg-neutral-900 text-white'
                            : 'border-gray-200 text-gray-600 hover:bg-gray-50'"
                        @click="activeCategory = category"
                    >
                        {{ category }}
                    </button>
                </div>
                <input
                    v-model="search"
                    type="search"
                    placeholder="Cari berita"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none sm:ml-auto sm:max-w-xs"
                >
            </div>

            <p v-if="error" class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-600">{{ error }}</p>

            <template v-if="loading">
                <div class="h-52 animate-pulse rounded-2xl bg-gray-100"></div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div v-for="n in 2" :key="n" class="h-40 animate-pulse rounded-2xl bg-gray-100"></div>
                </div>
            </template>

            <template v-else-if="featured">
                <RouterLink
                    :to="{ name: 'blog-post', params: { slug: featured.slug } }"
                    class="block overflow-hidden rounded-2xl border border-gray-200 bg-white transition hover:border-gray-300"
                >
                    <div class="space-y-3 p-6 sm:p-8">
                        <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500">
                            <span class="rounded-full bg-primary-50 px-2.5 py-0.5 font-semibold text-primary-700">{{ featured.category }}</span>
                            <span>{{ formatDate(featured.published_at) }}</span>
                            <span aria-hidden="true">·</span>
                            <span>{{ featured.read_minutes }} menit baca</span>
                        </div>
                        <h2 class="text-2xl font-bold leading-tight text-gray-900 sm:text-3xl">{{ featured.title }}</h2>
                        <p class="max-w-2xl text-sm leading-relaxed text-gray-600">{{ featured.excerpt }}</p>
                        <p class="text-xs font-semibold text-primary-700">Baca selengkapnya</p>
                    </div>
                </RouterLink>

                <div v-if="rest.length" class="grid gap-4 sm:grid-cols-2">
                    <RouterLink
                        v-for="article in rest"
                        :key="article.id"
                        :to="{ name: 'blog-post', params: { slug: article.slug } }"
                        class="flex flex-col rounded-2xl border border-gray-200 bg-white p-5 transition hover:border-gray-300"
                    >
                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            <span class="rounded-full bg-gray-100 px-2.5 py-0.5 font-semibold text-gray-600">{{ article.category }}</span>
                            <span>{{ formatDate(article.published_at) }}</span>
                        </div>
                        <h3 class="mt-3 text-base font-bold leading-snug text-gray-900">{{ article.title }}</h3>
                        <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-gray-600">{{ article.excerpt }}</p>
                        <p class="mt-auto pt-4 text-xs text-gray-400">{{ article.read_minutes }} menit baca</p>
                    </RouterLink>
                </div>
            </template>

            <div v-else class="rounded-2xl border border-dashed border-gray-200 px-6 py-14 text-center">
                <p class="text-sm font-medium text-gray-700">Belum ada berita yang cocok</p>
                <p class="mt-1 text-xs text-gray-400">Coba kategori lain atau hapus kata kunci.</p>
            </div>
        </div>
    </PublicLayout>
</template>
