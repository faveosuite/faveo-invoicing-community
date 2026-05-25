import { createRouter, createWebHistory } from 'vue-router'

const el         = document.getElementById('app-client')
const clientUrl  = el?.dataset?.clientUrl ?? ''
const base       = clientUrl ? new URL(clientUrl).pathname : '/client'

const isAuthenticated = () => el?.dataset?.authenticated === 'true'

// Routes added here as client pages are built
const routes = [
    { path: '/', redirect: '/dashboard' },
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
