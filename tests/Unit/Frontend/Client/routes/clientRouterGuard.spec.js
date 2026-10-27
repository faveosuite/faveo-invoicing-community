let mockAuthenticated = false
jest.mock('@/core/stores/auth', () => ({ useAuthStore: () => ({ isAuthenticated: mockAuthenticated }) }))

// Capture the beforeEach guard clientRouter.js registers, so it can be called directly.
// Stored on globalThis: the import below runs before any local binding would exist.
jest.mock('vue-router', () => {
    const actual = jest.requireActual('vue-router')
    return {
        ...actual,
        createRouter: opts => {
            const router = actual.createRouter(opts)
            const add = router.beforeEach.bind(router)
            router.beforeEach = fn => { globalThis.clientRouterGuard = fn; return add(fn) }
            return router
        },
    }
})

import router from '@/routes/client/clientRouter.js'

/** Where the guard sends a navigation to `path`: a redirect target, or true to let it through. */
const guard = path => globalThis.clientRouterGuard(router.resolve(path))

describe('client router guard', () => {
    it('sends guests from protected pages to login, keeping where they were going', () => {
        mockAuthenticated = false
        expect(guard('/client-dashboard')).toEqual({ path: '/login', query: { redirect: '/client-dashboard' } })
    })

    it('lets guests open guest-only and public pages', () => {
        mockAuthenticated = false
        expect(guard('/login')).toBe(true)
        expect(guard('/404')).toBe(true)
    })

    it('sends signed-in users away from guest-only pages', () => {
        mockAuthenticated = true
        expect(guard('/login')).toEqual({ path: '/client-dashboard' })
    })

    it('lets signed-in users open protected pages', () => {
        mockAuthenticated = true
        expect(guard('/client-dashboard')).toBe(true)
    })
})
