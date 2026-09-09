import { ref } from 'vue'
import { useAlertStore } from '@/core/stores/alert'
import { __ } from '@/plugins/i18n'

export function useDownload(componentName) {
    const alertStore = useAlertStore()
    // Holds the URL currently downloading (not just a boolean) so a caller
    // rendering one button per row can tell which row's spinner to show.
    const downloadingUrl = ref(null)

    const showError = (message) => {
        alertStore.setAlert({
            message,
            type:           'danger',
            component_name: componentName,
        })
    }

    const downloadFile = async (url) => {
        downloadingUrl.value = url
        try {
            const response = await fetch(url)

            if (! response.ok) {
                const json = await response.json().catch(() => ({}))
                showError(json.message ?? __('message.file_not_exist'))
                return
            }

            const blob        = await response.blob()
            const disposition = response.headers.get('content-disposition') ?? ''
            const match       = disposition.match(/filename="([^"]+)"|filename=([^;]+)/i)
            const filename    = (match?.[1] ?? match?.[2])?.trim() ?? 'download.zip'

            const a    = document.createElement('a')
            a.href     = URL.createObjectURL(blob)
            a.download = filename
            a.click()
            URL.revokeObjectURL(a.href)
        } catch {
            showError(__('message.file_not_exist'))
        } finally {
            downloadingUrl.value = null
        }
    }

    return { downloadFile, downloadingUrl }
}
