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
        // save() posts the form — this was previously mocked/asserted as a GET,
        // which never actually matched the real POST call.
        globalThis.mockHttp.onPost(/\/update-api-key\/payment-gateway\/stripe/).reply(200, { data: {} })
        await flushPromises()
        const { validateForm } = require('@/helpers/formUtils.js')
        validateForm.mockResolvedValueOnce(true)
        wrapper.vm.save()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.some(r => r.url.includes('update-api-key/payment-gateway/stripe'))).toBeTruthy()
    })

    it('warns before a save that would disable auto-renewal, and does not save until confirmed', async () => {
        // Loaded settings have auto_renewal: true this time, so toggling it
        // off and saving is an on-to-off transition.
        globalThis.mockHttp.onGet(/\/get-stripe-settings/).reply(200, {
            data: { stripe_key: 'pk_test', stripe_secret: 'sk_test', webhook_secret: '', processing_fee: '', webhook_url: '', auto_renewal: true },
        })
        wrapper = mount(PaymentGatewayEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'loader', 'TextField', 'action-button'],
            },
        })
        await flushPromises()

        globalThis.mockHttp.onPost(/\/update-api-key\/payment-gateway\/stripe/).reply(200, { data: {} })
        const { validateForm } = require('@/helpers/formUtils.js')
        validateForm.mockResolvedValueOnce(true)

        wrapper.vm.form.auto_renewal = false
        wrapper.vm.save()
        await flushPromises()

        expect(wrapper.vm.showDisableWarningModal).toBe(true)
        expect(globalThis.mockHttp.history.post.some(r => r.url.includes('update-api-key/payment-gateway/stripe'))).toBeFalsy()

        validateForm.mockResolvedValueOnce(true)
        wrapper.vm.confirmDisableAndSave()
        await flushPromises()

        expect(wrapper.vm.showDisableWarningModal).toBe(false)
        expect(globalThis.mockHttp.history.post.some(r => r.url.includes('update-api-key/payment-gateway/stripe'))).toBeTruthy()
    })
})
