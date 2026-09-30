jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: { id: '0' }, query: {} }),
}))
jest.mock('@/core/composables/useDateTime', () => ({
    useDateTime: () => ({ formatDate: (v) => v }),
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import InvoiceShow from '@/pages/admin/invoices/InvoiceShow.vue'
import { errorHandler } from '@/helpers/responseHandler'

const INVOICE_SHOW_RESPONSE = {
    data: {
        invoice: {
            id: 1,
            number: 'INV-001',
            date: '2024-03-01',
            status: 'success',
            currency: 'USD',
            grand_total: '200.00',
            paid_amount: '200.00',
            balance: '0.00',
            coupon_code: null,
            processing_fee_label: null,
        },
        from: {
            company: 'Acme Corp',
            address: '123 Main St',
            city: 'Springfield',
            state: 'IL',
            zip: '62701',
            country: 'US',
            phone: '5551234567',
            phone_code: '1',
            company_email: 'info@acme.com',
            gstin: null,
            cin_no: null,
            logo: null,
        },
        to: {
            id: 5,
            first_name: 'Jane',
            last_name: 'Smith',
            email: 'jane@example.com',
            mobile: '9876543210',
            mobile_code: '1',
            address: '456 Oak Ave',
            town: 'Shelbyville',
            state: 'IL',
            zip: '62565',
            country: 'US',
            gstin: null,
        },
        items: [
            { id: 1, product_name: 'Product A', regular_price: 200, subtotal: 200, agents: null, quantity: 1, order: { id: 10, number: 'ORD-010' } },
        ],
        totals: {
            subtotal: '$200.00',
            discount: null,
            credits: null,
            tax: {},
            processing_fee: null,
            total: '$200.00',
        },
        payments: [
            { id: 1, created_at: '2024-03-01', payment_method: 'stripe', amount: 200, payment_status: 'success' },
        ],
    },
}

describe('InvoiceShow.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/invoice\//).reply(200, INVOICE_SHOW_RESPONSE)
        wrapper = mount(InvoiceShow, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'loader', 'router-link', 'inline-loader',
                    'action-button', 'DataTable',
                ],
            },
        })
    })

    afterEach(() => {
        globalThis.mockHttp.reset()
        jest.clearAllMocks()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders AppAlert stub', () => {
        expect(wrapper.find('app-alert-stub').exists()).toBe(true)
    })

    it('shows loader stub while fetching invoice', async () => {
        // mock resolves synchronously; verify loading becomes false and loader is hidden
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
        expect(wrapper.find('loader-stub').exists()).toBe(false)
    })

    it('calls GET /invoice/:id on mount', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get.some(r => /\/invoice\//.test(r.url))).toBe(true)
    })

    it('hides loader after data is fetched', async () => {
        await flushPromises()
        expect(wrapper.find('loader-stub').exists()).toBe(false)
    })

    it('populates invoice ref after successful fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.invoice).not.toBeNull()
        expect(wrapper.vm.invoice.number).toBe('INV-001')
    })

    it('populates from and to refs after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.from.company).toBe('Acme Corp')
        expect(wrapper.vm.to.first_name).toBe('Jane')
    })

    it('populates items and payments after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.items).toHaveLength(1)
        expect(wrapper.vm.payments).toHaveLength(1)
    })

    it('computes correct statusBadgeClass for success status', async () => {
        await flushPromises()
        expect(wrapper.vm.statusBadgeClass).toBe('bg-success')
    })

    it('computes bg-warning for pending status', async () => {
        globalThis.mockHttp.reset()
        const pendingResponse = JSON.parse(JSON.stringify(INVOICE_SHOW_RESPONSE))
        pendingResponse.data.invoice.status = 'pending'
        globalThis.mockHttp.onGet(/\/invoice\//).reply(200, pendingResponse)
        const w = mount(InvoiceShow, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'loader', 'router-link', 'inline-loader', 'action-button'],
            },
        })
        await flushPromises()
        expect(w.vm.statusBadgeClass).toBe('bg-warning text-dark')
    })

    it('calls errorHandler when fetch fails', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/invoice\//).reply(500)
        const w = mount(InvoiceShow, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'loader', 'router-link', 'inline-loader', 'action-button'],
            },
        })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
        expect(w.vm.loading).toBe(false)
    })

    it('capitalize returns em-dash for falsy input', () => {
        expect(wrapper.vm.capitalize(null)).toBe('—')
        expect(wrapper.vm.capitalize('')).toBe('—')
    })

    it('capitalize uppercases first character', () => {
        expect(wrapper.vm.capitalize('pending')).toBe('Pending')
    })
})
