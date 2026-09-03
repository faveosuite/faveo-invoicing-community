<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.localized_license') }}</h4>
            </div>
            <div class="card-body">
                <DataTable
                    ref="dtRef"
                    :url="`${baseUrl}/localized-license/orders`"
                    :dataColumns="columns"
                    :option="tableOptions"
                >
                    <template #bulk-actions>
                        <div v-if="selected.length > 0" class="dropdown">
                            <button
                                class="btn btn-sm btn-secondary dropdown-toggle"
                                type="button"
                                data-bs-toggle="dropdown"
                                :disabled="disabling"
                            >
                                <spinner-loader v-if="disabling" :size="18" />
                                <span v-else>{{ __('message.bulk_action') }}</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <button class="dropdown-item" @click="bulkDisable">{{ __('message.disable') }}</button>
                                </li>
                            </ul>
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>
    </div>
</template>

<script setup>
import { h, ref, reactive } from 'vue'
import { RouterLink } from 'vue-router'
import AppAlert from '@/components/Reusable/Alert.vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import { useDateTime } from '@/core/composables/useDateTime'
import { useTableSelection } from '@/core/composables/useTableSelection'
import { makeRequestAdapter } from '@/helpers/tableUtils'

const COMPONENT = 'localized-license'
const baseUrl = useBaseUrl()
const { formatDate } = useDateTime()

const dtRef = ref(null)
const { selected, allSelected, toggleRow, toggleAll } = useTableSelection(dtRef)
const disabling = ref(false)

async function bulkDisable() {
    if (!selected.value.length) return
    disabling.value = true
    try {
        const res = await http.post(`/localized-license/bulk-disable`, { select: selected.value })
        successHandler(res, COMPONENT)
        selected.value = []
        dtRef.value?.refresh()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        disabling.value = false
    }
}

const columns = [
    'select', 'number', 'client_email', 'product_name', 'license_domain',
    'license_machine_id', 'is_bound', 'license_expire_date', 'action',
]

const tableOptions = reactive({
    headings: {
        select:              () => h('input', { type: 'checkbox', checked: allSelected.value, onChange: toggleAll }),
        number:              __('message.order_number'),
        client_email:        __('message.email'),
        product_name:        __('message.product'),
        license_domain:      __('message.domain'),
        license_machine_id:  __('message.machine_id'),
        is_bound:            __('message.status'),
        license_expire_date: __('message.license_expiry'),
        action:              __('message.action'),
    },
    columnsClasses: {
        select:              'dt-select',
        number:              'dt-number',
        client_email:        'dt-email',
        product_name:        'dt-name',
        license_domain:      'dt-text',
        license_machine_id:  'dt-text',
        is_bound:            'dt-status',
        license_expire_date: 'dt-date',
        action:              'dt-action',
    },
    templates: {
        select: (f, row) => h('input', { type: 'checkbox', checked: selected.value.includes(row.id), onChange: () => toggleRow(row.id) }),
        number: (f, row) => h(RouterLink, { to: `/orders/${row.id}` }, () => [row.number]),
        client_email: (f, row) => {
            if (row.client_email && row.client_id) {
                return h(RouterLink, { to: `/users/${row.client_id}` }, () => [row.client_email])
            }
            return row.client_email || '—'
        },
        product_name: (f, row) => {
            if (row.product_name && row.product_id) {
                return h(RouterLink, { to: `/products/${row.product_id}/edit` }, () => [row.product_name])
            }
            return row.product_name || '—'
        },
        license_domain: (f, row) => row.license_domain || '—',
        license_machine_id: (f, row) => row.license_machine_id || '—',
        license_expire_date: (f, row) => row.license_expire_date ? formatDate(row.license_expire_date) : '—',
        is_bound: (f, row) => h(
            'span',
            { class: row.is_bound ? 'badge bg-success' : 'badge bg-warning text-dark' },
            row.is_bound ? __('message.configured') : __('message.pending')
        ),
        action: (f, row) => h(RouterLink, {
            to: `/orders/${row.id}`,
            class: 'btn btn-light table_btn',
            title: __('message.view'),
        }, () => [h('i', { class: 'fas fa-eye' })]),
    },
    sortable: ['number'],
    filterable: true,
    requestAdapter: makeRequestAdapter('number'),
    orderBy: { column: 'number', ascending: false },
})
</script>
