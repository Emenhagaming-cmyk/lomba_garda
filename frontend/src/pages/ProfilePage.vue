<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useBusinessStore } from '../stores/business';
import { formatApiError, http } from '../api/client';
import { avatarClass, initials } from '../utils/avatar';
import FloatingNav from '../components/FloatingNav.vue';
import Footer from '../components/Footer.vue';

const auth = useAuthStore();
const business = useBusinessStore();
const router = useRouter();

const user = computed(() => auth.user);
const isSeller = computed(() => business.hasBusiness);

const editing = ref(false);
const name = ref(user.value?.name ?? '');
const saving = ref(false);
const errorMessage = ref('');

const icons = {
    profile: 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 19.5a7.5 7.5 0 0115 0',
    messages: 'M3 6.5A2.5 2.5 0 015.5 4h13A2.5 2.5 0 0121 6.5v8a2.5 2.5 0 01-2.5 2.5H8l-5 3.5v-14z',
    favourite: 'M12 20.5s-7.5-4.7-7.5-10a4.5 4.5 0 018-2.8 4.5 4.5 0 018 2.8c0 5.3-7.5 10-7.5 10z',
    location: 'M12 21s7-5.8 7-11a7 7 0 10-14 0c0 5.2 7 11 7 11zM12 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z',
    settings: 'M4 7h10M18 7h2M4 12h4M12 12h8M4 17h12M20 17h0',
    logout: 'M15 12H3m0 0l3.5-3.5M3 12l3.5 3.5M10 5h9a2 2 0 012 2v10a2 2 0 01-2 2h-9',
};

const menus = computed(() => [
    { key: 'profile', label: 'Profil Saya', enabled: true },
    { key: 'messages', label: 'Pesan', enabled: isSeller.value, hint: isSeller.value ? '' : 'Khusus penjual' },
    { key: 'favourite', label: 'Favorit', enabled: false, hint: 'Segera' },
    { key: 'location', label: 'Lokasi', enabled: false, hint: 'Segera' },
    { key: 'settings', label: 'Pengaturan', enabled: isSeller.value },
    { key: 'logout', label: 'Logout', enabled: true, danger: true },
]);

onMounted(async () => {
    if (!business.loaded) {
        await business.fetchBusiness();
    }

    name.value = user.value?.name ?? '';
});

function openMenu(menu) {
    if (!menu.enabled) {
        return;
    }

    if (menu.key === 'profile') {
        editing.value = true;
        name.value = user.value?.name ?? '';

        return;
    }

    if (menu.key === 'messages') {
        router.push({ name: 'messages' });

        return;
    }

    if (menu.key === 'settings') {
        router.push({ name: 'settings' });

        return;
    }

    if (menu.key === 'logout') {
        handleLogout();
    }
}

async function saveName() {
    errorMessage.value = '';
    saving.value = true;

    try {
        const { data } = await http.put('/profile', { name: name.value });
        auth.user = data.data.user;
        editing.value = false;
    } catch (error) {
        errorMessage.value = formatApiError(error, 'Gagal menyimpan profil.');
    } finally {
        saving.value = false;
    }
}

async function handleLogout() {
    try {
        await auth.logout();
    } catch (error) {
        console.error('Gagal logout di server:', error);
    } finally {
        await router.replace({ name: 'landing' });
    }
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 text-gray-900">
        <FloatingNav />

        <main class="mx-auto w-full max-w-md px-4 pt-24 pb-16 sm:pt-28">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-start gap-3 p-5">
                    <div
                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full text-lg font-semibold text-white"
                        :class="avatarClass(user?.email)"
                    >
                        {{ initials(user?.name, user?.email) }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <h1 class="truncate text-lg font-semibold text-gray-900">{{ user?.name }}</h1>
                        <p class="truncate text-sm text-gray-500">{{ user?.email }}</p>
                        <span
                            class="mt-1.5 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold"
                            :class="isSeller ? 'bg-primary-100 text-primary-700' : 'bg-gray-100 text-gray-600'"
                        >
                            {{ isSeller ? 'Penjual' : 'Pembeli' }}
                        </span>
                    </div>

                    <button
                        type="button"
                        class="shrink-0 rounded-lg border border-gray-200 p-2 text-gray-500 transition hover:border-gray-300 hover:text-gray-900"
                        aria-label="Edit profil"
                        @click="openMenu(menus[0])"
                    >
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75a2.12 2.12 0 013 3L7.5 18.75 3 20l1.25-4.5L16.5 3.75z" />
                        </svg>
                    </button>
                </div>

                <form v-if="editing" class="space-y-3 border-t border-gray-100 px-5 py-4" @submit.prevent="saveName">
                    <p v-if="errorMessage" class="text-sm text-red-600">{{ errorMessage }}</p>
                    <div>
                        <label for="profile-name" class="block text-sm font-medium text-gray-700">Nama tampilan</label>
                        <input
                            id="profile-name"
                            v-model="name"
                            type="text"
                            required
                            class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-500 focus:outline-none"
                        >
                    </div>
                    <div class="flex gap-2">
                        <button
                            type="submit"
                            :disabled="saving"
                            class="rounded-lg bg-primary-600 px-3 py-2 text-sm font-medium text-white transition hover:bg-primary-700 disabled:opacity-50"
                        >
                            {{ saving ? 'Menyimpan…' : 'Simpan' }}
                        </button>
                        <button
                            type="button"
                            class="rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600 transition hover:bg-gray-50"
                            @click="editing = false"
                        >
                            Batal
                        </button>
                    </div>
                </form>

                <ul class="divide-y divide-gray-100 border-t border-gray-100">
                    <li v-for="menu in menus" :key="menu.key">
                        <button
                            type="button"
                            class="flex w-full items-center gap-3 px-5 py-3.5 text-left transition"
                            :class="[
                                menu.enabled ? 'hover:bg-gray-50' : 'cursor-default',
                                menu.danger ? 'text-red-600' : 'text-gray-700',
                            ]"
                            @click="openMenu(menu)"
                        >
                            <span
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg"
                                :class="menu.danger ? 'bg-red-50 text-red-600' : 'bg-gray-100 text-gray-600'"
                            >
                                <svg viewBox="0 0 24 24" class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="icons[menu.key]" />
                                </svg>
                            </span>
                            <span class="flex-1 text-sm font-medium">{{ menu.label }}</span>
                            <span v-if="menu.hint" class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] text-gray-500">
                                {{ menu.hint }}
                            </span>
                            <svg
                                v-else-if="menu.enabled && !menu.danger"
                                viewBox="0 0 24 24"
                                class="h-4 w-4 text-gray-300"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </li>
                </ul>
            </div>

            <div v-if="isSeller" class="mt-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold tracking-wide text-gray-400 uppercase">Toko kamu</p>
                <p class="mt-1 text-base font-semibold text-gray-900">{{ business.business?.name }}</p>
                <p class="mt-1 text-sm text-gray-500">{{ business.business?.address || 'Alamat belum diisi' }}</p>
                <RouterLink
                    to="/dashboard"
                    class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700"
                >
                    Buka Dashboard
                </RouterLink>
            </div>

            <div v-else class="mt-4 rounded-2xl border border-dashed border-primary-300 bg-primary-50 p-5">
                <p class="text-xs font-semibold tracking-wide text-primary-700 uppercase">Jadi bagian dari TokoKu</p>
                <h2 class="mt-2 text-lg font-semibold text-gray-900">Punya toko? Kelola semuanya dari satu tempat.</h2>
                <p class="mt-1.5 text-sm text-gray-600">
                    Catat penjualan sekali, stok dan keuangan ter-update otomatis. Dashboard penjual terbuka setelah kamu
                    mendaftarkan toko.
                </p>
                <RouterLink
                    to="/onboarding"
                    class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700"
                >
                    Gabung sebagai penjual
                </RouterLink>
                <p class="mt-2 text-center text-xs text-gray-500">Gratis, tanpa kartu kredit.</p>
            </div>
        </main>

        <Footer />
    </div>
</template>
