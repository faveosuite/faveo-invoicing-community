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
import Register from '@/pages/client/auth/Register.vue'

const STUBS = ['client-field', 'client-checkbox', 'dynamic-select', 'phone-field', 'honeypot', 'recaptcha-field', 'social-buttons', 'router-link']
const mountRegister = () => mount(Register, { global: { plugins: [createTestingPinia()], stubs: STUBS } })

describe('Register.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        registerSchema.validateSync.mockReturnValue({ inner: [] })
        axiosMock.onGet('/auth/login-config').reply(200, {
            data: {
                social: { google: 0, github: 0, twitter: 0, linkedin: 0 },
                status: { terms: false },
                apiKeys: { terms_url: '#' },
            },
        })
        wrapper = mountRegister()
    })

    afterEach(() => {
        axiosMock.restore()
        jest.clearAllMocks()
        mockRoute.query = {}
    })

    it('renders one register form', () => {
        expect(wrapper.findAll('form')).toHaveLength(1)
    })


    it('calls POST /auth/register on register form submit when validation passes', async () => {
        axiosMock.onPost('/auth/register').reply(200, {
            data: { need_verify: 0 },
        })

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        const reqs = axiosMock.history.post.filter(r => r.url === '/auth/register')
        expect(reqs.length).toBeGreaterThan(0)
    })

    it('sends a registration without verification straight to log in with a notice', async () => {
        const original = globalThis.location
        Object.defineProperty(window, 'location', { writable: true, value: { href: '' } })
        axiosMock.onPost('/auth/register').reply(200, { data: { need_verify: 0 } })

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(globalThis.location.href).toMatch(/\/login\?registered=1$/)
        Object.defineProperty(window, 'location', { writable: true, value: original })
    })

    it('goes straight to verify without the "all set" message when verification is needed', async () => {
        axiosMock.onPost('/auth/register').reply(200, {
            message: 'You’re all set! Registration complete.',
            data: { need_verify: 1 },
        })

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(successHandler).not.toHaveBeenCalled()
    })

    it('calls errorHandler with setErrors on register API error', async () => {
        axiosMock.onPost('/auth/register').reply(422, {
            errors: { email: ['Email already taken.'] },
        })

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(errorHandler).toHaveBeenCalledWith(
            expect.anything(),
            expect.any(String),
            expect.objectContaining({ setErrors: expect.any(Function) })
        )
    })


    // ── onMobileCountryChange ────────────────────────────────────────
    it('onMobileCountryChange updates mobile_country_iso and mobile_code', () => {
        wrapper.vm.onMobileCountryChange({ iso: 'in', dialCode: '91' })
        expect(wrapper.vm.form.mobile_country_iso).toBe('in')
        expect(wrapper.vm.form.mobile_code).toBe('91')
    })

    // ── onMobileInput ────────────────────────────────────────────────
    it('onMobileInput strips non-digit characters from the mobile number', () => {
        wrapper.vm.onMobileInput('+91 999-888-7777')
        expect(wrapper.vm.form.mobile).toBe('919998887777')
    })

    it('onMobileInput handles numeric string input', () => {
        wrapper.vm.onMobileInput('9876543210')
        expect(wrapper.vm.form.mobile).toBe('9876543210')
    })

    // ── Register: inline validation errors from schema ───────────────
    it('register shows inline errors when registerSchema.validateSync throws', async () => {
        const { scrollToFirstError } = require('@/helpers/formUtils.js')
        registerSchema.validateSync.mockImplementationOnce(() => {
            const err = new Error('Validation')
            err.inner = [{ path: 'first_name', message: 'Required' }]
            throw err
        })
        await wrapper.find('form').trigger('submit')
        await flushPromises()
        expect(scrollToFirstError).toHaveBeenCalled()
    })

    // ── Register: termsEnabled validation ────────────────────────────
    it('register blocks submit when termsEnabled and terms not accepted', async () => {
        const { scrollToFirstError } = require('@/helpers/formUtils.js')
        wrapper.vm.termsEnabled = true
        wrapper.vm.form.terms = false
        await wrapper.find('form').trigger('submit')
        await flushPromises()
        expect(scrollToFirstError).toHaveBeenCalled()
    })

    // ── Register: need_verify redirect ───────────────────────────────
    it('register sets saving false after request completes', async () => {
        axiosMock.onPost('/auth/register').reply(200, { data: { need_verify: 0 } })
        await wrapper.find('form').trigger('submit')
        await flushPromises()
        expect(wrapper.vm.saving).toBe(false)
    })


    it('loads terms settings from login-config on mount', async () => {
        axiosMock.restore()
        const localMock = new MockAdapter(http)
        localMock.onGet('/auth/login-config').reply(200, {
            data: { social: {}, status: { terms: true }, apiKeys: { terms_url: '/terms' } },
        })
        const w = mountRegister()
        await flushPromises()
        expect(w.vm.termsEnabled).toBe(true)
        expect(w.vm.termsUrl).toBe('/terms')
        localMock.restore()
        axiosMock = new MockAdapter(http)
    })

    it('does not send company, address or country', async () => {
        axiosMock.onPost('/auth/register').reply(200, { data: { need_verify: 0 } })
        await wrapper.find('form').trigger('submit')
        await flushPromises()
        const body = JSON.parse(axiosMock.history.post[0].data)
        expect(body).not.toHaveProperty('company')
        expect(body).not.toHaveProperty('address')
        expect(body).not.toHaveProperty('country')
    })

})
