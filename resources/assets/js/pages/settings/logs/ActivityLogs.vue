<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">Activity Logs</h4>
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
import { reactive, ref } from 'vue'

const COMPONENT = 'activity-logs'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl = `${baseUrl}/get-activity-api`

const dtRef = ref(null)

const columns = ['module', 'event', 'description', 'performed_by', 'role', 'created_at']

const tableOptions = reactive({
    headings: {
        module: 'Module',
        event: 'Event',
        description: 'Description',
        performed_by: 'Performed By',
        role: 'Role',
        created_at: 'Date',
    },
    templates: {
        module: (f, row) => row.module || '—',
        event: (f, row) => row.event || '—',
        description: (f, row) => row.description || '—',
        performed_by: (f, row) => row.performed_by || '—',
        role: (f, row) => row.role || '—',
        created_at: (f, row) => row.created_at || '—',
    },
    sortable: ['module', 'event', 'created_at'],
    filterable: true,
    requestAdapter(data) {
        return {
            'sort-field': data.orderBy ?? 'created_at',
            'sort-order': data.ascending ? 'asc' : 'desc',
            'search-query': (data.query ?? '').trim(),
            page: data.page,
            limit: data.limit,
        }
    },
    orderBy: { column: 'created_at', ascending: false },
})
</script>
