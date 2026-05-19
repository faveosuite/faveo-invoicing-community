<template>
    <div>
        <AppAlert componentName="orders-index" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.orders') }}</h4>
                <div class="card-tools">
                    <button
                        class="btn btn-tool"
                        :title="__('message.filter')"
                        v-tooltip
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
                                    <button class="dropdown-item" @click="bulkDelete">
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
</template>

<script setup>
import { h, ref, computed, reactive } from 'vue'

import http from '@/plugins/axios'
import { errorHandler } from '@/helpers/responseHandler.js'
import OrderTableActions from './components/OrderTableActions.vue'
import OrderFilter from './components/OrderFilter.vue'

const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl = `${baseUrl}/orders`

const dtRef = ref(null)
const selectedOrders = ref([])
const showFilter = ref(false)
const activeFilters = ref({})

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
        const ids = data.map(r => r.id).filter(id => !selectedOrders.value.includes(id))
        selectedOrders.value.push(...ids)
    } else {
        const ids = data.map(r => r.id)
        selectedOrders.value = selectedOrders.value.filter(id => !ids.includes(id))
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

async function bulkDelete() {
    if (!selectedOrders.value.length) return
    if (!confirm(`Delete ${selectedOrders.value.length} selected order(s)? This cannot be undone.`)) return
    try {
        await http.delete(apiUrl, { data: { order_ids: selectedOrders.value } })
        selectedOrders.value = []
        dtRef.value?.refresh()
    } catch (e) {
        errorHandler(e, 'orders-index')
    }
}

const columns = ['select', 'client', 'email', 'mobile', 'country', 'number', 'order_status', 'product_name', 'group', 'plan', 'version', 'agents', 'status', 'order_date', 'update_ends_at', 'action']

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

    templates: {
        select:       (f, row) => h('input', { type: 'checkbox', checked: selectedOrders.value.includes(row.id), onChange: () => toggleRow(row.id) }),
        client:       (f, row) => row.user ? `${row.user.first_name ?? ''} ${row.user.last_name ?? ''}`.trim() || '—' : '—',
        email:        (f, row) => row.user?.email || '—',
        mobile:       (f, row) => row.user?.mobile ? `${row.user.mobile_code ?? ''} ${row.user.mobile}`.trim() : '—',
        country:      (f, row) => row.user?.country || '—',
        number:       (f, row) => row.number || '—',
        order_status: (f, row) => row.order_status || '—',
        product_name: (f, row) => row.product_name || '—',
        group:        (f, row) => row.group || '—',
        plan:         (f, row) => row.plan || '—',
        version:      (f, row) => row.version || '—',
        agents:       (f, row) => row.agents ?? '—',
        status:       (f, row) => row.status || '—',
        order_date:   (f, row) => row.order_date ? new Date(row.order_date).toLocaleDateString() : '—',
        update_ends_at: (f, row) => row.update_ends_at ? new Date(row.update_ends_at).toLocaleDateString() : '—',
        action: (f, row) => h(OrderTableActions, { orderId: row.id, baseUrl: baseUrl }),
    },

    sortable: ['number', 'order_status', 'order_date', 'update_ends_at'],
    filterable: true,

    requestAdapter(data) {
        const columnMap = { order_date: 'created_at' }
        return {
            'sort-field':   columnMap[data.orderBy] ?? data.orderBy ?? 'created_at',
            'sort-order':   data.ascending ? 'asc' : 'desc',
            'search-query': (data.query ?? '').trim(),
            page:           data.page,
            limit:          data.limit,
            ...activeFilters.value,
        }
    },

    orderBy: { column: 'order_date', ascending: false },
})
</script>
