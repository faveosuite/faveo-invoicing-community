import { useAlertStore } from '@/core/stores/alert.js'

export const errorHandler = (err, componentName = '') => {
    if (err.duplicateRequestRejection) {
        return;
    }

    const store  = useAlertStore()
    const status = err?.response?.status
    const data   = err?.response?.data

    // 422: Laravel validation errors — surface the top-level message via the global alert.
    // Field-level errors live in each form's vee-validate `useForm()` instance now.
    if (status === 422) {
        if (data?.message) {
            store.setAlert({ type: 'danger', message: data.message, component_name: componentName })
        }
        return
    }

    if ([400, 401, 429, 500].includes(status) && data?.message !== undefined) {
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
