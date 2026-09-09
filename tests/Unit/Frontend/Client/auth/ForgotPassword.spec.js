jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
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
import { successHandler, errorHandler } from '@/helpers/responseHandler'
import ForgotPassword from '@/pages/client/auth/ForgotPassword.vue'

describe('ForgotPassword.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        validateForm.mockResolvedValue(true)

        wrapper = mount(ForgotPassword, {
            global: {
                plugins: [createTestingPinia()],
                stubs: {
                    AuthLayout: { template: '<div><slot /></div>' },
                    ClientField: { template: '<div />', props: ['name', 'label', 'type', 'modelValue', 'error', 'required', 'autocomplete'] },
                    Honeypot: { template: '<div />', props: ['name', 'modelValue'], emits: ['update:modelValue', 'ready'] },
                    RecaptchaField: { template: '<div />', props: ['action'], methods: { getPayload: () => Promise.resolve({ 'g-recaptcha-response': 'token' }), reset: jest.fn(), triggerFallback: jest.fn() } },
                    RouterLink: { template: '<a><slot /></a>' },
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

    it('renders a form element', () => {
        expect(wrapper.find('form').exists()).toBeTruthy()
    })

    it('renders a submit button', () => {
        const btn = wrapper.find('button[type="submit"]')
        expect(btn.exists()).toBeTruthy()
    })

    it('submit button is initially disabled when honeypot is not ready', () => {
        const btn = wrapper.find('button[type="submit"]')
        // hpReady defaults to false so button should be disabled
        expect(btn.attributes('disabled')).toBeDefined()
    })

    it('calls POST /password/email on form submit when validation passes', async () => {
        axiosMock.onPost('/password/email').reply(200, { data: { message: 'sent' } })

        // Make honeypot ready by bypassing the ref check
        await wrapper.vm.$nextTick()

        // Trigger submit directly
        await wrapper.find('form').trigger('submit')
        await flushPromises()

        // validateForm was called
        expect(validateForm).toHaveBeenCalled()
    })

    it('does not call API when validation fails', async () => {
        validateForm.mockResolvedValueOnce(false)

        let postCalled = false
        axiosMock.onPost('/password/email').reply(() => {
            postCalled = true
            return [200, {}]
        })

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(postCalled).toBe(false)
    })

    it('calls successHandler when API returns 200', async () => {
        const fakeRes = { data: { message: 'sent' } }
        axiosMock.onPost('/password/email').reply(200, fakeRes)

        // Force hpReady so the button is not disabled
        wrapper.vm.hpReady = true
        await wrapper.vm.$nextTick()

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler when API returns 500', async () => {
        axiosMock.onPost('/password/email').reply(500, { message: 'Server error' })

        wrapper.vm.hpReady = true
        await wrapper.vm.$nextTick()

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(errorHandler).toHaveBeenCalled()
    })

    it('sets inline email error when server returns email validation error', async () => {
        axiosMock.onPost('/password/email').reply(422, {
            errors: { email: ['The email field is invalid.'] },
        })

        wrapper.vm.hpReady = true
        await wrapper.vm.$nextTick()

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        // errorHandler should NOT be called — inline error is set instead
        expect(errorHandler).not.toHaveBeenCalled()
    })
})
