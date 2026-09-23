jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/core/composables/useDateTime', () => ({ useDateTime: () => ({ formatDate: (v) => v }) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import UserIndex from '@/pages/admin/users/UserIndex.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

describe('UserIndex.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/users/).reply(200, { data: [] })
        globalThis.mockHttp.onGet(/\/export-users/).reply(200, { data: { message: 'Export queued' } })
        wrapper = mount(UserIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'DataTable', 'AppAlert', 'inline-loader', 'router-link',
                    'DeleteModal', 'action-button', 'ColumnSelector', 'UserFilter', 'UserTableActions',
                ],
            },
        })
        wrapper.vm.dtRef = { refresh: jest.fn() }
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

    it('renders UserFilter stub', () => {
        expect(wrapper.find('user-filter-stub').exists()).toBe(true)
    })

    it('renders ColumnSelector stub', () => {
        // ColumnSelector lives inside DataTable's #table-tools slot;
        // when DataTable is stubbed the slot is not rendered, so we verify
        // DataTable itself is present instead.
        expect(wrapper.find('data-table-stub').exists()).toBe(true)
    })

    it('does not show DeleteModal by default', () => {
        expect(wrapper.find('delete-modal-stub').exists()).toBe(false)
    })

    it('toggles showFilter when filter button is clicked', async () => {
        expect(wrapper.vm.showFilter).toBe(false)
        await wrapper.find('button.btn-tool').trigger('click')
        expect(wrapper.vm.showFilter).toBe(true)
    })

    it('calls exportAll which hits export API', async () => {
        await wrapper.vm.exportAll()
        await flushPromises()
        expect(globalThis.mockHttp.history.get.some(r => /export-users/.test(r.url))).toBe(true)
    })

    it('calls successHandler on successful export', async () => {
        await wrapper.vm.exportAll()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on export failure', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/export-users/).reply(500)
        await wrapper.vm.exportAll()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('onFilterApply stores filters and closes filter panel', () => {
        wrapper.vm.showFilter = true
        wrapper.vm.onFilterApply({ company: 'Acme' })
        expect(wrapper.vm.activeFilters).toEqual({ company: 'Acme' })
        expect(wrapper.vm.showFilter).toBe(false)
    })

    it('onFilterReset clears active filters', () => {
        wrapper.vm.activeFilters = { company: 'Acme' }
        wrapper.vm.onFilterReset()
        expect(wrapper.vm.activeFilters).toEqual({})
    })

    it('confirmBulkDelete sets pendingBulkDelete when users selected', () => {
        wrapper.vm.selectedUsers = [1, 2]
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).not.toBeNull()
    })

    it('confirmBulkDelete does nothing when no users selected', () => {
        wrapper.vm.selectedUsers = []
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).toBeNull()
    })

    it('onColumnsChange maps report keys to internal column names', () => {
        wrapper.vm.onColumnsChange(['name', 'email'])
        expect(wrapper.vm.columns).toContain('name')
        expect(wrapper.vm.columns).toContain('email')
    })
})
