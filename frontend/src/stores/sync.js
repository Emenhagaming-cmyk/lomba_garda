import { defineStore } from 'pinia';
import {
    saveTransaction,
    getPendingTransactions,
    updateTransactionStatus,
    addToSyncQueue,
    getSyncQueue,
    updateSyncQueueItem,
    removeSyncQueueItem,
    getSyncStats,
    isOnline,
    onOnline,
    onOffline
} from '../utils/indexedDB';
import { http, formatApiError } from '../api/client';

export const useSyncStore = defineStore('sync', {
    state: () => ({
        isOnline: navigator.onLine,
        syncing: false,
        lastSyncTime: null,
        stats: {
            pending: 0,
            synced: 0,
            failed: 0,
            syncPending: 0,
            syncFailed: 0
        },
        syncErrors: [],
        onlineListener: null,
        offlineListener: null
    }),

    getters: {
        hasPendingSync: (state) => state.stats.syncPending > 0 || state.stats.pending > 0,
        syncStatus: (state) => {
            if (!state.isOnline) return 'offline';
            if (state.syncing) return 'syncing';
            if (state.stats.syncPending > 0) return 'pending';
            if (state.stats.syncFailed > 0) return 'error';
            if (state.stats.pending > 0) return 'pending';
            return 'synced';
        },
        statusLabel: (state) => {
            const status = state.syncStatus;
            const labels = {
                offline: 'Offline',
                syncing: 'Menyinkronkan...',
                pending: 'Menunggu Sinkronisasi',
                error: 'Sinkronisasi Gagal',
                synced: 'Tersinkron'
            };
            return labels[status] || status;
        },
        statusColor: (state) => {
            const status = state.syncStatus;
            const colors = {
                offline: 'bg-gray-500',
                syncing: 'bg-blue-500 animate-pulse',
                pending: 'bg-yellow-500',
                error: 'bg-red-500',
                synced: 'bg-green-500'
            };
            return colors[status] || 'bg-gray-500';
        }
    },

    actions: {
        init() {
            this.isOnline = navigator.onLine;
            this.onlineListener = onOnline(() => {
                this.isOnline = true;
                this.syncAll();
            });
            this.offlineListener = onOffline(() => {
                this.isOnline = false;
            });
            this.refreshStats();
        },

        destroy() {
            if (this.onlineListener) this.onlineListener();
            if (this.offlineListener) this.offlineListener();
        },

        async refreshStats() {
            try {
                this.stats = await getSyncStats();
            } catch (error) {
                console.error('Failed to refresh sync stats:', error);
            }
        },

        async saveOfflineTransaction(transactionData) {
            try {
                const id = await saveTransaction({
                    ...transactionData,
                    type: 'sale',
                    endpoint: '/api/sales',
                    method: 'POST'
                });
                await this.refreshStats();
                return id;
            } catch (error) {
                console.error('Failed to save offline transaction:', error);
                throw error;
            }
        },

        async queueForSync(item) {
            try {
                await addToSyncQueue(item);
                await this.refreshStats();
            } catch (error) {
                console.error('Failed to queue for sync:', error);
                throw error;
            }
        },

        async syncAll() {
            if (this.syncing || !this.isOnline) return;

            this.syncing = true;
            this.syncErrors = [];

            try {
                await this.syncSales();
                await this.syncPurchases();
                await this.syncStockAdjustments();
                await this.syncExpenses();
                await this.syncLeads();
                await this.syncCustomers();
                await this.syncProducts();

                this.lastSyncTime = Date.now();
                await this.refreshStats();
            } catch (error) {
                console.error('Sync failed:', error);
                this.syncErrors.push({
                    message: error.message,
                    timestamp: Date.now()
                });
            } finally {
                this.syncing = false;
            }
        },

        async syncSales() {
            const queue = await getSyncQueue();
            const salesQueue = queue.filter(item => item.endpoint === '/api/sales' && item.method === 'POST');

            for (const item of salesQueue) {
                try {
                    await this.processSyncItem(item);
                } catch (error) {
                    this.syncErrors.push({
                        type: 'sale',
                        id: item.id,
                        message: error.message,
                        timestamp: Date.now()
                    });
                }
            }
        },

        async syncPurchases() {
            const queue = await getSyncQueue();
            const purchasesQueue = queue.filter(item =>
                item.endpoint === '/api/purchases' || item.endpoint.startsWith('/api/purchases/')
            );

            for (const item of purchasesQueue) {
                try {
                    await this.processSyncItem(item);
                } catch (error) {
                    this.syncErrors.push({
                        type: 'purchase',
                        id: item.id,
                        message: error.message,
                        timestamp: Date.now()
                    });
                }
            }
        },

        async syncStockAdjustments() {
            const queue = await getSyncQueue();
            const stockQueue = queue.filter(item => item.endpoint === '/api/stock/adjust');

            for (const item of stockQueue) {
                try {
                    await this.processSyncItem(item);
                } catch (error) {
                    this.syncErrors.push({
                        type: 'stock',
                        id: item.id,
                        message: error.message,
                        timestamp: Date.now()
                    });
                }
            }
        },

        async syncExpenses() {
            const queue = await getSyncQueue();
            const expensesQueue = queue.filter(item => item.endpoint === '/api/expenses');

            for (const item of expensesQueue) {
                try {
                    await this.processSyncItem(item);
                } catch (error) {
                    this.syncErrors.push({
                        type: 'expense',
                        id: item.id,
                        message: error.message,
                        timestamp: Date.now()
                    });
                }
            }
        },

        async syncLeads() {
            const queue = await getSyncQueue();
            const leadsQueue = queue.filter(item => item.endpoint === '/api/leads');

            for (const item of leadsQueue) {
                try {
                    await this.processSyncItem(item);
                } catch (error) {
                    this.syncErrors.push({
                        type: 'lead',
                        id: item.id,
                        message: error.message,
                        timestamp: Date.now()
                    });
                }
            }
        },

        async syncCustomers() {
            const queue = await getSyncQueue();
            const customersQueue = queue.filter(item => item.endpoint === '/api/customers');

            for (const item of customersQueue) {
                try {
                    await this.processSyncItem(item);
                } catch (error) {
                    this.syncErrors.push({
                        type: 'customer',
                        id: item.id,
                        message: error.message,
                        timestamp: Date.now()
                    });
                }
            }
        },

        async syncProducts() {
            const queue = await getSyncQueue();
            const productsQueue = queue.filter(item => item.endpoint === '/api/products');

            for (const item of productsQueue) {
                try {
                    await this.processSyncItem(item);
                } catch (error) {
                    this.syncErrors.push({
                        type: 'product',
                        id: item.id,
                        message: error.message,
                        timestamp: Date.now()
                    });
                }
            }
        },

        async processSyncItem(item) {
            await updateSyncQueueItem(item.id, { status: 'syncing' });

            try {
                const response = await http({
                    method: item.method,
                    url: item.endpoint,
                    data: item.data
                });

                await updateSyncQueueItem(item.id, { status: 'synced', response: response.data });
                await removeSyncQueueItem(item.id);
            } catch (error) {
                const retryCount = (item.retryCount || 0) + 1;
                const maxRetries = 3;

                if (retryCount >= maxRetries) {
                    await updateSyncQueueItem(item.id, {
                        status: 'failed',
                        error: formatApiError(error, 'Sync failed'),
                        retryCount
                    });
                } else {
                    await updateSyncQueueItem(item.id, {
                        status: 'pending',
                        retryCount,
                        lastError: formatApiError(error, 'Sync failed')
                    });
                }

                throw error;
            }
        },

        async retryFailedSyncs() {
            const queue = await getSyncQueue();
            const failedItems = queue.filter(item => item.status === 'failed');

            for (const item of failedItems) {
                await updateSyncQueueItem(item.id, { status: 'pending', retryCount: 0 });
            }

            await this.refreshStats();
            await this.syncAll();
        },

        async clearSyncedData() {
            try {
                await import('../utils/indexedDB').then(m => m.clearSyncedData());
                await this.refreshStats();
            } catch (error) {
                console.error('Failed to clear synced data:', error);
            }
        },

        getSyncStatusText() {
            const status = this.syncStatus;
            const texts = {
                offline: 'Mode Offline - Data tersimpan lokal',
                syncing: 'Menyinkronkan data ke server...',
                pending: `${this.stats.syncPending} item menunggu sinkronisasi`,
                error: `${this.stats.syncFailed} item gagal sinkronisasi`,
                synced: 'Semua data tersinkronisasi'
            };
            return texts[status] || '';
        }
    }
});