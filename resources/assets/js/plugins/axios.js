import axios from 'axios'

const el = document.getElementById('app-root')

// Base URL is the Laravel app root (e.g. https://domain/faveo/public)
// so all route paths can be used as-is without a /api prefix.
const baseURL = el?.dataset?.baseUrl ?? ''

const http = axios.create({
    baseURL,
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    },
    withCredentials: true, // send session cookie on every request
})

http.interceptors.request.use(config => {
    // Attach CSRF token for Laravel web routes
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content
    if (csrf) {
        config.headers['X-CSRF-TOKEN'] = csrf
    }
    return config
})

http.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            // Session expired — redirect to client panel login
            window.location.href = (el?.dataset?.baseUrl ?? '') + '/login'
        }
        return Promise.reject(error)
    }
)

/**
 * Extract a human-readable message from an Axios error response.
 *
 * Status conventions:
 *   200  { success, message }
 *   404  { success, message }
 *   422  { message, errors: { field: [msg, ...] } }
 *   412  { message, errors: { field: [msg, ...] } }  (field validation)
 *   500  { success, message }
 */
export function parseErrorMessage(err) {
    const res    = err.response?.data
    const status = err.response?.status

    if ((status === 422 || status === 412) && res?.errors) {
        const msgs = Object.values(res.errors).map(e => e[0]).join(' ')
        return msgs || res.message || 'Validation failed.'
    }

    return res?.message ?? 'Something went wrong.'
}

/**
 * Extract field-level validation errors into the shape vee-validate expects.
 *
 * Returns an object like { fieldName: 'First error message' } for use with
 * vee-validate's setErrors() on 422/412 responses.
 * Returns null for non-validation errors so callers can fall back to
 * parseErrorMessage().
 *
 * Usage in a form page:
 *   try { ... } catch (err) {
 *       const fieldErrors = parseValidationErrors(err)
 *       if (fieldErrors) setErrors(fieldErrors)
 *       else notify(parseErrorMessage(err), 'danger')
 *   }
 */
export function parseValidationErrors(err) {
    const res    = err.response?.data
    const status = err.response?.status

    if ((status === 422 || status === 412) && res?.errors) {
        return Object.fromEntries(
            Object.entries(res.errors).map(([field, msgs]) => [field, msgs[0]])
        )
    }

    return null
}

export default http
