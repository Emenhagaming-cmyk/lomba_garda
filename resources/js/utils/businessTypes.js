export const BUSINESS_TEMPLATES = {
    fnb: {
        label: 'Kopi / Kafe',
        emoji: '☕',
        description: 'Kopi, makanan ringan, dan minuman',
        categories: ['Kopi', 'Minuman', 'Makanan', 'Cemilan'],
    },
    'food-drink': {
        label: 'Makanan & Minuman',
        emoji: '🍜',
        description: 'Restoran, warung, hingga katering',
        categories: ['Makanan Berat', 'Minuman', 'Cemilan', 'Topping'],
    },
    fashion: {
        label: 'Fashion / Baju',
        emoji: '👕',
        description: 'Pakaian, aksesori, dan sepatu',
        categories: ['Atasan', 'Bawahan', 'Aksesori', 'Sepatu'],
    },
    retail: {
        label: 'Retail / Sembako',
        emoji: '🏪',
        description: 'Toko kelontong dan kebutuhan sehari-hari',
        categories: ['Sembako', 'Minuman', 'Snack', 'Rumah Tangga'],
    },
    reseller: {
        label: 'Reseller / Distributor',
        emoji: '📦',
        description: 'Jual ulang produk dari supplier',
        categories: ['Produk Umum', 'Elektronik', 'Kosmetik', 'Kebutuhan'],
    },
    service: {
        label: 'Jasa / Layanan',
        emoji: '✂️',
        description: 'Salon, laundry, bengkel, dan sejenisnya',
        categories: ['Layanan', 'Produk Pendukung'],
    },
    production: {
        label: 'Produksi / Manufaktur',
        emoji: '🏭',
        description: 'Membuat produk sendiri dari bahan baku',
        categories: ['Bahan Baku', 'Produk Jadi'],
    },
    other: {
        label: 'Lainnya',
        emoji: '➕',
        description: 'Usaha yang belum tercantum',
        categories: ['Umum'],
    },
};

export const BUSINESS_TYPES = Object.entries(BUSINESS_TEMPLATES).map(([value, template]) => ({
    value,
    label: template.label,
    emoji: template.emoji,
    description: template.description,
    categories: template.categories,
}));

export function businessTypeLabel(type) {
    return BUSINESS_TEMPLATES[type]?.label || type;
}

export function businessTemplate(type) {
    return BUSINESS_TEMPLATES[type] || BUSINESS_TEMPLATES.other;
}