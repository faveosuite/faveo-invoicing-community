jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ProductGroupIndex from '@/pages/admin/products/groups/ProductGroupIndex.vue'

describe('ProductGroupIndex.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/groups/).reply(200, { data: [] })
        global.mockHttp.onDelete(/\/group/).reply(200, { data: { message: 'Deleted' } })
        wrapper = mount(ProductGroupIndex, {
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

    it('confirmBulkDelete sets pendingBulkDelete when groups are selected', () => {
        wrapper.vm.selectedGroups = [1, 2]
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).not.toBeNull()
        expect(wrapper.vm.pendingBulkDelete).toHaveProperty('select')
    })

    it('confirmBulkDelete does nothing when no groups are selected', () => {
        wrapper.vm.selectedGroups = []
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).toBeNull()
    })

    it('pendingBulkDelete contains selected group ids', () => {
        wrapper.vm.selectedGroups = [4, 9]
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete.select).toEqual([4, 9])
    })

    it('shows DeleteModal when pendingBulkDelete is set', async () => {
        wrapper.vm.selectedGroups = [1]
        wrapper.vm.confirmBulkDelete()
        await wrapper.vm.$nextTick()
        expect(wrapper.find('delete-modal-stub').exists()).toBe(true)
    })
})
