/**
 * makeRequestAdapter — factory for the kebab-key DataTable requestAdapter.
 *
 * @param {string} defaultSort  - fallback sort field (e.g. 'created_at')
 * @param {Ref}    filtersRef   - reactive ref whose .value is spread into the params (optional)
 * @param {Object} columnMap    - display-column → API-field remapping (optional)
 */
export function makeRequestAdapter(defaultSort = 'created_at', filtersRef = null, columnMap = {}) {
    return function (data) {
        return {
            'sort-field':   data.orderBy ? (columnMap[data.orderBy] ?? data.orderBy) : defaultSort,
            'sort-order':   data.orderBy ? (data.ascending ? 'asc' : 'desc') : 'desc',
            'search-query': (data.query ?? '').trim(),
            page:           data.page,
            limit:          data.limit,
            ...(filtersRef ? filtersRef.value : {}),
        }
    }
}

