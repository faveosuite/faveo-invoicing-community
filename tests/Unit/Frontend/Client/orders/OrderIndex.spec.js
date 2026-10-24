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
const mockDownloadFile = jest.fn()
jest.mock('@/core/composables/useDownload', () => ({
    useDownload: () => ({ downloadFile: mockDownloadFile }),
}))

import { mount, flushPromises } from '@vue/test-utils'
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

    it('downloadVersion closes the popup and starts the download with the product name', () => {
        wrapper.vm.openDownloadModal({ id: 7, product_name: 'Helpdesk Pro' })
        wrapper.vm.downloadVersion({ version: 'v10.2.2', download_url: '/download/7/3' })

        expect(wrapper.vm.showDownloadModal).toBe(false)
        expect(mockDownloadFile).toHaveBeenCalledWith('/download/7/3', 'Helpdesk Pro v10.2.2')
    })

    it('opening the popup loads the featured versions and the updates-ended notice', async () => {
        axiosMock.onGet(/\/get-versions\/7/).reply(200, { data: {
            data: [], total: 0,
            featured: [{ version: 'v10.2.2', release_type: 'official', can_download: true }],
            updates_ended_on: '2026-07-01 00:00:00',
        } })

        wrapper.vm.openDownloadModal({ id: 7, product_name: 'Helpdesk Pro' })
        await flushPromises()

        expect(wrapper.vm.featured.map(v => v.version)).toEqual(['v10.2.2'])
        expect(wrapper.vm.versionsMeta).toEqual({ updates_ended_on: '2026-07-01 00:00:00', has_previous: false })
        expect(wrapper.vm.versionsLoading).toBe(false)
    })

    it('labels one card "latest", or official + release candidate when the latest is an RC', () => {
        wrapper.vm.featured = [{ version: 'v10.2.2', release_type: 'official' }]
        expect(wrapper.vm.featuredLabel(wrapper.vm.featured[0])).toBe('message.latest_version')

        wrapper.vm.featured = [{ version: 'v10.3.0.RC.1', release_type: 'pre_release' }, { version: 'v10.2.1', release_type: 'official' }]
        expect(wrapper.vm.featuredLabel(wrapper.vm.featured[0])).toBe('message.latest_release_candidate')
        expect(wrapper.vm.featuredLabel(wrapper.vm.featured[1])).toBe('message.latest_official_version')
    })

    it('previews release notes as plain text cut at a word, with "show more" only when cut', () => {
        const long = `<p>${'word '.repeat(60)}</p>`
        expect(wrapper.vm.notesTruncated(long)).toBe(true)
        expect(wrapper.vm.notesPreview(long).endsWith('word…')).toBe(true)
        expect(wrapper.vm.notesPreview(long)).not.toContain('<p>')

        expect(wrapper.vm.notesTruncated('<b>Short fix</b>')).toBe(false)
        expect(wrapper.vm.notesPreview('<b>Short fix</b>')).toBe('Short fix')
    })

    it('marks RC and Beta as previews with a not-for-live-use hint; official as recommended', () => {
        expect(wrapper.vm.isPreview({ release_type: 'pre_release' })).toBe(true)
        expect(wrapper.vm.isPreview({ release_type: 'beta' })).toBe(true)
        expect(wrapper.vm.isPreview({ release_type: 'official' })).toBe(false)
        expect(wrapper.vm.releaseHint({ release_type: 'pre_release' })).toBe('message.release_hint_pre_release')
        expect(wrapper.vm.releaseHint({ release_type: 'beta' })).toBe('message.release_hint_beta')
        expect(wrapper.vm.releaseHint({ release_type: 'official' })).toBe('message.release_hint_official')
    })

    it('renew from the popup closes it and opens the renew modal for that order', () => {
        const row = { id: 7, product_name: 'Helpdesk Pro' }
        wrapper.vm.openDownloadModal(row)
        wrapper.vm.renewFromVersions()

        expect(wrapper.vm.showDownloadModal).toBe(false)
        expect(wrapper.vm.showRenewModal).toBe(true)
        expect(wrapper.vm.renewRow).toEqual(row)
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

    it('confirmDelete calls DELETE /delete/domain/:number/1 when deleteRow has a number', async () => {
        axiosMock.onDelete(/\/delete\/domain\/ORD-001\/1/).reply(200, { data: { message: 'Deleted' } })
        wrapper.vm.deleteRow = { number: 'ORD-001' }
        await wrapper.vm.confirmDelete()
        expect(axiosMock.history.delete.some(r => r.url.includes('/delete/domain/ORD-001/1'))).toBe(true)
    })

    it('confirmDelete does nothing when deleteRow has no number', async () => {
        wrapper.vm.deleteRow = { number: null }
        await wrapper.vm.confirmDelete()
        expect(axiosMock.history.delete.length).toBe(0)
    })
})
