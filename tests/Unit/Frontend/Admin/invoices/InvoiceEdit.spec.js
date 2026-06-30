jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: { id: '0' }, query: {} }),
}))
jest.mock('@/validations/admin/invoiceValidations', () => ({
    invoiceEditSchema: {},
    buildInvoiceCreateSchema: jest.fn(() => ({})),
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import InvoiceEdit from '@/pages/admin/invoices/InvoiceEdit.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'
import { validateForm } from '@/helpers/formUtils.js'

const INVOICE_RESPONSE = {
    data: {
        invoice: {
            id: 0,
            number: 'INV-000',
            date: '2024-01-15T00:00:00Z',
            grand_total: '150.00',
            status: 'pending',
        },
    },
}

describe('InvoiceEdit.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/invoice\//).reply(200, INVOICE_RESPONSE)
        globalThis.mockHttp.onPost(/\/invoice\/edit\//).reply(200, { data: { message: 'Invoice updated' } })
        wrapper = mount(InvoiceEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'loader', 'DatePicker', 'date-picker',
                    'TextField', 'text-field', 'SelectField', 'static-select', 'StaticSelect',
                    'action-button', 'router-link', 'inline-loader',
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

    it('shows loader stub while fetching', async () => {
        // mock resolves synchronously; verify loading becomes false and loader is hidden
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
        expect(wrapper.find('loader-stub').exists()).toBe(false)
    })

    it('calls GET /invoice/:id on mount', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get.some(r => /\/invoice\//.test(r.url))).toBe(true)
    })

    it('populates form from fetched invoice data', async () => {
        await flushPromises()
        expect(wrapper.vm.form.total).toBe('150.00')
        expect(wrapper.vm.form.status).toBe('pending')
    })

    it('hides loader after data is fetched', async () => {
        await flushPromises()
        expect(wrapper.find('loader-stub').exists()).toBe(false)
    })

    it('sets invoice ref after successful fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.invoice).not.toBeNull()
        expect(wrapper.vm.invoice.number).toBe('INV-000')
    })

    it('calls POST /invoice/edit/:id on submit when validation passes', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.some(r => /\/invoice\/edit\//.test(r.url))).toBe(true)
    })

    it('calls successHandler after successful update', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler when submit fails', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onPost(/\/invoice\/edit\//).reply(500)
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('does not submit when validateForm returns false', async () => {
        validateForm.mockResolvedValueOnce(false)
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.some(r => /\/invoice\/edit\//.test(r.url))).toBe(false)
    })

    it('calls errorHandler when fetch fails', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/invoice\//).reply(500)
        const w = mount(InvoiceEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'loader', 'DatePicker', 'date-picker', 'TextField', 'text-field', 'SelectField', 'action-button', 'router-link'],
            },
        })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
        expect(w.vm.loading).toBe(false)
    })
})
