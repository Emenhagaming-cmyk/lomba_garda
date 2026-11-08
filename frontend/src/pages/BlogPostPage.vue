<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { http, formatApiError } from '../api/client';
import PublicLayout from '../components/PublicLayout.vue';
import { renderMarkdown } from '../utils/markdown';

const route = useRoute();

const article = ref(null);
const related = ref([]);
const loading = ref(true);
const error = ref('');

const bodyHtml = computed(() => (article.value ? renderMarkdown(article.value.body) : ''));

function formatDate(iso) {
    if (!iso) {
        return '';
    }

    return new Date(iso).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

async function loadArticle() {
    loading.value = true;
    error.value = '';
    article.value = null;

    try {
        const { data } = await http.get(`/articles/${route.params.slug}`);
        article.value = data.data.article;
        related.value = data.data.related;
    } catch (err) {
        error.value = formatApiError(err, 'Berita tidak ditemukan.');
    } finally {
        loading.value = false;
    }
}

watch(() => route.params.slug, loadArticle);
onMounted(loadArticle);
</script>

<template>
    <PublicLayout>
        <div v-if="loading" class="space-y-4">
            <div class="h-4 w-1/3 animate-pulse rounded bg-gray-100"></div>
            <div class="h-8 w-3/4 animate-pulse rounded bg-gray-100"></div>
            <div class="h-64 animate-pulse rounded-2xl bg-gray-100"></div>
        </div>

        <div v-else-if="error" class="space-y-4">
            <p class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-600">{{ error }}</p>
            <RouterLink :to="{ name: 'blog' }" class="text-sm font-semibold text-primary-700">Kembali ke Berita</RouterLink>
        </div>

        <article v-else-if="article" class="space-y-6">
            <RouterLink :to="{ name: 'blog' }" class="inline-flex items-center gap-1 text-xs font-semibold text-gray-500 hover:text-gray-900">
                <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M15 18l-6-6 6-6"></path>
                </svg>
                Berita
            </RouterLink>

            <header class="space-y-3">
                <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500">
                    <span class="rounded-full bg-primary-50 px-2.5 py-0.5 font-semibold text-primary-700">{{ article.category }}</span>
                    <span>{{ formatDate(article.published_at) }}</span>
                    <span aria-hidden="true">·</span>
                    <span>{{ article.read_minutes }} menit baca</span>
                </div>
                <h1 class="text-3xl font-bold leading-tight text-gray-900">{{ article.title }}</h1>
                <p class="text-sm text-gray-500">
                    Oleh {{ article.author }}<span v-if="article.author_role"> · {{ article.author_role }}</span>
                </p>
            </header>

            <div class="article-body space-y-4 text-[15px] leading-relaxed text-gray-700" v-html="bodyHtml"></div>

            <div v-if="related.length" class="space-y-3 border-t border-gray-100 pt-6">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Baca juga</p>
                <div class="grid gap-3 sm:grid-cols-2">
                    <RouterLink
                        v-for="item in related"
                        :key="item.id"
                        :to="{ name: 'blog-post', params: { slug: item.slug } }"
                        class="rounded-xl border border-gray-200 p-4 transition hover:border-gray-300"
                    >
                        <p class="text-sm font-bold leading-snug text-gray-900">{{ item.title }}</p>
                        <p class="mt-1 line-clamp-2 text-xs text-gray-500">{{ item.excerpt }}</p>
                    </RouterLink>
                </div>
            </div>
        </article>
    </PublicLayout>
</template>

<style>
.article-body strong {
    color: #111827;
}
</style>
