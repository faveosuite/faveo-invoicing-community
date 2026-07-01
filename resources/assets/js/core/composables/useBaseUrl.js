export function useBaseUrl() {
    const el = document.getElementById('app-root') ?? document.getElementById('app-client')
    return el?.dataset?.baseUrl ?? ''
}
