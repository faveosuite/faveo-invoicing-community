import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/core/stores/auth'
import { resolveBasePath } from '@/core/composables/useBaseUrl'
import { setMetaDescription } from '@/core/composables/useSeoMeta.js'
import { normalizeRoutePattern } from '@/core/utils/routePattern.js'

const el   = document.getElementById('app-client')
const base = resolveBasePath(el?.dataset?.clientUrl, '/')

// Pre-resolved meta title/description (browser tab + OG tags) for every
// static route, keyed by normalizeRoutePattern(route.path) — the server
// already ran the full page-wise → general → fallback cascade for each one,
// so this is a plain lookup, never a re-implementation of that cascade.
// Unrelated to `meta.title`/`titleKey` below, which drive the on-page
// header/breadcrumb (useBreadcrumb.js) instead — the two can differ.
let routeSeo = {}
try {
    routeSeo = JSON.parse(el?.dataset?.routeSeo || '{}')
} catch { /* ignore malformed/missing data */ }

// Guest auth pages: full-screen AuthLayout instead of the site header/footer.
const AUTH = { requiresAuth: false, sidebar: false, layout: 'auth' }

const routes = [
    { path: '/', redirect: '/client-dashboard' },

    // ── Guest auth pages (served at app root: /login, /verify, …) ────────────
    // These load on hard navigation from the client.blade shell; the server
    // guard bounces already-authenticated users to their panel.
    { path: '/login',                 meta: { ...AUTH, guestOnly: true, title: 'Login', titleKey: 'message.welcome_back', subtitleKey: 'message.login_subtitle' }, component: () => import('@/pages/client/auth/Login.vue') },
    { path: '/register',              meta: { ...AUTH, guestOnly: true, title: 'Register', titleKey: 'message.create_account_title', subtitleKey: 'message.register_subtitle' }, component: () => import('@/pages/client/auth/Register.vue') },
    { path: '/password/reset',        meta: { ...AUTH, guestOnly: true, title: 'Forgot Password', titleKey: 'message.forgot_password_title', subtitleKey: 'message.forgot_password_subtitle' }, component: () => import('@/pages/client/auth/ForgotPassword.vue') },
    { path: '/password/reset/:token', meta: { ...AUTH, guestOnly: true, title: 'Reset Password', titleKey: 'message.reset_password', subtitleKey: 'message.reset_password_subtitle' }, component: () => import('@/pages/client/auth/ResetPassword.vue') },
    // Verify and 2FA render their own heading (it changes per step / mode), so no titleKey.
    { path: '/verify',                meta: { ...AUTH, title: 'Verify Your Account' }, component: () => import('@/pages/client/auth/Verify.vue') },
    { path: '/social/complete',       meta: { ...AUTH, guestOnly: true, title: 'Complete your profile', titleKey: 'message.social_complete_title' }, component: () => import('@/pages/client/auth/SocialComplete.vue') },
    // After login, clients with no billing details yet (sign-up doesn't ask) land here first.
    { path: '/account-setup',         meta: { ...AUTH, requiresAuth: true, title: 'Account setup', titleKey: 'message.account_setup_title', subtitleKey: 'message.account_setup_subtitle' }, component: () => import('@/pages/client/auth/AccountSetup.vue') },
    { path: '/verify-2fa',            meta: { ...AUTH, title: 'Two-Factor Authentication' }, component: () => import('@/pages/client/auth/Verify2FA.vue') },

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
    { path: '/my-profile/2fa',             meta: { title: 'Two-Factor Authentication', titleKey: 'message.two_factor_authentication' },  component: () => import('@/pages/client/profile/TwoFactor.vue') },
    { path: '/store',          meta: { title: 'Store', sidebar: false, requiresAuth: false, ownsDocumentTitle: true }, component: () => import('@/pages/client/store/StoreIndex.vue') },
    { path: '/store/:groupId', meta: { title: 'Store', sidebar: false, requiresAuth: false, ownsDocumentTitle: true }, component: () => import('@/pages/client/store/StoreIndex.vue') },
    { path: '/cart',           meta: { title: 'Shopping Cart', sidebar: false, requiresAuth: false }, component: () => import('@/pages/client/cart/CartPage.vue') },
    { path: '/pricing',        redirect: to => ({ path: '/cart', query: to.query }) },
    { path: '/checkout',       meta: { title: 'Checkout', sidebar: false }, component: () => import('@/pages/client/checkout/CheckoutPage.vue') },
    { path: '/place-order',    meta: { title: 'Place Order', sidebar: false }, component: () => import('@/pages/client/checkout/PlaceOrderPage.vue') },
    { path: '/payment-success', meta: { title: 'Payment Successful', sidebar: false }, component: () => import('@/pages/client/checkout/PaymentSuccessPage.vue') },
    { path: '/contact-us', meta: { sidebar: false, requiresAuth: false, title: 'Contact Us', titleKey: 'message.contact_us' }, component: () => import('@/pages/client/pages/ContactUsPage.vue') },
    { path: '/pages/:slug', meta: { sidebar: false, requiresAuth: false, ownsDocumentTitle: true }, component: () => import('@/pages/client/pages/PageView.vue') },
    { path: '/pay', meta: { requiresAuth: false, sidebar: false, title: 'Secure Payment' }, component: () => import('@/pages/client/open-payment/OpenPaymentPage.vue') },
    { path: '/404', name: 'NotFound',    meta: { title: 'Not Found',    sidebar: false, requiresAuth: false, isErrorPage: true }, component: () => import('@/pages/client/errors/NotFound.vue') },
    { path: '/403', name: 'Forbidden',   meta: { title: 'Forbidden',    sidebar: false, requiresAuth: false, isErrorPage: true }, component: () => import('@/pages/client/errors/Forbidden.vue') },
    { path: '/500', name: 'ServerError', meta: { title: 'Server Error', sidebar: false, requiresAuth: false, isErrorPage: true }, component: () => import('@/pages/client/errors/ServerError.vue') },
    { path: '/:pathMatch(.*)*',          meta: { title: 'Not Found',    sidebar: false, requiresAuth: false, isErrorPage: true }, component: () => import('@/pages/client/errors/NotFound.vue') },
]

const router = createRouter({
    history: createWebHistory(base),
    routes,
    // New page → top; Back/Forward → where the user was; same page with only the query/hash
    // changed (filters, tabs, pagination) → stay put ({} = scrollTo with no top/left, i.e. no move).
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) return savedPosition
        return to.path === from.path ? {} : { top: 0 }
    },
})

router.beforeEach((to) => { // NOSONAR - vue-router guard: a redirect location, or true to continue
    const auth         = useAuthStore()
    const requiresAuth = to.meta?.requiresAuth !== false
    const guestOnly    = to.meta?.guestOnly === true

    if (guestOnly && auth.isAuthenticated) return { path: '/client-dashboard' }
    if (requiresAuth && !auth.isAuthenticated) return { path: '/login', query: { redirect: to.fullPath } }
    // Server flag (client.blade): billing details missing → finish setup before any signed-in page.
    // Read per navigation; setup itself reloads the page, which clears it.
    const needsSetup = document.getElementById('app-client')?.dataset?.needsSetup === 'true'
    if (needsSetup && requiresAuth && to.path !== '/account-setup') return { path: '/account-setup' }
    return true
})

router.afterEach((to) => {
    // Store/Pages fetch their own specific title async and set it themselves
    // once loaded — overwriting document.title here would flash the wrong
    // title before the real one arrives. Leave it alone entirely.
    if (to.meta?.ownsDocumentTitle) return

    const matched = to.matched[to.matched.length - 1]
    const pattern = matched ? normalizeRoutePattern(matched.path) : ''
    const entry   = routeSeo[pattern]
    if (!entry) return

    document.title = entry.title
    setMetaDescription(entry.description)
})

export default router
