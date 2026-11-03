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
            path: '/privacy',
            name: 'privacy',
            component: () => import('../pages/PrivacyPage.vue'),
            meta: { public: true },
        },
        {
            path: '/terms',
            name: 'terms',
            component: () => import('../pages/TermsPage.vue'),
            meta: { public: true },
        },
        {
            path: '/features',
            name: 'features',
            component: () => import('../pages/FeaturesPage.vue'),
            meta: { public: true },
        },
        {
            path: '/features/penjualan',
            name: 'feature-sales',
            component: () => import('../pages/FeatureSalesPage.vue'),
            meta: { public: true },
        },
        {
            path: '/features/inventori',
            name: 'feature-inventory',
            component: () => import('../pages/FeatureInventoryPage.vue'),
            meta: { public: true },
        },
        {
            path: '/features/pelanggan',
            name: 'feature-customers',
            component: () => import('../pages/FeatureCustomersPage.vue'),
            meta: { public: true },
        },
        {
            path: '/features/insight',
            name: 'feature-insight',
            component: () => import('../pages/FeatureInsightPage.vue'),
            meta: { public: true },
        },
        {
            path: '/about',
            name: 'about',
            component: () => import('../pages/AboutPage.vue'),
            meta: { public: true },
        },
        {
            path: '/careers',
            name: 'careers',
            component: () => import('../pages/CareersPage.vue'),
            meta: { public: true },
        },
        {
            path: '/blog',
            name: 'blog',
            component: () => import('../pages/BlogPage.vue'),
            meta: { public: true },
        },
        {
            path: '/help',
            name: 'help',
            component: () => import('../pages/HelpPage.vue'),
            meta: { public: true },
        },
        {
            path: '/docs',
            name: 'docs',
            component: () => import('../pages/DocsPage.vue'),
            meta: { public: true },
        },
        {
            path: '/status',
            name: 'status',
            component: () => import('../pages/StatusPage.vue'),
            meta: { public: true },
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
                    path: '/follow-ups',
                    name: 'follow-ups',
                    component: () => import('../pages/FollowUpsPage.vue'),
                },
                {
                    path: '/finance',
                    name: 'finance',
                    component: () => import('../pages/FinancePage.vue'),
                },
                {
                    path: '/analytics',
                    name: 'analytics',
                    component: () => import('../pages/ReportsPage.vue'),
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

        if (!business.loaded) {
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