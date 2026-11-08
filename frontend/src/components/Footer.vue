<template>
    <footer class="overflow-hidden border-t border-gray-200 bg-gray-50 text-gray-900">
        <div class="mx-auto max-w-6xl px-4 pt-10 sm:pt-12">
            <div class="flex flex-col gap-7 sm:flex-row sm:items-start sm:justify-between sm:gap-12">
                <nav class="grid grid-cols-3 gap-x-4 gap-y-7 sm:flex sm:gap-x-14" aria-label="Navigasi footer">
                    <div>
                        <h4 class="mb-2.5 text-sm font-semibold text-gray-900">Produk</h4>
                        <ul class="space-y-0.5 text-sm text-gray-500">
                            <li><RouterLink to="/features/penjualan" class="inline-block py-1 transition hover:text-gray-900">Penjualan</RouterLink></li>
                            <li><RouterLink to="/features/inventori" class="inline-block py-1 transition hover:text-gray-900">Inventori</RouterLink></li>
                            <li><RouterLink to="/features/pelanggan" class="inline-block py-1 transition hover:text-gray-900">Pelanggan</RouterLink></li>
                            <li><RouterLink to="/features/insight" class="inline-block py-1 transition hover:text-gray-900">Insight</RouterLink></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="mb-2.5 text-sm font-semibold text-gray-900">Perusahaan</h4>
                        <ul class="space-y-0.5 text-sm text-gray-500">
                            <li><RouterLink to="/about" class="inline-block py-1 transition hover:text-gray-900">Tentang</RouterLink></li>
                            <li><RouterLink to="/careers" class="inline-block py-1 transition hover:text-gray-900">Karir</RouterLink></li>
                            <li><RouterLink to="/blog" class="inline-block py-1 transition hover:text-gray-900">Blog</RouterLink></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="mb-2.5 text-sm font-semibold text-gray-900">Sumber daya</h4>
                        <ul class="space-y-0.5 text-sm text-gray-500">
                            <li><RouterLink to="/help" class="inline-block py-1 transition hover:text-gray-900">Bantuan</RouterLink></li>
                            <li><RouterLink to="/docs" class="inline-block py-1 transition hover:text-gray-900">Dokumentasi</RouterLink></li>
                            <li><RouterLink to="/status" class="inline-block py-1 transition hover:text-gray-900">Status</RouterLink></li>
                        </ul>
                    </div>
                </nav>

                <button
                    type="button"
                    class="self-start rounded-full border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-400 hover:bg-white"
                    @click="backToTop"
                >
                    Kembali ke atas <span aria-hidden="true">↑</span>
                </button>
            </div>

            <div class="mt-10 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500">
                <span>&copy; 2026 TokoKu</span>
                <RouterLink to="/privacy" class="transition hover:text-gray-900">Kebijakan privasi</RouterLink>
                <RouterLink to="/terms" class="transition hover:text-gray-900">Syarat penggunaan</RouterLink>
            </div>

            <div class="-mb-[0.06em] mt-3 select-none sm:mt-5" aria-hidden="true">
                <span class="block whitespace-nowrap text-[27vw] font-extrabold leading-[0.72] tracking-[-0.06em] text-gray-900 sm:text-[21vw] lg:text-[18rem]">TokoKu</span>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { RouterLink } from 'vue-router';

function backToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>
