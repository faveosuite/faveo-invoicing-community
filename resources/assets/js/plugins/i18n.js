/**
 * Laravel-compatible translation helper for Vue.
 *
 * Reads from the global `translator` object set by /js/lang route.
 * Structure: translator = { message: { dashboard: 'Dashboard', ... }, ... }
 *
 * Usage in templates:  {{ __('message.dashboard') }}
 * Usage in scripts:    __('message.dashboard')   (no import needed)
 */

export function __(key, replace = {}) {
    if (!key) return key

    // Walk dot-notation: 'message.dashboard' → translator['message']['dashboard']
    const parts = key.split('.')
    let value = (typeof translator === 'undefined') ? {} : translator
    for (const part of parts) {
        if (value !== null && typeof value === 'object' && part in value) {
            value = value[part] // nosemgrep: javascript.lang.security.audit.prototype-pollution.prototype-pollution-loop.prototype-pollution-loop
        } else {
            return key
        }
    }

    if (typeof value !== 'string') return key

    // Replace :placeholder patterns (Laravel-style)
    return Object.entries(replace).reduce(
        (msg, [k, v]) => msg.replace(new RegExp(`:${k}`, 'gi'), String(v)),
        value
    )
}

export default {
    install(app) {
        globalThis.__ = __
        app.config.globalProperties.__ = __
    },
}
