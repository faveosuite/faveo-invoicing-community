jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: { invoice: '42' } }) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import PaymentSuccessPage from '@/pages/client/checkout/PaymentSuccessPage.vue'

const invoiceSuccessResponse = {
    data: {
        invoice: {
            id: 42,
            number: 'INV-0042',
            date: '2026-06-20',
            grand_total: '118.80',
            currency_symbol: '$',
            status: 'Paid',
        },
        orders: [
            {
                number: 'ORD-001',
                product_name: 'Product A',
                qty: 1,
                price: '99.00',
                downloadable: false,
                download_url: null,
            },
        ],
        payment_method: 'stripe',
    },
}

describe('PaymentSuccessPage.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        axiosMock.onGet('/invoice/42/pay-success').reply(200, invoiceSuccessResponse)

        wrapper = mount(PaymentSuccessPage, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['loader', 'router-link'],
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

    it('fetches GET /invoice/42/pay-success on mount', async () => {
        await flushPromises()
        expect(axiosMock.history.get.some(r => r.url.includes('/invoice/42/pay-success'))).toBe(true)
    })

    it('renders success banner after data loads', async () => {
        await flushPromises()
        expect(wrapper.find('.border-color-success').exists()).toBeTruthy()
    })

    it('displays invoice number after load', async () => {
        await flushPromises()
        expect(wrapper.text()).toContain('INV-0042')
    })

    it('displays payment method after load', async () => {
        await flushPromises()
        expect(wrapper.text()).toContain('stripe')
    })

    it('displays order row after load', async () => {
        await flushPromises()
        expect(wrapper.text()).toContain('Product A')
    })

    it('shows error message on 500 response', async () => {
        axiosMock.onGet('/invoice/42/pay-success').reply(500, { message: 'Server error' })

        wrapper = mount(PaymentSuccessPage, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['loader', 'router-link'],
            },
        })

        await flushPromises()
        expect(wrapper.find('.fa-triangle-exclamation').exists()).toBeTruthy()
    })
})

describe('PaymentSuccessPage.vue — no invoice query param', () => {
    beforeEach(() => {
        jest.resetModules()
    })

    it('shows error state when no invoice param is provided', async () => {
        jest.doMock('vue-router', () => ({
            useRouter: () => ({ push: jest.fn() }),
            useRoute: () => ({ params: {}, query: {} }),
        }))

        const { mount: localMount } = await import('@vue/test-utils')
        const { createTestingPinia: localPinia } = await import('@pinia/testing')
        const PageModule = await import('@/pages/client/checkout/PaymentSuccessPage.vue')
        const Page = PageModule.default

        const w = localMount(Page, {
            global: {
                plugins: [localPinia()],
                stubs: ['loader', 'router-link'],
            },
        })

        await flushPromises()
        expect(w.find('.fa-triangle-exclamation').exists()).toBeTruthy()
    })
})
