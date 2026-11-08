const STORAGE_KEY = 'tokoku.accounts';

export function loadAccounts() {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        const parsed = raw ? JSON.parse(raw) : [];

        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
}

function persist(accounts) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(accounts));
}

export function saveAccount({ name, email, password }) {
    if (!email || !password) {
        return loadAccounts();
    }

    const accounts = loadAccounts();
    const existing = accounts.find((account) => account.email === email);
    const next = {
        name: name || existing?.name || email,
        email,
        password,
        last_active_at: new Date().toISOString(),
    };

    const merged = [
        next,
        ...accounts.filter((account) => account.email !== email),
    ].slice(0, 5);

    persist(merged);

    return merged;
}

export function removeAccount(email) {
    const merged = loadAccounts().filter((account) => account.email !== email);
    persist(merged);

    return merged;
}

export function clearAccounts() {
    localStorage.removeItem(STORAGE_KEY);

    return [];
}
