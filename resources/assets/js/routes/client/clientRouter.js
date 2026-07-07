import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/core/stores/auth'
import { resolveBasePath } from '@/core/composables/useBaseUrl'
import { setMetaDescription } from '@/core/composables/useSeoMeta.js'

const el   = document.getElementById('app-client')
const base = resolveBasePath(el?.dataset?.clientUrl, '/')

// Login/forgot-password/reset-password meta, keyed by page_key — kept in
// sync with whatever's configured in /admin/settings/seo (SeoDefaultPage),
// same data the server rendered into <title> before Vue mounted.
let defaultPagesSeo = {}
try {
    defaultPagesSeo = JSON.parse(el?.dataset?.defaultPagesSeo || '{}')
} catch { /* ignore malformed/missing data */ }

// Same General Description SeoMetaService's fallback() resolves server-side
// for authenticated/unknown routes — kept in sync on client-side navigation.
const generalDescription = el?.dataset?.generalDescription || ''

const routes = [
    { path: '/', redirect: '/client-dashboard' },

    // ── Guest auth pages (served at app root: /login, /verify, …) ────────────
    // These load on hard navigation from the client.blade shell; the server
    // guard bounces already-authenticated users to their panel.
    { path: '/login',                 meta: { requiresAuth: false, guestOnly: true,  sidebar: false, title: 'Login', titleKey: 'message.login', defaultSeoKey: 'login' }, component: () => import('@/pages/client/auth/LoginRegister.vue') },
    { path: '/password/reset',        meta: { requiresAuth: false, guestOnly: true,  sidebar: false, title: 'Forgot Password', titleKey: 'message.forgot-password', defaultSeoKey: 'forgot_password' }, component: () => import('@/pages/client/auth/ForgotPassword.vue') },
    { path: '/password/reset/:token', meta: { requiresAuth: false, guestOnly: true,  sidebar: false, title: 'Reset Password', titleKey: 'message.reset_password', defaultSeoKey: 'reset_password', breadcrumb: [{ title: 'Reset Password', titleKey: 'message.reset_password' }] }, component: () => import('@/pages/client/auth/ResetPassword.vue') },
    { path: '/verify',                meta: { requiresAuth: false, sidebar: false, title: 'Verify Email', titleKey: 'message.verify_email', usesGeneralSeo: true }, component: () => import('@/pages/client/auth/Verify.vue') },
    { path: '/verify-2fa',            meta: { requiresAuth: false, sidebar: false, title: 'Two-Factor Authentication', titleKey: 'message.two_factor_authentication', usesGeneralSeo: true }, component: () => import('@/pages/client/auth/Verify2FA.vue') },

    // Client panel pages use the pre-Vue-conversion (legacy) URLs so existing
    // links/emails keep working and they don't collide with the admin data APIs
    // at /orders, /invoices, /profile, /dashboard.
    // `usesGeneralSeo: true` — these are the same authenticated/transactional
    // routes SeoMetaService::fallback() resolves server-side; the client-side
    // title/description here mirror that same General cascade instead of a
    // static per-route string, so both layers stay consistent.
    { path: '/client-dashboard', meta: { title: 'Dashboard', usesGeneralSeo: true },     component: () => import('@/pages/client/dashboard/DashboardIndex.vue') },
    { path: '/my-orders',    meta: { title: 'My Orders', usesGeneralSeo: true },       component: () => import('@/pages/client/orders/OrderIndex.vue') },
    { path: '/my-order/:id', meta: { title: 'Order Details',   sidebar: false, usesGeneralSeo: true }, component: () => import('@/pages/client/orders/OrderShow.vue') },
    { path: '/my-invoices',  meta: { title: 'My Invoices', usesGeneralSeo: true },     component: () => import('@/pages/client/invoices/InvoiceIndex.vue') },
    { path: '/my-invoice/:id', meta: { title: 'Invoice Details', sidebar: false, usesGeneralSeo: true }, component: () => import('@/pages/client/invoices/InvoiceShow.vue') },
    { path: '/my-profile',                  meta: { title: 'My Profile', usesGeneralSeo: true },       component: () => import('@/pages/client/profile/ProfileIndex.vue') },
    { path: '/my-profile/change-password', meta: { title: 'Change Password', usesGeneralSeo: true },  component: () => import('@/pages/client/profile/ChangePassword.vue') },
    { path: '/my-profile/2fa',             meta: { title: 'Two-Factor Auth', usesGeneralSeo: true },  component: () => import('@/pages/client/profile/TwoFactor.vue') },
    { path: '/store',          meta: { title: 'Store', sidebar: false, requiresAuth: false, ownsDocumentTitle: true }, component: () => import('@/pages/client/store/StoreIndex.vue') },
    { path: '/store/:groupId', meta: { title: 'Store', sidebar: false, requiresAuth: false, ownsDocumentTitle: true }, component: () => import('@/pages/client/store/StoreIndex.vue') },
    { path: '/cart',           meta: { title: 'Shopping Cart', sidebar: false, usesGeneralSeo: true }, component: () => import('@/pages/client/cart/CartPage.vue') },
    { path: '/pricing',        redirect: to => ({ path: '/cart', query: to.query }) },
    { path: '/checkout',       meta: { title: 'Checkout', sidebar: false, usesGeneralSeo: true }, component: () => import('@/pages/client/checkout/CheckoutPage.vue') },
    { path: '/place-order',    meta: { title: 'Place Order', sidebar: false, usesGeneralSeo: true }, component: () => import('@/pages/client/checkout/PlaceOrderPage.vue') },
    { path: '/payment-success', meta: { title: 'Payment Successful', sidebar: false, usesGeneralSeo: true }, component: () => import('@/pages/client/checkout/PaymentSuccessPage.vue') },
    { path: '/contact-us', meta: { sidebar: false, requiresAuth: false, title: 'Contact Us', titleKey: 'message.contact_us' }, component: () => import('@/pages/client/pages/ContactUsPage.vue') },
    { path: '/pages/:slug', meta: { sidebar: false, requiresAuth: false, ownsDocumentTitle: true }, component: () => import('@/pages/client/pages/PageView.vue') },
    { path: '/pay', meta: { requiresAuth: false, sidebar: false, title: 'Secure Payment', usesGeneralSeo: true }, component: () => import('@/pages/client/open-payment/OpenPaymentPage.vue') },
    { path: '/404', name: 'NotFound',    meta: { title: 'Not Found',    sidebar: false, requiresAuth: false }, component: () => import('@/pages/client/errors/NotFound.vue') },
    { path: '/403', name: 'Forbidden',   meta: { title: 'Forbidden',    sidebar: false, requiresAuth: false }, component: () => import('@/pages/client/errors/Forbidden.vue') },
    { path: '/500', name: 'ServerError', meta: { title: 'Server Error', sidebar: false, requiresAuth: false }, component: () => import('@/pages/client/errors/ServerError.vue') },
    { path: '/:pathMatch(.*)*',          meta: { title: 'Not Found',    sidebar: false, requiresAuth: false }, component: () => import('@/pages/client/errors/NotFound.vue') },
]

const router = createRouter({
    history: createWebHistory(base),
    routes,
})

router.beforeEach((to, from, next) => {
    const auth         = useAuthStore()
    const requiresAuth = to.meta?.requiresAuth !== false
    const guestOnly    = to.meta?.guestOnly === true

    if (guestOnly && auth.isAuthenticated) return next('/client-dashboard')
    if (requiresAuth && !auth.isAuthenticated) return next({ path: '/login', query: { redirect: to.fullPath } })
    next()
})

const appName = el?.dataset?.pageTitle || 'Client Panel'
router.afterEach((to) => {
    // Store/Pages fetch their own specific title async and set it themselves
    // once loaded — the route's static `meta.title` here is just a generic
    // placeholder ("Store"). Overwriting document.title with it on every
    // navigation would flash the wrong title before the real one arrives, so
    // leave it alone entirely: whatever was already showing (the correct
    // server-rendered title on hard navigation) stays until the component's
    // own fetch resolves and sets the real one.
    if (to.meta?.ownsDocumentTitle) return

    const defaultSeo = to.meta?.defaultSeoKey ? defaultPagesSeo[to.meta.defaultSeoKey] : null

    if (defaultSeo) {
        // Real, admin-editable SEO page (login/forgot/reset) — no " | Company"
        // suffix, so the client-rendered title always matches what the server
        // already rendered into <title> for this same page.
        document.title = defaultSeo.meta_title || to.meta.title
        setMetaDescription(defaultSeo.meta_description)
    } else if (to.meta?.usesGeneralSeo) {
        // Authenticated/transactional route with no real per-page SEO —
        // mirrors SeoMetaService::fallback()'s General-first cascade instead
        // of a static per-route title/description. appName is shipped raw
        // (unresolved) so a {name} shortcode inside it (e.g. "{name} | Faveo
        // Billing") can be substituted per-route here, same as fallback()
        // does server-side with $mapTitle — a plain appName with no
        // shortcode is unaffected (String.replace is a no-op without a match).
        document.title = appName.replace('{name}', to.meta?.title || '')
        setMetaDescription(generalDescription)
    } else {
        document.title = to.meta?.title ? `${to.meta.title} | ${appName}` : appName
        setMetaDescription(to.meta?.description)
    }
})

export default router
