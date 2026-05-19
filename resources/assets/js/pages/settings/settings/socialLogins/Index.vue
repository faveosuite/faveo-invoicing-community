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

const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl = `${baseUrl}/social-logins`

const columns = ['type', 'status', 'action']

const tableOptions = reactive({
    headings: {
        type:   __('message.type'),
        status: __('message.status'),
        action: __('message.action'),
    },
    templates: {
        type:   (f, row) => row.type || '—',
        status:    (f, row) => h('span', {
            class: row.status ? 'badge bg-success' : 'badge bg-danger',
        }, row.status ? __('message.active') : __('message.inactive')),
        action:    (f, row) => h(RouterLink, { to: `/settings/social-logins/${row.id}/edit`, class: 'btn btn-light table_btn', title: __('message.edit') }, () => h('i', { class: 'fas fa-edit' })),
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
