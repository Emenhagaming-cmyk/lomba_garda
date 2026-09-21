export function formatRupiah(value) {
    if (value === null || value === undefined || Number.isNaN(Number(value))) {
        return 'Rp —';
    }

    return 'Rp ' + Number(value).toLocaleString('id-ID');
}

export function formatCompactRupiah(value) {
    if (value === null || value === undefined || Number.isNaN(Number(value))) {
        return 'Rp 0';
    }

    return 'Rp ' + Intl.NumberFormat('id-ID', { notation: 'compact', maximumFractionDigits: 1 }).format(Number(value));
}

export function formatNumber(value) {
    if (value === null || value === undefined) {
        return '—';
    }

    return Number(value).toLocaleString('id-ID');
}

export function formatDate(iso) {
    if (!iso) {
        return '—';
    }

    return new Date(iso).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

export function formatDateTime(iso) {
    if (!iso) {
        return '—';
    }

    return new Date(iso).toLocaleString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
}

export const PAYMENT_METHODS = [
    { value: 'cash', label: 'Tunai' },
    { value: 'qris', label: 'QRIS' },
    { value: 'transfer', label: 'Transfer Bank' },
    { value: 'edc', label: 'EDC / Kartu' },
    { value: 'other', label: 'Lainnya' },
];

export function paymentMethodLabel(value) {
    return PAYMENT_METHODS.find((method) => method.value === value)?.label || value;
}

export const SEGMENTS = {
    vip: { label: 'VIP', classes: 'bg-violet-50 text-violet-700' },
    loyal: { label: 'Loyal', classes: 'bg-emerald-50 text-emerald-700' },
    at_risk: { label: 'Melemah', classes: 'bg-amber-50 text-amber-700' },
    inactive: { label: 'Tidak Aktif', classes: 'bg-gray-100 text-gray-600' },
    new: { label: 'Baru', classes: 'bg-blue-50 text-blue-700' },
};

export function segmentLabel(segment) {
    return SEGMENTS[segment]?.label || segment;
}

export function segmentClasses(segment) {
    return SEGMENTS[segment]?.classes || 'bg-gray-100 text-gray-600';
}

export function badgeClasses(stock, minStock) {
    if (stock <= 0) {
        return 'bg-red-50 text-red-700';
    }

    if (stock <= minStock) {
        return 'bg-amber-50 text-amber-700';
    }

    return 'bg-emerald-50 text-emerald-700';
}

export function stockStatusLabel(stock, minStock) {
    if (stock <= 0) {
        return 'Habis';
    }

    if (stock <= minStock) {
        return 'Menipis';
    }

    return 'Cukup';
}

export const LEAD_STAGES = [
    { value: 'new', label: 'Baru' },
    { value: 'contacted', label: 'Dihubungi' },
    { value: 'qualified', label: 'Berkualitas' },
    { value: 'offer', label: 'Penawaran' },
    { value: 'converted', label: 'Konversi' },
];

export function leadStageLabel(stage) {
    return LEAD_STAGES.find((item) => item.value === stage)?.label || stage;
}

export function leadStageClasses(stage) {
    const map = {
        new: 'bg-blue-50 text-blue-700',
        contacted: 'bg-indigo-50 text-indigo-700',
        qualified: 'bg-emerald-50 text-emerald-700',
        offer: 'bg-amber-50 text-amber-700',
        converted: 'bg-violet-50 text-violet-700',
    };

    return map[stage] || 'bg-gray-100 text-gray-600';
}