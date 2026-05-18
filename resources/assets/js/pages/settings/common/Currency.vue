<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-secondary card-outline">
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
import CurrencyTableActions from './CurrencyTableActions.vue'

const COMPONENT = 'currency'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl  = `${baseUrl}/currency/list`

const dtRef            = ref(null)
const toggling         = ref(null)
const settingDefault   = ref(null)
const settingDashboard = ref(null)

async function toggleStatus(row) {
    toggling.value = row.id
    try {
        const res = await http.post(`${baseUrl}/currency/update-currency`, {
            current_id:     row.id,
            current_status: row.status ? '1' : '0',
        })
        successHandler(res, COMPONENT)
        dtRef.value?.refresh()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        toggling.value = null
    }
}

async function setDefault(id) {
    settingDefault.value = id
    try {
        const res = await http.post(`${baseUrl}/currency/default-currency/${id}`)
        successHandler(res, COMPONENT)
        dtRef.value?.refresh()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        settingDefault.value = null
    }
}

async function setDashboard(id) {
    settingDashboard.value = id
    try {
        const res = await http.post(`${baseUrl}/currency/dashboard-currency/${id}`)
        successHandler(res, COMPONENT)
        dtRef.value?.refresh()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        settingDashboard.value = null
    }
}

const columns = ['name', 'code', 'symbol', 'is_default', 'dashboard', 'action']

const tableOptions = reactive({
    headings: {
        name:       'Name',
        code:       'Code',
        symbol:     'Symbol',
        is_default: 'Default',
        dashboard:  'Dashboard',
        action:     'Action',
    },
    templates: {
        name:   (f, row) => row.name   || '—',
        code:   (f, row) => row.code   || '—',
        symbol: (f, row) => row.symbol || '—',
        is_default: (f, row) => h('span', {
            class: row.is_default ? 'badge bg-success' : 'badge bg-danger',
        }, row.is_default ? 'Yes' : 'No'),
        dashboard: (f, row) => h('span', {
            class: row.dashboard_currency ? 'badge bg-success' : 'badge bg-danger',
        }, row.dashboard_currency ? 'Yes' : 'No'),
        action: (f, row) => h(CurrencyTableActions, {
            status:           row.status,
            isDefault:        Boolean(row.is_default),
            isDashboard:      Boolean(row.dashboard_currency),
            toggling:         toggling.value === row.id,
            settingDefault:   settingDefault.value === row.id,
            settingDashboard: settingDashboard.value === row.id,
            onToggle:         () => toggleStatus(row),
            onSetDefault:     () => setDefault(row.id),
            onSetDashboard:   () => setDashboard(row.id),
        }),
    },
    sortable:   ['name', 'code'],
    filterable: true,
    requestAdapter(data) {
        return {
            'sort-field':   data.orderBy  ?? 'status',
            'sort-order':   data.ascending ? 'asc' : 'desc',
            'search-query': (data.query   ?? '').trim(),
            page:           data.page,
            limit:          data.limit,
        }
    },
    orderBy: { column: 'status', ascending: false },
})
</script>
