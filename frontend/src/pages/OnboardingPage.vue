<script setup>
import { computed, ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import { useBusinessStore } from "../stores/business";
import { BUSINESS_TYPES } from "../utils/businessTypes";
import { formatApiError } from "../api/client";
import BusinessTypeIcon from "../components/BusinessTypeIcon.vue";

const router = useRouter();
const auth = useAuthStore();
const business = useBusinessStore();

const selectedType = ref(null);
const name = ref("");
const error = ref("");
const saving = ref(false);

const selectedTemplate = computed(() =>
    BUSINESS_TYPES.find((item) => item.value === selectedType.value),
);

const steps = computed(() => {
    if (!selectedType.value) {
        return 1;
    }

    return 2;
});

const title = computed(() => {
    if (!selectedType.value) {
        return "Usaha kamu bergerak di bidang apa?";
    }

    return "Nama usahamu apa?";
});

const subtitle = computed(() => {
    if (!selectedType.value) {
        return "Pilih jenis usaha kamu. Kami akan menyesuaikan kategori produk kamu agar lebih mudah memulai menggunakan layanan kami.";
    }

    return `Kamu memilih "${selectedTemplate.value.label}". Lanjut dengan memberi nama usaha.`;
});

async function submit() {
    if (!selectedType.value || !name.value.trim()) {
        error.value = "Pilih jenis usaha dan isi nama usaha kamu.";

        return;
    }

    saving.value = true;
    error.value = "";

    try {
        await business.saveBusiness({
            name: name.value.trim(),
            type: selectedType.value,
            currency: "IDR",
            payment_methods: ["cash", "qris"],
        });

        await router.replace({ name: "dashboard" });
    } catch (err) {
        error.value = formatApiError(
            err,
            "Waduh gagal nyimpan usaha kamu. Coba lagi ya.",
        );
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <div
        class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-10"
    >
        <div class="w-full max-w-2xl">
            <div class="mb-6 flex items-center justify-center gap-2.5">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-100 p-2 ring-1 ring-gray-200"
                    aria-hidden="true"
                >
                    <img
                        :src="'/logo.png'"
                        alt=""
                        class="h-full w-full object-contain"
                    />
                </div>
                <div>
                    <p class="text-lg font-bold text-gray-900">TokoKu</p>
                    <p class="text-xs text-gray-500">
                        Atur usahamu dan mulai berbisnis lebih mudah
                    </p>
                </div>
            </div>

            <div
                class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8"
            >
                <!-- Stepper -->
                <div
                    class="mb-6 flex items-center justify-center gap-2 text-xs font-medium"
                >
                    <template v-for="(step, index) in 2" :key="step">
                        <span
                            class="flex h-6 w-6 items-center justify-center rounded-full"
                            :class="
                                steps >= step
                                    ? 'bg-primary-600 text-white'
                                    : 'bg-gray-100 text-gray-400'
                            "
                        >
                            {{ step }}
                        </span>
                        <span
                            v-if="index === 0"
                            class="h-px w-10 bg-gray-200"
                        ></span>
                    </template>
                </div>

                <h1
                    class="text-center text-xl font-bold text-gray-900 sm:text-2xl"
                >
                    {{ title }}
                </h1>
                <p
                    class="mx-auto mt-2 max-w-md text-center text-sm text-gray-500"
                >
                    {{ subtitle }}
                </p>

                <!-- Step 1: pilih tipe usaha -->
                <div
                    v-if="!selectedType"
                    class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4"
                >
                    <button
                        v-for="item in BUSINESS_TYPES"
                        :key="item.value"
                        type="button"
                        class="group flex flex-col items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-4 text-center transition hover:border-primary-400 hover:bg-primary-50/50 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500"
                        @click="selectedType = item.value"
                    >
                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-50 text-primary-600 transition group-hover:bg-primary-100"
                        >
                            <BusinessTypeIcon
                                :name="item.icon"
                                class="h-5 w-5"
                            />
                        </span>
                        <span
                            class="text-sm font-semibold text-gray-800 group-hover:text-primary-700"
                            >{{ item.label }}</span
                        >
                        <span class="text-[11px] leading-tight text-gray-400">{{
                            item.description
                        }}</span>
                    </button>
                </div>

                <!-- Step 2: nama usaha -->
                <form v-else class="mt-6" @submit.prevent="submit">
                    <label
                        for="business-name"
                        class="block text-sm font-medium text-gray-700"
                        >Nama usaha</label
                    >
                    <input
                        id="business-name"
                        v-model="name"
                        type="text"
                        maxlength="255"
                        autofocus
                        placeholder="contoh: Kopi Tertial, Toko Baju Andi"
                        class="mt-2 block w-full rounded-xl border border-gray-300 px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/30"
                    />

                    <div class="mt-4 rounded-xl bg-primary-50 px-4 py-3">
                        <p
                            class="flex items-center gap-2 text-sm font-semibold text-primary-700"
                        >
                            <BusinessTypeIcon
                                :name="selectedTemplate.icon"
                                class="h-4 w-4"
                            />
                            {{ selectedTemplate.label }}
                        </p>
                        <p class="mt-1 text-xs text-primary-600">
                            Kategori awal:
                            {{ selectedTemplate.categories.join(" · ") }}
                        </p>
                    </div>

                    <p
                        v-if="error"
                        class="mt-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600"
                    >
                        {{ error }}
                    </p>

                    <div class="mt-6 flex items-center justify-between gap-3">
                        <button
                            type="button"
                            class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-50"
                            @click="selectedType = null"
                        >
                            Kembali
                        </button>
                        <button
                            type="submit"
                            :disabled="saving"
                            class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {{ saving ? "Menyimpan…" : "Masuk ke Dashboard" }}
                            <svg
                                v-if="!saving"
                                viewBox="0 0 24 24"
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.5"
                                aria-hidden="true"
                            >
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

            <p class="mt-4 text-center text-xs text-gray-400">
                Kamu login sebagai {{ auth.user?.name }} ·
                <RouterLink
                    :to="{ name: 'landing' }"
                    class="font-medium text-primary-600 hover:underline"
                    >logout</RouterLink
                >
            </p>
        </div>
    </div>
</template>
