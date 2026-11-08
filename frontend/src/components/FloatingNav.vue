<template>
  <nav
    class="fixed top-5 left-1/2 z-50 max-w-[calc(100vw-1.5rem)] -translate-x-1/2 overflow-x-auto rounded-full border border-gray-200 bg-white/80 px-2 py-2 shadow-lg shadow-gray-200/50 backdrop-blur [scrollbar-width:none] sm:px-3 [&::-webkit-scrollbar]:hidden"
    aria-label="Main navigation"
  >
    <div class="flex items-center gap-1">
      <RouterLink
        to="/"
        class="flex items-center gap-1.5 rounded-full px-2.5 py-2 text-sm font-bold whitespace-nowrap text-primary-600 transition hover:bg-gray-100 sm:px-4"
      >
        <img :src="'/logo.png'" alt="" class="h-4 w-4 object-contain" aria-hidden="true">
        TokoKu
      </RouterLink>

      <div class="mx-1 h-4 w-px shrink-0 bg-gray-200"></div>

      <div class="flex items-center gap-0.5 sm:gap-1">
        <a
          href="#harga"
          @click.prevent="scrollTo('harga')"
          class="rounded-full px-2.5 py-2 text-xs whitespace-nowrap text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 sm:px-3.5 sm:text-sm"
        >Tentang<span class="hidden sm:inline"> Kami</span></a>
        <a
          href="#cara-kerja"
          @click.prevent="scrollTo('cara-kerja')"
          class="rounded-full px-2.5 py-2 text-xs whitespace-nowrap text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 sm:px-3.5 sm:text-sm"
        >Cara kerja</a>
        <RouterLink
          to="/blog"
          class="rounded-full px-2.5 py-2 text-xs whitespace-nowrap text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 sm:px-3.5 sm:text-sm"
        >Berita</RouterLink>

        <div class="mx-1 h-4 w-px shrink-0 bg-gray-200"></div>
      </div>

      <RouterLink
        v-if="!auth.isAuthenticated"
        to="/login"
        class="rounded-full bg-primary-600 px-3.5 py-2 text-xs font-semibold whitespace-nowrap text-white transition hover:bg-primary-700 sm:text-sm"
      >Masuk</RouterLink>
      <template v-else>
        <RouterLink
          v-if="business.hasBusiness"
          to="/dashboard"
          class="rounded-full px-2.5 py-2 text-xs whitespace-nowrap text-gray-600 transition hover:bg-gray-100 hover:text-gray-900 sm:px-3.5 sm:text-sm"
        >Dashboard</RouterLink>
        <RouterLink
          to="/profile"
          class="rounded-full bg-primary-600 px-3.5 py-2 text-xs font-semibold whitespace-nowrap text-white transition hover:bg-primary-700 sm:text-sm"
        >Profil</RouterLink>
      </template>
    </div>
  </nav>
</template>

<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useBusinessStore } from '../stores/business';

const auth = useAuthStore();
const business = useBusinessStore();

onMounted(() => {
    if (auth.isAuthenticated && !business.loaded) {
        business.fetchBusiness();
    }
});

function scrollTo(id) {
    const el = document.getElementById(id);

    if (el) {
        el.scrollIntoView({ behavior: 'smooth' });

        return;
    }

    window.location.assign(`/#${id}`);
}
</script>
