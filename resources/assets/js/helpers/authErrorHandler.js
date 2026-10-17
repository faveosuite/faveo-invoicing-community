/**
 * Auth-failure handling for the axios response interceptor (plugins/axios.js).
 *
 * Two jobs: tell a genuine backend 401 from one injected by the network edge,
 * and take the session-expired flow off the caller's hands entirely.
 */

const MARKER = 'x-faveo-app'

/**
 * Whether a response actually came from this application.
 *
 * Proxies, WAFs (mod_security, Cloudflare) and captive portals answer with
 * their own 401 page when connectivity breaks, and those must not log anyone
 * out. Our responses carry the X-Faveo-App header (MarkAppResponse middleware);
 * the JSON error envelope is kept as a fallback for responses cached before
 * that header existed.
 */
export function isAppResponse(response) {
    const headers = response?.headers ?? {}
    const marker = typeof headers.get === 'function' ? headers.get(MARKER) : headers[MARKER]

    if (marker) return true

    const data = response?.data
    return typeof data === 'object' && data !== null && ('error' in data || data.success === false)
}

/**
 * Handle a 401 from the response interceptor.
 *
 * @returns {'network'|'redirect'|false}
 *   'network'  — not ours; flagged as a connectivity error, reject as normal.
 *   'redirect' — genuine expired session; navigation to login has started and
 *                the caller must NOT see this error (see plugins/axios.js).
 *   false      — not handled here.
 */
export function handleAuthError(error, loginUrl) {
    if (error?.response?.status !== 401 || error.config?._skipAuthRedirect) {
        return false
    }

    if (!isAppResponse(error.response)) {
        // Not ours, so nobody's session actually ended. Flagged rather than
        // alerted here: the admin layout has no shared alert slot, so the
        // message belongs in whichever component made the call (errorHandler
        // turns this flag into the connectivity message).
        error.networkError = true

        return 'network'
    }

    if (globalThis.location.pathname.endsWith('/login')) {
        return false
    }

    // ?session_expired=1 rather than the server's message: the login page owns
    // the wording so it stays translated, and an untranslated backend string
    // never lands in the address bar.
    globalThis.location.href = `${loginUrl}?session_expired=1`

    return 'redirect'
}
