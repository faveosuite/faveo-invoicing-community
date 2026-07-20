import { useAlertStore } from '@/core/stores/alert'
import { __ } from '@/plugins/i18n'

export function useDownload(componentName) {
    const alertStore = useAlertStore()

    const showError = (message) => {
        alertStore.setAlert({
            message,
            type:           'danger',
            component_name: componentName,
        })
    }

    const downloadFile = async (url) => {
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
        }
    }

    return { downloadFile }
}
