jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({
    successHandler: jest.fn(),
    errorHandler: jest.fn(),
    applyServerValidation: jest.fn(),
}))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: { token: 'test-token-123' }, query: {} }),
}))
jest.mock('@/validations/client/authSchemas.js', () => ({
    forgotSchema: {},
    loginSchema: {},
    registerSchema: {},
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
import ResetPassword from '@/pages/client/auth/ResetPassword.vue'

describe('ResetPassword.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        validateForm.mockResolvedValue(true)

        // Stub the validate endpoint called on mount
        axiosMock.onGet('/auth/reset-validate/test-token-123').reply(200, {
            data: [{ reset_token: 'test-token-123', email: 'user@example.com' }],
        })

        wrapper = mount(ResetPassword, {
            global: {
                plugins: [createTestingPinia()],
                stubs: {
                    AuthLayout: { template: '<div><slot /></div>' },
                    ClientField: { template: '<div />', props: ['name', 'label', 'type', 'modelValue', 'error', 'required'] },
                    Honeypot: { template: '<div />', props: ['name', 'modelValue'], emits: ['update:modelValue', 'ready'] },
                    RecaptchaField: { template: '<div />', props: ['action'], methods: { getPayload: () => Promise.resolve({ 'g-recaptcha-response': 'token' }), reset: jest.fn(), triggerFallback: jest.fn() } },
                    RouterLink: { template: '<a><slot /></a>' },
                    Loader: { template: '<div />' },
                },
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

    it('calls GET /auth/reset-validate/:token on mount', async () => {
        await flushPromises()
        const reqs = axiosMock.history.get.filter(r => r.url === '/auth/reset-validate/test-token-123')
        expect(reqs.length).toBe(1)
    })

    it('shows the form after successful token validation', async () => {
        await flushPromises()
        expect(wrapper.find('form').exists()).toBeTruthy()
    })

    it('renders a submit button after successful token validation', async () => {
        await flushPromises()
        expect(wrapper.find('button[type="submit"]').exists()).toBeTruthy()
    })

    it('shows the invalid state when validate endpoint returns 500', async () => {
        axiosMock.onGet('/auth/reset-validate/test-token-123').reply(500, {
            message: 'Token expired',
        })

        const errorWrapper = mount(ResetPassword, {
            global: {
                plugins: [createTestingPinia()],
                stubs: {
                    AuthLayout: { template: '<div><slot /></div>' },
                    ClientField: { template: '<div />', props: ['name', 'label', 'type', 'modelValue', 'error', 'required'] },
                    Honeypot: { template: '<div />', props: ['name', 'modelValue'], emits: ['update:modelValue', 'ready'] },
                    RecaptchaField: { template: '<div />', props: ['action'], methods: { getPayload: () => Promise.resolve({}), reset: jest.fn() } },
                    RouterLink: { template: '<a><slot /></a>' },
                    Loader: { template: '<div />' },
                },
            },
        })

        await flushPromises()

        // invalid ref is true so the form should not be rendered
        expect(errorWrapper.vm.invalid).toBe(true)
    })

    it('calls POST /password/reset on form submit when validation passes', async () => {
        axiosMock.onPost('/password/reset').reply(200, {
            data: { redirect: '/login' },
        })

        await flushPromises()

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(validateForm).toHaveBeenCalled()
        const reqs = axiosMock.history.post.filter(r => r.url === '/password/reset')
        expect(reqs.length).toBe(1)
    })

    it('does not call API when validation fails', async () => {
        validateForm.mockResolvedValueOnce(false)

        await flushPromises()

        let postCalled = false
        axiosMock.onPost('/password/reset').reply(() => {
            postCalled = true
            return [200, {}]
        })

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(postCalled).toBe(false)
    })

    it('calls successHandler when password reset succeeds', async () => {
        axiosMock.onPost('/password/reset').reply(200, {
            data: { redirect: '/login' },
        })

        await flushPromises()

        wrapper.vm.hpReady = true
        await wrapper.vm.$nextTick()

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(successHandler).toHaveBeenCalled()
    })

    it('calls applyServerValidation when reset API returns 422', async () => {
        axiosMock.onPost('/password/reset').reply(422, {
            errors: { password: ['Password does not meet requirements.'] },
        })

        await flushPromises()

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(applyServerValidation).toHaveBeenCalled()
    })
})
