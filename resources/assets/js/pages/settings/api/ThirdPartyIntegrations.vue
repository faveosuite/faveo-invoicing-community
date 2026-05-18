<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.third_party_integrations') }}</h4>
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
import { h, ref, reactive, resolveComponent } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'third-party-integrations'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl  = `${baseUrl}/module-settings`

const router    = useRouter()
const dtRef     = ref(null)
const savingKey = ref(null)
const columns   = ['title', 'action']

async function toggle(row, newVal) {
    // Enabling a module that requires configuration → go to settings page first.
    // The settings page is responsible for saving config and enabling the module.
    if (newVal && row.route) {
        dtRef.value?.refresh()   // revert the switch back to off
        router.push(row.route)
        return
    }

    savingKey.value = row.key
    try {
        const payload = { [row.key]: newVal ? 1 : 0 }
        const res = await http.post(`${baseUrl}/licenseStatus`, payload)
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        savingKey.value = null
        dtRef.value?.refresh()
    }
}

const tableOptions = reactive({
    headings: {
        title:  '',
        action: '',
    },
    templates: {
        title: (f, row) => h('div', {}, [
            h('div', { class: 'fw-normal' }, [
                row.route && row.enabled
                    ? h(RouterLink, { to: row.route }, () => h('b', {}, row.name))
                    : row.name,
            ]),
            h('p', { class: 'mb-0 mt-2 text-muted' }, row.description),
        ]),
        action: (f, row) => h(resolveComponent('status-switch'), {
            name:      row.key,
            value:     row.enabled ? 1 : 0,
            disabled:  savingKey.value === row.key ? 1 : 0,
            classname: 'float-end me-1',
            onChange:  (val) => toggle(row, val),
        }),
    },
    skin:       'table',
    sortable:   [],
    filterable: true,
    requestAdapter(data) {
        return {
            'search-query': (data.query ?? '').trim(),
            page:           data.page,
            limit:          data.limit,
        }
    },
})
</script>

<style scoped>
/* Hide the empty header row */
:deep(.VueTables__table thead) {
    display: none;
}

/* Match favMer column widths */
:deep(.VueTables__table td:first-child) {
    width: 90%;
    border-top: none;
    border-bottom: 1px solid #dee2e6;
}

:deep(.VueTables__table td:last-child) {
    width: 10%;
    border-top: none;
    border-bottom: 1px solid #dee2e6;
    vertical-align: middle;
    text-align: right;
}

/* Remove bottom border on last row */
:deep(.VueTables__table tr:last-child td) {
    border-bottom: none;
}

:deep(.fw-normal) {
    font-size: 14px;
    font-weight: 500;
}
</style>
