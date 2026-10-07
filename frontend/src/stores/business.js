import { defineStore } from 'pinia';
import { http } from '../api/client';
import { businessTemplate } from '../utils/businessTypes';

let inflight = null;

export const useBusinessStore = defineStore('business', {
    state: () => ({
        business: null,
        loading: false,
        loaded: false,
    }),

    getters: {
        hasBusiness: (state) => state.business !== null,
        categories: (state) => businessTemplate(state.business?.type).categories,
    },

    actions: {
        fetchBusiness() {
            if (inflight) {
                return inflight;
            }

            this.loading = true;

            inflight = (async () => {
                try {
                    const { data } = await http.get('/business');
                    this.business = data.data.business ?? null;
                } catch {
                    this.business = null;
                } finally {
                    this.loading = false;
                    this.loaded = true;
                    inflight = null;
                }
            })();

            return inflight;
        },

        async saveBusiness(payload) {
            const { data } = await http.post('/business', payload);
            this.business = data.data.business;
            this.loaded = true;
            inflight = null;

            return this.business;
        },

        reset() {
            this.business = null;
            this.loading = false;
            this.loaded = false;
            inflight = null;
        },
    },
});
