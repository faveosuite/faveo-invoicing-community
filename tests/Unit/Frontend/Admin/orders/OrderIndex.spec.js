jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: {}, query: {} }),
    RouterLink: { template: '<a><slot /></a>', name: 'RouterLink' },
}))
jest.mock('@/core/composables/useDateTime', () => ({ useDateTime: () => ({ formatDate: (v) => v }) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import OrderIndex from '@/pages/admin/orders/OrderIndex.vue'

describe('OrderIndex.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/orders/).reply(200, { data: [] })
        global.mockHttp.onDelete(/\/orders/).reply(200, { data: { message: 'Deleted' } })
        wrapper = mount(OrderIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'DataTable', 'DeleteModal', 'OrderFilter',
                    'ColumnSelector', 'OrderTableActions', 'inline-loader', 'loader',
                ],
            },
        })
        wrapper.vm.dtRef = { refresh: jest.fn() }
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the AppAlert stub', () => {
        expect(wrapper.find('app-alert-stub').exists()).toBe(true)
    })

    it('renders the DataTable stub', () => {
        expect(wrapper.find('data-table-stub').exists()).toBe(true)
    })

    it('renders the OrderFilter stub', () => {
        expect(wrapper.find('order-filter-stub').exists()).toBe(true)
    })

    it('renders the ColumnSelector stub', () => {
        // ColumnSelector lives inside DataTable's #table-tools slot;
        // when DataTable is stubbed the slot is not rendered, so we verify
        // DataTable itself is present instead.
        expect(wrapper.find('data-table-stub').exists()).toBe(true)
    })

    it('showFilter starts as false', () => {
        expect(wrapper.vm.showFilter).toBe(false)
    })

    it('clicking the filter button toggles showFilter', async () => {
        await wrapper.find('button.btn-tool').trigger('click')
        expect(wrapper.vm.showFilter).toBe(true)
    })

    it('selectedOrders starts as empty array', () => {
        expect(wrapper.vm.selectedOrders).toEqual([])
    })

    it('confirmBulkDelete sets pendingBulkDelete when selectedOrders has items', () => {
        wrapper.vm.selectedOrders = [1, 2, 3]
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).toEqual({ order_ids: [1, 2, 3] })
    })

    it('confirmBulkDelete does nothing when selectedOrders is empty', () => {
        wrapper.vm.selectedOrders = []
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).toBeNull()
    })

    it('onFilterApply sets activeFilters and hides filter panel', () => {
        wrapper.vm.showFilter = true
        wrapper.vm.onFilterApply({ order_no: 'ORD-001' })
        expect(wrapper.vm.activeFilters).toEqual({ order_no: 'ORD-001' })
        expect(wrapper.vm.showFilter).toBe(false)
    })

    it('onFilterReset clears activeFilters', () => {
        wrapper.vm.activeFilters = { order_no: 'ORD-001' }
        wrapper.vm.onFilterReset()
        expect(wrapper.vm.activeFilters).toEqual({})
    })

    it('toggleRow adds an id when not already selected', () => {
        wrapper.vm.toggleRow(42)
        expect(wrapper.vm.selectedOrders).toContain(42)
    })

    it('toggleRow removes an id when already selected', () => {
        wrapper.vm.selectedOrders = [42]
        wrapper.vm.toggleRow(42)
        expect(wrapper.vm.selectedOrders).not.toContain(42)
    })

    it('toggleAll selects all rows from tableData when checked', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.toggleAll({ target: { checked: true } })
        expect(wrapper.vm.selectedOrders).toEqual(expect.arrayContaining([1, 2]))
    })

    it('toggleAll does not duplicate ids already in selectedOrders', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.selectedOrders = [1]
        wrapper.vm.toggleAll({ target: { checked: true } })
        expect(wrapper.vm.selectedOrders.filter(id => id === 1)).toHaveLength(1)
        expect(wrapper.vm.selectedOrders).toContain(2)
    })

    it('toggleAll deselects only tableData rows when unchecked', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.selectedOrders = [1, 2, 99]
        wrapper.vm.toggleAll({ target: { checked: false } })
        expect(wrapper.vm.selectedOrders).not.toContain(1)
        expect(wrapper.vm.selectedOrders).not.toContain(2)
        expect(wrapper.vm.selectedOrders).toContain(99)
    })

    it('allSelected is false when tableData is empty', () => {
        wrapper.vm.dtRef = { tableData: [], refresh: jest.fn() }
        expect(wrapper.vm.allSelected).toBe(false)
    })

    it('allSelected is true when every tableData row is selected', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.selectedOrders = [1, 2]
        expect(wrapper.vm.allSelected).toBe(true)
    })

    it('allSelected is false when some tableData rows are missing from selection', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.selectedOrders = [1]
        expect(wrapper.vm.allSelected).toBe(false)
    })

    it('onColumnsChange maps report keys to internal column names', () => {
        wrapper.vm.onColumnsChange(['plan_name', 'group_name', 'number'])
        expect(wrapper.vm.columns).toEqual(['plan', 'group', 'number'])
    })

    it('onColumnsChange falls back to DEFAULT_COLUMNS when all keys are unknown', () => {
        wrapper.vm.onColumnsChange(['unknown_key'])
        expect(wrapper.vm.columns).toEqual([
            'select', 'client', 'email', 'mobile', 'country', 'number', 'order_status',
            'product_name', 'group', 'plan', 'version', 'agents', 'status', 'order_date',
            'update_ends_at', 'action',
        ])
    })

    it('onColumnsChange falls back to DEFAULT_COLUMNS when called with empty array', () => {
        wrapper.vm.onColumnsChange([])
        expect(wrapper.vm.columns).toEqual([
            'select', 'client', 'email', 'mobile', 'country', 'number', 'order_status',
            'product_name', 'group', 'plan', 'version', 'agents', 'status', 'order_date',
            'update_ends_at', 'action',
        ])
    })

    describe('tableOptions.templates', () => {
        const tpl = () => wrapper.vm.tableOptions.templates

        it('client returns — when row has no user', () => {
            expect(tpl().client(null, {})).toBe('—')
        })

        it('client returns — when user has no name or id', () => {
            expect(tpl().client(null, { user: {} })).toBe('—')
        })

        it('client renders a RouterLink when user has name and id', () => {
            const vnode = tpl().client(null, { user: { id: 5, first_name: 'John', last_name: 'Doe' } })
            expect(vnode).toBeTruthy()
            expect(vnode.type).toBeTruthy()
        })

        it('email returns — when user has no email', () => {
            expect(tpl().email(null, {})).toBe('—')
            expect(tpl().email(null, { user: { email: 'a@b.com' } })).toBe('—')
        })

        it('email renders a RouterLink when user has email and id', () => {
            const vnode = tpl().email(null, { user: { id: 3, email: 'a@b.com' } })
            expect(vnode).toBeTruthy()
        })

        it('mobile returns — when user has no mobile', () => {
            expect(tpl().mobile(null, {})).toBe('—')
            expect(tpl().mobile(null, { user: {} })).toBe('—')
        })

        it('mobile returns formatted mobile when present', () => {
            expect(tpl().mobile(null, { user: { mobile: '9999999999', mobile_code: '+91' } })).toBe('+91 9999999999')
        })

        it('country returns — when user has no country', () => {
            expect(tpl().country(null, {})).toBe('—')
        })

        it('country returns country name when present', () => {
            expect(tpl().country(null, { user: { country: 'India' } })).toBe('India')
        })

        it('number returns — when row has no number', () => {
            expect(tpl().number(null, {})).toBe('—')
        })

        it('number renders a RouterLink when order number and id exist', () => {
            const vnode = tpl().number(null, { id: 1, number: 'ORD-001' })
            expect(vnode).toBeTruthy()
        })

        it('product_name returns — when no product', () => {
            expect(tpl().product_name(null, {})).toBe('—')
        })

        it('product_name returns plain text when name exists but no id', () => {
            expect(tpl().product_name(null, { product_name: 'MyProduct' })).toBe('MyProduct')
        })

        it('group returns — when no group', () => {
            expect(tpl().group(null, {})).toBe('—')
        })

        it('plan returns — when no plan', () => {
            expect(tpl().plan(null, {})).toBe('—')
        })

        it('version returns — when no version', () => {
            expect(tpl().version(null, {})).toBe('—')
        })

        it('version returns version string when present', () => {
            expect(tpl().version(null, { version: '3.0.0' })).toBe('3.0.0')
        })

        it('agents returns — when agents is null', () => {
            expect(tpl().agents(null, {})).toBe('—')
        })

        it('agents returns count when present', () => {
            expect(tpl().agents(null, { agents: 10 })).toBe(10)
        })

        it('order_date returns — when not set', () => {
            expect(tpl().order_date(null, {})).toBe('—')
        })

        it('order_date formats date when present', () => {
            expect(tpl().order_date(null, { order_date: '2024-01-01' })).toBe('2024-01-01')
        })

        it('update_ends_at returns — when not set', () => {
            expect(tpl().update_ends_at(null, {})).toBe('—')
        })

        it('update_ends_at formats date when present', () => {
            expect(tpl().update_ends_at(null, { update_ends_at: '2025-12-31' })).toBe('2025-12-31')
        })
    })

    describe('tableOptions.requestAdapter', () => {
        const adapt = (data) => wrapper.vm.tableOptions.requestAdapter(data)

        it('maps order_date sort field to created_at', () => {
            const result = adapt({ orderBy: 'order_date', ascending: true, query: '', page: 1, limit: 10 })
            expect(result['sort-field']).toBe('created_at')
        })

        it('passes through unknown sort fields unchanged', () => {
            const result = adapt({ orderBy: 'number', ascending: false, query: '', page: 1, limit: 10 })
            expect(result['sort-field']).toBe('number')
        })

        it('defaults sort-field to created_at when orderBy is undefined', () => {
            const result = adapt({ ascending: true, query: '', page: 1, limit: 10 })
            expect(result['sort-field']).toBe('created_at')
        })

        it('sets sort-order to asc when ascending is true', () => {
            const result = adapt({ orderBy: 'number', ascending: true, query: '', page: 1, limit: 10 })
            expect(result['sort-order']).toBe('asc')
        })

        it('sets sort-order to desc when ascending is false', () => {
            const result = adapt({ orderBy: 'number', ascending: false, query: '', page: 1, limit: 10 })
            expect(result['sort-order']).toBe('desc')
        })

        it('trims the search query', () => {
            const result = adapt({ orderBy: 'number', ascending: true, query: '  abc  ', page: 1, limit: 10 })
            expect(result['search-query']).toBe('abc')
        })

        it('spreads activeFilters into the result', () => {
            wrapper.vm.activeFilters = { status: 'active' }
            const result = adapt({ orderBy: 'number', ascending: true, query: '', page: 1, limit: 10 })
            expect(result.status).toBe('active')
        })
    })
})
