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
})
