<template>
    <div class="flex min-h-screen flex-col bg-white text-gray-900">
        <FloatingNav />

        <main class="mx-auto w-full max-w-4xl flex-1 px-4 pb-16 pt-24 sm:px-6 sm:pt-28">
            <slot />
        </main>

        <Footer />
    </div>
</template>

<script setup>
import FloatingNav from './FloatingNav.vue';
import Footer from './Footer.vue';
</script>
