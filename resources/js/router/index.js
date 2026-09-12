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