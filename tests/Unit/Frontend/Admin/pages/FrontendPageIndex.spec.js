jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/core/composables/useDateTime', () => ({ useDateTime: () => ({ formatDate: (v) => v }) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import FrontendPageIndex from '@/pages/admin/pages/FrontendPageIndex.vue'

describe('FrontendPageIndex.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/pages/).reply(200, { data: [] })
        global.mockHttp.onDelete(/\/pages/).reply(200, { data: { message: 'Deleted' } })
        wrapper = mount(FrontendPageIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'DataTable', 'AppAlert', 'inline-loader', 'action-button',
                    'DeleteModal', 'router-link',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders a DataTable stub', () => {
        expect(wrapper.find('data-table-stub').exists()).toBe(true)
    })

    it('renders AppAlert', () => {
        expect(wrapper.find('app-alert-stub').exists()).toBe(true)
    })

    it('does not show DeleteModal by default', () => {
        expect(wrapper.find('delete-modal-stub').exists()).toBe(false)
    })

    it('confirmBulkDelete sets pendingBulkDelete when pages are selected', () => {
        wrapper.vm.selected = [1, 2]
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).not.toBeNull()
        expect(wrapper.vm.pendingBulkDelete).toHaveProperty('page_ids')
    })

    it('confirmBulkDelete does nothing when no pages are selected', () => {
        wrapper.vm.selected = []
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).toBeNull()
    })

    it('pendingBulkDelete contains selected page ids', () => {
        wrapper.vm.selected = [3, 7]
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete.page_ids).toEqual([3, 7])
    })

    it('shows DeleteModal when pendingBulkDelete is set', async () => {
        wrapper.vm.selected = [1]
        wrapper.vm.confirmBulkDelete()
        await wrapper.vm.$nextTick()
        expect(wrapper.find('delete-modal-stub').exists()).toBe(true)
    })
})
