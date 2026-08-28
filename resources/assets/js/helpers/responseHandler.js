import { useAlertStore } from '@/core/stores/alert.js'

/**
 * @param {Error}     err                Axios error
 * @param {string}    componentName      component name for the alert store
 * @param {Object}    [validation]
 * @param {Function}  validation.setErrors  vee-validate setErrors() — when given, a 412's field
 *                                           errors (RequestJsonValidation / Handler::invalidJson(),
 *                                           sent as message: { field: "msg" }) are mapped inline
 *                                           (by field name) instead of only toasted. Every
 *                                           returned field is mapped as-is.
 */
export const errorHandler = (err, componentName = '', { setErrors } = {}) => {
    if (err.duplicateRequestRejection) {
        return;
    }

    const store  = useAlertStore()
    const status = err?.response?.status
    const data   = err?.response?.data

    if (status === 403) {
        globalThis.__router?.push('/403')
        return
    }

    if (status === 404) {
        globalThis.__router?.push('/404')
        return
    }

    if (status === 412 && data?.message && typeof data.message === 'object' && setErrors) {
        setErrors(Object.fromEntries(
            Object.entries(data.message).map(([key, val]) => [key, Array.isArray(val) ? val[0] : val])
        ))
        return
    }

    // Extract a human-readable message, prioritizing the structured data.message,
    // then axios error message, and finally a default fallback.
    let message = data?.message
    if (message && typeof message === 'object') {
        // Object-shaped message (412 field errors) with no setErrors to catch it — flatten for the toast.
        message = Object.values(message).map(e => Array.isArray(e) ? e[0] : e).join(' ')
    }
    if (!message) {
        message = err.message || 'Something went wrong.'
    }

    store.setAlert({ type: 'danger', message, component_name: componentName })
}

export const successHandler = (res, componentName = '') => {
    if (res.status === 200 || res.status === 201) {
        if (res.data.message !== undefined) {
            useAlertStore().setAlert({ type: 'success', message: res.data.message, component_name: componentName })
        }
    }
}
