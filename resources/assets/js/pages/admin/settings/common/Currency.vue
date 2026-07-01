<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.all_currency') }}</h4>
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
import { makeRequestAdapter } from '@/helpers/tableUtils'

const COMPONENT = 'currency'
const apiUrl  = `/currency/list`

const dtRef            = ref(null)
const toggling         = ref(null)
const settingDefault   = ref(null)
const settingDashboard = ref(null)

async function toggleStatus(row) {
    toggling.value = row.id
    try {
        const res = await http.post(`/currency/update-currency`, {
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
        const res = await http.post(`/currency/default-currency/${id}`)
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
        const res = await http.post(`/currency/dashboard-currency/${id}`)
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
        name:       __('message.name'),
        code:       __('message.code'),
        symbol:     __('message.symbol'),
        is_default: __('message.default'),
        dashboard:  __('message.dashboard_currency'),
        action:     __('message.action'),
    },
    columnsClasses: {
        name: 'dt-name',
        code: 'dt-code',
        symbol: 'dt-code',
        is_default: 'dt-status',
        dashboard: 'dt-status',
        action: 'dt-action',
    },
    templates: {
        name:   (f, row) => row.name   || '—',
        code:   (f, row) => row.code   || '—',
        symbol: (f, row) => row.symbol || '—',
        is_default: (f, row) => h('span', {
            class: row.is_default ? 'badge bg-success' : 'badge bg-danger',
        }, row.is_default ? __('message.yes') : __('message.no')),
        dashboard: (f, row) => h('span', {
            class: row.dashboard_currency ? 'badge bg-success' : 'badge bg-danger',
        }, row.dashboard_currency ? __('message.yes') : __('message.no')),
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
    sortable:   ['name', 'code', 'symbol'],
    filterable: true,
    requestAdapter: makeRequestAdapter('status'),
    orderBy: { column: 'status', ascending: false },
})
</script>
