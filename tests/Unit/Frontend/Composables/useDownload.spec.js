jest.mock('@/plugins/i18n', () => ({
    __: (key) => key,
}))

import {
    useDownload, downloadTray, cancelDownload, retryDownload, cancelAllDownloads, closeDownloadTray,
} from '@/core/composables/useDownload.js'

const ok = (headers = {}, extra = {}) => ({
    ok: true,
    blob: () => Promise.resolve(new Blob(['file content'])),
    headers: { get: (h) => headers[h] ?? null },
    ...extra,
})

describe('useDownload', () => {
    let anchor

    beforeEach(() => {
        Object.assign(downloadTray, { items: [], open: false, collapsed: false })
        globalThis.fetch = jest.fn()
        globalThis.URL.createObjectURL = jest.fn(() => 'blob:url')
        globalThis.URL.revokeObjectURL = jest.fn()
        anchor = { href: '', download: '', click: jest.fn() }
        jest.spyOn(document, 'createElement').mockImplementation(() => anchor)
    })

    afterEach(() => jest.restoreAllMocks())

    it('saves the file under the server filename and marks it started', async () => {
        globalThis.fetch.mockResolvedValue(ok({ 'content-disposition': 'attachment; filename="report.zip"' }))

        await useDownload().downloadFile('/api/download/file', 'Helpdesk v1')

        expect(fetch).toHaveBeenCalledWith('/api/download/file', expect.objectContaining({ signal: expect.anything() }))
        expect(anchor.download).toBe('report.zip')
        expect(anchor.click).toHaveBeenCalled()
        expect(downloadTray.open).toBe(true)
        expect(downloadTray.items[0]).toMatchObject({ name: 'report.zip', status: 'started' })
    })

    it('hands another site\'s file (a GitHub release link) straight to the browser', async () => {
        const url = 'https://codeload.github.com/faveo/helpdesk/legacy.zip/refs/tags/v9.4.3.8'

        await useDownload().downloadFile(url, 'Helpdesk v9.4.3.8')

        expect(fetch).not.toHaveBeenCalled() // GitHub refuses cross-site fetch
        expect(anchor.href).toBe(url)
        expect(anchor.click).toHaveBeenCalled()
        expect(downloadTray.items[0].status).toBe('started')
    })

    it('hands a redirected download (GitHub releases) to the browser instead of fetching it', async () => {
        // codeload.github.com refuses cross-site fetch, so following the redirect here always failed.
        globalThis.fetch.mockResolvedValue({ type: 'opaqueredirect', ok: false, status: 0 })

        await useDownload().downloadFile('/api/download/github', 'Helpdesk v9.4.3.8')

        expect(fetch).toHaveBeenCalledWith('/api/download/github', expect.objectContaining({ redirect: 'manual' }))
        expect(anchor.href).toBe('/api/download/github')
        expect(anchor.click).toHaveBeenCalled()
        expect(downloadTray.items[0].status).toBe('started')
    })

    it('keeps the caller name in the tray but saves as download.zip when the server sends no filename', async () => {
        globalThis.fetch.mockResolvedValue(ok())

        await useDownload().downloadFile('/api/download/file', 'Helpdesk v1')

        expect(anchor.download).toBe('download.zip')
        expect(downloadTray.items[0].name).toBe('Helpdesk v1')
    })

    it('goes preparing → downloading with real byte progress → started', async () => {
        const chunks = [new Uint8Array(50), new Uint8Array(50)]
        const seen = []
        const reader = {
            read: jest.fn(() => {
                const item = downloadTray.items[0]
                seen.push({ status: item.status, loaded: item.loaded })
                const value = chunks.shift()
                return Promise.resolve(value ? { done: false, value } : { done: true })
            }),
        }
        let resolveFetch
        globalThis.fetch.mockReturnValue(new Promise(r => { resolveFetch = r }))

        const done = useDownload().downloadFile('/api/download/file')
        expect(downloadTray.items[0].status).toBe('preparing') // server still building the file

        resolveFetch(ok({ 'content-length': '100' }, { body: { getReader: () => reader } }))
        await done

        expect(seen).toEqual([
            { status: 'downloading', loaded: 0 },
            { status: 'downloading', loaded: 50 },
            { status: 'downloading', loaded: 100 },
        ])
        expect(downloadTray.items[0]).toMatchObject({ total: 100, status: 'started' })
    })

    it('shows the server message on a failed row (stays on the same page)', async () => {
        globalThis.fetch.mockResolvedValue({ ok: false, json: () => Promise.resolve({ message: 'Renew to download' }) })

        await useDownload().downloadFile('/api/download/file')

        expect(downloadTray.items[0]).toMatchObject({ status: 'failed', error: 'Renew to download' })
        expect(anchor.click).not.toHaveBeenCalled()
    })

    it('falls back to the generic message on a network error', async () => {
        globalThis.fetch.mockRejectedValue(new Error('Network error'))

        await useDownload().downloadFile('/api/download/file')

        expect(downloadTray.items[0]).toMatchObject({ status: 'failed', error: 'message.download_meta_failed' })
    })

    it('cancel aborts the request and the row stays cancelled', async () => {
        globalThis.fetch.mockImplementation((url, { signal }) => new Promise((resolve, reject) => {
            signal.addEventListener('abort', () => reject(new Error('aborted')))
        }))

        const done = useDownload().downloadFile('/api/download/file')
        cancelDownload(downloadTray.items[0])
        await done

        expect(downloadTray.items[0].status).toBe('cancelled')
    })

    it('retry runs a failed row again', async () => {
        globalThis.fetch.mockResolvedValueOnce({ ok: false, json: () => Promise.resolve({}) })
        await useDownload().downloadFile('/api/download/file')

        globalThis.fetch.mockResolvedValueOnce(ok({ 'content-disposition': 'attachment; filename="a.zip"' }))
        await retryDownload(downloadTray.items[0])

        expect(downloadTray.items[0].status).toBe('started')
    })

    it('cancel all stops only running rows; close drops finished rows', async () => {
        downloadTray.items = [
            { id: 1, status: 'preparing', controller: { abort: jest.fn() } },
            { id: 2, status: 'started' },
        ]
        cancelAllDownloads()
        expect(downloadTray.items.map(i => i.status)).toEqual(['cancelled', 'started'])

        downloadTray.items.push({ id: 3, status: 'downloading' })
        closeDownloadTray()
        expect(downloadTray.open).toBe(false)
        expect(downloadTray.items.map(i => i.id)).toEqual([3])
    })

    it('tracks which URL is downloading for the button spinner', async () => {
        let resolveFetch
        globalThis.fetch.mockReturnValue(new Promise(r => { resolveFetch = r }))
        const { downloadFile, downloadingUrl } = useDownload()

        const done = downloadFile('/api/download/file')
        expect(downloadingUrl.value).toBe('/api/download/file')
        resolveFetch(ok())
        await done

        expect(downloadingUrl.value).toBeNull()
    })
})
