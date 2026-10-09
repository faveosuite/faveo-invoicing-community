import { useChunkedFileUpload } from '@/core/composables/useChunkedFileUpload'

const CHUNK_SIZE = 5 * 1024 * 1024

function makeFile(name, sizeBytes = 7) {
    return new File([new Uint8Array(sizeBytes)], name, { type: 'application/zip' })
}

function fileInputEvent(file) {
    return { target: { files: file ? [file] : [] } }
}

describe('useChunkedFileUpload', () => {
    beforeEach(() => {
        globalThis.mockHttp.onPost(/\/chunkupload/).reply(200, { name: 'uploaded-file.zip' })
    })

    it('starts with empty/default state', () => {
        const { file, uploading, uploadProgress, fileError, uploadedName, uploadedForFile } = useChunkedFileUpload()
        expect(file.value).toBeNull()
        expect(uploading.value).toBe(false)
        expect(uploadProgress.value).toBe(0)
        expect(fileError.value).toBe('')
        expect(uploadedName.value).toBe('')
        expect(uploadedForFile.value).toBeNull()
    })

    it('onFile with no file selected clears state', () => {
        const { file, onFile } = useChunkedFileUpload()
        onFile(fileInputEvent(null))
        expect(file.value).toBeNull()
    })

    it('onFile sets file immediately and starts uploading', () => {
        const { file, uploading, onFile } = useChunkedFileUpload()
        const picked = makeFile('test.zip')
        onFile(fileInputEvent(picked))
        expect(file.value).toBe(picked)
        expect(uploading.value).toBe(true)
    })

    it('onFile resets any stale error/uploadedName from a previous attempt', () => {
        const { fileError, uploadedName, uploadedForFile, onFile } = useChunkedFileUpload()
        fileError.value = 'previous error'
        uploadedName.value = 'previous-file.zip'
        uploadedForFile.value = makeFile('previous.zip')
        onFile(fileInputEvent(makeFile('new.zip')))
        expect(fileError.value).toBe('')
        expect(uploadedName.value).toBe('')
    })

    it('resolves uploadedName/uploadedForFile and clears uploading on success', async () => {
        const { file, uploading, uploadedName, uploadedForFile, onFile } = useChunkedFileUpload()
        const picked = makeFile('test.zip')
        onFile(fileInputEvent(picked))
        await flushPromises()
        expect(uploading.value).toBe(false)
        expect(uploadedName.value).toBe('uploaded-file.zip')
        expect(uploadedForFile.value).toBe(file.value)
    })

    it('splits a file larger than the chunk size into multiple upload requests', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onPost(/\/chunkupload/).reply(200, { name: 'big-file.zip' })
        const { uploadProgress, uploadedName, onFile } = useChunkedFileUpload()
        onFile(fileInputEvent(makeFile('big.zip', CHUNK_SIZE + 1024)))
        await flushPromises()
        expect(globalThis.mockHttp.history.post.length).toBe(2)
        expect(uploadProgress.value).toBe(100)
        expect(uploadedName.value).toBe('big-file.zip')
    })

    it('sets fileError from the server error message when the upload request fails', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onPost(/\/chunkupload/).reply(422, { message: 'File too large' })
        const { uploading, fileError, uploadedName, onFile } = useChunkedFileUpload()
        onFile(fileInputEvent(makeFile('bad.zip')))
        await flushPromises()
        expect(fileError.value).toBe('File too large')
        expect(uploadedName.value).toBe('')
        expect(uploading.value).toBe(false)
    })

    it('sets a generic fileError when the server response has no file name', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onPost(/\/chunkupload/).reply(200, {})
        const { fileError, uploadedName, onFile } = useChunkedFileUpload()
        onFile(fileInputEvent(makeFile('test.zip')))
        await flushPromises()
        expect(fileError.value).toBeTruthy()
        expect(uploadedName.value).toBe('')
    })

    // A slow upload that's abandoned for a newer file pick must not clobber
    // the newer pick's result once its own (slower) request finally resolves.
    it('ignores a stale upload result from a file that is no longer the current selection', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onPost(/\/chunkupload/).reply(200, { name: 'uploaded-file.zip' })
        const { uploadedForFile, uploadedName, fileError, onFile } = useChunkedFileUpload()

        const slow = makeFile('slow.zip', CHUNK_SIZE + 1024) // 2 chunks — resolves after `quick`
        const quick = makeFile('quick.zip') // 1 chunk — resolves first

        onFile(fileInputEvent(slow))
        onFile(fileInputEvent(quick)) // supersedes `slow` before its chunks resolve
        await flushPromises()

        expect(uploadedForFile.value).toBe(quick)
        expect(uploadedName.value).toBe('uploaded-file.zip')
        expect(fileError.value).toBe('')
    })
})
