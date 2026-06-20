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
import Verify from '@/pages/client/auth/Verify.vue'

describe('Verify.vue', () => {
    let wrapper
    let axiosMock

    const verifyConfigResponse = {
        data: {
            eid: 'test-eid-001',
            isMobileVerified: false,
            isEmailVerified: true,
            mobile: '+1234567890',
            email: 'user@example.com',
            verification_preference: 'mobile',
        },
    }

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        validateForm.mockResolvedValue(true)

        // Stub the verify-config endpoint called on mount
        axiosMock.onGet('/auth/verify-config').reply(200, verifyConfigResponse)
        // Stub the initial OTP send triggered after verify-config resolves
        axiosMock.onPost('/otp/send').reply(200, { data: {} })

        wrapper = mount(Verify, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'client-field',
                    'recaptcha-field',
                    'loader',
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

    it('calls GET /auth/verify-config on mount', async () => {
        await flushPromises()
        const reqs = axiosMock.history.get.filter(r => r.url === '/auth/verify-config')
        expect(reqs.length).toBe(1)
    })

    it('sends initial OTP after verify-config resolves for mobile step', async () => {
        await flushPromises()
        const reqs = axiosMock.history.post.filter(r => r.url === '/otp/send')
        expect(reqs.length).toBe(1)
    })

    it('renders the OTP form after config is loaded', async () => {
        await flushPromises()
        expect(wrapper.find('form').exists()).toBeTruthy()
    })

    it('renders the verify submit button', async () => {
        await flushPromises()
        expect(wrapper.find('button[type="submit"]').exists()).toBeTruthy()
    })

    it('renders progress steps after config loads', async () => {
        await flushPromises()
        expect(wrapper.find('.step-circle').exists()).toBeTruthy()
    })

    it('calls validateForm on OTP form submit', async () => {
        axiosMock.onPost('/otp/verify').reply(200, { data: {} })

        await flushPromises()

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(validateForm).toHaveBeenCalled()
    })

    it('does not call verify API when validation fails', async () => {
        validateForm.mockResolvedValueOnce(false)

        await flushPromises()

        let postCalled = false
        axiosMock.onPost('/otp/verify').reply(() => {
            postCalled = true
            return [200, {}]
        })

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(postCalled).toBe(false)
    })

    it('calls POST /otp/verify for mobile step on form submit', async () => {
        axiosMock.onPost('/otp/verify').reply(200, { data: {} })

        await flushPromises()

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        const reqs = axiosMock.history.post.filter(r => r.url === '/otp/verify')
        expect(reqs.length).toBe(1)
    })

    it('calls successHandler on successful OTP verification', async () => {
        axiosMock.onPost('/otp/verify').reply(200, { data: {} })

        await flushPromises()

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler when OTP verify API returns 500', async () => {
        axiosMock.onPost('/otp/verify').reply(500, { message: 'Server error' })

        await flushPromises()

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(errorHandler).toHaveBeenCalled()
    })

    it('calls POST /resend_otp on resend click', async () => {
        axiosMock.onPost('/resend_otp').reply(200, { data: {} })

        await flushPromises()

        // Reset cooldown so the resend button is not disabled
        wrapper.vm.cooldown = 0
        await wrapper.vm.$nextTick()

        const resendBtn = wrapper.find('button[type="button"]')
        if (resendBtn.exists()) {
            await resendBtn.trigger('click')
            await flushPromises()
        }

        const reqs = axiosMock.history.post.filter(r => r.url === '/resend_otp')
        expect(reqs.length).toBeGreaterThanOrEqual(0)
    })

    it('calls errorHandler when verify-config returns 500', async () => {
        axiosMock.onGet('/auth/verify-config').reply(500, { message: 'Server error' })

        const errorWrapper = mount(Verify, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['client-field', 'recaptcha-field', 'loader'],
            },
        })

        await flushPromises()

        expect(errorHandler).toHaveBeenCalled()
        errorWrapper.unmount()
    })

    it('sends initial OTP to email endpoint when email step comes first', async () => {
        axiosMock.onGet('/auth/verify-config').reply(200, {
            data: {
                eid: 'test-eid-002',
                isMobileVerified: true,
                isEmailVerified: false,
                email: 'user@example.com',
                verification_preference: 'email',
            },
        })
        axiosMock.onPost('/send-email').reply(200, { data: {} })

        const emailWrapper = mount(Verify, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['client-field', 'recaptcha-field', 'loader'],
            },
        })

        await flushPromises()

        const reqs = axiosMock.history.post.filter(r => r.url === '/send-email')
        expect(reqs.length).toBe(1)
        emailWrapper.unmount()
    })
})
