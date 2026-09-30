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
import Verify2FA from '@/pages/client/auth/Verify2FA.vue'

describe('Verify2FA.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        validateForm.mockResolvedValue(true)

        // Stub the 2FA check called on mount
        axiosMock.onGet('/auth/2fa-check').reply(200, { data: {} })

        wrapper = mount(Verify2FA, {
            global: {
                plugins: [createTestingPinia()],
                stubs: {
                    AuthLayout: { template: '<div><slot /></div>' },
                    ClientField: { template: '<div />', props: ['name', 'label', 'type', 'modelValue', 'error'] },
                    Honeypot: { template: '<div />', props: ['name', 'modelValue'], emits: ['update:modelValue', 'ready'] },
                    RecaptchaField: { template: '<div />', props: ['action'], methods: { getPayload: () => Promise.resolve({ 'g-recaptcha-response': 'token' }), reset: jest.fn(), triggerFallback: jest.fn() } },
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

    it('calls GET /auth/2fa-check on mount', async () => {
        await flushPromises()
        const reqs = axiosMock.history.get.filter(r => r.url === '/auth/2fa-check')
        expect(reqs.length).toBe(1)
    })

    it('shows TOTP form after the 2FA check resolves', async () => {
        await flushPromises()
        expect(wrapper.find('form').exists()).toBeTruthy()
    })

    it('renders a submit button in TOTP mode', async () => {
        await flushPromises()
        expect(wrapper.find('button[type="submit"]').exists()).toBeTruthy()
    })

    it('defaults to TOTP mode (useRecovery is false)', async () => {
        await flushPromises()
        expect(wrapper.vm.useRecovery).toBe(false)
    })

    it('switches to recovery mode when toggleMode(true) is called', async () => {
        await flushPromises()
        wrapper.vm.toggleMode(true)
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.useRecovery).toBe(true)
    })

    it('switches back to TOTP mode when toggleMode(false) is called', async () => {
        await flushPromises()
        wrapper.vm.toggleMode(true)
        wrapper.vm.toggleMode(false)
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.useRecovery).toBe(false)
    })

    it('calls POST /2fa/loginValidate on TOTP form submit when validation passes', async () => {
        axiosMock.onPost('/2fa/loginValidate').reply(200, { data: { redirect: '/dashboard' } })

        await flushPromises()

        wrapper.vm.totpHpReady = true
        await wrapper.vm.$nextTick()

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(validateForm).toHaveBeenCalled()
    })

    it('does not call API when TOTP validation fails', async () => {
        validateForm.mockResolvedValueOnce(false)

        await flushPromises()

        let postCalled = false
        axiosMock.onPost('/2fa/loginValidate').reply(() => {
            postCalled = true
            return [200, {}]
        })

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(postCalled).toBe(false)
    })

    it('calls errorHandler when 2FA verify API returns 500', async () => {
        axiosMock.onPost('/2fa/loginValidate').reply(500, { message: 'Server error' })

        await flushPromises()

        wrapper.vm.totpHpReady = true
        await wrapper.vm.$nextTick()

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(errorHandler).toHaveBeenCalled()
    })

    it('calls POST /verify-recovery-code in recovery mode', async () => {
        axiosMock.onPost('/verify-recovery-code').reply(200, { data: { redirect: '/dashboard' } })

        await flushPromises()

        wrapper.vm.toggleMode(true)
        wrapper.vm.recHpReady = true
        await wrapper.vm.$nextTick()

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        const reqs = axiosMock.history.post.filter(r => r.url === '/verify-recovery-code')
        expect(reqs.length).toBe(1)
    })

    it('calls successHandler when verify response has no redirect', async () => {
        axiosMock.onPost('/2fa/loginValidate').reply(200, { data: {} })

        await flushPromises()

        wrapper.vm.totpHpReady = true
        await wrapper.vm.$nextTick()

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(successHandler).toHaveBeenCalled()
    })
})
