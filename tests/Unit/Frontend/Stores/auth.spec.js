import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '@/core/stores/auth.js'
import { useDateTimeStore } from '@/core/stores/dateTimeStore.js'
import { userFixture } from '../mocks/fixtures/index.js'

describe('useAuthStore', () => {
    beforeEach(() => {
        setActivePinia(createPinia())
    })

    describe('initial state', () => {
        it('has user as null (DOM seed has data-authenticated="false")', () => {
            const store = useAuthStore()
            expect(store.user).toBeNull()
        })
    })

    describe('isAuthenticated getter', () => {
        it('returns false when user is null', () => {
            const store = useAuthStore()
            expect(store.isAuthenticated).toBe(false)
        })

        it('returns true after user is set', () => {
            const store = useAuthStore()
            store.user = userFixture
            expect(store.isAuthenticated).toBe(true)
        })
    })

    describe('isAdmin getter', () => {
        it('returns false when user is null', () => {
            const store = useAuthStore()
            expect(store.isAdmin).toBe(false)
        })

        it('returns true when user role is admin', () => {
            const store = useAuthStore()
            store.user = { ...userFixture, role: 'admin' }
            expect(store.isAdmin).toBe(true)
        })

        it('returns false when user role is not admin', () => {
            const store = useAuthStore()
            store.user = { ...userFixture, role: 'user' }
            expect(store.isAdmin).toBe(false)
        })
    })

    describe('hydrate', () => {
        it('sets user on success', async () => {
            const store = useAuthStore()
            await store.hydrate()
            await flushPromises()
            expect(store.user).toEqual(userFixture)
        })

        it('sets isAuthenticated to true after successful hydration', async () => {
            const store = useAuthStore()
            await store.hydrate()
            await flushPromises()
            expect(store.isAuthenticated).toBe(true)
        })

        it('sets user to null on HTTP error', async () => {
            global.mockHttp.reset()
            global.mockHttp.onGet('/api/user').replyOnce(401, { message: 'Unauthenticated.' })
            const store = useAuthStore()
            await store.hydrate()
            await flushPromises()
            expect(store.user).toBeNull()
        })

        it('sets user to null on 500 error', async () => {
            global.mockHttp.reset()
            global.mockHttp.onGet('/api/user').replyOnce(500)
            const store = useAuthStore()
            await store.hydrate()
            await flushPromises()
            expect(store.user).toBeNull()
        })

        it('calls setUserTimezone when user has a timezone', async () => {
            const store = useAuthStore()
            const dtStore = useDateTimeStore()
            await store.hydrate()
            await flushPromises()
            // userFixture.timezone.name === 'UTC'
            expect(dtStore.userTimezone).toBe('UTC')
        })

        it('does not call setUserTimezone when timezone is absent', async () => {
            const userWithoutTz = { ...userFixture, timezone: null }
            global.mockHttp.reset()
            global.mockHttp.onGet('/api/user').replyOnce(200, { data: userWithoutTz })
            const store = useAuthStore()
            const dtStore = useDateTimeStore()
            await store.hydrate()
            await flushPromises()
            expect(dtStore.userTimezone).toBeNull()
        })
    })

    describe('clear', () => {
        it('sets user to null', () => {
            const store = useAuthStore()
            store.user = userFixture
            store.clear()
            expect(store.user).toBeNull()
        })

        it('sets isAuthenticated to false after clear', () => {
            const store = useAuthStore()
            store.user = userFixture
            store.clear()
            expect(store.isAuthenticated).toBe(false)
        })

        it('is safe to call when user is already null', () => {
            const store = useAuthStore()
            store.clear()
            expect(store.user).toBeNull()
        })
    })

    describe('patchUser', () => {
        it('patches the specified fields on the user object', () => {
            const store = useAuthStore()
            store.user = { ...userFixture }
            store.patchUser({ first_name: 'Jane', email: 'jane@example.com' })
            expect(store.user.first_name).toBe('Jane')
            expect(store.user.email).toBe('jane@example.com')
        })

        it('leaves unpatched fields unchanged', () => {
            const store = useAuthStore()
            store.user = { ...userFixture }
            store.patchUser({ first_name: 'Jane' })
            expect(store.user.last_name).toBe(userFixture.last_name)
            expect(store.user.role).toBe(userFixture.role)
        })

        it('is a no-op when user is null', () => {
            const store = useAuthStore()
            expect(() => store.patchUser({ first_name: 'Jane' })).not.toThrow()
            expect(store.user).toBeNull()
        })
    })
})
