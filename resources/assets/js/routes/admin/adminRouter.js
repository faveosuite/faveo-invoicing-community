import { createRouter, createWebHistory } from 'vue-router'
import dashboardRoutes from './dashboard.js'
import profileRoutes from './profile.js'
import userRoutes from './users.js'
import orderRoutes from './orders.js'
import invoiceRoutes from './invoices.js'
import pageRoutes from './pages.js'
import productRoutes from './products.js'
import reportRoutes from './reports.js'
import settingsRoutes from './settings/settings.js'
import logsRoutes from './settings/logs.js'
import emailRoutes from './settings/email.js'
import apiRoutes from './settings/api.js'
import commonRoutes from './settings/common.js'
import widgetRoutes from './settings/widgets.js'
import licenseRoutes from './license.js'
import { normalizeRoutePattern } from '@/core/utils/routePattern.js'

const routes = [
    { path: '/', redirect: '/dashboard' },
    ...dashboardRoutes,
    ...profileRoutes,
    ...userRoutes,
    ...orderRoutes,
    ...invoiceRoutes,
    ...pageRoutes,
    ...productRoutes,
    ...reportRoutes,
    ...settingsRoutes,
    ...logsRoutes,
    ...emailRoutes,
    ...apiRoutes,
    ...commonRoutes,
    ...widgetRoutes,
    ...licenseRoutes,
    { path: '/404', name: 'NotFound',    meta: { title: 'Not Found',    isErrorPage: true, requiresAuth: false }, component: () => import('@/pages/admin/errors/NotFound.vue') },
    { path: '/403', name: 'Forbidden',   meta: { title: 'Forbidden',    isErrorPage: true, requiresAuth: false }, component: () => import('@/pages/admin/errors/Forbidden.vue') },
    { path: '/500', name: 'ServerError', meta: { title: 'Server Error', isErrorPage: true, requiresAuth: false }, component: () => import('@/pages/admin/errors/ServerError.vue') },
    { path: '/:pathMatch(.*)*',          meta: { title: 'Not Found',    isErrorPage: true, requiresAuth: false }, component: () => import('@/pages/admin/errors/NotFound.vue') },
]

// Read the admin base path from the blade-rendered data attribute.
// Falls back to '/admin' if the attribute is missing.
import { useBaseUrl, resolveBasePath } from '@/core/composables/useBaseUrl'

const el = document.getElementById('app-root')
const base = resolveBasePath(el?.dataset?.adminUrl, '/admin')

const router = createRouter({
    history: createWebHistory(base),
    routes,
})

// Auth navigation guard
// Login is handled by the client panel (not the admin Vue SPA).
// If the session expires mid-session, redirect back to the client panel login.
import { useAuthStore } from '@/core/stores/auth'

router.beforeEach((to, from, next) => {
    const requiresAuth = to.meta?.requiresAuth !== false

    if (requiresAuth && !useAuthStore().isAuthenticated) {
        globalThis.location.href = useBaseUrl() + '/login'
    } else {
        next()
    }
})

// Pre-resolved title/description for every admin route (already ran through
// AdminMetaService's General SEO -> per-route default -> hardcoded literal
// cascade server-side) — looked up by the route's own path pattern
// (normalizeRoutePattern), never re-implemented here.
let adminRoutes = {}
try {
    adminRoutes = JSON.parse(el?.dataset?.adminRoutes || '{}')
} catch { /* ignore malformed/missing data */ }

// beforeEach (not afterEach): to.meta is available as soon as the route is
// matched, before Vue Router fetches the target route's async component
// chunk — which in dev mode (unbundled ES modules) can itself take a couple
// of seconds on a cold load. Using afterEach here would tie the title update
// to that fetch instead of the navigation itself.
router.beforeEach((to) => {
    const matched = to.matched[to.matched.length - 1]
    const pattern = matched ? normalizeRoutePattern(matched.path) : ''
    const title   = adminRoutes[pattern]?.title
    if (title) document.title = title
})

export default router
