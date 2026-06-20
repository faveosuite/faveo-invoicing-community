jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/core/stores/dateTimeStore', () => ({ useDateTimeStore: () => ({ timezone: 'UTC' }) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { successHandler, errorHandler } from '@/helpers/responseHandler'
import ActivityLogs from '@/pages/admin/settings/logs/ActivityLogs.vue'

describe('ActivityLogs.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/get-activity-filters/).reply(200, {
            data: { modules: [], users: [] },
        })
        global.mockHttp.onDelete(/\/logs\/delete/).reply(200, { message: 'Deleted' })

        wrapper = mount(ActivityLogs, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'AppModal', 'DataTable', 'DatePicker',
                    'action-button', 'ActivityFilter', 'inline-loader',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the card with activity logs title', () => {
        expect(wrapper.find('.card').exists()).toBeTruthy()
    })

    it('contains a DataTable stub', () => {
        expect(wrapper.findComponent({ name: 'DataTable' }).exists() ||
            wrapper.find('data-table-stub').exists() ||
            wrapper.html().includes('datatable')).toBeTruthy()
    })

    it('toggles showFilter when filter button is clicked', async () => {
        const filterBtn = wrapper.find('button.btn-tool')
        expect(filterBtn.exists()).toBeTruthy()
        await filterBtn.trigger('click')
        expect(wrapper.vm.showFilter).toBe(true)
    })

    it('opens delete modal when delete button is clicked', async () => {
        const buttons = wrapper.findAll('button.btn-tool')
        const deleteBtn = buttons[1]
        expect(deleteBtn.exists()).toBeTruthy()
        await deleteBtn.trigger('click')
        expect(wrapper.vm.showDeleteModal).toBe(true)
    })

    it('calls successHandler on successful log deletion', async () => {
        wrapper.vm.deleteDate = '2026-01-01'
        await wrapper.vm.confirmDelete()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on delete failure', async () => {
        global.mockHttp.onDelete(/\/logs\/delete/).reply(500)
        wrapper.vm.deleteDate = '2026-01-01'
        await wrapper.vm.confirmDelete()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('sets deleteError when confirmDelete called without date', async () => {
        wrapper.vm.deleteDate = null
        await wrapper.vm.confirmDelete()
        expect(wrapper.vm.deleteError).toBeTruthy()
    })
})
