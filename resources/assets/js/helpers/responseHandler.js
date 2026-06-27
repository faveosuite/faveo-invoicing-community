import { useAlertStore } from '@/core/stores/alert.js'

export const errorHandler = (err, componentName = '') => {
    if (err.duplicateRequestRejection) {
        return;
    }

    const store  = useAlertStore()
    const status = err?.response?.status
    const data   = err?.response?.data

    if (status === 403) {
        window.__router?.push('/403')
        return
    }

    if (status === 404) {
        window.__router?.push('/404')
        return
    }

    // Extract a human-readable message, prioritizing the structured data.message,
    // then validation errors, then axios error message, and finally a default fallback.
    let message = data?.message
    if (!message && data && typeof data === 'object') {
        if (data.errors) {
            message = Object.values(data.errors)
                .map(e => Array.isArray(e) ? e[0] : e)
                .join(' ')
        }
    }
    if (!message) {
        message = err.message || 'Something went wrong.'
    }

    store.setAlert({ type: 'danger', message, component_name: componentName })
}

/**
 * Apply Laravel validation errors from a failed request.
 * - An error whose key matches a known form field → set as a field-level error.
 * - An error for any other key (e.g. a hidden honeypot field) → surfaced in the
 *   top alert via errorHandler, so it's never silently swallowed.
 *
 * @param {Error}    err              Axios error
 * @param {Object}   opts
 * @param {Function} opts.setErrors   vee-validate setErrors()
 * @param {string[]} opts.fields      known/visible field names on the form
 * @param {string}   opts.component   component name for the alert store
 */
export const applyServerValidation = (err, { setErrors, fields = [], component = '' } = {}) => {
    const serverErrors = err?.response?.data?.errors
    if (!serverErrors) {
        errorHandler(err, component)
        return
    }

    const fieldMap = {}
    let hasUnknown = false
    Object.entries(serverErrors).forEach(([key, val]) => {
        const message = Array.isArray(val) ? val[0] : val
        if (fields.includes(key)) {
            fieldMap[key] = message
        } else {
            hasUnknown = true
        }
    })

    if (Object.keys(fieldMap).length) setErrors(fieldMap)
    if (hasUnknown) errorHandler(err, component) // show on top
}

export const successHandler = (res, componentName = '') => {
    if (res.status === 200 || res.status === 201) {
        if (res.data.message !== undefined) {
            useAlertStore().setAlert({ type: 'success', message: res.data.message, component_name: componentName })
        }
    }
}
