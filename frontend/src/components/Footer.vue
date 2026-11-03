<template>
    <footer class="border-t border-gray-200 bg-gray-50 py-12">
        <div class="mx-auto max-w-6xl px-4 grid grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="col-span-2 lg:col-span-1">
                <div class="flex items-start gap-6">
                    <span class="whitespace-nowrap text-2xl font-bold text-primary-600">TokoKu</span>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-3">Legal</h4>
                        <ul class="space-y-2 text-sm text-gray-500">
                            <li><RouterLink to="/privacy" class="hover:text-primary-600 transition">Kebijakan privasi</RouterLink></li>
                            <li><RouterLink to="/terms" class="hover:text-primary-600 transition">Syarat penggunaan</RouterLink></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div>
                <h4 class="font-semibold text-gray-900 mb-3">Produk</h4>
                <ul class="space-y-2 text-sm text-gray-500">
                    <li><RouterLink to="/features/penjualan" class="hover:text-primary-600 transition">Penjualan</RouterLink></li>
                    <li><RouterLink to="/features/inventori" class="hover:text-primary-600 transition">Inventori</RouterLink></li>
                    <li><RouterLink to="/features/pelanggan" class="hover:text-primary-600 transition">Pelanggan</RouterLink></li>
                    <li><RouterLink to="/features/insight" class="hover:text-primary-600 transition">Insight & laporan</RouterLink></li>
                </ul>
            </div>

            <div>
                <h4 class="font-semibold text-gray-900 mb-3">Company</h4>
                <ul class="space-y-2 text-sm text-gray-500">
                    <li><RouterLink to="/about" class="hover:text-primary-600 transition">Tentang kami</RouterLink></li>
                    <li><RouterLink to="/careers" class="hover:text-primary-600 transition">Karir</RouterLink></li>
                    <li><RouterLink to="/blog" class="hover:text-primary-600 transition">Blog</RouterLink></li>
                </ul>
            </div>

            <div>
                <h4 class="font-semibold text-gray-900 mb-3">Resources</h4>
                <ul class="space-y-2 text-sm text-gray-500">
                    <li><RouterLink to="/help" class="hover:text-primary-600 transition">Bantuan</RouterLink></li>
                    <li><RouterLink to="/docs" class="hover:text-primary-600 transition">Dokumentasi</RouterLink></li>
                    <li><RouterLink to="/status" class="hover:text-primary-600 transition">Status sistem</RouterLink></li>
                </ul>
            </div>
        </div>

        <div class="mt-8 flex flex-col items-center text-sm text-gray-400">
            <span>&copy; 2026 TokoKu</span>
        </div>
    </footer>
</template>

<script setup>
import { RouterLink } from 'vue-router';
</script>