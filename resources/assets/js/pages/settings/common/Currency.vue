<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">Currencies</h4>
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
import { h, ref, reactive } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'currency'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl = `${baseUrl}/currency/list`

const dtRef = ref(null)

async function toggleStatus(row) {
    try {
        const res = await http.post(`${baseUrl}/currency/update-currency`, {
            current_id: row.id,
            current_status: row.status ? '1' : '0',
        })
        successHandler(res, COMPONENT)
        dtRef.value?.refresh()
    } catch (e) { errorHandler(e, COMPONENT) }
}

async function setDashboard(id) {
    try {
        const res = await http.post(`${baseUrl}/currency/dashboard-currency/${id}`)
        successHandler(res, COMPONENT)
        dtRef.value?.refresh()
    } catch (e) { errorHandler(e, COMPONENT) }
}

const columns = ['name', 'code', 'symbol', 'status', 'dashboard', 'action']

const tableOptions = reactive({
    headings: {
        name: 'Name',
        code: 'Code',
        symbol: 'Symbol',
        status: 'Status',
        dashboard: 'Dashboard',
        action: 'Action',
    },
    templates: {
        name: (f, row) => row.name || '—',
        code: (f, row) => row.code || '—',
        symbol: (f, row) => row.symbol || '—',
        status: (f, row) => h('span', {
            class: row.status ? 'badge bg-success' : 'badge bg-secondary',
        }, row.status ? 'Active' : 'Inactive'),
        dashboard: (f, row) => h('span', {
            class: row.dashboard_currency ? 'badge bg-info' : 'badge bg-light text-dark',
        }, row.dashboard_currency ? 'Shown' : 'Hidden'),
        action: (f, row) => h('div', { class: 'd-flex gap-1' }, [
            h('button', {
                class: 'btn btn-sm btn-light table_btn',
                title: row.status ? 'Disable' : 'Enable',
                onClick: () => toggleStatus(row),
            }, h('i', { class: row.status ? 'fas fa-toggle-on' : 'fas fa-toggle-off' })),
            h('button', {
                class: 'btn btn-light table_btn',
                title: 'Set as Dashboard Currency',
                onClick: () => setDashboard(row.id),
            }, h('i', { class: 'fas fa-chart-bar' })),
        ]),
    },
    sortable: ['name', 'code'],
    filterable: true,
    requestAdapter(data) {
        return {
            'sort-field': data.orderBy ?? 'id',
            'sort-order': data.ascending ? 'asc' : 'desc',
            'search-query': (data.query ?? '').trim(),
            page: data.page,
            limit: data.limit,
        }
    },
    orderBy: { column: 'id', ascending: false },
})
</script>
