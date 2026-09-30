jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: { id: '42' }, query: {} }),
    RouterLink: { template: '<a><slot /></a>' },
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import { errorHandler } from '@/helpers/responseHandler'
import InvoiceShow from '@/pages/client/invoices/InvoiceShow.vue'

const invoiceShowResponse = {
    data: {
        invoice: {
            id: 42,
            number: 'INV-0042',
            date: '2026-06-20',
            status: 'Paid',
            grand_total: '118.80',
            currency: 'USD',
            coupon_code: null,
            processing_fee_label: null,
        },
        from: {
            company: 'Acme Corp',
            address: '123 Main St',
            city: 'Springfield',
            state: 'IL',
            zip: '62701',
            country: 'USA',
            phone: '5551234567',
            phone_code: '1',
            company_email: 'info@acme.com',
            gstin: null,
            cin_no: null,
            logo: null,
        },
        to: {
            first_name: 'Jane',
            last_name: 'Doe',
            address: '456 Oak Ave',
            town: 'Shelbyville',
            state: 'IL',
            zip: '62702',
            country: 'USA',
            mobile: '5559876543',
            mobile_code: '1',
            email: 'jane@example.com',
            gstin: null,
        },
        items: [
            {
                id: 1,
                product_name: 'Product A',
                regular_price: 99,
                subtotal: 99,
                quantity: 1,
                agents: null,
                image: null,
                order: { id: 10, number: 'ORD-001' },
            },
        ],
        totals: {
            subtotal: '$99.00',
            discount: null,
            credits: null,
            tax: {},
            processing_fee: null,
            total: '$118.80',
        },
        payments: [
            {
                id: 1,
                created_at: '2026-06-20T10:00:00Z',
                payment_method: 'stripe',
                amount: 118.80,
                payment_status: 'success',
            },
        ],
    },
}

describe('InvoiceShow.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        axiosMock.onGet('/invoice/42').reply(200, invoiceShowResponse)

        wrapper = mount(InvoiceShow, {
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

    it('fetches GET /invoice/42 on mount', async () => {
        await flushPromises()
        expect(axiosMock.history.get.some(r => r.url.includes('/invoice/42'))).toBe(true)
    })

    it('hides loader after fetch completes', async () => {
        await flushPromises()
        expect(wrapper.find('loader-stub').exists()).toBeFalsy()
    })

    it('displays invoice number after load', async () => {
        await flushPromises()
        expect(wrapper.text()).toContain('INV-0042')
    })

    it('displays the From company name', async () => {
        await flushPromises()
        expect(wrapper.text()).toContain('Acme Corp')
    })

    it('displays the To customer name', async () => {
        await flushPromises()
        expect(wrapper.text()).toContain('Jane')
        expect(wrapper.text()).toContain('Doe')
    })

    it('renders line items table', async () => {
        await flushPromises()
        expect(wrapper.find('table.table-bordered').exists()).toBeTruthy()
    })

    it('displays product name in items table', async () => {
        await flushPromises()
        expect(wrapper.text()).toContain('Product A')
    })

    it('renders totals section', async () => {
        await flushPromises()
        expect(wrapper.text()).toContain('$99.00')
    })

    it('renders payments section when payments exist', async () => {
        await flushPromises()
        // capitalize() transforms 'stripe' → 'Stripe'
        expect(wrapper.text()).toContain('Stripe')
    })

    it('shows paid badge for paid invoice', async () => {
        await flushPromises()
        const badge = wrapper.find('.badge')
        expect(badge.exists()).toBeTruthy()
        expect(badge.classes()).toContain('bg-success')
    })

    it('calls errorHandler when API returns 500', async () => {
        axiosMock.onGet('/invoice/42').reply(500, { message: 'Server error' })

        wrapper = mount(InvoiceShow, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['loader', 'router-link'],
            },
        })

        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('shows empty items message when no items returned', async () => {
        const emptyItemsResponse = { data: { ...invoiceShowResponse.data, items: [] } }
        axiosMock.onGet('/invoice/42').reply(200, emptyItemsResponse)

        wrapper = mount(InvoiceShow, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['loader', 'router-link'],
            },
        })

        await flushPromises()
        expect(wrapper.text()).toContain('message.no_items_found')
    })
})
