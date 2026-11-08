<template>
    <section class="mx-auto mt-12 max-w-6xl sm:mt-16">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-gray-900 sm:text-4xl">Sebelum dan sesudah pakai TokoKu</h2>
            <p class="mx-auto mt-3 max-w-xl text-base text-gray-500 sm:mt-4">
                Cara lama bikin data bisnis tercecer. TokoKu menyatukannya jadi satu alur kerja.
            </p>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-2 md:gap-6">
            <div class="rounded-2xl border border-dashed border-gray-300 bg-neutral-50 p-5 sm:p-6">
                <p class="text-xs font-semibold tracking-wide text-gray-500 uppercase">Tanpa TokoKu</p>

                <ul class="mt-4 space-y-3">
                    <li v-for="item in pains" :key="item" class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded bg-gray-200 text-[11px] font-bold text-gray-500">
                            ✕
                        </span>
                        <span class="text-sm leading-relaxed text-gray-500">{{ item }}</span>
                    </li>
                </ul>
            </div>

            <div class="flex flex-col rounded-2xl bg-neutral-900 p-5 text-white sm:p-6">
                <p class="w-fit rounded-full bg-white/10 px-2.5 py-0.5 text-xs font-semibold tracking-wide uppercase">
                    Dengan TokoKu
                </p>

                <ul class="mt-4 flex-1 space-y-3">
                    <li v-for="item in outcomes" :key="item" class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded bg-white text-[11px] font-bold text-neutral-900">
                            ✓
                        </span>
                        <span class="text-sm leading-relaxed text-neutral-100">{{ item }}</span>
                    </li>
                </ul>

                <div class="mt-5 rounded-xl border border-white/15 bg-white/5 px-4 py-3">
                    <p class="text-sm font-semibold text-white">Rata-rata setup: 9 menit</p>
                    <p class="text-xs text-neutral-400">Dari daftar sampai transaksi pertama tercatat.</p>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
const pains = [
    'Catat penjualan di buku, stok di Excel, keuangan di kepala.',
    'Stok sering minus atau kehabisan tanpa sadar.',
    'Lupa pelanggan langganan yang sudah lama tidak kembali.',
    'Tutup toko baru sadar laba tidak ketahuan.',
    'Data tercecer, susah dipakai untuk ambil keputusan.',
];

const outcomes = [
    'Satu input transaksi: stok, keuangan, pelanggan ter-update otomatis.',
    'Peringatan restock dan stok mati muncul sebelum jadi masalah.',
    'Pelanggan berisiko hilang ditandai dan siap di follow-up.',
    'Laba, omzet, dan pengeluaran terlihat real-time.',
    'Insight dan laporan siap dipakai untuk ambil keputusan.',
];
</script>
