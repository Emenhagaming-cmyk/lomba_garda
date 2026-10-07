export const BUSINESS_TEMPLATES = {
    fnb: {
        label: 'Kopi / Kafe',
        icon: 'coffee',
        description: 'Kopi, makanan ringan, dan minuman',
        categories: ['Kopi', 'Minuman', 'Makanan', 'Cemilan'],
    },
    'food-drink': {
        label: 'Makanan & Minuman',
        icon: 'utensils',
        description: 'Restoran, warung, hingga katering',
        categories: ['Makanan Berat', 'Minuman', 'Cemilan', 'Topping'],
    },
    fashion: {
        label: 'Fashion / Baju',
        icon: 'shirt',
        description: 'Pakaian, aksesori, dan sepatu',
        categories: ['Atasan', 'Bawahan', 'Aksesori', 'Sepatu'],
    },
    retail: {
        label: 'Retail / Sembako',
        icon: 'store',
        description: 'Toko kelontong dan kebutuhan sehari-hari',
        categories: ['Sembako', 'Minuman', 'Snack', 'Rumah Tangga'],
    },
    reseller: {
        label: 'Reseller / Distributor',
        icon: 'package',
        description: 'Jual ulang produk dari supplier',
        categories: ['Produk Umum', 'Elektronik', 'Kosmetik', 'Kebutuhan'],
    },
    service: {
        label: 'Jasa / Layanan',
        icon: 'scissors',
        description: 'Salon, laundry, bengkel, dan sejenisnya',
        categories: ['Layanan', 'Produk Pendukung'],
    },
    production: {
        label: 'Produksi / Manufaktur',
        icon: 'factory',
        description: 'Membuat produk sendiri dari bahan baku',
        categories: ['Bahan Baku', 'Produk Jadi'],
    },
    other: {
        label: 'Lainnya',
        icon: 'plus',
        description: 'Usaha yang belum tercantum',
        categories: ['Umum'],
    },
};

export const BUSINESS_TYPES = Object.entries(BUSINESS_TEMPLATES).map(([value, template]) => ({
    value,
    label: template.label,
    icon: template.icon,
    description: template.description,
    categories: template.categories,
}));

export function businessTypeLabel(type) {
    return BUSINESS_TEMPLATES[type]?.label || type;
}

export function businessTemplate(type) {
    return BUSINESS_TEMPLATES[type] || BUSINESS_TEMPLATES.other;
}