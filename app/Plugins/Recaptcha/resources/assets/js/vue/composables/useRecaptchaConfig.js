import { reactive, readonly } from 'vue'
import http from '@/plugins/axios'
import { DEFAULTS, FAILOVER, versionToMode } from '../core/constants.js'

/**
 * Fetches the guest-safe reCAPTCHA config from the backend and caches it for the
 * lifetime of the page. Every consumer shares the same reactive object, so the
 * network request fires at most once regardless of how many forms ask for it.
 *
 * The endpoint never returns secret keys — verification stays server-side in
 * the RecaptchaMiddleware.
 */

const ENDPOINT = '/recaptcha/config'

const state = reactive({
    loading: false,
    loaded: false,
    error: null,
    config: {
        enabled: false,
        version: null,
        mode: DEFAULTS.mode,
        failoverAction: FAILOVER.NONE,
        v2SiteKey: '',
        v3SiteKey: '',
        theme: DEFAULTS.theme,
        size: DEFAULTS.size,
        badge: DEFAULTS.badge,
        lang: DEFAULTS.lang,
    },
})

let inflight = null

function applyResponse(data = {}) {
    Object.assign(state.config, {
        enabled: Boolean(data.enabled),
        version: data.version ?? null,
        mode: versionToMode(data.version),
        failoverAction: data.failover_action ?? FAILOVER.NONE,
        v2SiteKey: data.v2_site_key ?? '',
        v3SiteKey: data.v3_site_key ?? '',
        theme: data.theme ?? DEFAULTS.theme,
        size: data.size ?? DEFAULTS.size,
        badge: data.badge_position ?? DEFAULTS.badge,
        lang: data.lang ?? DEFAULTS.lang,
    })
}

export function fetchRecaptchaConfig(force = false) {
    if (inflight && !force) {
        return inflight
    }
    if (state.loaded && !force) {
        return Promise.resolve(state.config)
    }

    state.loading = true
    state.error = null

    inflight = http
        .get(ENDPOINT)
        .then(res => {
            applyResponse(res.data?.data ?? {})
            state.loaded = true
            return state.config
        })
        .catch(err => {
            state.error = err
            // Fail safe: treat as disabled so forms are never blocked by a
            // config-fetch failure.
            state.config.enabled = false
            throw err
        })
        .finally(() => {
            state.loading = false
            inflight = null
        })

    return inflight
}

/**
 * Reactive accessor. Triggers a fetch on first use unless one already ran.
 *
 * @returns {{ state: object, config: object, refresh: function }}
 */
export function useRecaptchaConfig({ immediate = true } = {}) {
    if (immediate && !state.loaded && !inflight) {
        fetchRecaptchaConfig().catch(() => {})
    }

    return {
        state: readonly(state),
        config: state.config,
        refresh: () => fetchRecaptchaConfig(true),
    }
}
