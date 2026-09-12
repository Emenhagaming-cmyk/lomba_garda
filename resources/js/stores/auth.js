import { defineStore } from 'pinia';
import { fetchCsrfCookie, http } from '../api/client';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        loading: true,
    }),

    getters: {
        isAuthenticated: (state) => state.user !== null,
    },

    actions: {
        async fetchUser() {
            this.loading = true;

            try {
                const { data } = await http.get('/user');
                this.user = data.data.user;
            } catch (error) {
                this.user = null;
            } finally {
                this.loading = false;
            }
        },

        async login(email, password) {
            await fetchCsrfCookie();
            await http.post('/login', { email, password });
            await this.fetchUser();
        },

        async register(name, email, password, passwordConfirmation) {
            await fetchCsrfCookie();
            await http.post('/register', {
                name,
                email,
                password,
                password_confirmation: passwordConfirmation,
            });
            await this.fetchUser();
        },

        async logout() {
            await fetchCsrfCookie();
            await http.post('/logout');
            this.user = null;
        },
    },
});