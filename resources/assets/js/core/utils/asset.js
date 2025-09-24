/**
 * asset(path) — resolves a public asset path to a full URL using the base
 * set by Laravel's asset() helper in the blade template.
 *
 * Works regardless of subdirectory installs
 * e.g.  asset('themes/common/images/faveo.png')
 *   →   https://sakthi.desk/faveo-invoicing-community/public/themes/common/images/faveo.png
 */
const base = document.getElementById('app-root')
    ?.dataset
    ?.assetUrl
    ?.replace(/\/$/, '') ?? ''

export const assetBase = base

export function asset(path) {
    return `${base}/${path.replace(/^\//, '')}`
}
