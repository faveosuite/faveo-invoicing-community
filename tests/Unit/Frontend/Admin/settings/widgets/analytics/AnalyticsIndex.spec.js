jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot /></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import AnalyticsIndex from '@/pages/admin/settings/widgets/analytics/AnalyticsIndex.vue'

describe('AnalyticsIndex.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(AnalyticsIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'DataTable', 'DeleteModal', 'action-button', 'inline-loader', 'RouterLink'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the analytics index card', () => {
        expect(wrapper.find('.card').exists()).toBeTruthy()
    })

    it('contains a DataTable stub', () => {
        expect(
            wrapper.find('data-table-stub').exists() ||
            wrapper.findComponent({ name: 'DataTable' }).exists() ||
            wrapper.html().toLowerCase().includes('datatable')
        ).toBeTruthy()
    })

    it('initializes with no selected rows', () => {
        expect(wrapper.vm.selected).toEqual([])
    })

    it('initializes with no pending delete', () => {
        expect(wrapper.vm.pendingDelete).toBeNull()
        expect(wrapper.vm.pendingBulkDelete).toBeNull()
    })

    it('sets pendingDelete when confirmDelete is called', () => {
        wrapper.vm.confirmDelete(7)
        expect(wrapper.vm.pendingDelete).toEqual({ select: [7] })
    })

    it('does not set pendingBulkDelete when no rows selected', () => {
        wrapper.vm.selected = []
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).toBeNull()
    })

    it('sets pendingBulkDelete when rows are selected', () => {
        wrapper.vm.selected = [1, 2, 3]
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).toEqual({ select: [1, 2, 3] })
    })

    it('toggleRow adds a new id to selected', () => {
        wrapper.vm.toggleRow(10)
        expect(wrapper.vm.selected).toContain(10)
    })

    it('toggleRow removes an existing id from selected', () => {
        wrapper.vm.selected = [10]
        wrapper.vm.toggleRow(10)
        expect(wrapper.vm.selected).not.toContain(10)
    })
})
