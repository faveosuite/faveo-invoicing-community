import axios from 'axios'

// Pick whichever app mount is present: admin (#app-root) or client (#app-client).
const el = document.getElementById('app-root') ?? document.getElementById('app-client')

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
    async error => {
        const status    = error.response?.status
        const loginUrl  = (el?.dataset?.baseUrl ?? '') + '/login'

        // 401 — session expired; skip if the caller flagged this request
        // (e.g. auth.hydrate() which expects 401 for guests)
        if (status === 401 && !error.config?._skipAuthRedirect && !globalThis.location.href.endsWith('/login')) {
            globalThis.location.href = loginUrl
        }

        // 419 — CSRF token expired; refresh the token and retry the request once
        if (status === 419 && !error.config?._csrfRetry) {
            const freshToken = document.querySelector('meta[name="csrf-token"]')?.content
            if (freshToken) {
                error.config.headers['X-CSRF-TOKEN'] = freshToken
                error.config._csrfRetry = true
                return http(error.config)
            }
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
// ── Global progress-bar loader state ────────────────────────────────────────
let totalAxiosRequests = 0
let successfulResponses = 0
let loaderStarted = false
let setLoader = false
let progressStatus = 0
let stopClearAxiosCount = true
let fromSetLoader = false

/**
 * Call this once after the Vue app is created and VueProgressBar is installed.
 * Adds request/response interceptors to the shared `http` instance that drive
 * the top progress bar automatically for every API call.
 *
 * Usage in main.js:
 *   import { setupLoaderInterceptors } from './plugins/axios.js'
 *   // after app.use(VueProgressBar, options):
 *   setupLoaderInterceptors(app.config.globalProperties.$Progress)
 */
export function setupLoaderInterceptors(progress) {
    const clearAxiosCount = () => {
        stopClearAxiosCount = true
        setTimeout(() => {
            if (stopClearAxiosCount) {
                progressStatus = progress.get()
                if (progressStatus !== 0) {
                    progress.finish()
                    stopClearAxiosCount = false
                    totalAxiosRequests = 0
                    successfulResponses = 0
                    loaderStarted = false
                }
            }
        }, 90000)
    }

    const startProgress = () => {
        if (!loaderStarted) {
            progress.start()
            if (!fromSetLoader) successfulResponses = 0
            loaderStarted = true
            setLoader = true
            fromSetLoader = false
            clearAxiosCount()
        }
    }

    http.interceptors.request.use(config => {
        totalAxiosRequests++
        startProgress()
        return config
    })

    http.interceptors.response.use(
        response => {
            if (totalAxiosRequests !== 0) successfulResponses++

            if (successfulResponses === totalAxiosRequests && response.status === 200) {
                if (successfulResponses === 1 && totalAxiosRequests === 1) {
                    progress.finish()
                } else {
                    setTimeout(() => progress.finish(), 500)
                }
                if (stopClearAxiosCount) stopClearAxiosCount = false
                successfulResponses = 0
                totalAxiosRequests = 0
                loaderStarted = false
            } else if (setLoader) {
                const progressValue = totalAxiosRequests <= 3 ? 60 : (totalAxiosRequests <= 6 ? 50 : 30)
                progress.set(progressValue)
                setLoader = false
            } else if (successfulResponses > totalAxiosRequests) {
                setTimeout(() => progress.finish(), 100)
                if (stopClearAxiosCount) stopClearAxiosCount = false
                successfulResponses = 0
                totalAxiosRequests = 0
                loaderStarted = false
            } else {
                progressStatus = progress.get()
                if (progressStatus === 0) {
                    loaderStarted = false
                    fromSetLoader = true
                    startProgress()
                }
                const increaseValue = totalAxiosRequests <= 3 ? 5 : (totalAxiosRequests <= 6 ? 4 : 3)
                progress.increase(increaseValue)
            }

            return response
        },
        error => {
            progress.finish()
            if (stopClearAxiosCount) stopClearAxiosCount = false
            successfulResponses = 0
            totalAxiosRequests = 0
            loaderStarted = false
            return Promise.reject(error)
        }
    )
}

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
