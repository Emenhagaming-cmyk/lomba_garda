import axios from 'axios';
import { addToSyncQueue } from '../utils/indexedDB';
import { useSyncStore } from '../stores/sync';

export const http = axios.create({
    baseURL: '/api',
    withCredentials: true,
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

http.interceptors.response.use(
    (response) => response,
    async (error) => {
        if (error.code === 'ECONNABORTED' || !error.response) {
            if (!navigator.onLine) {
                const syncStore = useSyncStore();
                const config = error.config;

                if (config && config.method !== 'get') {
                    await syncStore.queueForSync({
                        endpoint: config.url,
                        method: config.method.toUpperCase(),
                        data: config.data,
                        headers: config.headers
                    });
                }
            }
        }
        return Promise.reject(error);
    }
);

export async function fetchCsrfCookie() {
    await axios.get('/sanctum/csrf-cookie', {
        baseURL: '',
        withCredentials: true,
    });
}

export function formatApiError(error, fallback) {
    const payload = error?.response?.data?.error;
    const message = payload?.message;

    if (payload?.details) {
        return Object.values(payload.details).flat().join('\n');
    }

    if (message) {
        return message;
    }

    const status = error?.response?.status;

    if (status === 419) {
        return 'Sesi kedaluwarsa. Muat ulang halaman lalu coba lagi.';
    }

    if (status >= 500) {
        return 'Terjadi kesalahan server. Coba lagi beberapa saat.';
    }

    return fallback;
}

export async function requestWithOfflineSupport(config, offlineData = null) {
    try {
        const response = await http(config);
        return response;
    } catch (error) {
        if (!navigator.onLine && config.method !== 'get') {
            const syncStore = useSyncStore();
            await syncStore.queueForSync({
                endpoint: config.url,
                method: config.method.toUpperCase(),
                data: config.data,
                headers: config.headers
            });

            return {
                data: {
                    data: offlineData,
                    offline: true,
                    message: 'Data tersimpan lokal, akan disinkronkan saat online'
                }
            };
        }
        throw error;
    }
}