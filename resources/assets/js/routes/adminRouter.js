import { createRouter, createWebHistory } from 'vue-router'
import dashboardRoutes from './admin/dashboard.js'
import profileRoutes from './admin/profile.js'
import userRoutes from './admin/users.js'
import orderRoutes from './admin/orders.js'
import invoiceRoutes from './admin/invoices.js'
import pageRoutes from './admin/pages.js'
import productRoutes from './admin/products.js'
import reportRoutes from './admin/reports.js'
import settingsRoutes from './admin/settings/settings.js'
import logsRoutes from './admin/settings/logs.js'
import emailRoutes from './admin/settings/email.js'
import apiRoutes from './admin/settings/api.js'
import commonRoutes from './admin/settings/common.js'
import widgetRoutes from './admin/settings/widgets.js'
import licenseRoutes from './admin/license.js'

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
]

// Read the admin base path from the blade-rendered data attribute.
// Falls back to '/admin' if the attribute is missing.
const el = document.getElementById('app-root')
const adminUrl = el?.dataset?.adminUrl ?? ''
const base = adminUrl ? new URL(adminUrl).pathname : '/admin'

const router = createRouter({
    history: createWebHistory(base),
    routes,
})

// Auth navigation guard
// Login is handled by the client panel (not the admin Vue SPA).
// If the session expires mid-session, redirect back to the client panel login.
const isAuthenticated = () => el?.dataset?.authenticated === 'true'

router.beforeEach((to, from, next) => {
    const requiresAuth = to.meta?.requiresAuth !== false

    if (requiresAuth && !isAuthenticated()) {
        // Send user to client panel login; it will redirect back after auth
        window.location.href = (el?.dataset?.baseUrl ?? '') + '/login'
    } else {
        next()
    }
})

// Update browser tab title on every navigation
const appName = el?.dataset?.pageTitle || 'Admin Panel'
router.afterEach((to) => {
    if (to.meta?.title) {
        const titleKey  = to.meta.titleKey
        const translated = titleKey ? __(titleKey) : null
        const label = (translated && translated !== titleKey) ? translated : to.meta.title
        document.title = `${label} | ${appName}`
    } else {
        document.title = appName
    }
})

export default router
