<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">Payment Log</h4>
            </div>
            <div class="card-body">
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
import { RouterLink } from 'vue-router'

const COMPONENT = 'payment-log'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl = `${baseUrl}/get-payment-log-api`

const dtRef = ref(null)

const columns = ['date', 'user', 'order', 'amount', 'payment_method', 'payment_type', 'status']

const tableOptions = reactive({
    headings: {
        date: 'Date',
        user: 'User',
        order: 'Order',
        amount: 'Amount',
        payment_method: 'Method',
        payment_type: 'Type',
        status: 'Status',
    },
    templates: {
        date: (f, row) => row.date || '—',
        user: (f, row) => row.user_id
            ? h(RouterLink, { to: `/clients/${row.user_id}` }, () => row.user)
            : (row.user || '—'),
        order: (f, row) => row.order || '—',
        amount: (f, row) => row.amount || '—',
        payment_method: (f, row) => row.payment_method || '—',
        payment_type: (f, row) => row.payment_type || '—',
        status: (f, row) => {
            if (row.status?.toLowerCase() === 'failed') {
                return h('span', {
                    class: 'badge bg-danger',
                    title: row.exception || '',
                    style: 'cursor:help',
                }, row.status)
            }
            return h('span', {
                class: row.status?.toLowerCase() === 'success' ? 'badge bg-success' : 'badge bg-secondary',
            }, row.status || '—')
        },
    },
    sortable: ['date', 'amount', 'status'],
    filterable: true,
    requestAdapter(data) {
        return {
            'sort-field': data.orderBy ?? 'date',
            'sort-order': data.ascending ? 'asc' : 'desc',
            'search-query': (data.query ?? '').trim(),
            page: data.page,
            limit: data.limit,
        }
    },
    orderBy: { column: 'date', ascending: false },
})
</script>
