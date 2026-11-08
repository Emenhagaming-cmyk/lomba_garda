<script setup>
import { onMounted, ref } from 'vue';
import { http } from '../../api/client';
import { avatarClass, initials } from '../../utils/avatar';

const stories = ref([]);
const loading = ref(true);

const gradients = [
    'from-neutral-900 via-neutral-800 to-stone-700',
    'from-stone-900 via-neutral-800 to-neutral-700',
    'from-neutral-900 via-stone-800 to-neutral-700',
];

onMounted(async () => {
    try {
        const { data } = await http.get('/articles');
        stories.value = (data.data.articles ?? []).slice(0, 3);
    } catch {
        stories.value = [];
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <section v-if="loading || stories.length" id="berita" class="mx-auto mt-12 max-w-6xl sm:mt-16">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-primary-600">Cerita pelaku usaha</p>
                <h2 class="mt-2 text-2xl font-bold text-gray-900 sm:text-4xl">Berita &amp; cerita dari UMKM</h2>
                <p class="mt-3 max-w-xl text-base text-gray-500 sm:text-lg">
                    Pelajaran nyata dari penjual yang tumbuh bersama TokoKu.
                </p>
            </div>
            <RouterLink
                to="/blog"
                class="inline-flex w-fit items-center gap-1.5 rounded-full border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-900 transition hover:border-gray-300 hover:bg-gray-50 md:ml-auto"
            >
                Lihat semua {{ stories.length || 6 }} berita
                <span aria-hidden="true">→</span>
            </RouterLink>
        </div>

        <div v-if="loading" class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="n in 3" :key="n" class="overflow-hidden rounded-2xl border border-gray-200">
                <div class="aspect-video animate-pulse bg-gray-100"></div>
                <div class="space-y-3 p-4">
                    <div class="h-3 w-4/5 animate-pulse rounded bg-gray-100"></div>
                    <div class="h-3 w-3/5 animate-pulse rounded bg-gray-100"></div>
                    <div class="h-8 w-1/2 animate-pulse rounded-full bg-gray-100"></div>
                </div>
            </div>
        </div>

        <div v-else class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <RouterLink
                v-for="(story, index) in stories"
                :key="story.id"
                :to="{ name: 'blog-post', params: { slug: story.slug } }"
                class="group flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white transition hover:border-gray-300 hover:shadow-md"
            >
                <div class="relative aspect-video overflow-hidden bg-gradient-to-br" :class="gradients[index % gradients.length]">
                    <span
                        class="pointer-events-none absolute -bottom-8 right-2 select-none text-[11rem] font-bold leading-none text-white/10"
                        aria-hidden="true"
                    >
                        {{ story.author?.charAt(0) ?? 'T' }}
                    </span>

                    <span class="absolute left-3 top-3 rounded-full bg-white/15 px-2.5 py-0.5 text-[11px] font-semibold text-white backdrop-blur">
                        {{ story.category }}
                    </span>

                    <span class="absolute inset-0 flex items-center justify-center">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-white shadow-lg shadow-black/20 transition duration-300 group-hover:scale-110">
                            <svg viewBox="0 0 24 24" class="ml-0.5 h-5 w-5 text-neutral-900" fill="currentColor" aria-hidden="true">
                                <path d="M8 5.5v13l11-6.5z"></path>
                            </svg>
                        </span>
                    </span>

                    <span class="absolute bottom-3 right-3 rounded-full bg-black/50 px-2 py-0.5 text-[11px] font-medium text-white backdrop-blur">
                        {{ story.read_minutes }} menit
                    </span>
                </div>

                <div class="flex flex-1 flex-col p-4">
                    <p class="line-clamp-3 text-sm font-medium leading-relaxed text-gray-900 transition group-hover:text-primary-700">
                        {{ story.excerpt }}
                    </p>

                    <div class="mt-auto flex items-center gap-2.5 pt-4">
                        <span
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-[11px] font-semibold text-white"
                            :class="avatarClass(story.author)"
                        >
                            {{ initials(story.author) }}
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-xs font-semibold text-gray-900">{{ story.author }}</p>
                            <p class="truncate text-[11px] text-gray-500">{{ story.author_role || story.category }}</p>
                        </div>
                    </div>
                </div>
            </RouterLink>
        </div>
    </section>
</template>
