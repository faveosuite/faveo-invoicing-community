import { useAlertStore } from '@/core/stores/alert.js'

export const errorHandler = (err, componentName = '') => {
    if (err.duplicateRequestRejection) {
        return;
    }

    const store  = useAlertStore()
    const status = err?.response?.status
    const data   = err?.response?.data

    if ([400, 422, 401, 429, 500].includes(status) && data?.message !== undefined) {
        store.setAlert({ type: 'danger', message: data.message, component_name: componentName })
    }

    if (status === 412 && data?.message !== undefined) {
        store.setAlert({ type: 'danger', message: data.message, component_name: componentName })
    }

    if (status === 404) {
        window.location = window.axios.defaults.baseURL + '/404'
    }
}

export const successHandler = (res, componentName = '') => {
    if (res.status === 200 || res.status === 201) {
        if (res.data.message !== undefined) {
            useAlertStore().setAlert({ type: 'success', message: res.data.message, component_name: componentName })
        }
    }
}
