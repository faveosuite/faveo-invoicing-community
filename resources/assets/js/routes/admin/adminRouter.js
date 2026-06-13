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
