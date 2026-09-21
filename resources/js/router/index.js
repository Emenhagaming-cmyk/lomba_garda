import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useBusinessStore } from '../stores/business';

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
            path: '/onboarding',
            name: 'onboarding',
            component: () => import('../pages/OnboardingPage.vue'),
            meta: { requiresAuth: true, onboarding: true },
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
                    component: () => import('../pages/SalesPage.vue'),
                },
                {
                    path: '/sales/new',
                    name: 'sales-new',
                    component: () => import('../pages/SaleNewPage.vue'),
                },
                {
                    path: '/products',
                    name: 'products',
                    component: () => import('../pages/ProductsPage.vue'),
                },
                {
                    path: '/products/new',
                    name: 'products-new',
                    component: () => import('../pages/ProductsPage.vue'),
                    props: { autoOpenNew: true },
                },
                {
                    path: '/inventory',
                    name: 'inventory',
                    component: () => import('../pages/InventoryPage.vue'),
                },
                {
                    path: '/purchase',
                    name: 'purchase',
                    component: () => import('../pages/PurchasePage.vue'),
                },
                {
                    path: '/customers',
                    name: 'customers',
                    component: () => import('../pages/CustomersPage.vue'),
                },
                {
                    path: '/leads',
                    name: 'leads',
                    component: () => import('../pages/LeadsPage.vue'),
                },
                {
                    path: '/finance',
                    name: 'finance',
                    component: () => import('../pages/FinancePage.vue'),
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
                    component: () => import('../pages/SettingsPage.vue'),
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

    if (to.meta.requiresAuth && auth.isAuthenticated) {
        const business = useBusinessStore();

        if (!business.hasBusiness && !business.loading) {
            await business.fetchBusiness();
        }

        if (!business.hasBusiness && to.name !== 'onboarding') {
            return { name: 'onboarding' };
        }

        if (business.hasBusiness && to.name === 'onboarding') {
            return { name: 'dashboard' };
        }
    }
});

export default router;