<template>
    <div>
        <AppAlert componentName="social-logins-index" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">Social Logins</h4>
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
import { h, reactive } from 'vue'
import { RouterLink } from 'vue-router'

const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl = `${baseUrl}/social-logins`

const columns = ['type', 'client_id', 'status', 'action']

const tableOptions = reactive({
    headings: {
        type:      'Type',
        client_id: 'Client ID / API Key',
        status:    'Status',
        action:    'Action',
    },
    templates: {
        type:      (f, row) => row.type || '—',
        client_id: (f, row) => row.client_id || '—',
        status:    (f, row) => h('span', {
            class: row.status ? 'badge bg-success' : 'badge bg-danger',
        }, row.status ? 'Active' : 'Inactive'),
        action:    (f, row) => h(RouterLink, { to: `/settings/social-logins/${row.id}/edit`, class: 'btn btn-light table_btn', title: 'Edit' }, () => h('i', { class: 'fas fa-edit' })),
    },
    sortable: ['type', 'status'],
    filterable: true,
    requestAdapter(data) {
        return {
            'sort-field':   data.orderBy ?? 'created_at',
            'sort-order':   data.ascending ? 'asc' : 'desc',
            'search-query': (data.query ?? '').trim(),
            page:           data.page,
            limit:          data.limit,
        }
    },
    orderBy: { column: 'created_at', ascending: false },
})
</script>
