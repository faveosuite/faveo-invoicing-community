jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot /></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import SocialMediaIndex from '@/pages/admin/settings/widgets/socialMedia/SocialMediaIndex.vue'

describe('SocialMediaIndex.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(SocialMediaIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'DataTable', 'DeleteModal', 'action-button', 'inline-loader', 'RouterLink'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the social media index card', () => {
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

    it('initializes with no pending deletes', () => {
        expect(wrapper.vm.pendingDeleteRow).toBeNull()
        expect(wrapper.vm.pendingBulkDelete).toBeNull()
    })

    it('sets pendingDeleteRow when confirmDeleteRow is called', () => {
        wrapper.vm.confirmDeleteRow(5)
        expect(wrapper.vm.pendingDeleteRow).toEqual({ id: 5 })
    })

    it('does not set pendingBulkDelete when no rows selected', () => {
        wrapper.vm.selected = []
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).toBeNull()
    })

    it('sets pendingBulkDelete when rows are selected', () => {
        wrapper.vm.selected = [2, 4, 6]
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).toEqual({ id: [2, 4, 6] })
    })

    it('toggleRow adds a new id to selected', () => {
        wrapper.vm.toggleRow(8)
        expect(wrapper.vm.selected).toContain(8)
    })

    it('toggleRow removes an existing id from selected', () => {
        wrapper.vm.selected = [8]
        wrapper.vm.toggleRow(8)
        expect(wrapper.vm.selected).not.toContain(8)
    })
})
