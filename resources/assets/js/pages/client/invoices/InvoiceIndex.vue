<template>
    <div>
        <AppAlert componentName="client-invoices-index" />
        <DataTable :url="apiUrl" :dataColumns="columns" :option="tableOptions">
            <template #number="{ row }">
                <RouterLink :to="'/invoices/' + row.id" class="fw-semibold">{{ row.number || '—' }}</RouterLink>
            </template>
            <template #date="{ row }">{{ formatDate(row.date) }}</template>
            <template #status="{ row }">
                <span class="badge" :class="statusBadge(row.status)">{{ row.status || '—' }}</span>
            </template>
            <template #action="{ row }">
                <RouterLink :to="'/invoices/' + row.id" class="btn btn-sm btn-light" v-tooltip :title="__('message.view')">
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
const apiUrl  = `${baseUrl}/get-my-invoices`

const columns = ['number', 'date', 'grand_total', 'status', 'action']

const tableOptions = reactive({
    headings: {
        number:      () => __('message.invoice_no'),
        date:        () => __('message.date'),
        grand_total: () => __('message.grand_total'),
        status:      () => __('message.status'),
        action:      () => __('message.actions'),
    },
    sortable:   ['number', 'date', 'grand_total'],
    filterable: true,
})

function formatDate(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
}

function statusBadge(status) {
    const s = (status ?? '').toLowerCase()
    if (s === 'paid' || s === 'success')   return 'bg-success'
    if (s === 'pending' || s === 'unpaid') return 'bg-warning text-dark'
    if (s === 'partially paid')            return 'bg-info text-dark'
    if (s === 'cancelled')                 return 'bg-danger'
    if (s === 'overdue')                   return 'bg-danger'
    return 'bg-secondary'
}
</script>
