jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: {}, query: {} }),
    RouterLink: { template: '<a><slot /></a>' },
}))
jest.mock('@/core/composables/useDateTime', () => ({
    useDateTime: () => ({ formatDate: (v) => v ?? '' }),
}))
jest.mock('@/core/composables/useDownload', () => ({
    useDownload: () => ({ downloadFile: jest.fn() }),
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import OrderIndex from '@/pages/client/orders/OrderIndex.vue'

describe('OrderIndex.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)

        wrapper = mount(OrderIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'app-card',
                    'app-modal',
                    'data-table',
                    'action-button',
                    'alert',
                    'router-link',
                    'renew-modal',
                ],
            },
        })
    })

    afterEach(() => {
        axiosMock.restore()
        jest.clearAllMocks()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the root div element', () => {
        expect(wrapper.find('div').exists()).toBeTruthy()
    })

    it('has showDownloadModal defaulting to false', () => {
        expect(wrapper.vm.showDownloadModal).toBe(false)
    })

    it('has showRenewModal defaulting to false', () => {
        expect(wrapper.vm.showRenewModal).toBe(false)
    })

    it('has showDeleteModal defaulting to false', () => {
        expect(wrapper.vm.showDeleteModal).toBe(false)
    })

    it('openDownloadModal sets the row and opens the modal', () => {
        const row = { id: 5, product_name: 'Faveo' }
        wrapper.vm.openDownloadModal(row)
        expect(wrapper.vm.showDownloadModal).toBe(true)
        expect(wrapper.vm.downloadRow).toEqual(row)
    })

    it('closeDownloadModal clears the row and closes the modal', () => {
        wrapper.vm.downloadRow = { id: 5 }
        wrapper.vm.showDownloadModal = true
        wrapper.vm.closeDownloadModal()
        expect(wrapper.vm.showDownloadModal).toBe(false)
        expect(wrapper.vm.downloadRow).toBeNull()
    })

    it('openRenewModal sets the row and opens the renew modal', () => {
        const row = { id: 2, product_id: 10, sub_id: 3, client_id: 1 }
        wrapper.vm.openRenewModal(row)
        expect(wrapper.vm.showRenewModal).toBe(true)
        expect(wrapper.vm.renewRow).toEqual(row)
    })

    it('openDeleteModal sets the row and opens the delete modal', () => {
        const row = { id: 3, number: 'ORD-001' }
        wrapper.vm.openDeleteModal(row)
        expect(wrapper.vm.showDeleteModal).toBe(true)
        expect(wrapper.vm.deleteRow).toEqual(row)
    })

    it('closeDeleteModal closes the delete modal', () => {
        wrapper.vm.showDeleteModal = true
        wrapper.vm.closeDeleteModal()
        expect(wrapper.vm.showDeleteModal).toBe(false)
    })

    it('downloadVersionsUrl is null when no downloadRow is set', () => {
        expect(wrapper.vm.downloadVersionsUrl).toBeNull()
    })

    it('downloadVersionsUrl is set when downloadRow is set', () => {
        wrapper.vm.downloadRow = { id: 99 }
        expect(wrapper.vm.downloadVersionsUrl).toContain('/get-versions/99')
    })
})
