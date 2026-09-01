import { useAlertStore } from '@/core/stores/alert.js'

const DEFAULT_ERROR_MESSAGE = 'Something went wrong.'

/**
 * @typedef {Object} ApiError
 * @property {boolean} [duplicateRequestRejection]  set by the axios interceptor when this
 *                                                   request was superseded by a newer one
 * @property {string}  [message]
 * @property {Object}  [response]
 * @property {number}  [response.status]
 * @property {*}       [response.data]
 */

// Laravel validation values arrive as either a string or an array of strings — collapse to one.
const normalizeMessage = (value) => Array.isArray(value) ? (value[0] ?? '') : String(value ?? '')

/**
 * Handle a 412 (RequestJsonValidation) field-errors payload: { field: message|[message] }.
 * `excludeFields` are fields with no inline error UI to bind to (e.g. an ImageUpload's
 * FormData key) — those are alerted instead of handed to setErrors.
 *
 * @returns {boolean} whether the error was fully handled (caller should stop here).
 */
function handleValidationError(data, componentName, setErrors, excludeFields) {
    if (!data?.message || typeof data.message !== 'object' || Array.isArray(data.message)) {
        return false
    }

    const excluded = new Set(excludeFields)
    const inlineErrors = {}
    const alertMessages = []

    for (const [field, value] of Object.entries(data.message)) {
        const message = normalizeMessage(value)
        if (!message) continue

        if (excluded.has(field)) {
            alertMessages.push(message)
        } else {
            inlineErrors[field] = message
        }
    }

    if (alertMessages.length) {
        useAlertStore().setAlert({ type: 'danger', message: alertMessages.join(' '), component_name: componentName })
    }

    const hasInlineErrors = Object.keys(inlineErrors).length > 0
    if (hasInlineErrors && setErrors) {
        setErrors(inlineErrors)
    }

    return alertMessages.length > 0 || (hasInlineErrors && Boolean(setErrors))
}

/**
 * Central Axios error handler: redirects on 403/404, maps a 412's field errors onto
 * setErrors (or the alert box for `excludeFields`), and otherwise toasts the message.
 *
 * @param {ApiError} err
 * @param {string}   [componentName]           alert-store component key
 * @param {Object}   [validation]
 * @param {Function} [validation.setErrors]     vee-validate setErrors()
 * @param {string[]} [validation.excludeFields] field names to alert instead of inline
 */
export const errorHandler = (err, componentName = '', { setErrors, excludeFields = [] } = {}) => {
    // No null-guard here on purpose — every real caller passes a caught error object;
    // a bare `null`/`undefined` is a caller bug that should surface, not be swallowed.
    if (err.duplicateRequestRejection) return

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
    if (status === 412 && handleValidationError(data, componentName, setErrors, excludeFields)) {
        return
    }

    let message = data?.message
    if (message && typeof message === 'object') {
        message = Object.values(message).map(normalizeMessage).filter(Boolean).join(' ')
    }
    message ||= err?.message || DEFAULT_ERROR_MESSAGE

    useAlertStore().setAlert({ type: 'danger', message, component_name: componentName })
}

export const successHandler = (res, componentName = '') => {
    const message = res?.data?.message
    if ((res?.status === 200 || res?.status === 201) && message !== undefined) {
        useAlertStore().setAlert({ type: 'success', message, component_name: componentName })
    }
}
