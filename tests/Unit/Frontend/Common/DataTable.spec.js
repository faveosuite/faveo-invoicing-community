import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import DataTable from '@/components/Reusable/DataTable.vue'

const mountDataTable = (props = {}, options = {}) =>
    mount(DataTable, {
        props: {
            url: '/api/users',
            dataColumns: ['name', 'email', 'action'],
            ...props,
        },
        global: {
            plugins: [createTestingPinia()],
            stubs: {
                'v-server-table': {
                    template: '<div class="v-server-table"><slot /></div>',
                    props: ['url', 'columns', 'options'],
                    methods: { refresh: () => {}, setFilter: () => {}, setPage: () => {}, setLimit: () => {} },
                },
                Pagination: true,
            },
            ...options.global,
        },
        slots: options.slots,
    })

describe('DataTable.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mountDataTable()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the datatable container', () => {
        expect(wrapper.find('.datatable').exists()).toBe(true)
    })

    // ── perpage selector ─────────────────────────────────────────────
    it('renders perpage select by default at the start of the table', () => {
        const header = wrapper.find('.datatable-header')
        expect(header.exists()).toBe(true)
        const perPageSelect = header.find('.VueTables__limit-field select')
        expect(perPageSelect.exists()).toBe(true)
        expect(perPageSelect.element.value).toBe('10')
    })

    it('does not render perpage select when perPageValues has 1 or fewer items', () => {
        wrapper = mountDataTable({ option: { perPageValues: [10] } })
        expect(wrapper.find('.VueTables__limit-field').exists()).toBe(false)
    })

    it('renders perpage select and search on the same line inside .datatable-header', () => {
        wrapper = mountDataTable({ option: { filterable: true } })
        const header = wrapper.find('.datatable-header')
        expect(header.exists()).toBe(true)
        expect(header.find('.VueTables__limit-field').exists()).toBe(true)
        expect(header.find('input.globe-search').exists()).toBe(true)
    })

    it('onPerPageChange updates perPage and calls setLimit on tableRef', () => {
        const mockTable = { setLimit: jest.fn() }
        wrapper.vm.tableRef = mockTable
        const event = { target: { value: '25' } }
        wrapper.vm.onPerPageChange(event)
        expect(wrapper.vm.perPage).toBe(25)
        expect(mockTable.setLimit).toHaveBeenCalledWith(25)
    })

    // ── search ────────────────────────────────────────────────────────
    it('does not render search input when filterable is false', () => {
        expect(wrapper.find('input.globe-search').exists()).toBe(false)
    })

    it('renders search input when option.filterable is true', () => {
        wrapper = mountDataTable({ option: { filterable: true } })
        expect(wrapper.find('input.globe-search').exists()).toBe(true)
    })

    it('onSearch calls setFilter on tableRef', () => {
        const mockTable = { setFilter: jest.fn() }
        wrapper.vm.tableRef = mockTable
        wrapper.vm.searchStr = 'hello'
        wrapper.vm.onSearch()
        expect(mockTable.setFilter).toHaveBeenCalledWith('hello')
    })

    it('renders v-server-table stub', () => {
        expect(wrapper.find('.v-server-table').exists()).toBe(true)
    })

    // ── pagination & record counts ───────────────────────────────────
    it('shows total record count when total <= perPage', async () => {
        wrapper.vm.total = 5
        wrapper.vm.perPage = 10
        wrapper.vm.isLoading = false
        await wrapper.vm.$nextTick()
        expect(wrapper.text()).toContain('message.x_records')
    })

    it('shows "1 record" when total is 1', async () => {
        wrapper.vm.total = 1
        wrapper.vm.isLoading = false
        await wrapper.vm.$nextTick()
        expect(wrapper.text()).toContain('message.one_record')
    })

    it('shows Showing X to Y of Z records for paginated data', async () => {
        wrapper.vm.total = 50
        wrapper.vm.perPage = 10
        wrapper.vm.from = 1
        wrapper.vm.to = 10
        wrapper.vm.isLoading = false
        await wrapper.vm.$nextTick()
        expect(wrapper.text()).toContain('message.datatable_info')
    })

    it('exposes currentPage ref', () => {
        expect(wrapper.vm.currentPage).toBeDefined()
    })

    it('exposes lastPage ref', () => {
        expect(wrapper.vm.lastPage).toBeDefined()
    })

    it('exposes total ref', () => {
        expect(wrapper.vm.total).toBeDefined()
    })

    it('exposes perPage ref', () => {
        expect(wrapper.vm.perPage).toBeDefined()
    })

    it('exposes isLoading ref', () => {
        expect(wrapper.vm.isLoading).toBeDefined()
    })

    it('does not show record count text when isLoading is true', async () => {
        wrapper.vm.isLoading = true
        await wrapper.vm.$nextTick()
        expect(wrapper.find('.pagination-container').text()).not.toContain('records')
    })

    it('onPageChange sets page on tableRef', () => {
        const mockTable = { setPage: jest.fn() }
        wrapper.vm.tableRef = mockTable
        wrapper.vm.paginate(3)
        expect(mockTable.setPage).toHaveBeenCalledWith(3)
    })

    // ── defaultResponseAdapter ─────────────────────────────────────────
    it('defaultResponseAdapter extracts pagination from full response', () => {
        const response = {
            data: { data: {
                data: [{ id: 1 }],
                total: 25, per_page: 10, current_page: 2, last_page: 3,
                from: 11, to: 20,
            }}
        }
        const result = wrapper.vm.defaultResponseAdapter(response)
        expect(result.data).toHaveLength(1)
        expect(result.count).toBe(25)
        expect(wrapper.vm.total).toBe(25)
        expect(wrapper.vm.perPage).toBe(10)
        expect(wrapper.vm.currentPage).toBe(2)
        expect(wrapper.vm.lastPage).toBe(3)
    })

    it('defaultResponseAdapter handles undefined response', () => {
        const result = wrapper.vm.defaultResponseAdapter(undefined)
        expect(result.data).toEqual([])
        expect(wrapper.vm.total).toBeNull()
    })

    it('defaultResponseAdapter handles empty data object', () => {
        const result = wrapper.vm.defaultResponseAdapter({ data: {} })
        expect(result.data).toEqual([])
    })
})
