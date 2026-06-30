jest.mock('@/plugins/i18n', () => ({
    __: (key) => key,
}))

import { useDownload } from '@/core/composables/useDownload.js'
import { useAlertStore } from '@/core/stores/alert'

describe('useDownload', () => {
    let alertStore
    let setAlertSpy

    beforeEach(() => {
        alertStore = useAlertStore()
        // Spy on the real action since jest.setup.js uses createPinia() (not createTestingPinia)
        setAlertSpy = jest.spyOn(alertStore, 'setAlert')

        globalThis.fetch = jest.fn()
        globalThis.URL.createObjectURL = jest.fn(() => 'blob:url')
        globalThis.URL.revokeObjectURL = jest.fn()

        // Mock document.createElement('a') click behavior
        const mockAnchor = {
            href: '',
            download: '',
            click: jest.fn(),
        }
        jest.spyOn(document, 'createElement').mockImplementation((tag) => {
            if (tag === 'a') return mockAnchor
            return document.createElement.wrappedMethod
                ? document.createElement.wrappedMethod(tag)
                : Object.getPrototypeOf(document).createElement.call(document, tag)
        })
    })

    afterEach(() => {
        jest.restoreAllMocks()
    })

    it('returns downloadFile function', () => {
        const result = useDownload('TestComponent')
        expect(result).toHaveProperty('downloadFile')
        expect(typeof result.downloadFile).toBe('function')
    })

    it('downloads file successfully and extracts filename from header', async () => {
        const mockBlob = new Blob(['file content'])
        globalThis.fetch.mockResolvedValue({
            ok: true,
            blob: () => Promise.resolve(mockBlob),
            headers: { get: () => 'attachment; filename="report.zip"' },
        })

        const { downloadFile } = useDownload('TestComponent')
        await downloadFile('/api/download/file')

        expect(fetch).toHaveBeenCalledWith('/api/download/file')
        expect(URL.createObjectURL).toHaveBeenCalledWith(mockBlob)
        expect(URL.revokeObjectURL).toHaveBeenCalledWith('blob:url')
    })

    it('uses fallback filename when content-disposition header is missing', async () => {
        const mockBlob = new Blob(['data'])
        globalThis.fetch.mockResolvedValue({
            ok: true,
            blob: () => Promise.resolve(mockBlob),
            headers: { get: () => null },
        })

        const mockAnchor = { href: '', download: '', click: jest.fn() }
        document.createElement.mockReturnValue(mockAnchor)

        const { downloadFile } = useDownload('TestComponent')
        await downloadFile('/api/download/file')

        expect(mockAnchor.download).toBe('download.zip')
    })

    it('uses fallback filename when content-disposition has no filename', async () => {
        const mockBlob = new Blob(['data'])
        globalThis.fetch.mockResolvedValue({
            ok: true,
            blob: () => Promise.resolve(mockBlob),
            headers: { get: () => 'attachment' },
        })

        const mockAnchor = { href: '', download: '', click: jest.fn() }
        document.createElement.mockReturnValue(mockAnchor)

        const { downloadFile } = useDownload('TestComponent')
        await downloadFile('/api/download/file')

        expect(mockAnchor.download).toBe('download.zip')
    })

    it('calls alertStore.setAlert with danger when response is not ok and has JSON message', async () => {
        globalThis.fetch.mockResolvedValue({
            ok: false,
            json: () => Promise.resolve({ message: 'File not found' }),
        })

        const { downloadFile } = useDownload('TestComponent')
        await downloadFile('/api/download/missing')

        expect(setAlertSpy).toHaveBeenCalledWith({
            message:        'File not found',
            type:           'danger',
            component_name: 'TestComponent',
        })
    })

    it('calls alertStore.setAlert with i18n key when response not ok and JSON has no message', async () => {
        globalThis.fetch.mockResolvedValue({
            ok:   false,
            json: () => Promise.resolve({}),
        })

        const { downloadFile } = useDownload('TestComponent')
        await downloadFile('/api/download/missing')

        expect(setAlertSpy).toHaveBeenCalledWith({
            message:        'message.file_not_exist',
            type:           'danger',
            component_name: 'TestComponent',
        })
    })

    it('calls alertStore.setAlert when response.json() throws', async () => {
        globalThis.fetch.mockResolvedValue({
            ok:   false,
            json: () => Promise.reject(new Error('not json')),
        })

        const { downloadFile } = useDownload('TestComponent')
        await downloadFile('/api/download/missing')

        expect(setAlertSpy).toHaveBeenCalledWith({
            message:        'message.file_not_exist',
            type:           'danger',
            component_name: 'TestComponent',
        })
    })

    it('calls alertStore.setAlert when fetch throws a network error', async () => {
        globalThis.fetch.mockRejectedValue(new Error('Network error'))

        const { downloadFile } = useDownload('TestComponent')
        await downloadFile('/api/download/file')

        expect(setAlertSpy).toHaveBeenCalledWith({
            message:        'message.file_not_exist',
            type:           'danger',
            component_name: 'TestComponent',
        })
    })

    it('uses the componentName passed to useDownload in the alert', async () => {
        globalThis.fetch.mockRejectedValue(new Error('fail'))

        const { downloadFile } = useDownload('InvoiceList')
        await downloadFile('/api/invoice/export')

        expect(setAlertSpy).toHaveBeenCalledWith(
            expect.objectContaining({ component_name: 'InvoiceList' }),
        )
    })
})
