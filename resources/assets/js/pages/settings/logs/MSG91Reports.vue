<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">MSG91 Delivery Reports</h4>
            </div>
            <div class="card-body">
                <DataTable
                    :url="apiUrl"
                    :dataColumns="columns"
                    :option="tableOptions"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue'

const COMPONENT = 'msg91-reports'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl = `${baseUrl}/getMsgReports`

const columns = ['request_id', 'mobile_number', 'user_fullname', 'status', 'delivery_date', 'created_at']

const tableOptions = reactive({
    headings: {
        request_id: 'Request ID',
        mobile_number: 'Mobile',
        user_fullname: 'User',
        status: 'Status',
        delivery_date: 'Delivery Date',
        created_at: 'Created',
    },
    templates: {
        request_id: (f, row) => row.request_id || '—',
        mobile_number: (f, row) => row.mobile_number || '—',
        user_fullname: (f, row) => row.user_fullname || (row.user_email ?? '—'),
        status: (f, row) => row.status || '—',
        delivery_date: (f, row) => row.delivery_date || '—',
        created_at: (f, row) => row.created_at || '—',
    },
    sortable: ['mobile_number', 'status', 'created_at'],
    filterable: true,
    requestAdapter(data) {
        return {
            'sort_field': data.orderBy ?? 'created_at',
            'sort_order': data.ascending ? 'asc' : 'desc',
            'search-query': (data.query ?? '').trim(),
            page: data.page,
            limit: data.limit,
        }
    },
    orderBy: { column: 'created_at', ascending: false },
})
</script>
