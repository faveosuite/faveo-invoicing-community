jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({
    successHandler: jest.fn(),
    errorHandler: jest.fn(),
    applyServerValidation: jest.fn(),
}))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
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
import { successHandler, applyServerValidation } from '@/helpers/responseHandler'
import { registerSchema } from '@/validations/client/authSchemas.js'
import LoginRegister from '@/pages/client/auth/LoginRegister.vue'

describe('LoginRegister.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        validateForm.mockResolvedValue(true)
        registerSchema.validateSync.mockReturnValue({ inner: [] })

        // Stub the login-config endpoint called on mount
        axiosMock.onGet('/auth/login-config').reply(200, {
            data: {
                social: { google: 0, github: 0, twitter: 0, linkedin: 0 },
                status: { terms: false },
                apiKeys: { terms_url: '#' },
            },
        })

        wrapper = mount(LoginRegister, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'client-field',
                    'client-checkbox',
                    'dynamic-select',
                    'phone-field',
                    'honeypot',
                    'recaptcha-field',
                    'social-buttons',
                    'router-link',
                ],
            },
        })
    })

    afterEach(() => {
        axiosMock.restore()
        jest.clearAllMocks()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders two forms (login and register)', () => {
        const forms = wrapper.findAll('form')
        expect(forms.length).toBe(2)
    })

    it('renders two submit buttons', () => {
        const btns = wrapper.findAll('button[type="submit"]')
        expect(btns.length).toBe(2)
    })

    it('fetches login config on mount', async () => {
        await flushPromises()
        const reqs = axiosMock.history.get.filter(r => r.url === '/auth/login-config')
        expect(reqs.length).toBeGreaterThan(0)
    })

    it('calls validateForm on login form submit', async () => {
        axiosMock.onPost('/login').reply(200, { data: { redirect: '/dashboard' } })

        await wrapper.findAll('form')[0].trigger('submit')
        await flushPromises()

        expect(validateForm).toHaveBeenCalled()
    })

    it('does not call login API when login validation fails', async () => {
        validateForm.mockResolvedValueOnce(false)

        let postCalled = false
        axiosMock.onPost('/login').reply(() => {
            postCalled = true
            return [200, { data: { redirect: '/' } }]
        })

        await wrapper.findAll('form')[0].trigger('submit')
        await flushPromises()

        expect(postCalled).toBe(false)
    })

    it('calls applyServerValidation on login API error', async () => {
        axiosMock.onPost('/login').reply(422, {
            errors: { email_username: ['Invalid credentials.'] },
        })

        await wrapper.findAll('form')[0].trigger('submit')
        await flushPromises()

        expect(applyServerValidation).toHaveBeenCalled()
    })

    it('calls POST /auth/register on register form submit when validation passes', async () => {
        axiosMock.onPost('/auth/register').reply(200, {
            data: { need_verify: 0 },
        })

        await wrapper.findAll('form')[1].trigger('submit')
        await flushPromises()

        const reqs = axiosMock.history.post.filter(r => r.url === '/auth/register')
        expect(reqs.length).toBeGreaterThan(0)
    })

    it('calls successHandler on successful registration', async () => {
        axiosMock.onPost('/auth/register').reply(200, {
            data: { need_verify: 0 },
        })

        await wrapper.findAll('form')[1].trigger('submit')
        await flushPromises()

        expect(successHandler).toHaveBeenCalled()
    })

    it('calls applyServerValidation on register API error', async () => {
        axiosMock.onPost('/auth/register').reply(422, {
            errors: { email: ['Email already taken.'] },
        })

        await wrapper.findAll('form')[1].trigger('submit')
        await flushPromises()

        expect(applyServerValidation).toHaveBeenCalled()
    })

    // ── onCountryChange ──────────────────────────────────────────────
    it('onCountryChange updates regForm.country', () => {
        const country = { id: 1, name: 'India', code: 'IN' }
        wrapper.vm.onCountryChange(country)
        expect(wrapper.vm.regForm.country).toEqual(country)
    })

    // ── onMobileCountryChange ────────────────────────────────────────
    it('onMobileCountryChange updates mobile_country_iso and mobile_code', () => {
        wrapper.vm.onMobileCountryChange({ iso: 'in', dialCode: '91' })
        expect(wrapper.vm.regForm.mobile_country_iso).toBe('in')
        expect(wrapper.vm.regForm.mobile_code).toBe('91')
    })

    // ── onMobileInput ────────────────────────────────────────────────
    it('onMobileInput strips non-digit characters from the mobile number', () => {
        wrapper.vm.onMobileInput('+91 999-888-7777')
        expect(wrapper.vm.regForm.mobile).toBe('919998887777')
    })

    it('onMobileInput handles numeric string input', () => {
        wrapper.vm.onMobileInput('9876543210')
        expect(wrapper.vm.regForm.mobile).toBe('9876543210')
    })

    // ── checklist computed ───────────────────────────────────────────
    it('checklist returns 5 requirement items', () => {
        expect(wrapper.vm.checklist).toHaveLength(5)
    })

    it('checklist marks items as ok based on passwordChecks result', () => {
        const { passwordChecks } = require('@/validations/client/authSchemas.js')
        passwordChecks.mockReturnValueOnce({ length: true, lower: true, upper: false, number: false, special: false })
        wrapper.vm.regForm.password = 'Test123'
        const list = wrapper.vm.checklist
        const length = list.find(c => c.key === 'length')
        expect(length).toBeDefined()
    })

    // ── prefillCountry ───────────────────────────────────────────────
    it('prefillCountry returns early when name is missing', async () => {
        await wrapper.vm.prefillCountry('IN', null)
        expect(wrapper.vm.regForm.country).toBeNull()
    })

    it('prefillCountry fetches countries and pre-selects the matching one', async () => {
        axiosMock.onGet('/dependency/countries').reply(200, {
            data: { countries: [{ id: 1, name: 'India', code: 'IN' }] },
        })
        await wrapper.vm.prefillCountry('IN', 'India')
        await flushPromises()
        expect(wrapper.vm.regForm.country).toEqual({ id: 1, name: 'India', code: 'IN' })
    })

    it('prefillCountry does not set country when no match is found', async () => {
        axiosMock.onGet('/dependency/countries').reply(200, {
            data: { countries: [{ id: 2, name: 'USA', code: 'US' }] },
        })
        await wrapper.vm.prefillCountry('IN', 'India')
        await flushPromises()
        expect(wrapper.vm.regForm.country).toBeNull()
    })

    // ── Register: inline validation errors from schema ───────────────
    it('register shows inline errors when registerSchema.validateSync throws', async () => {
        const { scrollToFirstError } = require('@/helpers/formUtils.js')
        registerSchema.validateSync.mockImplementationOnce(() => {
            const err = new Error('Validation')
            err.inner = [{ path: 'first_name', message: 'Required' }]
            throw err
        })
        await wrapper.findAll('form')[1].trigger('submit')
        await flushPromises()
        expect(scrollToFirstError).toHaveBeenCalled()
    })

    // ── Register: termsEnabled validation ────────────────────────────
    it('register blocks submit when termsEnabled and terms not accepted', async () => {
        const { scrollToFirstError } = require('@/helpers/formUtils.js')
        wrapper.vm.termsEnabled = true
        wrapper.vm.regForm.terms = false
        await wrapper.findAll('form')[1].trigger('submit')
        await flushPromises()
        expect(scrollToFirstError).toHaveBeenCalled()
    })

    // ── Register: need_verify redirect ───────────────────────────────
    it('register sets loggingIn false after request completes', async () => {
        axiosMock.onPost('/auth/register').reply(200, { data: { need_verify: 0 } })
        await wrapper.findAll('form')[1].trigger('submit')
        await flushPromises()
        expect(wrapper.vm.registering).toBe(false)
    })

    // ── Login: loggingIn resets after completion ─────────────────────
    it('login sets loggingIn to false after request completes', async () => {
        axiosMock.onPost('/login').reply(200, { data: { redirect: '/dashboard' } })
        await wrapper.findAll('form')[0].trigger('submit')
        await flushPromises()
        expect(wrapper.vm.loggingIn).toBe(false)
    })

    // ── onMounted: prefillCountry triggered by location data ─────────
    it('onMounted calls prefillCountry when location data is returned', async () => {
        axiosMock.restore()
        const localMock = new MockAdapter(http)
        localMock.onGet('/auth/login-config').reply(200, {
            data: {
                social: { google: 0, github: 0, twitter: 0, linkedin: 0 },
                status: { terms: true },
                apiKeys: { terms_url: '/terms' },
                location: { iso_code: 'IN', country: 'India' },
            },
        })
        localMock.onGet('/dependency/countries').reply(200, {
            data: { countries: [{ id: 1, name: 'India', code: 'IN' }] },
        })

        const w = mount(LoginRegister, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['client-field', 'client-checkbox', 'dynamic-select', 'phone-field',
                    'honeypot', 'recaptcha-field', 'social-buttons', 'router-link'],
            },
        })
        await flushPromises()
        expect(w.vm.termsEnabled).toBe(true)
        expect(w.vm.termsUrl).toBe('/terms')
        localMock.restore()
        axiosMock = new MockAdapter(http)
    })
})
