import { ref, reactive } from 'vue'
import { __ } from '@/plugins/i18n'

// Shared state for the download tray (DownloadTray.vue), Google-Drive style. Module-level so a
// download started on any page shows in the one tray. Each item moves through:
//   preparing   – waiting for the server, which builds/stamps the file (time unknown)
//   downloading – bytes arriving (real % when the server sends Content-Length)
//   started     – handed to the browser, which saves it ("see your browser downloads")
//   failed      – server refused or the request broke; message shown, can retry
//   cancelled   – the customer cancelled it
export const downloadTray = reactive({
    items:     [],
    open:      false,
    collapsed: false,
})

const ACTIVE = ['preparing', 'downloading']
export const isActiveDownload = (item) => ACTIVE.includes(item.status)

let nextId = 0

function filenameFrom(response) {
    const disposition = response.headers.get('content-disposition') ?? ''
    const match       = disposition.match(/filename="([^"]+)"|filename=([^;]+)/i)

    return (match?.[1] ?? match?.[2])?.trim() ?? null
}

async function readWithProgress(response, item) {
    item.status = 'downloading'
    item.total  = Number(response.headers.get('content-length')) || 0

    if (!response.body?.getReader) return response.blob()

    const reader = response.body.getReader()
    const chunks = []
    for (;;) {
        const { done, value } = await reader.read()
        if (done) break
        chunks.push(value)
        item.loaded += value.length
    }

    return new Blob(chunks, { type: response.headers.get('content-type') ?? '' })
}

// No progress for these: the browser's own download bar shows it.
function handToBrowser(item) {
    const a = document.createElement('a')
    a.href  = item.url
    a.click()
    item.status = 'started'
}

async function run(item) {
    Object.assign(item, { status: 'preparing', startedAt: Date.now(), loaded: 0, total: 0, error: '' })
    item.controller = new AbortController()

    // Another site's file (GitHub release links in the versions list) can't be fetched from
    // here — the browser blocks cross-site fetch (CORS). A plain download has no such check.
    if (new URL(item.url, globalThis.location.href).origin !== globalThis.location.origin) {
        handToBrowser(item)
        return
    }

    try {
        // Don't follow redirects: GitHub-hosted releases redirect to codeload.github.com, which
        // doesn't allow cross-site fetch (CORS), so reading it here always failed.
        const response = await fetch(item.url, { signal: item.controller.signal, redirect: 'manual' })

        if (response.type === 'opaqueredirect') {
            handToBrowser(item) // let the browser follow the redirect itself
            return
        }

        if (! response.ok) {
            const json = await response.json().catch(() => ({}))
            Object.assign(item, { status: 'failed', error: json.message ?? __('message.download_meta_failed') })
            return
        }

        const filename = filenameFrom(response)
        if (filename) item.name = filename
        const blob = await readWithProgress(response, item)

        const a    = document.createElement('a')
        a.href     = URL.createObjectURL(blob)
        a.download = filename ?? 'download.zip'
        a.click()
        URL.revokeObjectURL(a.href)

        item.status = 'started'
    } catch {
        // An abort lands here too — keep the "cancelled" the cancel action already set.
        if (item.status !== 'cancelled') {
            Object.assign(item, { status: 'failed', error: __('message.download_meta_failed') })
        }
    }
}

export function cancelDownload(item) {
    item.status = 'cancelled'
    item.controller?.abort()
}

export function retryDownload(item) {
    return run(item)
}

export function cancelAllDownloads() {
    downloadTray.items.filter(isActiveDownload).forEach(cancelDownload)
}

// Hides the tray and drops finished rows; downloads still running keep going and
// reopen the tray when the next one starts.
export function closeDownloadTray() {
    downloadTray.open  = false
    downloadTray.items = downloadTray.items.filter(isActiveDownload)
}

export function useDownload() {
    // Holds the URL currently downloading (not just a boolean) so a caller
    // rendering one button per row can tell which row's spinner to show.
    const downloadingUrl = ref(null)

    // name: shown until the server's own filename arrives with the response.
    const downloadFile = async (url, name = '') => {
        downloadingUrl.value = url
        downloadTray.items.unshift({ id: ++nextId, url, name: name || __('message.download_default_name') })
        Object.assign(downloadTray, { open: true, collapsed: false })

        try {
            await run(downloadTray.items[0])
        } finally {
            downloadingUrl.value = null
        }
    }

    return { downloadFile, downloadingUrl }
}
