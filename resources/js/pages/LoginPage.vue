<script setup>
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { formatApiError } from '../api/client';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

const email = ref('');
const password = ref('');
const remember = ref(false);
const errorMessage = ref('');
const submitting = ref(false);

async function submit() {
    errorMessage.value = '';
    submitting.value = true;

    try {
        await auth.login(email.value, password.value);
        await router.replace(route.query.redirect || { name: 'dashboard' });
    } catch (error) {
        errorMessage.value = formatApiError(error, 'Unable to sign in. Please check your credentials.');
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-zinc-950 px-4">
        <div class="w-full max-w-sm">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-white">NADI</h1>
                <p class="text-zinc-400 mt-2">Predictive Operations untuk UMKM</p>
            </div>

            <form @submit.prevent="submit" class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 space-y-4">
                <div v-if="errorMessage" role="alert" class="text-sm text-red-400 bg-red-950/50 border border-red-900 rounded-lg px-3 py-2">
                    {{ errorMessage }}
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-zinc-300">Email</label>
                    <input
                        id="email"
                        v-model="email"
                        type="email"
                        required
                        autocomplete="email"
                        class="mt-1 w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-zinc-300">Password</label>
                    <input
                        id="password"
                        v-model="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        class="mt-1 w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-zinc-400">
                        <input v-model="remember" type="checkbox" class="rounded border-zinc-700 bg-zinc-800">
                        Ingat saya
                    </label>
                    <RouterLink to="/register" class="text-sm text-emerald-400 hover:text-emerald-300">
                        Daftar
                    </RouterLink>
                </div>

                <button
                    type="submit"
                    :disabled="submitting"
                    class="w-full rounded-lg bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 px-3 py-2 font-medium text-white transition"
                >
                    {{ submitting ? 'Masuk…' : 'Masuk' }}
                </button>
            </form>
        </div>
    </div>
</template>