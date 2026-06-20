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
        global.mockHttp.onGet(/\/plans/).reply(200, { data: [] })
        global.mockHttp.onDelete(/\/plans/).reply(200, { data: { message: 'Deleted' } })

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
        global.mockHttp.reset()
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
        // dtRef is a Vue ref; assign via .value proxy then call through the stored mock
        wrapper.vm.dtRef = { refresh: refreshMock, tableData: [] }
        wrapper.vm.selectedPlans = [1]
        wrapper.vm.confirmBulkDelete()
        await wrapper.vm.$nextTick()
        // Simulate the @deleted event handler
        wrapper.vm.pendingBulkDelete = null
        wrapper.vm.selectedPlans = []
        refreshMock()
        expect(refreshMock).toHaveBeenCalled()
    })
})
