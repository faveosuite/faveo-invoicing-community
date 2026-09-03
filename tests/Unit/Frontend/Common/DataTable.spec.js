import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import DataTable from '@/components/Reusable/DataTable.vue'

const mountDataTable = (props = {}) =>
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
                    methods: { refresh: () => {}, setFilter: () => {}, setPage: () => {} },
                },
                SimplePagination: true,
            },
        },
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

    it('does not render search input when filterable is false', () => {
        expect(wrapper.find('input.globe-search').exists()).toBe(false)
    })

    it('renders search input when option.filterable is true', () => {
        wrapper = mountDataTable({ option: { filterable: true } })
        expect(wrapper.find('input.globe-search').exists()).toBe(true)
    })

    it('renders v-server-table stub', () => {
        expect(wrapper.find('.v-server-table').exists()).toBe(true)
    })

    it('does not render SimplePagination when nextPage and prevPage are null', () => {
        expect(wrapper.find('simplepagination-stub').exists()).toBe(false)
    })

    it('shows total record count when total <= perPage', async () => {
        wrapper.vm.total = 5
        wrapper.vm.perPage = 10
        wrapper.vm.isLoading = false
        await wrapper.vm.$nextTick()
        expect(wrapper.text()).toContain('5 records')
    })

    it('shows "1 record" when total is 1', async () => {
        wrapper.vm.total = 1
        wrapper.vm.isLoading = false
        await wrapper.vm.$nextTick()
        expect(wrapper.text()).toContain('1 record')
    })

    it('exposes nextPage ref', () => {
        expect(wrapper.vm.nextPage).toBeDefined()
    })

    it('exposes prevPage ref', () => {
        expect(wrapper.vm.prevPage).toBeDefined()
    })

    it('exposes total ref', () => {
        expect(wrapper.vm.total).toBeDefined()
    })

    it('exposes isLoading ref', () => {
        expect(wrapper.vm.isLoading).toBeDefined()
    })

    it('does not show pagination container when isLoading is true', async () => {
        wrapper.vm.isLoading = true
        await wrapper.vm.$nextTick()
        // Record count text is inside v-if="!isLoading"
        expect(wrapper.find('.pagination-container').text()).not.toContain('records')
    })

    it('shows Showing X to Y of Z records for paginated data', async () => {
        wrapper.vm.total = 50
        wrapper.vm.perPage = 10
        wrapper.vm.from = 1
        wrapper.vm.to = 10
        wrapper.vm.isLoading = false
        await wrapper.vm.$nextTick()
        expect(wrapper.text()).toContain('Showing 1 to 10 of 50 records')
    })

    // ── onPaginate ────────────────────────────────────────────────────
    it('onPaginate sets page on tableRef for next direction', () => {
        const mockTable = { setPage: jest.fn() }
        wrapper.vm.tableRef = mockTable
        wrapper.vm.nextPage = 'http://example.com/api?page=3'
        wrapper.vm.onPaginate('next')
        expect(mockTable.setPage).toHaveBeenCalledWith(3)
    })

    it('onPaginate sets page on tableRef for prev direction', () => {
        const mockTable = { setPage: jest.fn() }
        wrapper.vm.tableRef = mockTable
        wrapper.vm.prevPage = 'http://example.com/api?page=1'
        wrapper.vm.onPaginate('prev')
        expect(mockTable.setPage).toHaveBeenCalledWith(1)
    })

    it('onPaginate returns early when target URL is null', () => {
        const mockTable = { setPage: jest.fn() }
        wrapper.vm.tableRef = mockTable
        wrapper.vm.nextPage = null
        wrapper.vm.onPaginate('next')
        expect(mockTable.setPage).not.toHaveBeenCalled()
    })

    it('onPaginate skips setPage when URL has no page param', () => {
        const mockTable = { setPage: jest.fn() }
        wrapper.vm.tableRef = mockTable
        wrapper.vm.nextPage = 'http://example.com/api'
        wrapper.vm.onPaginate('next')
        expect(mockTable.setPage).not.toHaveBeenCalled()
    })

    // ── onSearch ──────────────────────────────────────────────────────
    it('onSearch calls setFilter on tableRef', () => {
        const mockTable = { setFilter: jest.fn() }
        wrapper.vm.tableRef = mockTable
        wrapper.vm.searchStr = 'hello'
        wrapper.vm.onSearch()
        expect(mockTable.setFilter).toHaveBeenCalledWith('hello')
    })

    // ── defaultResponseAdapter ─────────────────────────────────────────
    it('defaultResponseAdapter extracts pagination from full response', () => {
        const response = {
            data: { data: {
                data: [{ id: 1 }],
                total: 25, per_page: 10, current_page: 1,
                from: 1, to: 10,
                next_page_url: 'http://example.com?page=2',
                prev_page_url: null,
            }}
        }
        const result = wrapper.vm.defaultResponseAdapter(response)
        expect(result.data).toHaveLength(1)
        expect(result.count).toBe(25)
        expect(wrapper.vm.total).toBe(25)
        expect(wrapper.vm.nextPage).toBe('http://example.com?page=2')
        expect(wrapper.vm.prevPage).toBeNull()
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

    it('defaultResponseAdapter uses next_page_url count heuristic', () => {
        const response = {
            data: { data: {
                data: [{ id: 1 }],
                total: null, per_page: 10, current_page: 2,
                from: 11, to: 20,
                next_page_url: 'http://example.com?page=3',
            }}
        }
        const result = wrapper.vm.defaultResponseAdapter(response)
        // count = currentPage * pp + 1 when total is null and next_page_url exists
        expect(result.count).toBeGreaterThan(0)
    })

    // ── record count display branches ────────────────────────────────
    it('shows "1 record" when total is 1', async () => {
        wrapper.vm.isLoading = false
        wrapper.vm.total = 1
        wrapper.vm.perPage = 10
        await wrapper.vm.$nextTick()
        expect(wrapper.text()).toContain('1 record')
    })

    it('shows "X records" when total <= perPage', async () => {
        wrapper.vm.isLoading = false
        wrapper.vm.total = 5
        wrapper.vm.perPage = 10
        await wrapper.vm.$nextTick()
        expect(wrapper.text()).toContain('5 records')
    })

    it('shows "Showing X to Y records of many" when total is null and nextPage exists', async () => {
        wrapper.vm.isLoading = false
        wrapper.vm.total = null
        wrapper.vm.from = 1
        wrapper.vm.to = 10
        wrapper.vm.nextPage = 'http://example.com?page=2'
        await wrapper.vm.$nextTick()
        expect(wrapper.text()).toContain('Showing')
    })

    it('shows "Showing X to Y of Y records" when total is null and no nextPage', async () => {
        wrapper.vm.isLoading = false
        wrapper.vm.total = null
        wrapper.vm.from = 1
        wrapper.vm.to = 8
        wrapper.vm.nextPage = null
        wrapper.vm.prevPage = null
        await wrapper.vm.$nextTick()
        expect(wrapper.text()).toContain('Showing')
    })
})
