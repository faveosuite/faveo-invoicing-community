import { h } from 'vue'
import { __ } from '@/plugins/i18n'

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
            'sort-order':   data.orderBy && data.ascending ? 'asc' : 'desc',
            'search-query': (data.query ?? '').trim(),
            page:           data.page,
            limit:          data.limit,
            ...(filtersRef ? filtersRef.value : {}),
        }
    }
}


// bg-info/bg-warning need text-dark to stay readable in Bootstrap 5.
const INVOICE_TYPE_BADGE = {
    purchase:          'badge bg-primary',
    renewal:           'badge bg-info text-dark',
    upgrade_downgrade: 'badge bg-warning text-dark',
    agent_alteration:  'badge bg-secondary',
}

/** Type badge (purchase / renewal / …) for an invoice's `type` — same look on every invoice list. */
export function invoiceTypeBadge(type) {
    if (!type) return h('span', '—')

    return h('span', { class: INVOICE_TYPE_BADGE[type] ?? 'badge bg-secondary' }, __('message.invoice_type_' + type))
}
