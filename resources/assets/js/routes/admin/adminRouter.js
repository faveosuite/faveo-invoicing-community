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

// Update browser tab title on every navigation. Meta Title (Admin Panel) is
// admin-editable and may itself be a {name}/{company} template (matching the
// SEO shortcodes on Settings > SEO) — e.g. "{name} | {company}". A plain
// string with no placeholder (the common case) is shown exactly as typed on
// every page; the current page name is only included if the admin opts in
// by adding {name} to the template themselves.
const titleTemplate = el?.dataset?.pageTitle || 'Admin Panel'
const company = el?.dataset?.company || 'Admin Panel'

function resolveTitle(name) {
    return titleTemplate.replace('{name}', name || '').replace('{company}', company)
}

// beforeEach (not afterEach): to.meta is available as soon as the route is
// matched, before Vue Router fetches the target route's async component
// chunk — which in dev mode (unbundled ES modules) can itself take a couple
// of seconds on a cold load. Using afterEach here would tie the title update
// to that fetch instead of the navigation itself.
router.beforeEach((to) => {
    if (to.meta?.title) {
        const titleKey  = to.meta.titleKey
        const translated = titleKey ? __(titleKey) : null
        const label = (translated && translated !== titleKey) ? translated : to.meta.title
        document.title = resolveTitle(label)
    } else {
        document.title = resolveTitle('')
    }
})

export default router
