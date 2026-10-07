<template>
  <nav
    class="fixed top-5 left-1/2 z-50 -translate-x-1/2 rounded-full border border-gray-200 bg-white/80 px-2.5 py-2 shadow-lg shadow-gray-200/50 backdrop-blur sm:px-3"
    aria-label="Main navigation"
  >
    <div class="flex items-center gap-1">
      <a
        href="/"
        @click.prevent="$router.push('/')"
        class="flex items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-bold whitespace-nowrap text-primary-600 transition hover:bg-gray-100 sm:px-4"
      >
        <svg viewBox="0 0 16 16" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6">
          <path d="M2 7l5-4 5 4v5a1 1 0 01-1 1H3a1 1 0 01-1-1V7z"></path>
          <path d="M6 13v-4h4v4"></path>
        </svg>
        TokoKu
      </a>

      <div class="mx-1 h-4 w-px bg-gray-200"></div>

      <div class="hidden items-center gap-1 sm:flex">
        <a
          href="#harga"
          @click.prevent="scrollTo('harga')"
          class="rounded-full px-3.5 py-1.5 text-sm whitespace-nowrap text-gray-500 transition hover:bg-gray-100 hover:text-gray-900"
        >Kenapa kami</a>
        <a
          href="#cara-kerja"
          @click.prevent="scrollTo('cara-kerja')"
          class="rounded-full px-3.5 py-1.5 text-sm whitespace-nowrap text-gray-500 transition hover:bg-gray-100 hover:text-gray-900"
        >Cara kerja</a>

        <div class="mx-1 h-4 w-px bg-gray-200"></div>
      </div>

      <a
        href="#"
        @click.prevent="$router.push('/login')"
        class="rounded-full px-3.5 py-1.5 text-sm whitespace-nowrap text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
      >Masuk</a>
      <a
        href="#"
        @click.prevent="$router.push('/register')"
        class="rounded-full bg-primary-600 px-4 py-1.5 text-sm font-semibold whitespace-nowrap text-white transition hover:bg-primary-700"
      >Daftar</a>
    </div>
  </nav>
</template>

<script setup>
function scrollTo(id) {
  document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' });
}
</script>
