jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/core/composables/useDateTime', () => ({ useDateTime: () => ({ formatDate: (v) => v }) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import Suspended from '@/pages/admin/users/Suspended.vue'
import { errorHandler } from '@/helpers/responseHandler'

describe('Suspended.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/soft-delete/).reply(200, { data: [] })
        globalThis.mockHttp.onGet(/\/user\/restore\//).reply(200, { data: { message: 'Restored' } })
        wrapper = mount(Suspended, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['DataTable', 'AppAlert', 'DeleteModal', 'SuspendedTableActions', 'inline-loader', 'loader'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the page card', () => {
        expect(wrapper.find('.card').exists()).toBe(true)
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

    it('calls bulkRestore which hits restore API', async () => {
        wrapper.vm.selected = [1, 2]
        await wrapper.vm.bulkRestore()
        await flushPromises()
        expect(globalThis.mockHttp.history.get.length).toBeGreaterThan(0)
    })

    it('calls errorHandler when bulkRestore fails', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/user\/restore\//).reply(500)
        wrapper.vm.selected = [1]
        await wrapper.vm.bulkRestore()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('sets pendingBulkDelete when confirmBulkDelete is called with selected users', () => {
        wrapper.vm.selected = [3, 4]
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).not.toBeNull()
        expect(wrapper.vm.pendingBulkDelete.user_ids).toEqual([3, 4])
    })

    it('does not set pendingBulkDelete when no users selected', () => {
        wrapper.vm.selected = []
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).toBeNull()
    })
})
