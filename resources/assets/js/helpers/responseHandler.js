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
