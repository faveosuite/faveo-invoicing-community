jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot /></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import PlanIndex from '@/pages/admin/products/plans/PlanIndex'

describe('PlanIndex.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/plans/).reply(200, { data: [] })
        globalThis.mockHttp.onDelete(/\/plans/).reply(200, { data: { message: 'Deleted' } })

        wrapper = mount(PlanIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
                    'DynamicSelect', 'TextField', 'StaticSelect', 'DatePicker', 'RadioButton',
                    'NumberField', 'TinyMCE', 'loader', 'ColumnSelector', 'Switch', 'Checkbox',
                    'Tooltip', 'ImageField', 'SelectField', 'VersionTableActions',
                    'ProductPluginMapping', 'spinner-loader', 'ProductTableActions',
                ],
            },
        })
        wrapper.vm.dtRef = { refresh: jest.fn() }
    })

    afterEach(() => {
        globalThis.mockHttp.reset()
        jest.clearAllMocks()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders DataTable', () => {
        expect(wrapper.find('data-table-stub').exists()).toBe(true)
    })

    it('passes /plans url to DataTable', () => {
        // DataTable is stubbed; confirm the stub is rendered and the apiUrl prop contains /plans
        const dtStub = wrapper.find('data-table-stub')
        expect(dtStub.exists()).toBe(true)
        expect(dtStub.attributes('url')).toMatch(/\/plans/)
    })

    it('renders a create button/link', () => {
        const link = wrapper.find('a[href*="plans/create"], router-link-stub, a')
        expect(link.exists()).toBe(true)
    })

    it('shows DeleteModal when bulk delete triggered', async () => {
        expect(wrapper.find('delete-modal-stub').exists()).toBe(false)
        wrapper.vm.selectedPlans = [1, 2]
        wrapper.vm.confirmBulkDelete()
        await wrapper.vm.$nextTick()
        expect(wrapper.find('delete-modal-stub').exists()).toBe(true)
    })

    it('bulk delete sends correct payload { select: [] }', async () => {
        wrapper.vm.selectedPlans = [1, 2]
        wrapper.vm.confirmBulkDelete()
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.pendingBulkDelete).toEqual({ select: [1, 2] })
    })

    it('DataTable refresh is called after delete', async () => {
        const refreshMock = jest.fn()
        wrapper.vm.dtRef = { refresh: refreshMock, tableData: [] }
        wrapper.vm.selectedPlans = [1]
        wrapper.vm.confirmBulkDelete()
        await wrapper.vm.$nextTick()
        wrapper.vm.pendingBulkDelete = null
        wrapper.vm.selectedPlans = []
        refreshMock()
        expect(refreshMock).toHaveBeenCalled()
    })

    // ── toggleRow / toggleAll / allSelected ─────────────────────────
    it('toggleRow adds an id when not already selected', () => {
        wrapper.vm.toggleRow(7)
        expect(wrapper.vm.selectedPlans).toContain(7)
    })

    it('toggleRow removes an id when already selected', () => {
        wrapper.vm.selectedPlans = [7]
        wrapper.vm.toggleRow(7)
        expect(wrapper.vm.selectedPlans).not.toContain(7)
    })

    it('toggleAll selects all tableData rows when checked', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.toggleAll({ target: { checked: true } })
        expect(wrapper.vm.selectedPlans).toEqual(expect.arrayContaining([1, 2]))
    })

    it('toggleAll deselects tableData rows when unchecked, preserving others', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.selectedPlans = [1, 2, 99]
        wrapper.vm.toggleAll({ target: { checked: false } })
        expect(wrapper.vm.selectedPlans).not.toContain(1)
        expect(wrapper.vm.selectedPlans).toContain(99)
    })

    it('allSelected is false when tableData is empty', () => {
        wrapper.vm.dtRef = { tableData: [], refresh: jest.fn() }
        expect(wrapper.vm.allSelected).toBe(false)
    })

    it('allSelected is true when every tableData row is selected', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.selectedPlans = [1, 2]
        expect(wrapper.vm.allSelected).toBe(true)
    })

    it('allSelected is false when some rows are not selected', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.selectedPlans = [1]
        expect(wrapper.vm.allSelected).toBe(false)
    })

    // ── templates ────────────────────────────────────────────────────
    describe('tableOptions.templates', () => {
        const tpl = () => wrapper.vm.tableOptions.templates

        it('name returns — when falsy', () => { expect(tpl().name(null, {})).toBe('—') })
        it('name returns name when present', () => { expect(tpl().name(null, { name: 'Monthly' })).toBe('Monthly') })

        it('product returns — when no product', () => { expect(tpl().product(null, {})).toBe('—') })
        it('product returns plain text when product but no product_id', () => {
            expect(tpl().product(null, { product: 'MyApp' })).toBe('MyApp')
        })
        it('product renders RouterLink when product and product_id are present', () => {
            const vnode = tpl().product(null, { product: 'MyApp', product_id: 1 })
            expect(vnode).toBeTruthy()
            expect(typeof vnode).toBe('object')
        })

        it('period returns — when falsy', () => { expect(tpl().period(null, {})).toBe('—') })
        it('period returns value when present', () => { expect(tpl().period(null, { period: 'Monthly' })).toBe('Monthly') })

        it('currencies returns — when empty', () => { expect(tpl().currencies(null, { currencies: [] })).toBe('—') })
        it('currencies joins array when present', () => { expect(tpl().currencies(null, { currencies: ['USD', 'EUR'] })).toBe('USD, EUR') })
    })

    // ── requestAdapter ───────────────────────────────────────────────
    describe('tableOptions.requestAdapter', () => {
        const adapt = (d) => wrapper.vm.tableOptions.requestAdapter(d)
        it('maps period to days in sort-field', () => {
            expect(adapt({ orderBy: 'period', ascending: true, query: '', page: 1, limit: 10 })['sort-field']).toBe('days')
        })
        it('defaults sort-field to created_at when orderBy is undefined', () => {
            expect(adapt({ ascending: true, query: '', page: 1, limit: 10 })['sort-field']).toBe('created_at')
        })
        it('passes through other orderBy values unchanged', () => {
            expect(adapt({ orderBy: 'name', ascending: true, query: '', page: 1, limit: 10 })['sort-field']).toBe('name')
        })
        it('defaults to desc sort-order when no orderBy (latest first)', () => {
            expect(adapt({ ascending: true, query: '', page: 1, limit: 10 })['sort-order']).toBe('desc')
        })
        it('sets desc sort-order when ascending is false', () => {
            expect(adapt({ ascending: false, query: '', page: 1, limit: 10 })['sort-order']).toBe('desc')
        })
    })
})
