jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({
    successHandler: jest.fn(),
    errorHandler: jest.fn(),
}))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
// Mutable so a test can set ?redirect=; reset in afterEach.
const mockRoute = { params: {}, query: {} }
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => mockRoute, RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/validations/client/authSchemas.js', () => ({
    loginSchema: {},
    registerSchema: { validateSync: jest.fn(() => ({ inner: [] })) },
    forgotSchema: {},
    resetSchema: {},
    twoFaSchema: {},
    recoverySchema: {},
    otpSchema: {},
    passwordChecks: jest.fn(() => ({ length: false, lower: false, upper: false, number: false, special: false })),
}))
jest.mock('@recaptcha', () => ({
    RecaptchaField: { template: '<div />', props: ['action'], methods: { getPayload: () => Promise.resolve({ 'g-recaptcha-response': 'token' }), reset: jest.fn(), triggerFallback: jest.fn() } },
}), { virtual: true })

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import { validateForm } from '@/helpers/formUtils.js'
import { successHandler, errorHandler } from '@/helpers/responseHandler'
import { registerSchema } from '@/validations/client/authSchemas.js'
import Login from '@/pages/client/auth/Login.vue'

const mountLogin = () => mount(Login, { global: { plugins: [createTestingPinia()], stubs: STUBS } })
const STUBS = ['client-field', 'client-checkbox', 'dynamic-select', 'phone-field', 'honeypot', 'recaptcha-field', 'social-buttons', 'router-link']

describe('Login.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        validateForm.mockResolvedValue(true)
        axiosMock.onGet('/auth/login-config').reply(200, {
            data: { social: { google: 0, github: 0, twitter: 0, linkedin: 0 } },
        })
        wrapper = mountLogin()
    })

    afterEach(() => {
        axiosMock.restore()
        jest.clearAllMocks()
        mockRoute.query = {}
    })

    it('renders one login form', () => {
        expect(wrapper.findAll('form')).toHaveLength(1)
        expect(wrapper.findAll('button[type="submit"]')).toHaveLength(1)
    })

    it('shows the "registration complete" notice when Verify sends ?verified=1', async () => {
        globalThis.history.replaceState({}, '', '/login?verified=1')
        const { useAlertStore } = await import('@/core/stores/alert.js')
        const verified = mountLogin()

        expect(useAlertStore().setAlert).toHaveBeenCalledWith(expect.objectContaining({ type: 'success', message: 'message.registration_complete' }))
        expect(globalThis.location.search).toBe('') // flag stripped so a refresh doesn't repeat it
        verified.unmount()
    })

    it('shows the translated social error for ?social_error=<reason>', async () => {
        globalThis.history.replaceState({}, '', '/login?social_error=denied')
        const { useAlertStore } = await import('@/core/stores/alert.js')
        const w = mountLogin()

        expect(useAlertStore().setAlert).toHaveBeenCalledWith(expect.objectContaining({ type: 'danger', message: 'message.social_error.denied' }))
        expect(globalThis.location.search).toBe('')
        w.unmount()
    })

    it('fetches login config on mount without asking for the visitor location', async () => {
        await flushPromises()
        const reqs = axiosMock.history.get.filter(r => r.url === '/auth/login-config')
        expect(reqs.length).toBeGreaterThan(0)
        expect(reqs[0].params?.with_location).toBeUndefined()
    })

    it('calls validateForm on submit', async () => {
        axiosMock.onPost('/login').reply(200, { data: { redirect: '/dashboard' } })
        await wrapper.find('form').trigger('submit')
        await flushPromises()
        expect(validateForm).toHaveBeenCalled()
    })

    it('does not call login API when validation fails', async () => {
        validateForm.mockResolvedValueOnce(false)
        let postCalled = false
        axiosMock.onPost('/login').reply(() => {
            postCalled = true
            return [200, { data: { redirect: '/' } }]
        })
        await wrapper.find('form').trigger('submit')
        await flushPromises()
        expect(postCalled).toBe(false)
    })

    it('calls errorHandler with setErrors on login API error', async () => {
        axiosMock.onPost('/login').reply(422, { errors: { email_username: ['Invalid credentials.'] } })
        await wrapper.find('form').trigger('submit')
        await flushPromises()
        expect(errorHandler).toHaveBeenCalledWith(
            expect.anything(),
            expect.any(String),
            expect.objectContaining({ setErrors: expect.any(Function) })
        )
    })

    it('sets saving to false after request completes', async () => {
        axiosMock.onPost('/login').reply(200, { data: { redirect: '/dashboard' } })
        await wrapper.find('form').trigger('submit')
        await flushPromises()
        expect(wrapper.vm.saving).toBe(false)
    })

    // ── Login: ?redirect= is handed to the backend, which decides ─────
    describe('login ?redirect= handling', () => {
        let originalLocation

        beforeEach(() => {
            originalLocation = globalThis.location
            Object.defineProperty(window, 'location', { writable: true, value: { href: '', search: '', pathname: '/login' } })
        })

        afterEach(() => {
            Object.defineProperty(window, 'location', { writable: true, value: originalLocation })
        })

        it('sends ?redirect= with the login request', async () => {
            mockRoute.query = { redirect: '/store?group=1' }
            axiosMock.onPost('/login').reply(200, { data: { redirect: 'http://localhost/store?group=1' } })
            await wrapper.find('form').trigger('submit')
            await flushPromises()
            expect(JSON.parse(axiosMock.history.post[0].data).redirect).toBe('/store?group=1')
        })

        it('sends no redirect when the page has none', async () => {
            axiosMock.onPost('/login').reply(200, { data: { redirect: 'http://localhost/client-dashboard' } })
            await wrapper.find('form').trigger('submit')
            await flushPromises()
            expect(JSON.parse(axiosMock.history.post[0].data)).not.toHaveProperty('redirect')
        })

        it.each([
            'http://localhost/store?group=1',
            'http://localhost/verify-2fa',
            'http://localhost/verify',
        ])('always follows the backend redirect (%s), even with ?redirect= set', async (target) => {
            mockRoute.query = { redirect: '/store?group=1' }
            axiosMock.onPost('/login').reply(200, { data: { redirect: target } })
            await wrapper.find('form').trigger('submit')
            await flushPromises()
            expect(globalThis.location.href).toBe(target)
        })
    })
})
