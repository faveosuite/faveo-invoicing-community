<template>
    <div>
        <AppAlert componentName="orders-index" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.orders') }}</h4>
                <div class="card-tools">
                    <button
                        class="btn btn-tool"
                        v-tooltip="__('message.filter')"
                        @click="showFilter = !showFilter"
                    >
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </div>

            <div class="card-body">
                <OrderFilter
                    :show="showFilter"
                    :baseUrl="baseUrl"
                    :initialValues="activeFilters"
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
                    <template #table-tools>
                        <ColumnSelector
                            :entityType="'orders'"
                            :labels="columnLabels"
                            componentName="orders-index"
                            @change="onColumnsChange"
                        />
                    </template>
                    <template #bulk-actions>
                        <div v-if="selectedOrders.length > 0" class="dropdown">
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
        :deleteUrl="`${baseUrl}/orders`"
        :deleteData="pendingBulkDelete"
        :title="__('message.Delete')"
        :message="__('message.are_you_sure')"
        componentName="orders-index"
        @deleted="() => { pendingBulkDelete = null; selectedOrders.value = []; dtRef.value?.refresh() }"
    />
</template>

<script setup>
import { h, ref, computed, reactive, watch, withDirectives, resolveDirective } from 'vue'
import { RouterLink, useRoute } from 'vue-router'

import { useDateTime } from '@/core/composables/useDateTime'
import OrderTableActions from './components/OrderTableActions.vue'
import OrderFilter from './components/OrderFilter.vue'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'
import ColumnSelector from '@/components/Reusable/ColumnSelector.vue'

const { formatDate } = useDateTime()

const vTooltipDirective = resolveDirective('tooltip')

const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl = `${baseUrl}/orders`
const route = useRoute()

const dtRef = ref(null)
const selectedOrders = ref([])
const showFilter = ref(false)

const allowedOrderFilters = ['order_no', 'product_id', 'from', 'till', 'domain', 'act_ins', 'renewal', 'version']

function parseOrderQuery(query) {
    const params = {}
    allowedOrderFilters.forEach(k => { if (query[k]) params[k] = query[k] })
    return params
}

const activeFilters = ref(parseOrderQuery(route.query))

watch(() => route.query, (newQuery) => {
    activeFilters.value = parseOrderQuery(newQuery)
    dtRef.value?.refresh()
}, { deep: true })
const pendingBulkDelete = ref(null)

const allSelected = computed(() => {
    const data = dtRef.value?.tableData ?? []
    return data.length > 0 && data.every(row => selectedOrders.value.includes(row.id))
})

function toggleRow(id) {
    const idx = selectedOrders.value.indexOf(id)
    if (idx === -1) selectedOrders.value.push(id)
    else selectedOrders.value.splice(idx, 1)
}

function toggleAll(e) {
    const data = dtRef.value?.tableData ?? []
    if (e.target.checked) {
        const ids = new Set(data.map(r => r.id)).filter(id => !selectedOrders.value.includes(id))
        selectedOrders.value.push(...ids)
    } else {
        const ids = new Set(data.map(r => r.id))
        selectedOrders.value = selectedOrders.value.filter(id => !ids.has(id))
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
    if (!selectedOrders.value.length) return
    pendingBulkDelete.value = { order_ids: [...selectedOrders.value] }
}

// report_columns keys (type 'orders') ↔ this table's internal column names.
const REPORT_TO_COL = {
    checkbox:       'select',
    client:         'client',
    email:          'email',
    mobile:         'mobile',
    country:        'country',
    number:         'number',
    status:         'status',
    product_name:   'product_name',
    plan_name:      'plan',
    version:        'version',
    agents:         'agents',
    order_status:   'order_status',
    order_date:     'order_date',
    update_ends_at: 'update_ends_at',
    group_name:     'group',
    action:         'action',
}

// Labels shown in the ColumnSelector dropdown (keyed by report_columns key).
const columnLabels = {
    client:         __('message.user'),
    email:          __('message.email'),
    mobile:         __('message.mobile'),
    country:        __('message.country'),
    number:         __('message.order_no'),
    status:         __('message.status'),
    product_name:   __('message.product'),
    plan_name:      __('message.plan'),
    version:        __('message.version'),
    agents:         __('message.agents'),
    order_status:   __('message.order-status'),
    order_date:     __('message.order_date'),
    update_ends_at: __('message.expiry'),
    group_name:     __('message.group'),
}

const DEFAULT_COLUMNS = ['select', 'client', 'email', 'mobile', 'country', 'number', 'order_status', 'product_name', 'group', 'plan', 'version', 'agents', 'status', 'order_date', 'update_ends_at', 'action']
const columns = ref([...DEFAULT_COLUMNS])

function onColumnsChange(reportKeys) {
    const mapped = reportKeys.map(k => REPORT_TO_COL[k]).filter(Boolean)
    columns.value = mapped.length ? mapped : [...DEFAULT_COLUMNS]
}

const tableOptions = reactive({
    headings: {
        select:        () => h('input', { type: 'checkbox', checked: allSelected.value, onChange: toggleAll }),
        client:        __('message.user'),
        email:         __('message.email'),
        mobile:        __('message.mobile'),
        country:       __('message.country'),
        number:        __('message.order_no'),
        order_status:  __('message.order-status'),
        product_name:  __('message.product'),
        group:         __('message.group'),
        plan:          __('message.plan'),
        version:       __('message.version'),
        agents:        __('message.agents'),
        status:        __('message.status'),
        order_date:    __('message.order_date'),
        update_ends_at: __('message.expiry'),
        action:        __('message.actions'),
    },

    columnsClasses: {
        select: 'dt-select',
        client: 'dt-name',
        email: 'dt-email',
        mobile: 'dt-mobile',
        country: 'dt-country',
        number: 'dt-number',
        order_status: 'dt-status',
        product_name: 'dt-name',
        group: 'dt-name',
        plan: 'dt-name',
        version: 'dt-code',
        agents: 'dt-code',
        status: 'dt-status',
        order_date: 'dt-date',
        update_ends_at: 'dt-date',
        action: 'dt-action',
    },

    templates: {
        select:       (f, row) => h('input', { type: 'checkbox', checked: selectedOrders.value.includes(row.id), onChange: () => toggleRow(row.id) }),
        client:       (f, row) => {
            if (!row.user) return '—'
            const fullName = `${row.user.first_name ?? ''} ${row.user.last_name ?? ''}`.trim()
            if (fullName && row.user.id) return h(RouterLink, { to: '/users/' + row.user.id }, () => fullName)
            return '—'
        },
        email:        (f, row) => {
            if (row.user?.email && row.user?.id) return h(RouterLink, { to: '/users/' + row.user.id }, () => row.user.email)
            return '—'
        },
        mobile: (f, row) => {
            if (!row.user?.mobile?.trim()) return '—'
            const code = row.user.mobile_code?.trim()
            return code ? `+${code} ${row.user.mobile.trim()}` : row.user.mobile.trim()
        },
        country:      (f, row) => row.user?.country || '—',
        number:       (f, row) => row.number && row.id ? h(RouterLink, { to: '/orders/' + row.id }, () => row.number) : '—',
        order_status: (f, row) => row.order_status || '—',
        product_name: (f, row) => row.product_name && row.product_id ? h(RouterLink, { to: '/products/' + row.product_id + '/edit' }, () => row.product_name) : (row.product_name || '—'),
        group:        (f, row) => row.group && row.group_id ? h(RouterLink, { to: '/products/groups/' + row.group_id + '/edit' }, () => row.group) : (row.group || '—'),
        plan:         (f, row) => row.plan && row.plan_id ? h(RouterLink, { to: '/products/plans/' + row.plan_id + '/edit' }, () => row.plan) : (row.plan || '—'),
        version: (f, row) => {
            if (!row.versions?.length) return '—'
            return h('div', { class: 'd-flex flex-wrap gap-1' },
                row.versions.map(({ version, active }) =>
                    withDirectives(
                        h('span', {
                            class: `badge ${active ? 'bg-success' : 'bg-danger'}`,
                            style: 'cursor:default',
                        }, version),
                        [[vTooltipDirective, active ? 'Active' : 'Inactive']]
                    )
                )
            )
        },
        agents:       (f, row) => row.agents ?? '—',
        status:       (f, row) => row.status || '—',
        order_date:   (f, row) => row.order_date ? formatDate(row.order_date) : '—',
        update_ends_at: (f, row) => row.update_ends_at ? formatDate(row.update_ends_at) : '—',
        action: (f, row) => h(OrderTableActions, { orderId: row.id, canRenew: !!row.can_renew, baseUrl: baseUrl, showDelete: true }),
    },

    sortable: ['number', 'order_status', 'order_date', 'update_ends_at'],
    filterable: true,

    requestAdapter(data) {
        const columnMap = { order_date: 'created_at' }
        return {
            'sort-field':   columnMap[data.orderBy] ?? data.orderBy ?? 'created_at',
            'sort-order':   data.orderBy ? (data.ascending ? 'asc' : 'desc') : 'desc',
            'search-query': (data.query ?? '').trim(),
            page:           data.page,
            limit:          data.limit,
            ...activeFilters.value,
        }
    },

    orderBy: { column: 'order_date', ascending: false },
})
</script>
