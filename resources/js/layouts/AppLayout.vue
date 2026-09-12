<script setup>
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const auth = useAuthStore();
const router = useRouter();

async function logout() {
    await auth.logout();
    await router.replace({ name: 'login' });
}
</script>

<template>
    <div class="min-h-screen bg-zinc-950 text-zinc-100">
        <nav class="border-b border-zinc-800 bg-zinc-900">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
                <div class="flex items-center gap-8">
                    <span class="text-lg font-bold text-emerald-400">NADI</span>
                    <span class="text-sm text-zinc-400">{{ auth.user?.name }}</span>
                </div>
                <span class="text-xs text-zinc-500 uppercase">{{ auth.user?.role }}</span>
            </div>
        </nav>

        <main class="mx-auto max-w-6xl px-4 py-8">
            <router-view />
        </main>

        <footer class="mx-auto max-w-6xl px-4 pb-8 text-right">
            <button type="button" @click="logout" class="text-sm text-zinc-500 hover:text-red-400 transition">
                Keluar
            </button>
        </footer>
    </div>
</template>