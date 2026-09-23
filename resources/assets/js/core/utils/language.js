/**
 * Native name of a locale in its own script, e.g. 'ja' -> '日本語'.
 * Returns '' when the runtime has no data for the locale, so callers can
 * fall back to the English name.
 */
export function nativeName(locale) {
    try {
        return new Intl.DisplayNames([locale], {type: 'language'}).of(locale) ?? ''
    } catch {
        return ''
    }
}
