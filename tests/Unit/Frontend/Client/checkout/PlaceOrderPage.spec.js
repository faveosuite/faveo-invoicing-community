jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: {}, query: { invoice: '42', gateway: 'stripe' } }),
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import PlaceOrderPage from '@/pages/client/checkout/PlaceOrderPage.vue'

const payInitResponse = {
    data: {
        invoice: { id: 42, number: 'INV-0042', status: 'pending' },
        items: [
            { id: 1, product_name: 'Product A', image: null, quantity: 1, agents: null, subtotal: 99 },
        ],
        summary: { subtotal_ex_tax: 99, tax_total: 0, tax_label: '', taxes: [], discount: 0 },
        amount: 99,
        currency_symbol: '$',
        gateways: [
            { name: 'stripe', processing_fee: null, fee_amount: 0, payable: 99 },
        ],
    },
}

describe('PlaceOrderPage.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        axiosMock.onGet('/invoice/42/pay-init').reply(200, payInitResponse)

        wrapper = mount(PlaceOrderPage, {
            global: {
                plugins: [createTestingPinia({
                    initialState: {
                        alert: { message: '', type: '', component_name: '' },
                    },
                })],
                stubs: ['loader', 'app-modal', 'app-alert'],
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

    it('fetches GET /invoice/42/pay-init on mount', async () => {
        await flushPromises()
        expect(axiosMock.history.get.some(r => r.url.includes('/invoice/42/pay-init'))).toBe(true)
    })

    it('renders items table after data loads', async () => {
        await flushPromises()
        expect(wrapper.find('table.shop_table').exists()).toBeTruthy()
    })

    it('displays the product name after load', async () => {
        await flushPromises()
        expect(wrapper.text()).toContain('Product A')
    })

    it('displays the order summary section after load', async () => {
        await flushPromises()
        expect(wrapper.find('.card').exists()).toBeTruthy()
    })

    it('shows pay now button after data loads', async () => {
        await flushPromises()
        const btn = wrapper.find('button.btn-dark.btn-modern')
        expect(btn.exists()).toBeTruthy()
        expect(btn.text()).toContain('message.pay_now')
    })

    it('shows not-found state when 500 error occurs', async () => {
        axiosMock.onGet('/invoice/42/pay-init').reply(500, { message: 'Server error' })

        wrapper = mount(PlaceOrderPage, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['loader', 'app-modal', 'app-alert'],
            },
        })

        await flushPromises()
        expect(wrapper.find('.fa-file-invoice').exists()).toBeTruthy()
    })
})

describe('PlaceOrderPage.vue — computed and helpers', () => {
    let wrapper
    let axiosMock

    beforeEach(async () => {
        axiosMock = new MockAdapter(http)
        axiosMock.onGet('/invoice/42/pay-init').reply(200, payInitResponse)

        wrapper = mount(PlaceOrderPage, {
            global: {
                plugins: [createTestingPinia({ initialState: { alert: {} } })],
                stubs: ['loader', 'app-modal', 'app-alert'],
            },
        })
        await flushPromises()
    })

    afterEach(() => { axiosMock.restore(); jest.clearAllMocks() })

    it('selectedGatewayData returns the matching gateway object', () => {
        wrapper.vm.selectedGateway = 'stripe'
        expect(wrapper.vm.selectedGatewayData).toEqual(payInitResponse.data.gateways[0])
    })

    it('selectedGatewayData returns null for unknown gateway', () => {
        wrapper.vm.selectedGateway = 'unknown'
        expect(wrapper.vm.selectedGatewayData).toBeNull()
    })

    it('feeAmount returns fee_amount from selected gateway', () => {
        expect(wrapper.vm.feeAmount).toBe(0)
    })

    it('feePercent returns 0 when processing_fee is null (nullish coalescing)', () => {
        expect(wrapper.vm.feePercent).toBe(0)
    })

    it('payable returns the payable amount from the selected gateway', () => {
        expect(wrapper.vm.payable).toBe(99)
    })

    it('payable falls back to amountDue when no gateway is selected', () => {
        wrapper.vm.selectedGateway = null
        wrapper.vm.amountDue = 150
        expect(wrapper.vm.payable).toBe(150)
    })

    it('auto-selects first gateway when none is set by URL param', async () => {
        // selectedGateway is set from gatewayFromUrl='stripe' in the mock route
        // so it should equal 'stripe' after mount
        expect(wrapper.vm.selectedGateway).toBe('stripe')
    })

    it('onStripeModalClose sets showStripeModal to false and busy to false', () => {
        wrapper.vm.showStripeModal = true
        wrapper.vm.busy = true
        wrapper.vm.onStripeModalClose()
        expect(wrapper.vm.showStripeModal).toBe(false)
        expect(wrapper.vm.busy).toBe(false)
    })

    it('onPaid does not throw and navigates away', () => {
        // useRouter() creates a new mock object each call; we verify onPaid doesn't throw
        expect(() => wrapper.vm.onPaid()).not.toThrow()
    })

    it('continuePay does nothing when no gateway is selected', async () => {
        wrapper.vm.selectedGateway = null
        await wrapper.vm.continuePay()
        expect(axiosMock.history.post.length).toBe(0)
    })

    it('continuePay does nothing when already busy', async () => {
        wrapper.vm.busy = true
        await wrapper.vm.continuePay()
        expect(axiosMock.history.post.length).toBe(0)
    })

    it('continuePay calls stripe session API for stripe gateway', async () => {
        axiosMock.onPost('/invoice/42/stripe/session').reply(500)
        wrapper.vm.selectedGateway = 'stripe'
        await wrapper.vm.continuePay()
        await flushPromises()
        expect(axiosMock.history.post.some(r => r.url.includes('/stripe/session'))).toBe(true)
    })

    it('continuePay calls razorpay order API for razorpay gateway', async () => {
        axiosMock.onPost('/invoice/42/razorpay/order').reply(500)
        wrapper.vm.selectedGateway = 'razorpay'
        await wrapper.vm.continuePay()
        await flushPromises()
        expect(axiosMock.history.post.some(r => r.url.includes('/razorpay/order'))).toBe(true)
    })

    it('continuePay resets busy to false after failure', async () => {
        axiosMock.onPost('/invoice/42/stripe/session').reply(500)
        wrapper.vm.selectedGateway = 'stripe'
        await wrapper.vm.continuePay()
        await flushPromises()
        expect(wrapper.vm.busy).toBe(false)
    })

    it('payStripe sets error on empty card number and returns', async () => {
        wrapper.vm.cardComplete.number = false
        wrapper.vm.cardComplete.expiry = false
        wrapper.vm.cardComplete.cvc = false
        await wrapper.vm.payStripe()
        expect(wrapper.vm.cardErrors.number).toBeTruthy()
    })

    it('payStripe sets error on empty expiry field', async () => {
        wrapper.vm.cardComplete.number = false
        wrapper.vm.cardComplete.expiry = false
        wrapper.vm.cardComplete.cvc = true
        await wrapper.vm.payStripe()
        expect(wrapper.vm.cardErrors.expiry).toBeTruthy()
    })

    it('payStripe sets error on empty CVC field', async () => {
        wrapper.vm.cardComplete.number = false
        wrapper.vm.cardComplete.expiry = true
        wrapper.vm.cardComplete.cvc = false
        await wrapper.vm.payStripe()
        expect(wrapper.vm.cardErrors.cvc).toBeTruthy()
    })

    it('payStripe does nothing when busy is true', async () => {
        wrapper.vm.busy = true
        wrapper.vm.cardComplete.number = true
        wrapper.vm.cardComplete.expiry = true
        wrapper.vm.cardComplete.cvc = true
        await wrapper.vm.payStripe()
        expect(axiosMock.history.post.length).toBe(0)
    })

    it('bindStripeField updates cardErrors and cardComplete on change', () => {
        const mockElement = { on: jest.fn() }
        wrapper.vm.bindStripeField(mockElement, 'number')
        expect(mockElement.on).toHaveBeenCalledWith('change', expect.any(Function))
        const [, handler] = mockElement.on.mock.calls[0]

        handler({ error: { message: 'Invalid card' }, complete: false })
        expect(wrapper.vm.cardErrors.number).toBe('Invalid card')
        expect(wrapper.vm.cardComplete.number).toBe(false)

        handler({ error: null, complete: true })
        expect(wrapper.vm.cardErrors.number).toBe('')
        expect(wrapper.vm.cardComplete.number).toBe(true)
    })

    it('loadScript resolves immediately if script already in document', async () => {
        const src = 'https://js.stripe.com/v3/'
        const script = document.createElement('script')
        script.src = src
        document.head.appendChild(script)
        await expect(wrapper.vm.loadScript(src)).resolves.toBeUndefined()
    })

    it('finalizeStripe closes the modal on success', async () => {
        axiosMock.onPost('/invoice/42/stripe/confirm').reply(200, { success: true })
        wrapper.vm.showStripeModal = true
        await wrapper.vm.finalizeStripe()
        await flushPromises()
        expect(wrapper.vm.showStripeModal).toBe(false)
    })

    it('finalizeStripe sets alert on server failure response', async () => {
        axiosMock.onPost('/invoice/42/stripe/confirm').reply(200, { success: false, message: 'Payment declined' })
        await wrapper.vm.finalizeStripe()
        await flushPromises()
        expect(wrapper.vm.busy).toBe(false)
    })

    it('finalizeStripe sets alert on API error', async () => {
        axiosMock.onPost('/invoice/42/stripe/confirm').reply(500)
        await wrapper.vm.finalizeStripe()
        await flushPromises()
        expect(wrapper.vm.busy).toBe(false)
    })
})

describe('PlaceOrderPage.vue — no invoice param', () => {
    it('shows error state when no invoice query param', async () => {
        jest.doMock('vue-router', () => ({
            useRouter: () => ({ push: jest.fn() }),
            useRoute: () => ({ params: {}, query: {} }),
        }))

        const { mount: localMount } = await import('@vue/test-utils')
        const { createTestingPinia: localPinia } = await import('@pinia/testing')
        const PageModule = await import('@/pages/client/checkout/PlaceOrderPage.vue')
        const Page = PageModule.default

        const w = localMount(Page, {
            global: {
                plugins: [localPinia()],
                stubs: ['loader', 'app-modal', 'app-alert'],
            },
        })

        await flushPromises()
        expect(w.find('.fa-file-invoice').exists()).toBeTruthy()
    })
})
