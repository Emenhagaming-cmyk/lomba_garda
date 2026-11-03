const DB_NAME = 'TokoKuOfflineDB';
const DB_VERSION = 1;
const STORE_NAMES = {
    TRANSACTIONS: 'transactions',
    SYNC_QUEUE: 'syncQueue',
    SETTINGS: 'settings'
};

let dbInstance = null;

export function openDB() {
    return new Promise((resolve, reject) => {
        if (dbInstance) {
            resolve(dbInstance);
            return;
        }

        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onerror = () => reject(request.error);
        request.onsuccess = () => {
            dbInstance = request.result;
            resolve(dbInstance);
        };

        request.onupgradeneeded = (event) => {
            const db = event.target.result;

            if (!db.objectStoreNames.contains(STORE_NAMES.TRANSACTIONS)) {
                const transactionStore = db.createObjectStore(STORE_NAMES.TRANSACTIONS, { keyPath: 'id', autoIncrement: true });
                transactionStore.createIndex('timestamp', 'timestamp', { unique: false });
                transactionStore.createIndex('status', 'status', { unique: false });
            }

            if (!db.objectStoreNames.contains(STORE_NAMES.SYNC_QUEUE)) {
                const syncStore = db.createObjectStore(STORE_NAMES.SYNC_QUEUE, { keyPath: 'id', autoIncrement: true });
                syncStore.createIndex('endpoint', 'endpoint', { unique: false });
                syncStore.createIndex('status', 'status', { unique: false });
                syncStore.createIndex('retryCount', 'retryCount', { unique: false });
            }

            if (!db.objectStoreNames.contains(STORE_NAMES.SETTINGS)) {
                db.createObjectStore(STORE_NAMES.SETTINGS, { keyPath: 'key' });
            }
        };
    });
}

export async function saveTransaction(transaction) {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([STORE_NAMES.TRANSACTIONS], 'readwrite');
        const store = transaction.objectStore(STORE_NAMES.TRANSACTIONS);
        const request = store.add({
            ...transaction,
            timestamp: Date.now(),
            status: 'pending',
            syncAttempts: 0
        });
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

export async function getPendingTransactions() {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([STORE_NAMES.TRANSACTIONS], 'readonly');
        const store = transaction.objectStore(STORE_NAMES.TRANSACTIONS);
        const index = store.index('status');
        const request = index.getAll('pending');
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

export async function updateTransactionStatus(id, status, syncData = {}) {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([STORE_NAMES.TRANSACTIONS], 'readwrite');
        const store = transaction.objectStore(STORE_NAMES.TRANSACTIONS);
        const request = store.get(id);
        request.onsuccess = () => {
            const item = request.result;
            if (item) {
                item.status = status;
                item.lastSyncAttempt = Date.now();
                Object.assign(item, syncData);
                const updateRequest = store.put(item);
                updateRequest.onsuccess = () => resolve();
                updateRequest.onerror = () => reject(updateRequest.error);
            } else {
                reject(new Error('Transaction not found'));
            }
        };
        request.onerror = () => reject(request.error);
    });
}

export async function addToSyncQueue(queueItem) {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([STORE_NAMES.SYNC_QUEUE], 'readwrite');
        const store = transaction.objectStore(STORE_NAMES.SYNC_QUEUE);
        const request = store.add({
            ...queueItem,
            timestamp: Date.now(),
            status: 'pending',
            retryCount: 0
        });
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

export async function getSyncQueue() {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([STORE_NAMES.SYNC_QUEUE], 'readonly');
        const store = transaction.objectStore(STORE_NAMES.SYNC_QUEUE);
        const index = store.index('status');
        const request = index.getAll('pending');
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

export async function updateSyncQueueItem(id, updates) {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([STORE_NAMES.SYNC_QUEUE], 'readwrite');
        const store = transaction.objectStore(STORE_NAMES.SYNC_QUEUE);
        const request = store.get(id);
        request.onsuccess = () => {
            const item = request.result;
            if (item) {
                Object.assign(item, updates);
                const updateRequest = store.put(item);
                updateRequest.onsuccess = () => resolve();
                updateRequest.onerror = () => reject(updateRequest.error);
            } else {
                reject(new Error('Sync queue item not found'));
            }
        };
        request.onerror = () => reject(request.error);
    });
}

export async function removeSyncQueueItem(id) {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([STORE_NAMES.SYNC_QUEUE], 'readwrite');
        const store = transaction.objectStore(STORE_NAMES.SYNC_QUEUE);
        const request = store.delete(id);
        request.onsuccess = () => resolve();
        request.onerror = () => reject(request.error);
    });
}

export async function getSetting(key) {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([STORE_NAMES.SETTINGS], 'readonly');
        const store = transaction.objectStore(STORE_NAMES.SETTINGS);
        const request = store.get(key);
        request.onsuccess = () => resolve(request.result?.value);
        request.onerror = () => reject(request.error);
    });
}

export async function setSetting(key, value) {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([STORE_NAMES.SETTINGS], 'readwrite');
        const store = transaction.objectStore(STORE_NAMES.SETTINGS);
        const request = store.put({ key, value });
        request.onsuccess = () => resolve();
        request.onerror = () => reject(request.error);
    });
}

export async function clearSyncedData() {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([STORE_NAMES.TRANSACTIONS, STORE_NAMES.SYNC_QUEUE], 'readwrite');
        const transactionStore = transaction.objectStore(STORE_NAMES.TRANSACTIONS);
        const syncStore = transaction.objectStore(STORE_NAMES.SYNC_QUEUE);

        const transIndex = transactionStore.index('status');
        const syncIndex = syncStore.index('status');

        transIndex.openCursor('synced').onsuccess = (event) => {
            const cursor = event.target.result;
            if (cursor) {
                cursor.delete();
                cursor.continue();
            }
        };

        syncIndex.openCursor('synced').onsuccess = (event) => {
            const cursor = event.target.result;
            if (cursor) {
                cursor.delete();
                cursor.continue();
            }
        };

        transaction.oncomplete = () => resolve();
        transaction.onerror = () => reject(transaction.error);
    });
}

export async function getSyncStats() {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([STORE_NAMES.TRANSACTIONS, STORE_NAMES.SYNC_QUEUE], 'readonly');
        const transactionStore = transaction.objectStore(STORE_NAMES.TRANSACTIONS);
        const syncStore = transaction.objectStore(STORE_NAMES.SYNC_QUEUE);

        const transIndex = transactionStore.index('status');
        const syncIndex = syncStore.index('status');

        const stats = { pending: 0, synced: 0, failed: 0, syncPending: 0, syncFailed: 0 };

        transIndex.openCursor().onsuccess = (event) => {
            const cursor = event.target.result;
            if (cursor) {
                stats[cursor.value.status] = (stats[cursor.value.status] || 0) + 1;
                cursor.continue();
            }
        };

        syncIndex.openCursor().onsuccess = (event) => {
            const cursor = event.target.result;
            if (cursor) {
                stats[`sync${cursor.value.status.charAt(0).toUpperCase() + cursor.value.status.slice(1)}`] = (stats[`sync${cursor.value.status.charAt(0).toUpperCase() + cursor.value.status.slice(1)}`] || 0) + 1;
                cursor.continue();
            }
        };

        transaction.oncomplete = () => resolve(stats);
        transaction.onerror = () => reject(transaction.error);
    });
}

export function isOnline() {
    return navigator.onLine;
}

export function onOnline(callback) {
    window.addEventListener('online', callback);
    return () => window.removeEventListener('online', callback);
}

export function onOffline(callback) {
    window.addEventListener('offline', callback);
    return () => window.removeEventListener('offline', callback);
}