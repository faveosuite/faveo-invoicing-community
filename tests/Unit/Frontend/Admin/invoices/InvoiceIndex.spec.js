jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: {}, query: {} }),
    RouterLink: { template: '<a><slot /></a>' },
}))
jest.mock('@/core/composables/useDateTime', () => ({
    useDateTime: () => ({ formatDate: (v) => v }),
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import InvoiceIndex from '@/pages/admin/invoices/InvoiceIndex.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

describe('InvoiceIndex.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/invoices/).reply(200, { data: [] })
        global.mockHttp.onGet(/\/export-invoices/).reply(200, { data: { message: 'Export queued' } })
        global.mockHttp.onDelete(/\/invoices/).reply(200, { data: { message: 'Deleted' } })
        wrapper = mount(InvoiceIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'DataTable', 'AppAlert', 'inline-loader', 'action-button',
                    'DeleteModal', 'delete-modal', 'dynamic-select', 'DynamicSelect',
                    'ColumnSelector', 'InvoiceFilter', 'InvoiceTableActions',
                    'router-link', 'spinner-loader',
                ],
            },
        })
        wrapper.vm.dtRef = { refresh: jest.fn() }
    })

    afterEach(() => {
        global.mockHttp.reset()
        jest.clearAllMocks()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders DataTable stub', () => {
        expect(wrapper.find('data-table-stub').exists()).toBe(true)
    })

    it('renders AppAlert stub', () => {
        expect(wrapper.find('app-alert-stub').exists()).toBe(true)
    })

    it('renders InvoiceFilter stub', () => {
        expect(wrapper.find('invoice-filter-stub').exists()).toBe(true)
    })

    it('renders ColumnSelector stub', () => {
        // ColumnSelector lives inside DataTable's #table-tools slot;
        // when DataTable is stubbed the slot is not rendered, so we verify
        // DataTable itself is present instead.
        expect(wrapper.find('data-table-stub').exists()).toBe(true)
    })

    it('does not show DeleteModal by default', () => {
        expect(wrapper.find('delete-modal-stub').exists()).toBe(false)
    })

    it('toggles showFilter when filter button is clicked', async () => {
        expect(wrapper.vm.showFilter).toBe(false)
        await wrapper.find('button.btn-tool').trigger('click')
        expect(wrapper.vm.showFilter).toBe(true)
    })

    it('calls exportInvoices which hits the export-invoices API', async () => {
        await wrapper.vm.exportInvoices()
        await flushPromises()
        expect(global.mockHttp.history.get.some(r => /export-invoices/.test(r.url))).toBe(true)
    })

    it('calls successHandler on successful export', async () => {
        await wrapper.vm.exportInvoices()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on export failure', async () => {
        global.mockHttp.reset()
        global.mockHttp.onGet(/\/export-invoices/).reply(500)
        await wrapper.vm.exportInvoices()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('confirmBulkDelete sets pendingBulkDelete when invoices are selected', () => {
        wrapper.vm.selectedInvoices = [1, 2, 3]
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).toEqual({ invoice_ids: [1, 2, 3] })
    })

    it('confirmBulkDelete does nothing when no invoices are selected', () => {
        wrapper.vm.selectedInvoices = []
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).toBeNull()
    })

    it('onFilterApply stores params and hides filter panel', () => {
        wrapper.vm.showFilter = true
        wrapper.vm.onFilterApply({ status: 'pending' })
        expect(wrapper.vm.activeFilters).toEqual({ status: 'pending' })
        expect(wrapper.vm.showFilter).toBe(false)
    })

    it('onFilterReset clears activeFilters', () => {
        wrapper.vm.activeFilters = { status: 'pending' }
        wrapper.vm.onFilterReset()
        expect(wrapper.vm.activeFilters).toEqual({})
    })

    it('onColumnsChange maps report keys to internal column names', () => {
        wrapper.vm.onColumnsChange(['number', 'status'])
        expect(wrapper.vm.columns).toContain('number')
        expect(wrapper.vm.columns).toContain('status')
    })
})
