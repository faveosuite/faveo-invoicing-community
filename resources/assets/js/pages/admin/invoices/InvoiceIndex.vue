<template>
    <div>
        <AppAlert componentName="invoices-index" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.invoices') }}</h4>
                <div class="card-tools">
                    <button
                        class="btn btn-tool"
                        :title="__('message.filter')"
                        v-tooltip
                        @click="showFilter = !showFilter"
                    >
                        <i class="fas fa-filter"></i>
                    </button>
                    <button
                        class="btn btn-tool"
                        :title="__('message.export')"
                        v-tooltip
                        @click="exportInvoices"
                        :disabled="exporting"
                    >
                        <spinner-loader v-if="exporting" :size="18" />
                        <i v-else class="fas fa-paper-plane"></i>
                    </button>
                    <router-link to="/invoices/create" class="btn btn-tool" :title="__('message.create_invoice')" v-tooltip>
                        <i class="fas fa-plus"></i>
                    </router-link>
                </div>
            </div>

            <div class="card-body">
                <InvoiceFilter
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
                >
                    <template #bulk-actions>
                        <div v-if="selectedInvoices.length > 0" class="dropdown">
                            <button
                                class="btn btn-sm btn-secondary dropdown-toggle"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                            >
                                {{ __('message.bulk_action') }}
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <button class="dropdown-item" @click="confirmBulkDelete">
                                        {{ __('message.Delete') }}
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>
    </div>

    <DeleteModal
        v-if="pendingBulkDelete"
        :showModal="true"
        :onClose="() => pendingBulkDelete = null"
        :deleteUrl="apiUrl"
        :deleteData="pendingBulkDelete"
        :title="__('message.Delete')"
        :message="__('message.are_you_sure')"
        :componentName="'invoices-index'"
        @deleted="() => { pendingBulkDelete = null; selectedInvoices.value = []; dtRef?.refresh() }"
    />
</template>

<script setup>
import { h, ref, computed, reactive } from 'vue'
import { RouterLink } from 'vue-router'

import http from '@/plugins/axios'
import { errorHandler, successHandler } from '@/helpers/responseHandler.js'
import InvoiceTableActions from './components/InvoiceTableActions.vue'
import InvoiceFilter from './components/InvoiceFilter.vue'
import DeleteModal from '@/themes/adminlte/components/common/DeleteModal.vue'

const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl = `${baseUrl}/invoices`

const dtRef = ref(null)
const selectedInvoices = ref([])
const showFilter = ref(false)
const activeFilters = ref({})
const exporting = ref(false)
const pendingBulkDelete = ref(null)

const allSelected = computed(() => {
    const data = dtRef.value?.tableData ?? []
    return data.length > 0 && data.every(row => selectedInvoices.value.includes(row.id))
})

function toggleRow(id) {
    const idx = selectedInvoices.value.indexOf(id)
    if (idx === -1) selectedInvoices.value.push(id)
    else selectedInvoices.value.splice(idx, 1)
}

function toggleAll(e) {
    const data = dtRef.value?.tableData ?? []
    if (e.target.checked) {
        const ids = data.map(r => r.id).filter(id => !selectedInvoices.value.includes(id))
        selectedInvoices.value.push(...ids)
    } else {
        const ids = data.map(r => r.id)
        selectedInvoices.value = selectedInvoices.value.filter(id => !ids.includes(id))
    }
}

function onFilterApply(params) {
    activeFilters.value = params
    showFilter.value = false
    dtRef.value?.refresh()
}

function onFilterReset() {
    activeFilters.value = {}
    dtRef.value?.refresh()
}

function confirmBulkDelete() {
    if (!selectedInvoices.value.length) return
    pendingBulkDelete.value = { invoice_ids: [...selectedInvoices.value] }
}

async function exportInvoices() {
    exporting.value = true
    try {
        const params = new URLSearchParams()
        Object.entries(activeFilters.value).forEach(([k, v]) => {
            if (v !== '' && v !== null) params.append(k, v)
        })
        
        const res = await http.get(`${baseUrl}/export-invoices?${params.toString()}`)
        successHandler(res, 'invoices-index')
    } catch (e) {
        errorHandler(e, 'invoices-index')
    } finally {
        exporting.value = false
    }
}

const columns = ['select', 'user', 'email', 'mobile', 'country', 'number', 'product', 'date', 'grand_total', 'status', 'action']

const tableOptions = reactive({
    headings: {
        select:        () => h('input', { type: 'checkbox', checked: allSelected.value, onChange: toggleAll }),
        user:          __('message.user'),
        email:         __('message.email'),
        mobile:        __('message.mobile'),
        country:       __('message.country'),
        number:        __('message.invoice_no'),
        product:       __('message.product'),
        date:          __('message.date'),
        grand_total:   __('message.total'),
        status:        __('message.status'),
        action:        __('message.actions'),
    },

    columnsClasses: {
        select: 'dt-select',
        user: 'dt-name',
        email: 'dt-email',
        mobile: 'dt-mobile',
        country: 'dt-country',
        number: 'dt-number',
        product: 'dt-name',
        date: 'dt-date',
        grand_total: 'dt-amount',
        status: 'dt-status',
        action: 'dt-action',
    },

    templates: {
        select:       (f, row) => h('input', { type: 'checkbox', checked: selectedInvoices.value.includes(row.id), onChange: () => toggleRow(row.id) }),
        user:         (f, row) => {
            if (!row.user) return '—'
            const fullName = `${row.user.first_name ?? ''} ${row.user.last_name ?? ''}`.trim()
            if (fullName && row.user.id) return h(RouterLink, { to: '/users/' + row.user.id }, () => fullName)
            return '—'
        },
        email:        (f, row) => {
            if (row.user?.email && row.user?.id) return h(RouterLink, { to: '/users/' + row.user.id }, () => row.user.email)
            return '—'
        },
        mobile:       (f, row) => row.user?.mobile ? `${row.user.mobile_code ? '+' + row.user.mobile_code + ' ' : ''}${row.user.mobile}`.trim() : '—',
        country:      (f, row) => row.user?.country || '—',
        number:       (f, row) => row.number || '—',
        product:      (f, row) => (row.products ?? []).join(', ') || '—',
        date:         (f, row) => row.created_at ? new Date(row.created_at).toLocaleDateString() : '—',
        grand_total:  (f, row) => row.grand_total || '—',
        status:       (f, row) => {
            let badgeClass = 'badge bg-secondary'
            if (row.status === 'Paid') badgeClass = 'badge bg-success'
            else if (row.status === 'Partially Paid') badgeClass = 'badge bg-info text-dark'
            return h('span', { class: badgeClass }, row.status)
        },
        action:       (f, row) => h(InvoiceTableActions, { invoiceId: row.id, showDelete: true }),
    },

    sortable: ['number', 'date', 'grand_total', 'status'],
    filterable: true,

    requestAdapter(data) {
        const columnMap = { date: 'created_at' }
        return {
            'sort-field':   columnMap[data.orderBy] ?? data.orderBy ?? 'created_at',
            'sort-order':   data.ascending ? 'asc' : 'desc',
            'search-query': (data.query ?? '').trim(),
            page:           data.page,
            limit:          data.limit,
            ...activeFilters.value,
        }
    },

    orderBy: { column: 'date', ascending: false },
})
</script>
