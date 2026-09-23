/**
 * Shared constants for the in-house reCAPTCHA Vue layer.
 *
 * The backend persists a "version" (what the admin picked) while the frontend
 * widgets think in terms of a "mode". This module is the single source of truth
 * for that mapping and for the injection key.
 */

// Effective rendering modes understood by the widget components.
export const MODE = Object.freeze({
    V2_CHECKBOX: 'v2',
    V2_INVISIBLE: 'v2-invisible',
    V3: 'v3',
})

// Backend "captcha_version" -> frontend mode.
export const VERSION_TO_MODE = Object.freeze({
    v2_checkbox: MODE.V2_CHECKBOX,
    v2_invisible: MODE.V2_INVISIBLE,
    v3_invisible: MODE.V3,
})

// Failover actions configured on the v3 flow.
export const FAILOVER = Object.freeze({
    NONE: 'none',
    V2_CHECKBOX: 'v2_checkbox',
})

// Injection key for the provider/consumer contract.
export const RECAPTCHA_CONTEXT = Symbol('faveo-recaptcha-context')

// Defaults applied when a setting is missing from the public config.
export const DEFAULTS = Object.freeze({
    mode: MODE.V3,
    theme: 'light',
    size: 'normal',
    badge: 'bottomright',
    lang: 'en',
})

export function versionToMode(version) {
    return VERSION_TO_MODE[version] ?? DEFAULTS.mode
}
