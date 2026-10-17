export function useBaseUrl() {
    const el = document.getElementById('app-root') ?? document.getElementById('app-client')
    return el?.dataset?.baseUrl ?? ''
}

// Derives a Vue Router `base` path from a full URL dataset attribute
// (e.g. data-admin-url / data-client-url), falling back when unset.
export function resolveBasePath(url, fallback) {
    return url ? new URL(url).pathname : fallback
}
