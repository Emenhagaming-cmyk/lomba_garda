import { defineStore } from 'pinia';
import { http } from '../api/client';
import { businessTemplate } from '../utils/businessTypes';

export const useBusinessStore = defineStore('business', {
    state: () => ({
        business: null,
        loading: false,
    }),

    getters: {
        hasBusiness: (state) => state.business !== null,
        categories: (state) => businessTemplate(state.business?.type).categories,
    },

    actions: {
        async fetchBusiness() {
            if (this.loading) {
                return;
            }

            this.loading = true;

            try {
                const { data } = await http.get('/business');
                this.business = data.data.business ?? null;
            } catch {
                this.business = null;
            } finally {
                this.loading = false;
            }
        },

        async saveBusiness(payload) {
            const { data } = await http.post('/business', payload);
            this.business = data.data.business;

            return this.business;
        },
    },
});