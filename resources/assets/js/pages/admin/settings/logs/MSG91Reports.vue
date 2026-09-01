<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.msg_reports') }}</h4>
                <div class="card-tools">
                    <button class="btn btn-tool" v-tooltip="__('message.filters')" @click="showFilter = !showFilter">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <MSG91Filter
                    :show="showFilter"
                    :baseUrl="baseUrl"
                    @apply="onFilterApply"
                    @reset="onFilterReset"
                    @close="showFilter = false"
                />
                <DataTable
                    ref="dtRef"
                    :url="apiUrl"
                    :dataColumns="columns"
                    :option="tableOptions"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { h, reactive, ref } from 'vue'
import MSG91Filter from './MSG91Filter.vue'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import { makeRequestAdapter } from '@/helpers/tableUtils'

const COMPONENT = 'msg91-reports'
const baseUrl = useBaseUrl()
const apiUrl  = `/getMsgReports`

// ── filter ────────────────────────────────────────────────────────────────────
const dtRef         = ref(null)
const showFilter    = ref(false)
const activeFilters = ref({})

function onFilterApply(params) {
    activeFilters.value = params
    showFilter.value    = false
    dtRef.value?.refresh()
}

function onFilterReset() {
    activeFilters.value = {}
    dtRef.value?.refresh()
}

// ── helpers ───────────────────────────────────────────────────────────────────
const ordinals = ['First', 'Second', 'Third', 'Fourth', 'Fifth']

function formatAction(action) {
    if (!action) return '—'
    if (action === 'send') return 'First OTP sent'
    const retryMatch = action.match(/^retry_(\d+)$/)
    if (retryMatch) {
        const n = parseInt(retryMatch[1], 10)
        const ord = ordinals[n - 1] ?? `${n}th`
        return `${ord} retry`
    }
    return action.replace(/[-_]/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
}

const STATUS_BADGE = {
    0:  'badge bg-warning',   // Pending
    1:  'badge bg-success',   // Delivered
    2:  'badge bg-danger',    // Failed
    9:  'badge bg-dark',      // NDNC
    16: 'badge bg-secondary', // Rejected
    17: 'badge bg-info',      // Blocked number
    25: 'badge bg-secondary', // Rejected
}

function statusBadgeClass(statusCode) {
    return STATUS_BADGE[statusCode] ?? 'badge bg-danger'
}

// ── table ─────────────────────────────────────────────────────────────────────
const columns = [
    'request_id', 'user_fullname', 'user_email',
    'mobile_number', 'source', 'action',
    'status', 'failure_reason', 'created_at', 'delivery_date',
]

const tableOptions = reactive({
    headings: {
        request_id:     __('message.request_id'),
        user_fullname:  __('message.user'),
        user_email:     __('message.email'),
        mobile_number:  __('message.mobile_number'),
        source:         __('message.source'),
        action:         __('message.attempt'),
        status:         __('message.status'),
        failure_reason: __('message.failure_reason'),
        created_at:     __('message.sent_at'),
        delivery_date:  __('message.delivered_at'),
    },
    columnsClasses: {
        request_id: 'dt-number',
        user_fullname: 'dt-name',
        user_email: 'dt-email',
        mobile_number: 'dt-mobile',
        source: 'dt-code',
        action: 'dt-code',
        status: 'dt-status',
        failure_reason: 'dt-text',
        created_at: 'dt-date',
        delivery_date: 'dt-date',
    },
    templates: {
        request_id: (f, row) => row.request_id || '—',
        user_fullname: (f, row) => {
            const name = row.user_fullname || '—'
            if (!row.user_id) return name
            return h('a', { href: `${baseUrl}/admin/clients/${row.user_id}` }, name)
        },
        user_email:     (f, row) => row.user_email     || '—',
        mobile_number:  (f, row) => row.mobile_number  || '—',
        source:         (f, row) => row.source         || '—',
        action:         (f, row) => formatAction(row.action),
        status: (f, row) => h('span', { class: statusBadgeClass(row.status_code) }, row.status || '—'),
        failure_reason: (f, row) => row.failure_reason || '—',
        created_at:     (f, row) => row.created_at     || '—',
        delivery_date:  (f, row) => row.delivery_date  || '—',
    },
    sortable: ['mobile_number', 'status', 'created_at', 'delivery_date'],
    filterable: true,
    requestAdapter: makeRequestAdapter('created_at', activeFilters, { delivery_date: 'date' }),
    orderBy: { column: 'created_at', ascending: false },
})
</script>
