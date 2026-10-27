<template>
    <div>
        <AppAlert componentName="social-logins-index" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.social_logins') }}</h4>
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
import { makeRequestAdapter } from '@/helpers/tableUtils'
const apiUrl = `/social-logins`

const columns = ['type', 'status', 'action']

const tableOptions = reactive({
    headings: {
        type:   __('message.type'),
        status: __('message.status'),
        action: __('message.action'),
    },
    columnsClasses: {
        type: 'dt-name',
        status: 'dt-status',
        action: 'dt-action',
    },
    templates: {
        type:   (f, row) => row.type || '—',
        status:    (f, row) => h('span', {
            class: row.status ? 'badge bg-success' : 'badge bg-danger',
        }, row.status ? __('message.active') : __('message.inactive')),
        action:    (f, row) => h(RouterLink, { to: `/settings/social-logins/${row.id}/edit`, class: 'btn btn-light table_btn', title: __('message.settings') }, () => h('i', { class: 'fas fa-gear' })),
    },
    sortable: ['type', 'status'],
    filterable: true,
    requestAdapter: makeRequestAdapter('created_at'),
    orderBy: { column: 'created_at', ascending: false },
})
</script>
