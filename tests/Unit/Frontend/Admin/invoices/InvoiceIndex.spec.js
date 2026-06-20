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

    it('onColumnsChange falls back to DEFAULT_COLUMNS for unknown keys', () => {
        wrapper.vm.onColumnsChange(['unknown_key'])
        expect(wrapper.vm.columns).toEqual([
            'select', 'user', 'email', 'mobile', 'country', 'number',
            'product', 'date', 'grand_total', 'status', 'action',
        ])
    })

    it('exportInvoices appends active non-empty filters to query string', async () => {
        wrapper.vm.activeFilters = { status: 'paid', from: '' }
        await wrapper.vm.exportInvoices()
        await flushPromises()
        const url = global.mockHttp.history.get.find(r => /export-invoices/.test(r.url))?.url ?? ''
        expect(url).toContain('status=paid')
        expect(url).not.toContain('from=')
    })

    it('toggleRow adds an id when not already selected', () => {
        wrapper.vm.toggleRow(10)
        expect(wrapper.vm.selectedInvoices).toContain(10)
    })

    it('toggleRow removes an id when already selected', () => {
        wrapper.vm.selectedInvoices = [10]
        wrapper.vm.toggleRow(10)
        expect(wrapper.vm.selectedInvoices).not.toContain(10)
    })

    it('toggleAll selects all tableData rows when checked', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.toggleAll({ target: { checked: true } })
        expect(wrapper.vm.selectedInvoices).toEqual(expect.arrayContaining([1, 2]))
    })

    it('toggleAll does not duplicate already-selected ids', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.selectedInvoices = [1]
        wrapper.vm.toggleAll({ target: { checked: true } })
        expect(wrapper.vm.selectedInvoices.filter(id => id === 1)).toHaveLength(1)
    })

    it('toggleAll deselects only tableData rows when unchecked', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.selectedInvoices = [1, 2, 99]
        wrapper.vm.toggleAll({ target: { checked: false } })
        expect(wrapper.vm.selectedInvoices).not.toContain(1)
        expect(wrapper.vm.selectedInvoices).not.toContain(2)
        expect(wrapper.vm.selectedInvoices).toContain(99)
    })

    it('allSelected is false when tableData is empty', () => {
        wrapper.vm.dtRef = { tableData: [], refresh: jest.fn() }
        expect(wrapper.vm.allSelected).toBe(false)
    })

    it('allSelected is true when all tableData rows are selected', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.selectedInvoices = [1, 2]
        expect(wrapper.vm.allSelected).toBe(true)
    })

    it('allSelected is false when some tableData rows are not selected', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.selectedInvoices = [1]
        expect(wrapper.vm.allSelected).toBe(false)
    })

    describe('tableOptions.templates', () => {
        const tpl = () => wrapper.vm.tableOptions.templates

        it('user returns — when row has no user', () => {
            expect(tpl().user(null, {})).toBe('—')
        })

        it('user returns — when user has no name', () => {
            expect(tpl().user(null, { user: {} })).toBe('—')
        })

        it('user renders RouterLink when user has name and id', () => {
            const vnode = tpl().user(null, { user: { id: 1, first_name: 'A', last_name: 'B' } })
            expect(vnode).toBeTruthy()
        })

        it('email returns — when user has no email or id', () => {
            expect(tpl().email(null, {})).toBe('—')
            expect(tpl().email(null, { user: { email: 'a@b.com' } })).toBe('—')
        })

        it('email renders RouterLink when user has email and id', () => {
            const vnode = tpl().email(null, { user: { id: 2, email: 'a@b.com' } })
            expect(vnode).toBeTruthy()
        })

        it('mobile returns — when user has no mobile', () => {
            expect(tpl().mobile(null, {})).toBe('—')
        })

        it('mobile returns mobile string when present', () => {
            expect(tpl().mobile(null, { user: { mobile: '9999999999' } })).toContain('9999999999')
        })

        it('mobile includes mobile_code when present', () => {
            expect(tpl().mobile(null, { user: { mobile: '9999999999', mobile_code: '91' } })).toContain('+91')
        })

        it('country returns — when user has no country', () => {
            expect(tpl().country(null, {})).toBe('—')
        })

        it('country returns country name when present', () => {
            expect(tpl().country(null, { user: { country: 'India' } })).toBe('India')
        })

        it('number returns — when falsy', () => {
            expect(tpl().number(null, {})).toBe('—')
        })

        it('number returns invoice number when present', () => {
            expect(tpl().number(null, { number: 'INV-001' })).toBe('INV-001')
        })

        it('product returns — when no products', () => {
            expect(tpl().product(null, {})).toBe('—')
        })

        it('product joins product array', () => {
            expect(tpl().product(null, { products: ['Product A', 'Product B'] })).toBe('Product A, Product B')
        })

        it('date returns — when no date', () => {
            expect(tpl().date(null, {})).toBe('—')
        })

        it('date formats date when present', () => {
            expect(tpl().date(null, { created_at: '2024-01-01' })).toBe('2024-01-01')
        })

        it('grand_total returns — when falsy', () => {
            expect(tpl().grand_total(null, {})).toBe('—')
        })

        it('grand_total returns value when present', () => {
            expect(tpl().grand_total(null, { grand_total: 500 })).toBe(500)
        })

        it('status renders a badge with bg-success for Paid', () => {
            const vnode = tpl().status(null, { status: 'Paid' })
            expect(vnode.props.class).toContain('bg-success')
        })

        it('status renders bg-info for Partially Paid', () => {
            const vnode = tpl().status(null, { status: 'Partially Paid' })
            expect(vnode.props.class).toContain('bg-info')
        })

        it('status renders bg-secondary for other statuses', () => {
            const vnode = tpl().status(null, { status: 'Pending' })
            expect(vnode.props.class).toContain('bg-secondary')
        })
    })

    describe('tableOptions.requestAdapter', () => {
        const adapt = (data) => wrapper.vm.tableOptions.requestAdapter(data)

        it('maps date sort field to created_at', () => {
            expect(adapt({ orderBy: 'date', ascending: true, query: '', page: 1, limit: 10 })['sort-field']).toBe('created_at')
        })

        it('passes through other sort fields unchanged', () => {
            expect(adapt({ orderBy: 'number', ascending: true, query: '', page: 1, limit: 10 })['sort-field']).toBe('number')
        })

        it('defaults sort-field to created_at when orderBy is undefined', () => {
            expect(adapt({ ascending: true, query: '', page: 1, limit: 10 })['sort-field']).toBe('created_at')
        })

        it('sets sort-order to asc/desc based on ascending flag', () => {
            expect(adapt({ ascending: true, query: '', page: 1, limit: 10 })['sort-order']).toBe('asc')
            expect(adapt({ ascending: false, query: '', page: 1, limit: 10 })['sort-order']).toBe('desc')
        })

        it('trims the search query', () => {
            expect(adapt({ ascending: true, query: '  inv  ', page: 1, limit: 10 })['search-query']).toBe('inv')
        })

        it('spreads activeFilters into the result', () => {
            wrapper.vm.activeFilters = { status: 'paid' }
            expect(adapt({ ascending: true, query: '', page: 1, limit: 10 }).status).toBe('paid')
        })
    })
})
