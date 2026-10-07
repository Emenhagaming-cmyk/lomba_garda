<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { formatApiError } from '../api/client';
import AppMockCard from '../components/landing/AppMockCard.vue';

const auth = useAuthStore();
const router = useRouter();

const name = ref('');
const email = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const errorMessage = ref('');
const submitting = ref(false);

async function submit() {
    errorMessage.value = '';
    submitting.value = true;

    try {
        await auth.register(name.value, email.value, password.value, passwordConfirmation.value);
        await router.replace({ name: 'dashboard' });
    } catch (error) {
        errorMessage.value = formatApiError(error, 'Unable to create your account.');
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <div class="min-h-screen flex bg-white">
        <!-- Left: Form -->
        <div class="relative flex w-full items-center justify-center px-6 py-12 lg:w-1/2">
            <RouterLink
                to="/"
                class="absolute top-6 left-6 flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-sm font-semibold text-primary-600 transition hover:border-primary-200 hover:bg-primary-50"
            >
                <svg viewBox="0 0 16 16" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10 3l-5 5 5 5"></path>
                </svg>
                Kembali
            </RouterLink>

            <div class="w-full max-w-sm">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-primary-600">TokoKu</h1>
                    <p class="text-gray-500 mt-2">Buat akun dan mulai kelola bisnis UMKM-mu</p>
                </div>

                <form @submit.prevent="submit" class="bg-white border border-gray-200 rounded-2xl p-6 space-y-4 shadow-sm">
                    <div v-if="errorMessage" role="alert" class="text-sm text-red-500 bg-red-50 border border-red-200 rounded-lg px-3 py-2 whitespace-pre-line">
                        {{ errorMessage }}
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama</label>
                        <input
                            id="name"
                            v-model="name"
                            type="text"
                            required
                            autocomplete="name"
                            class="mt-1 w-full rounded-lg bg-gray-50 border border-gray-300 px-3 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        >
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input
                            id="email"
                            v-model="email"
                            type="email"
                            required
                            autocomplete="email"
                            class="mt-1 w-full rounded-lg bg-gray-50 border border-gray-300 px-3 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        >
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input
                            id="password"
                            v-model="password"
                            type="password"
                            required
                            autocomplete="new-password"
                            minlength="8"
                            class="mt-1 w-full rounded-lg bg-gray-50 border border-gray-300 px-3 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        >
                    </div>

                    <div>
                        <label for="password-confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                        <input
                            id="password-confirmation"
                            v-model="passwordConfirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                            class="mt-1 w-full rounded-lg bg-gray-50 border border-gray-300 px-3 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        >
                    </div>

                    <button
                        type="submit"
                        :disabled="submitting"
                        class="w-full rounded-lg bg-primary-600 hover:bg-primary-700 disabled:opacity-50 px-3 py-2 font-medium text-white transition"
                    >
                        {{ submitting ? 'Mendaftar…' : 'Daftar' }}
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-gray-500">
                    Sudah punya akun?
                    <RouterLink to="/login" class="font-semibold text-primary-600 hover:text-primary-500">Masuk</RouterLink>
                </p>
            </div>
        </div>

        <!-- Right: Visual Panel -->
        <div class="relative hidden w-1/2 items-center justify-center overflow-hidden bg-gradient-to-br from-primary-600 via-primary-700 to-indigo-800 lg:flex">
            <div aria-hidden="true" class="pointer-events-none absolute inset-0">
                <div class="absolute -top-24 -left-24 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute -right-24 -bottom-24 h-96 w-96 rounded-full bg-indigo-300/20 blur-3xl"></div>
                <div class="absolute top-1/3 right-1/4 h-40 w-40 rounded-full bg-blue-300/20 blur-2xl"></div>
            </div>

            <div class="relative z-10 max-w-md px-10 text-center">
                <div class="-rotate-1">
                    <AppMockCard class="shadow-2xl shadow-primary-900/30" />
                </div>

                <div class="mt-8">
                    <h2 class="text-2xl font-bold text-white">
                        Gratis selamanya, tanpa kartu kredit.
                    </h2>
                    <p class="mt-3 text-primary-100">
                        Daftar sekarang, catat transaksi pertamamu dalam hitungan menit — stok, keuangan, dan pelanggan ter-update otomatis.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>