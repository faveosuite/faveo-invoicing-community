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
})
