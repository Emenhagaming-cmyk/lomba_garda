import axios from 'axios';

export const http = axios.create({
    baseURL: '/api',
    withCredentials: true,
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

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

    return message || fallback;
}