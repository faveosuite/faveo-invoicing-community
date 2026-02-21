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

const router = createRouter({
    history: createWebHistory('/faveo-invoicing-community/public/admin'),
    routes
})

export default router
