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
