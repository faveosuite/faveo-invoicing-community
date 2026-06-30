jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: { id: 'stripe' }, query: {} }) }))
jest.mock('@/validations/admin/gatewayValidations', () => ({ buildGatewaySchema: jest.fn(() => ({})) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import PaymentGatewayEdit from '@/pages/admin/settings/settings/paymentGateway/PaymentGatewayEdit.vue'

describe('PaymentGatewayEdit.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/payment-gateway-list/).reply(200, {
            data: [{ name: 'stripe', status: true, description: 'Stripe gateway' }],
        })
        globalThis.mockHttp.onGet(/\/get-stripe-settings/).reply(200, {
            data: { stripe_key: 'pk_test', stripe_secret: 'sk_test', webhook_secret: '', processing_fee: '', webhook_url: '', auto_renewal: false },
        })
        wrapper = mount(PaymentGatewayEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'loader', 'TextField', 'action-button'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches payment gateway list on mount', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get.some(r => r.url.includes('payment-gateway-list'))).toBeTruthy()
    })

    it('fetches gateway settings for stripe on mount', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get.some(r => r.url.includes('get-stripe-settings'))).toBeTruthy()
    })

    it('calls save endpoint on form submit', async () => {
        globalThis.mockHttp.onGet(/\/update-api-key\/payment-gateway\/stripe/).reply(200, { data: {} })
        await flushPromises()
        const { validateForm } = require('@/helpers/formUtils.js')
        validateForm.mockResolvedValueOnce(true)
        wrapper.vm.save()
        await flushPromises()
        expect(globalThis.mockHttp.history.get.some(r => r.url.includes('update-api-key/payment-gateway/stripe'))).toBeTruthy()
    })
})
