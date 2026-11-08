const AVATAR_CLASSES = [
    'bg-primary-600',
    'bg-emerald-600',
    'bg-sky-600',
    'bg-amber-600',
    'bg-rose-600',
    'bg-violet-600',
    'bg-teal-600',
    'bg-orange-600',
];

export function initials(name = '', email = '') {
    const source = String(name || email || '?').trim();
    const parts = source.split(/\s+/).filter(Boolean);

    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }

    return source.slice(0, 2).toUpperCase();
}

export function avatarClass(seed = '') {
    const source = String(seed || '?');
    let hash = 0;

    for (let i = 0; i < source.length; i++) {
        hash = (hash * 31 + source.charCodeAt(i)) % 997;
    }

    return AVATAR_CLASSES[hash % AVATAR_CLASSES.length];
}
