<template>
    <div>
        <AppAlert componentName="client-orders-index" />
        <DataTable :url="apiUrl" :dataColumns="columns" :option="tableOptions">
            <template #number="{ row }">
                <RouterLink :to="'/orders/' + row.id" class="fw-semibold">{{ row.number || '—' }}</RouterLink>
            </template>
            <template #status="{ row }">
                <span :class="statusBadge(row.status)">{{ row.status || '—' }}</span>
            </template>
            <template #order_date="{ row }">{{ formatDate(row.order_date) }}</template>
            <template #update_ends_at="{ row }">{{ formatDate(row.update_ends_at) }}</template>
            <template #action="{ row }">
                <RouterLink :to="'/orders/' + row.id" class="btn btn-sm btn-light" v-tooltip :title="__('message.view')">
                    <i class="fas fa-eye"></i>
                </RouterLink>
            </template>
        </DataTable>
    </div>
</template>

<script setup>
import { reactive } from 'vue'
import { RouterLink } from 'vue-router'
import { __ } from '@/plugins/i18n'

const el      = document.getElementById('app-client')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl  = `${baseUrl}/get-my-orders`

const columns = ['number', 'product_name', 'version', 'status', 'order_date', 'update_ends_at', 'action']

const tableOptions = reactive({
    headings: {
        number:         () => __('message.order_no'),
        product_name:   () => __('message.product'),
        version:        () => __('message.version'),
        status:         () => __('message.status'),
        order_date:     () => __('message.order_date'),
        update_ends_at: () => __('message.expiry'),
        action:         () => __('message.actions'),
    },
    sortable:   ['number', 'order_date', 'update_ends_at'],
    filterable: true,
})

function formatDate(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
}

function statusBadge(status) {
    if (!status) return 'badge bg-secondary'
    const s = status.toLowerCase()
    if (s === 'active')    return 'badge bg-success'
    if (s === 'expired')   return 'badge bg-danger'
    if (s === 'pending')   return 'badge bg-warning text-dark'
    if (s === 'cancelled' || s === 'canceled') return 'badge bg-danger'
    if (s === 'suspended') return 'badge bg-warning text-dark'
    return 'badge bg-secondary'
}
</script>
