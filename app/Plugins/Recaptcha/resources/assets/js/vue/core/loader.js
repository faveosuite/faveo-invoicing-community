/**
 * grecaptcha script loader.
 *
 * Google exposes a single `window.grecaptcha` global. Loading api.js more than
 * once (or with conflicting params) leads to "reCAPTCHA already rendered" and
 * duplicate-badge bugs, so this module owns a single load and hands every caller
 * the same promise.
 *
 * Key insight that lets us support the v3 -> v2 fallback with ONE script tag:
 * once api.js is loaded, both `grecaptcha.render()` (v2, explicit) and
 * `grecaptcha.execute(siteKey, {action})` (v3) are available regardless of the
 * `render` param used. So we load with `render=<v3SiteKey>` when the primary
 * mode is v3 (gives us the badge) and `render=explicit` otherwise; the v2
 * fallback widget can still be rendered explicitly on top of either.
 */

const SCRIPT_URL = 'https://www.google.com/recaptcha/api.js'
const READY_TIMEOUT = 15000

let loadPromise = null
let currentRender = null // the `render` param the live script was loaded with

/**
 * @param {object}  opts
 * @param {string}  opts.render  v3 site key, or 'explicit' for v2.
 * @param {string} [opts.hl]     language code.
 * @param {string} [opts.badge]  badge position for v3 (bottomright|bottomleft|inline).
 * @returns {Promise<object>}    resolves with the ready `window.grecaptcha`.
 */
export function loadRecaptcha({ render = 'explicit', hl = 'en', badge = 'bottomright' } = {}) {
    // A v3 script is bound to its site key at load time, so a different `render`
    // (e.g. the admin preview switching keys/version) requires a full reload.
    if (loadPromise && currentRender !== null && currentRender !== render) {
        teardownRecaptcha()
    }

    if (loadPromise) {
        return loadPromise
    }

    // Script may already be on the page (e.g. admin preview loaded it first).
    if (window.grecaptcha && (window.grecaptcha.render || window.grecaptcha.execute)) {
        currentRender = render
        loadPromise = waitForReady(window.grecaptcha)
        return loadPromise
    }

    currentRender = render

    loadPromise = new Promise((resolve, reject) => {
        const params = new URLSearchParams({ render })
        if (hl) params.set('hl', hl)
        // `badge` only applies to a v3 (render=siteKey) load. 'inline' is rendered by us.
        if (render !== 'explicit' && badge && badge !== 'inline') {
            params.set('badge', badge)
        }

        const script = document.createElement('script')
        script.src = `${SCRIPT_URL}?${params.toString()}`
        script.async = true
        script.defer = true

        script.onload = () => {
            waitForReady(window.grecaptcha).then(resolve).catch(reject)
        }
        script.onerror = () => {
            loadPromise = null
            currentRender = null
            reject(new Error('Failed to load the reCAPTCHA script.'))
        }

        document.head.appendChild(script)
    })

    return loadPromise
}

/**
 * Resolve once grecaptcha is fully ready (handles both v2 and v3 init paths).
 */
function waitForReady(grecaptcha) {
    return new Promise((resolve, reject) => {
        const started = performance.now()

        const settle = () => resolve(grecaptcha)

        // v3 exposes ready(); v2 explicit exposes render() synchronously.
        if (grecaptcha && typeof grecaptcha.ready === 'function') {
            grecaptcha.ready(settle)
            return
        }

        const poll = () => {
            if (window.grecaptcha && (window.grecaptcha.render || window.grecaptcha.execute)) {
                settle()
            } else if (performance.now() - started > READY_TIMEOUT) {
                reject(new Error('reCAPTCHA failed to become ready in time.'))
            } else {
                setTimeout(poll, 100)
            }
        }
        poll()
    })
}

/**
 * Forget the cached load so the next call re-evaluates `window.grecaptcha`.
 * Does not remove the script tag.
 */
export function resetLoader() {
    loadPromise = null
    currentRender = null
}

/**
 * Fully remove the loaded reCAPTCHA from the page: drop the script tag, the
 * floating badge and the global, and reset loader state. Used when the script
 * must be reloaded with a different site key (admin preview key switching).
 */
export function teardownRecaptcha() {
    document
        .querySelectorAll('script[src^="https://www.google.com/recaptcha/api.js"]')
        .forEach(el => el.remove())
    document.querySelectorAll('.grecaptcha-badge').forEach(el => el.remove())
    try {
        delete window.grecaptcha
    } catch {
        window.grecaptcha = undefined
    }
    loadPromise = null
    currentRender = null
}
