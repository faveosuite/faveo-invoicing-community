<template>
    <div>
        <AppAlert :componentName="COMPONENT" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.cache') }}</h4>
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
import { RouterLink } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'cache-settings'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl  = `${baseUrl}/cache-settings/list`

const dtRef      = ref(null)
const activating = ref(null)

async function activate(driver) {
    activating.value = driver
    try {
        const res = await http.post(`${baseUrl}/cache-settings/${driver}/activate`)
        successHandler(res, COMPONENT)
        dtRef.value?.refresh()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        activating.value = null
    }
}

const columns = ['name', 'status', 'action']

const tableOptions = reactive({
    headings: {
        name:   __('message.name'),
        status: __('message.status'),
        action: __('message.action'),
    },
    columnsClasses: {
        name:   'dt-name',
        status: 'dt-status',
        action: 'dt-action',
    },
    templates: {
        name: (f, row) => {
            const driver = row.DriverDetails?.id
            const link   = row.DriverDetails?.name?.link
            const text   = row.DriverDetails?.name?.text ?? '—'
            if (link && driver) return h(RouterLink, { to: `/settings/common/cache/${driver}` }, () => text)
            return text
        },
        status: (f, row) => h('span', {
            class:  row.DriverDetails?.status?.code === 1 ? 'btn btn-success btn-sm' : 'btn btn-danger btn-sm',
            style: 'cursor:default',
        }, row.DriverDetails?.status?.label ?? '—'),
        action: (f, row) => {
            const driver       = row.DriverDetails?.id
            const isActivated  = row.DriverDetails?.action?.type === 'activated'
            const isConfigured = row.DriverDetails?.configured !== false
            const busy         = activating.value === driver
            const disabled     = isActivated || busy || !isConfigured

            return h('span', { title: isConfigured ? '' : __('message.activate_configure_first', { name: driver }) }, [
                h('button', {
                    class:    'btn btn-sm btn-primary',
                    disabled,
                    onClick:  () => !disabled && activate(driver),
                }, busy
                    ? [h('span', { class: 'spinner-border spinner-border-sm me-1' }), __('message.activate')]
                    : [h('i',    { class: 'fas fa-check-circle me-1' }),              __('message.activate')]
                ),
            ])
        },
    },
    sortable:   ['name'],
    filterable: false,
    requestAdapter(data) {
        return {
            'sort-field':   data.orderBy  ?? 'name',
            'sort-order':   data.orderBy ? (data.ascending ? 'asc' : 'desc') : 'desc',
            'search-query': (data.query   ?? '').trim(),
            page:           data.page,
            limit:          data.limit,
        }
    },
    responseAdapter({ data }) {
        const d       = data?.data ?? {}
        const drivers = d.drivers  ?? {}
        return {
            data:  drivers.data  ?? [],
            count: drivers.total ?? drivers.data?.length ?? 0,
        }
    },
    orderBy: { column: 'name', ascending: true },
})
</script>
