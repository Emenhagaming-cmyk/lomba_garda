<script setup>
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const auth = useAuthStore();
const router = useRouter();

async function logout() {
    await auth.logout();
    await router.replace({ name: 'landing' });
}
</script>

<template>
    <div class="min-h-screen bg-white text-gray-900">
        <nav class="border-b border-gray-200 bg-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
                <div class="flex items-center gap-8">
                    <span class="text-lg font-bold text-emerald-600">BelanjaYuk!</span>
                    <span class="text-sm text-gray-500">{{ auth.user?.name }}</span>
                </div>
                <span class="text-xs text-gray-400 uppercase">{{ auth.user?.role }}</span>
            </div>
        </nav>

        <main class="mx-auto max-w-6xl px-4 py-8">
            <router-view />
        </main>

        <footer class="mx-auto max-w-6xl px-4 pb-8 text-right">
            <button type="button" @click="logout" class="text-sm text-gray-400 hover:text-red-500 transition">
                Keluar
            </button>
        </footer>
    </div>
</template>
