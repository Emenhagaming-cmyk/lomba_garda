<template>
    <div class="rounded-2xl border border-gray-200 bg-white text-left shadow-lg shadow-gray-200/50">
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-2.5">
            <span class="text-sm font-bold text-emerald-600">BelanjaYuk!</span>
            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-gray-500">OWNER</span>
        </div>

        <div class="space-y-4 p-4">
            <div>
                <p class="text-[10px] text-gray-400 uppercase">Dashboard</p>
                <div class="mt-2 grid grid-cols-3 gap-2">
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-2.5">
                        <p class="text-[9px] text-gray-400">Stok kritis</p>
                        <p class="mt-0.5 text-sm font-bold text-red-500">3</p>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-2.5">
                        <p class="text-[9px] text-gray-400">Rekomendasi beli</p>
                        <p class="mt-0.5 text-sm font-bold text-emerald-600">Rp 1,2 jt</p>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-2.5">
                        <p class="text-[9px] text-gray-400">Nilai inventori</p>
                        <p class="mt-0.5 text-sm font-bold text-gray-900">12,4 jt</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-100 bg-gray-50 p-3">
                <div class="flex items-center justify-between">
                    <p class="text-[10px] font-medium text-gray-500">Forecast 7 hari</p>
                    <span class="rounded-full bg-emerald-50 px-1.5 py-0.5 text-[9px] font-semibold text-emerald-600">+12%</span>
                </div>
                <svg viewBox="0 0 200 40" preserveAspectRatio="none" class="mt-2 h-10 w-full" role="img" aria-label="Grafik forecast">
                    <path
                        d="M0,32 L22,27 L44,30 L66,20 L88,25 L110,14 L132,19 L154,8 L176,13 L199,5"
                        fill="none"
                        stroke="#10b981"
                        stroke-width="2"
                        stroke-linecap="round"
                    ></path>
                </svg>
            </div>

            <div class="rounded-xl border border-gray-100 bg-gray-50 p-3">
                <p class="text-[10px] font-medium text-gray-500">Rekomendasi pembelian</p>
                <ul class="mt-2 space-y-1.5 text-[11px] text-gray-600">
                    <li class="flex items-center justify-between">
                        <span>Telur ayam</span>
                        <span class="rounded bg-gray-100 px-1.5 py-0.5 font-medium">4 tray</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span>Tepung terigu</span>
                        <span class="rounded bg-gray-100 px-1.5 py-0.5 font-medium">25 kg</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
