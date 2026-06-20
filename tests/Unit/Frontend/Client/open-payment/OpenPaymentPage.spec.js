jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/validations/client/openPaymentSchema', () => ({ openPaymentSchema: {} }))
jest.mock('@recaptcha', () => ({
    RecaptchaField: {
        template: '<div />',
        props: ['action'],
        methods: {
            getPayload: () => Promise.resolve({ 'g-recaptcha-response': 'token' }),
            reset: jest.fn(),
            triggerFallback: jest.fn(),
        },
    },
}), { virtual: true })
jest.mock('@/core/composables/useBreadcrumb.js', () => ({ setPageTitle: jest.fn() }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import { validateForm } from '@/helpers/formUtils.js'
import OpenPaymentPage from '@/pages/client/open-payment/OpenPaymentPage.vue'

describe('OpenPaymentPage.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        axiosMock.onGet('/pay/config').reply(200, {
            data: {
                app_title: 'Test App',
                currencies: [{ code: 'USD', symbol: '$', name: 'United States dollar' }],
                gateways: [{ name: 'Razorpay', processing_fee: 0 }],
            },
        })
        axiosMock.onGet('/pay/detect-country').reply(200, { data: { country: null } })

        wrapper = mount(OpenPaymentPage, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'app-alert',
                    'app-modal',
                    'client-field',
                    'phone-field',
                    'dynamic-select',
                    'select-field',
                    'recaptcha-field',
                    'global-loader',
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

    it('renders the stepper with 3 steps', () => {
        const steps = wrapper.findAll('.step-item')
        expect(steps.length).toBe(3)
    })

    it('starts on the form step', () => {
        expect(wrapper.find('[key="form"]').exists() || wrapper.find('form').exists()).toBeTruthy()
    })

    it('loads config on mount and calls GET /pay/config', async () => {
        await flushPromises()
        expect(axiosMock.history.get.some(r => r.url.includes('/pay/config'))).toBe(true)
    })

    it('falls back to defaults when /pay/config returns 500', async () => {
        axiosMock.onGet('/pay/config').reply(500)
        axiosMock.onGet('/pay/detect-country').reply(200, { data: { country: null } })

        const w = mount(OpenPaymentPage, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'app-alert', 'app-modal', 'client-field', 'phone-field',
                    'dynamic-select', 'select-field', 'recaptcha-field',
                    'global-loader', 'router-link',
                ],
            },
        })
        await flushPromises()
        // Component should still exist after error fallback
        expect(w.exists()).toBeTruthy()
        w.unmount()
    })

    it('shows a submit button on the form step', () => {
        const btn = wrapper.find('button[type="submit"]')
        expect(btn.exists()).toBeTruthy()
    })

    it('calls validateForm on form submit', async () => {
        await flushPromises()
        await wrapper.find('form').trigger('submit')
        await flushPromises()
        expect(validateForm).toHaveBeenCalled()
    })

    it('does not advance to summary step when validation fails', async () => {
        validateForm.mockResolvedValueOnce(false)
        await flushPromises()
        await wrapper.find('form').trigger('submit')
        await flushPromises()
        expect(wrapper.vm.step).toBe('form')
    })

    it('advances to summary step when validation passes', async () => {
        axiosMock.onGet('/pay/calculate').reply(200, {
            data: { base_amount: '100.00', processing_fee: '0.00', processing_fee_rate: 0, total: '100.00' },
        })
        validateForm.mockResolvedValueOnce(true)
        await flushPromises()
        await wrapper.find('form').trigger('submit')
        await flushPromises()
        expect(wrapper.vm.step).toBe('summary')
    })
})
