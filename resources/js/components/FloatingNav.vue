<template>
  <nav
    class="fixed top-5 left-1/2 -translate-x-1/2 z-50 rounded-full border border-gray-200 bg-white/80 px-3 py-2 shadow-lg shadow-gray-200/50 backdrop-blur"
    aria-label="Main navigation"
  >
    <div class="flex items-center gap-1">
      <a
        href="/"
        @click.prevent="$router.push('/')"
        class="flex items-center gap-1.5 rounded-full px-4 py-1.5 text-sm font-bold text-emerald-600 transition hover:bg-gray-100"
      >
        <svg viewBox="0 0 16 16" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6">
          <path d="M3 11L6.5 6.5l2.5 2.5L13 4"></path>
          <path d="M3 5h10M3 11h6"></path>
        </svg>
        BelanjaYuk!
      </a>

      <div class="mx-1 h-4 w-px bg-gray-200"></div>

      <a
        href="#harga"
        @click.prevent="scrollTo('harga')"
        class="rounded-full px-3.5 py-1.5 text-sm text-gray-500 transition hover:bg-gray-100 hover:text-gray-900"
      >Kenapa kami</a>
      <a
        href="#cara-kerja"
        @click.prevent="scrollTo('cara-kerja')"
        class="rounded-full px-3.5 py-1.5 text-sm text-gray-500 transition hover:bg-gray-100 hover:text-gray-900"
      >Cara kerja</a>

      <div class="mx-1 h-4 w-px bg-gray-200"></div>

      <a
        href="#"
        @click.prevent="$router.push('/login')"
        class="rounded-full px-3.5 py-1.5 text-sm text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
      >Masuk</a>
      <a
        href="#"
        @click.prevent="$router.push('/register')"
        class="rounded-full bg-emerald-600 px-4 py-1.5 text-sm font-semibold text-white transition hover:bg-emerald-500"
      >Daftar</a>
    </div>
  </nav>
</template>

<script setup>
function scrollTo(id) {
  document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' });
}
</script>
