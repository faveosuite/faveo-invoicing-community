import { createRouter, createWebHistory } from 'vue-router'

const el         = document.getElementById('app-client')
const clientUrl  = el?.dataset?.clientUrl ?? ''
const base       = clientUrl ? new URL(clientUrl).pathname : '/'

const isAuthenticated = () => el?.dataset?.authenticated === 'true'

const routes = [
    { path: '/', redirect: '/client-dashboard' },

    // ── Guest auth pages (served at app root: /login, /verify, …) ────────────
    // These load on hard navigation from the client.blade shell; the server
    // guard bounces already-authenticated users to their panel.
    { path: '/login',                 meta: { requiresAuth: false, sidebar: false, title: 'Login', titleKey: 'message.login' }, component: () => import('@/pages/client/auth/LoginRegister.vue') },
    { path: '/password/reset',        meta: { requiresAuth: false, sidebar: false, title: 'Forgot Password', titleKey: 'message.forgot-password' }, component: () => import('@/pages/client/auth/ForgotPassword.vue') },
    { path: '/password/reset/:token', meta: { requiresAuth: false, sidebar: false, title: 'Reset Password', titleKey: 'message.reset_password', breadcrumb: [{ title: 'Reset Password', titleKey: 'message.reset_password' }] }, component: () => import('@/pages/client/auth/ResetPassword.vue') },
    { path: '/verify',                meta: { requiresAuth: false, sidebar: false, title: 'Verify Email', titleKey: 'message.verify_email' }, component: () => import('@/pages/client/auth/Verify.vue') },
    { path: '/verify-2fa',            meta: { requiresAuth: false, sidebar: false, title: 'Two-Factor Authentication', titleKey: 'message.two_factor_authentication' }, component: () => import('@/pages/client/auth/Verify2FA.vue') },

    // Client panel pages use the pre-Vue-conversion (legacy) URLs so existing
    // links/emails keep working and they don't collide with the admin data APIs
    // at /orders, /invoices, /profile, /dashboard.
    { path: '/client-dashboard', meta: { title: 'Dashboard' },     component: () => import('@/pages/client/dashboard/DashboardIndex.vue') },
    { path: '/my-orders',    meta: { title: 'My Orders' },       component: () => import('@/pages/client/orders/OrderIndex.vue') },
    { path: '/my-order/:id', meta: { title: 'Order Details',   sidebar: false }, component: () => import('@/pages/client/orders/OrderShow.vue') },
    { path: '/my-invoices',  meta: { title: 'My Invoices' },     component: () => import('@/pages/client/invoices/InvoiceIndex.vue') },
    { path: '/my-invoice/:id', meta: { title: 'Invoice Details', sidebar: false }, component: () => import('@/pages/client/invoices/InvoiceShow.vue') },
    { path: '/my-profile',                  meta: { title: 'My Profile' },       component: () => import('@/pages/client/profile/ProfileIndex.vue') },
    { path: '/my-profile/change-password', meta: { title: 'Change Password' },  component: () => import('@/pages/client/profile/ChangePassword.vue') },
    { path: '/my-profile/2fa',             meta: { title: 'Two-Factor Auth' },  component: () => import('@/pages/client/profile/TwoFactor.vue') },
    { path: '/store',          meta: { title: 'Store', sidebar: false, requiresAuth: false }, component: () => import('@/pages/client/store/StoreIndex.vue') },
    { path: '/store/:groupId', meta: { title: 'Store', sidebar: false, requiresAuth: false }, component: () => import('@/pages/client/store/StoreIndex.vue') },
    { path: '/cart',           meta: { title: 'Shopping Cart', sidebar: false, requiresAuth: false }, component: () => import('@/pages/client/cart/CartPage.vue') },
    { path: '/checkout',       meta: { title: 'Checkout', sidebar: false }, component: () => import('@/pages/client/checkout/CheckoutPage.vue') },
    { path: '/place-order',    meta: { title: 'Place Order', sidebar: false }, component: () => import('@/pages/client/checkout/PlaceOrderPage.vue') },
    { path: '/payment-success', meta: { title: 'Payment Successful', sidebar: false }, component: () => import('@/pages/client/checkout/PaymentSuccessPage.vue') },
    { path: '/contact-us', meta: { sidebar: false, requiresAuth: false, title: 'Contact Us', titleKey: 'message.contact_us' }, component: () => import('@/pages/client/pages/ContactUsPage.vue') },
    { path: '/pages/:slug', meta: { sidebar: false, requiresAuth: false }, component: () => import('@/pages/client/pages/PageView.vue') },
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
