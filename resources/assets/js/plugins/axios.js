import axios from 'axios'
import { handleAuthError } from '@/helpers/authErrorHandler.js'

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
    // Prefer the XSRF-TOKEN cookie: Laravel refreshes it on every response, while the
    // <meta> token goes stale once the session rotates (re-login in another tab,
    // remember-me after expiry). EncryptCookies is off (Kernel.php), so the cookie
    // holds the raw token — if that middleware is ever enabled, revisit this.
    const cookie = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]*)/)?.[1]
    const csrf = cookie ? decodeURIComponent(cookie) : document.querySelector('meta[name="csrf-token"]')?.content
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

        // 401 — expired session, or a 401 faked by a proxy/WAF. handleAuthError
        // decides which; requests flagged _skipAuthRedirect (e.g. auth.hydrate(),
        // which expects a 401 for guests) are left alone.
        if (handleAuthError(error, loginUrl) === 'redirect') {
            // Deliberately never settles. We are navigating to /login, and any
            // rejection reaching the caller runs its catch first — that is what
            // used to flash "Request failed with status code 401" over the page
            // being torn down. The pending promise dies with the document.
            return new Promise(() => {})
        }

        // 419 — CSRF mismatch; the 419 response set a fresh XSRF-TOKEN cookie, so retry once
        if (status === 419 && !error.config?._csrfRetry) {
            error.config._csrfRetry = true
            return http(error.config)
        }

        throw error
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

// Progress-bar step sized to how many requests are in flight: few, some (≤6), many.
function byRequestLoad(few, some, many) {
    if (totalAxiosRequests <= 3) return few
    if (totalAxiosRequests <= 6) return some
    return many
}
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

    // Batch over (or abandoned): forget the counts so the next request starts a new bar.
    const resetCounts = () => {
        stopClearAxiosCount = false
        successfulResponses = 0
        totalAxiosRequests = 0
        loaderStarted = false
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
                resetCounts()
            } else if (setLoader) {
                progress.set(byRequestLoad(60, 50, 30))
                setLoader = false
            } else if (successfulResponses > totalAxiosRequests) {
                setTimeout(() => progress.finish(), 100)
                resetCounts()
            } else {
                progressStatus = progress.get()
                if (progressStatus === 0) {
                    loaderStarted = false
                    fromSetLoader = true
                    startProgress()
                }
                progress.increase(byRequestLoad(5, 4, 3))
            }

            return response
        },
        error => {
            progress.finish()
            resetCounts()
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
