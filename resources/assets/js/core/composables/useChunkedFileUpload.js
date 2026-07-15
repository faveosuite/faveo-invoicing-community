import { ref } from 'vue'
import http from '@/plugins/axios'

const CHUNK_SIZE = 5 * 1024 * 1024 // 5MB per request, well under any server post-size limit

// Splits a large file into small pieces so no single request is too big for
// the server to accept. Each piece is tagged with a shared id (dzuuid) plus
// its own index/total count — fields the backend's chunk-upload handler
// (Dropzone's convention) already understands, so nothing changes server-side.
async function uploadFileInChunks(fileToUpload, onProgress) {
    const totalChunks = Math.max(1, Math.ceil(fileToUpload.size / CHUNK_SIZE))
    const uuid = crypto.randomUUID()

    let lastResponse = null

    for (let index = 0; index < totalChunks; index++) {
        const start = index * CHUNK_SIZE
        const chunk = fileToUpload.slice(start, start + CHUNK_SIZE)

        const fd = new FormData()
        fd.append('file', chunk, fileToUpload.name)
        fd.append('dzuuid', uuid)
        fd.append('dzchunkindex', index)
        fd.append('dztotalchunkcount', totalChunks)
        fd.append('dztotalfilesize', fileToUpload.size)
        fd.append('dzchunksize', CHUNK_SIZE)
        fd.append('dzchunkbyteoffset', start)

        lastResponse = await http.post('/chunkupload', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
        onProgress?.(Math.round(((index + 1) / totalChunks) * 100))
    }

    return lastResponse
}

/**
 * Uploads a file the moment it's picked — independent of any surrounding
 * form's Save action — so a bad file (wrong zip layout, too large, etc.) is
 * caught and shown immediately, right at the file field, without needing the
 * rest of the form filled out first. Callers read `uploadedName`/
 * `uploadedForFile` at submit time to confirm the currently-picked file
 * actually finished uploading before using it.
 */
export function useChunkedFileUpload() {
    const file = ref(null)
    const uploading = ref(false)
    const uploadProgress = ref(0)
    const fileError = ref('')
    const uploadedName = ref('')
    const uploadedForFile = ref(null) // which File object uploadedName actually belongs to

    async function startUpload(selectedFile) {
        uploading.value = true
        uploadProgress.value = 0
        try {
            const up = await uploadFileInChunks(selectedFile, (pct) => { uploadProgress.value = pct })
            if (file.value !== selectedFile) return // a different file was picked meanwhile — ignore this result

            const filename = up.data?.name
            if (!filename) throw new Error(__('message.something_wrong'))
            uploadedName.value = filename
            uploadedForFile.value = selectedFile
        } catch (err) {
            if (file.value !== selectedFile) return
            fileError.value = err?.response?.data?.message || err.message || __('message.something_wrong')
        } finally {
            if (file.value === selectedFile) uploading.value = false
        }
    }

    function onFile(e) {
        const picked = e.target.files?.[0] ?? null
        file.value = picked
        // A newly picked file has never been uploaded yet — don't let a stale
        // filename/error from a previous attempt linger against it.
        uploadedName.value = ''
        uploadedForFile.value = null
        fileError.value = ''

        if (picked) startUpload(picked)
    }

    return { file, uploading, uploadProgress, fileError, uploadedName, uploadedForFile, onFile }
}
