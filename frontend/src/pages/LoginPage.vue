<script setup>
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { formatApiError } from '../api/client';
import AppMockCard from '../components/landing/AppMockCard.vue';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

const email = ref('');
const password = ref('');
const remember = ref(false);
const errorMessage = ref('');
const submitting = ref(false);
const showPassword = ref(false);

const emailError = ref('');
const passwordError = ref('');

function validateForm() {
    let valid = true;
    emailError.value = '';
    passwordError.value = '';

    if (!email.value.trim()) {
        emailError.value = 'Email wajib diisi';
        valid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
        emailError.value = 'Format email tidak valid';
        valid = false;
    }

    if (!password.value) {
        passwordError.value = 'Password wajib diisi';
        valid = false;
    }

    return valid;
}

async function submit() {
    errorMessage.value = '';
    emailError.value = '';
    passwordError.value = '';

    if (!validateForm()) {
        return;
    }

    submitting.value = true;

    try {
        await auth.login(email.value, password.value);
        await router.replace(route.query.redirect || { name: 'dashboard' });
    } catch (error) {
        errorMessage.value = formatApiError(error, 'Gagal masuk. Periksa email dan password kamu.');
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <div class="min-h-screen flex bg-white">
        <!-- Left: Form -->
        <div class="relative flex w-full items-center justify-center px-5 py-8 lg:w-1/2 sm:px-6 sm:py-12">
            <RouterLink
                to="/"
                class="absolute top-5 left-5 flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-primary-600 transition hover:border-primary-200 hover:bg-primary-50 sm:top-6 sm:left-6"
            >
                <svg viewBox="0 0 16 16" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10 3l-5 5 5 5"></path>
                </svg>
                Kembali
            </RouterLink>

            <div class="w-full max-w-sm">
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-bold text-primary-600 sm:text-3xl">TokoKu</h1>
                    <p class="text-gray-500 mt-2">Masuk dan kelola seluruh operasional bisnismu</p>
                </div>

                <form @submit.prevent="submit" class="bg-white border border-gray-200 rounded-2xl p-6 space-y-4 shadow-sm">
                    <div v-if="errorMessage" role="alert" class="text-sm text-red-500 bg-red-50 border border-red-200 rounded-lg px-3 py-2 whitespace-pre-line">
                        {{ errorMessage }}
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input
                            id="email"
                            v-model="email"
                            type="email"
                            required
                            autocomplete="email"
                            :class="[
                                'mt-1 w-full rounded-lg bg-gray-50 border px-3 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500',
                                emailError ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300'
                            ]"
                        >
                        <p v-if="emailError" class="mt-1 text-sm text-red-500" role="alert">{{ emailError }}</p>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <div class="relative mt-1">
                            <input
                                id="password"
                                v-model="password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                autocomplete="current-password"
                                class="w-full rounded-lg bg-gray-50 border border-gray-300 px-3 py-2 pr-11 text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            >
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-1 top-1/2 -translate-y-1/2 rounded-lg p-2 text-gray-500 hover:text-gray-700 cursor-pointer"
                                aria-label="Toggle password visibility"
                            >
                                <svg
                                    v-if="showPassword"
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                                <svg
                                    v-else
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="passwordError" class="mt-1 text-sm text-red-500" role="alert">{{ passwordError }}</p>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <label class="flex items-center gap-2 py-1 text-sm text-gray-500">
                            <input v-model="remember" type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            Ingat saya
                        </label>
                        <RouterLink to="/register" class="inline-block py-1 text-sm text-primary-600 hover:text-primary-500">
                            Daftar
                        </RouterLink>
                    </div>

                    <button
                        type="submit"
                        :disabled="submitting"
                        class="w-full rounded-lg bg-primary-600 hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed px-3 py-2 font-medium text-white transition"
                    >
                        {{ submitting ? 'Memproses…' : 'Masuk' }}
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-gray-500">
                    Belum punya akun?
                    <RouterLink to="/register" class="font-semibold text-primary-600 hover:text-primary-500">Daftar</RouterLink>
                </p>
            </div>
        </div>

        <!-- Right: Visual Panel -->
        <div class="relative hidden w-1/2 items-center justify-center overflow-hidden bg-primary-700 lg:flex">
            <div class="relative z-10 max-w-md px-10 text-center">
                <div class="rotate-1">
                    <AppMockCard class="shadow-2xl shadow-primary-900/30" />
                </div>

                <div class="mt-8">
                    <h2 class="text-2xl font-bold text-white">
                        Satu transaksi, semua ter-update.
                    </h2>
                    <p class="mt-3 text-primary-100">
                        Stok, keuangan, riwayat pelanggan dan laporan semua itu mengalir dari satu pencatatan. Di TokoKu Bisa dipakai secara offline maupun online. Semua data kamu dijamin tersinkron otomatis.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>