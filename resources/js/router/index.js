import { createRouter, createWebHistory } from 'vue-router'
import dashboardRoutes from './routes/dashboard.js'
import userRoutes from './routes/users.js'
import orderRoutes from './routes/orders.js'
import invoiceRoutes from './routes/invoices.js'
import pageRoutes from './routes/pages.js'
import productRoutes from './routes/products.js'
import reportRoutes from './routes/reports.js'
import settingsRoutes from './routes/settings/settings.js'
import logsRoutes from './routes/settings/logs.js'
import emailRoutes from './routes/settings/email.js'
import apiRoutes from './routes/settings/api.js'
import commonRoutes from './routes/settings/common.js'
import widgetRoutes from './routes/settings/widgets.js'

const routes = [
    ...dashboardRoutes,
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
        window.location.href = (el?.dataset?.baseUrl ?? '') + '/auth/login'
    } else {
        next()
    }
})

export default router
