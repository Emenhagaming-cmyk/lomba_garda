<template>
    <div class="w-72 rounded-2xl border border-gray-200 bg-white p-4 text-left shadow-lg shadow-gray-200/50">
        <div class="flex items-center justify-between">
            <p class="text-xs font-medium text-gray-600">Forecast 7 hari</p>
            <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-600">+12%</span>
        </div>

        <svg
            viewBox="0 0 200 72"
            preserveAspectRatio="none"
            class="mt-3 h-24 w-full"
            role="img"
            aria-label="Grafik perkiraan penjualan mingguan"
        >
            <defs>
                <linearGradient id="forecast-fill" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#10b981" stop-opacity="0.2" />
                    <stop offset="100%" stop-color="#10b981" stop-opacity="0" />
                </linearGradient>
            </defs>
            <path
                d="M0,56 L22,50 L44,54 L66,38 L88,44 L110,28 L132,34 L154,18 L176,24 L199,10 L199,72 L0,72 Z"
                fill="url(#forecast-fill)"
            ></path>
            <path
                d="M0,56 L22,50 L44,54 L66,38 L88,44 L110,28 L132,34 L154,18 L176,24 L199,10"
                fill="none"
                stroke="#10b981"
                stroke-width="2"
                stroke-linecap="round"
            ></path>
            <circle cx="154" cy="18" r="3" fill="#10b981"></circle>
        </svg>

        <div class="mt-2 flex justify-between text-[10px] text-gray-400">
            <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span>
        </div>
    </div>
</template>
