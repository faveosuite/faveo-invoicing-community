jest.mock('@/plugins/i18n', () => ({
    __: (key, replace = {}) => Object.entries(replace).reduce((s, [k, v]) => `${s} ${k}=${v}`, key),
}))

import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'
import DownloadTray from '@/components/Reusable/DownloadTray.vue'
import { downloadTray } from '@/core/composables/useDownload'

const mountTray = () => mount(DownloadTray, { global: { directives: { tooltip: {} } } })

describe('DownloadTray', () => {
    afterEach(() => Object.assign(downloadTray, { items: [], open: false, collapsed: false }))

    it('renders nothing until a download starts', () => {
        expect(mountTray().find('.card').exists()).toBe(false)
    })

    it('shows the preparing header, summary and a running timer while the server builds the file', async () => {
        downloadTray.items = [
            { id: 1, name: 'helpdesk-v10.zip', status: 'preparing', startedAt: Date.now() - 23000 },
            { id: 2, name: 'invoice.pdf', status: 'started' },
        ]
        downloadTray.open = true
        const wrapper = mountTray()
        await nextTick()

        expect(wrapper.text()).toContain('message.download_tray_preparing_one count=1')
        expect(wrapper.text()).toContain('message.download_tray_hint')
        expect(wrapper.text()).toContain('message.download_meta_preparing time=0:23')
        expect(wrapper.find('.progress-bar-animated').exists()).toBe(true) // time unknown: moving bar
        expect(wrapper.find('.fa-circle-check').exists()).toBe(true)        // the started row
    })

    it('shows the real percentage while bytes arrive', async () => {
        downloadTray.items = [{ id: 1, name: 'a.zip', status: 'downloading', loaded: 126 * 1048576, total: 300 * 1048576 }]
        downloadTray.open = true
        const wrapper = mountTray()
        await nextTick()

        expect(wrapper.text()).toContain('message.download_meta_progress percent=42 loaded=126 MB total=300 MB')
        expect(wrapper.find('.progress-bar').attributes('style')).toContain('width: 42%')
    })

    it('shows the error and a retry button on a failed row', async () => {
        downloadTray.items = [{ id: 1, name: 'a.zip', status: 'failed', error: 'Renew to download' }]
        downloadTray.open = true
        const wrapper = mountTray()
        await nextTick()

        expect(wrapper.text()).toContain('message.download_tray_ready_failed ready=0 failed=1')
        expect(wrapper.find('.text-danger').text()).toBe('Renew to download')
        expect(wrapper.text()).toContain('message.retry')
    })

    it('uses the plural header, KB for small files, and says when everything was cancelled', async () => {
        downloadTray.items = [
            { id: 1, name: 'a.pdf', status: 'downloading', loaded: 240 * 1024, total: 512 * 1024 },
            { id: 2, name: 'b.pdf', status: 'preparing', startedAt: Date.now() },
        ]
        downloadTray.open = true
        const wrapper = mountTray()
        await nextTick()

        expect(wrapper.text()).toContain('message.download_tray_preparing_other count=2')
        expect(wrapper.text()).toContain('loaded=240 KB total=512 KB') // not "0 of 0 MB"

        downloadTray.items = [{ id: 3, name: 'c.zip', status: 'cancelled' }]
        await nextTick()
        expect(wrapper.text()).toContain('message.download_tray_cancelled')
    })

    it('minimizing hides the rows but keeps the header', async () => {
        downloadTray.items = [{ id: 1, name: 'a.zip', status: 'started' }]
        Object.assign(downloadTray, { open: true, collapsed: true })
        const wrapper = mountTray()
        await nextTick()

        expect(wrapper.find('.card-header').exists()).toBe(true)
        expect(wrapper.find('.list-group').exists()).toBe(false)
    })
})
