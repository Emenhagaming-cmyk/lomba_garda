import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            name: 'landing',
            component: () => import('../pages/LandingPage.vue'),
            meta: { public: true },
        },
        {
            path: '/login',
            name: 'login',
            component: () => import('../pages/LoginPage.vue'),
            meta: { guest: true },
        },
        {
            path: '/register',
            name: 'register',
            component: () => import('../pages/RegisterPage.vue'),
            meta: { guest: true },
        },
        {
            path: '/dashboard',
            component: () => import('../layouts/AppLayout.vue'),
            meta: { requiresAuth: true },
            children: [
                {
                    path: '',
                    name: 'dashboard',
                    component: () => import('../pages/DashboardPage.vue'),
                },
                {
                    path: '/sales',
                    name: 'sales',
                    component: () => import('../pages/PlaceholderPage.vue'),
                    props: { title: 'Penjualan', description: 'Catat transaksi penjualan dan pantau performa tiap produk.', icon: 'cart' },
                },
                {
                    path: '/sales/new',
                    name: 'sales-new',
                    component: () => import('../pages/PlaceholderPage.vue'),
                    props: { title: 'Transaksi Baru', description: 'Input penjualan cepat — stok, keuangan, dan pelanggan ter-update otomatis.', icon: 'cart' },
                },
                {
                    path: '/products',
                    name: 'products',
                    component: () => import('../pages/PlaceholderPage.vue'),
                    props: { title: 'Produk', description: 'Kelola katalog produk, harga jual, dan margin.', icon: 'box' },
                },
                {
                    path: '/products/new',
                    name: 'products-new',
                    component: () => import('../pages/PlaceholderPage.vue'),
                    props: { title: 'Produk Baru', description: 'Tambahkan produk ke dalam katalog.', icon: 'box' },
                },
                {
                    path: '/inventory',
                    name: 'inventory',
                    component: () => import('../pages/PlaceholderPage.vue'),
                    props: { title: 'Stok', description: 'Pantau stok masuk/keluar dan stock opname.', icon: 'warehouse' },
                },
                {
                    path: '/purchase',
                    name: 'purchase',
                    component: () => import('../pages/PlaceholderPage.vue'),
                    props: { title: 'Pembelian', description: 'Kelola supplier dan purchase order.', icon: 'truck' },
                },
                {
                    path: '/customers',
                    name: 'customers',
                    component: () => import('../pages/PlaceholderPage.vue'),
                    props: { title: 'Pelanggan', description: 'Database pelanggan, riwayat transaksi, dan segmentasi.', icon: 'users' },
                },
                {
                    path: '/leads',
                    name: 'leads',
                    component: () => import('../pages/PlaceholderPage.vue'),
                    props: { title: 'Leads', description: 'Kelola prospek dan sales pipeline.', icon: 'target' },
                },
                {
                    path: '/finance',
                    name: 'finance',
                    component: () => import('../pages/PlaceholderPage.vue'),
                    props: { title: 'Keuangan', description: 'Pemasukan, pengeluaran, cashflow sederhana, dan profit.', icon: 'wallet' },
                },
                {
                    path: '/analytics',
                    name: 'analytics',
                    component: () => import('../pages/PlaceholderPage.vue'),
                    props: { title: 'Laporan', description: 'Rekomendasi dari data: restock, dead stock, forecast, dan insight.', icon: 'chart' },
                },
                {
                    path: '/settings',
                    name: 'settings',
                    component: () => import('../pages/PlaceholderPage.vue'),
                    props: { title: 'Pengaturan', description: 'Profil bisnis, pengguna, peran, dan sinkronisasi offline.', icon: 'settings' },
                },
            ],
        },
        {
            path: '/:pathMatch(.*)*',
            redirect: '/',
        },
    ],
});

router.beforeEach(async (to) => {
    const auth = useAuthStore();

    if (auth.loading) {
        await auth.fetchUser();
    }

    if (to.meta.public && auth.isAuthenticated) {
        return { name: 'dashboard' };
    }

    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return {
            name: 'login',
            query: { redirect: to.fullPath },
        };
    }

    if (to.meta.guest && auth.isAuthenticated) {
        return { name: 'dashboard' };
    }
});

export default router;