import { createRouter, createWebHistory } from 'vue-router'

const el         = document.getElementById('app-client')
const clientUrl  = el?.dataset?.clientUrl ?? ''
const base       = clientUrl ? new URL(clientUrl).pathname : '/client'

const isAuthenticated = () => el?.dataset?.authenticated === 'true'

const routes = [
    { path: '/', redirect: '/dashboard' },
    { path: '/dashboard',    meta: { title: 'Dashboard' },       component: () => import('@/pages/client/dashboard/DashboardIndex.vue') },
    { path: '/orders',       meta: { title: 'My Orders' },       component: () => import('@/pages/client/orders/OrderIndex.vue') },
    { path: '/orders/:id',   meta: { title: 'Order Details',   sidebar: false }, component: () => import('@/pages/client/orders/OrderShow.vue') },
    { path: '/invoices',     meta: { title: 'My Invoices' },     component: () => import('@/pages/client/invoices/InvoiceIndex.vue') },
    { path: '/invoices/:id', meta: { title: 'Invoice Details', sidebar: false }, component: () => import('@/pages/client/invoices/InvoiceShow.vue') },
    { path: '/profile',                  meta: { title: 'My Profile' },       component: () => import('@/pages/client/profile/ProfileIndex.vue') },
    { path: '/profile/change-password', meta: { title: 'Change Password' },  component: () => import('@/pages/client/profile/ChangePassword.vue') },
    { path: '/profile/2fa',             meta: { title: 'Two-Factor Auth' },  component: () => import('@/pages/client/profile/TwoFactor.vue') },
    { path: '/store',          meta: { title: 'Store', sidebar: false }, component: () => import('@/pages/client/store/StoreIndex.vue') },
    { path: '/store/:groupId', meta: { title: 'Store', sidebar: false }, component: () => import('@/pages/client/store/StoreIndex.vue') },
]

const router = createRouter({
    history: createWebHistory(base),
    routes,
})

router.beforeEach((to, from, next) => {
    const requiresAuth = to.meta?.requiresAuth !== false
    if (requiresAuth && !isAuthenticated()) {
        window.location.href = (el?.dataset?.baseUrl ?? '') + '/login'
    } else {
        next()
    }
})

const appName = el?.dataset?.pageTitle || 'Client Panel'
router.afterEach((to) => {
    document.title = to.meta?.title ? `${to.meta.title} | ${appName}` : appName
})

export default router
