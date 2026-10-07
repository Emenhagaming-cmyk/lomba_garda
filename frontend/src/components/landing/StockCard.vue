<template>
    <div class="rounded-2xl border border-gray-200 bg-white p-4 text-left shadow-lg shadow-gray-200/50">
        <div class="flex items-center justify-between">
            <p class="text-xs font-medium text-gray-600">Stok kritis</p>
            <span class="rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-semibold text-red-500">3 item</span>
        </div>

        <ul class="mt-3 space-y-3">
            <li>
                <div class="flex items-center justify-between text-[11px]">
                    <span class="text-gray-700">Tepung terigu</span>
                    <span class="font-medium text-gray-400">2 kg</span>
                </div>
                <div class="mt-1.5 h-1 w-full rounded-full bg-gray-100">
                    <div class="h-1 w-1/4 rounded-full bg-amber-400"></div>
                </div>
            </li>
            <li>
                <div class="flex items-center justify-between text-[11px]">
                    <span class="text-gray-700">Gula pasir</span>
                    <span class="font-medium text-gray-400">1,5 kg</span>
                </div>
                <div class="mt-1.5 h-1 w-full rounded-full bg-gray-100">
                    <div class="h-1 w-1/6 rounded-full bg-red-400"></div>
                </div>
            </li>
            <li>
                <div class="flex items-center justify-between text-[11px]">
                    <span class="text-gray-700">Margarin</span>
                    <span class="font-medium text-gray-400">2 pcs</span>
                </div>
                <div class="mt-1.5 h-1 w-full rounded-full bg-gray-100">
                    <div class="h-1 w-2/5 rounded-full bg-amber-400"></div>
                </div>
            </li>
        </ul>
    </div>
</template>
