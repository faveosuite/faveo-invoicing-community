<template>
    <div>
        <AppAlert componentName="dataTableModal" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ lang('all_versions') }}</h4>
            </div>
            <div class="card-body">
                <DataTable :url="endPoint" :dataColumns="columns" :option="options">
                    <template #actions="props"><table-actions :data="props.row" /></template>
                </DataTable>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, h } from 'vue'
import { RouterLink } from 'vue-router'
import { lang } from '@/helpers/extraLogics'

const baseUrl = document.getElementById('app-root')?.dataset?.baseUrl ?? ''

const endPoint = baseUrl + '/api/admin/viewVersions'

const columns = ['version_number', 'product_title', 'version_date', 'version_install_count', 'callback_count', 'version_status', 'actions']

const options = reactive({
    sortable: ['product_title', 'version_date', 'version_install_count', 'callback_count', 'version_status'],
    filterable: ['product_title'],
    requestAdapter(data) {
        return {
            'sort_field': data.orderBy ? data.orderBy : 'id',
            'sort_order': data.ascending ? 'asc' : 'desc',
            'search_query': data.query.trim(),
            perPage: data.limit,
            page: data.page,
        }
    },
    responseAdapter({ data }) {
        return {
            data: data.data.data.map(data => {
                data.view_url = '/versions/' + data.id + '/view'
                data.keyVal = 'id'
                data.idVal = data.id
                return data
            }),
            count: data.data.total
        }
    },
    columnsClasses: {
        product_title: 'dt-name',
        version_number: 'dt-code',
        version_date: 'dt-date',
        version_install_count: 'dt-number',
        callback_count: 'dt-number',
        version_status: 'dt-status',
        actions: 'dt-action',
    },
    templates: {
        product_title: (f, row) => {
            if (row.product_title && row.product_id) {
                return h(RouterLink, { to: '/products/' + row.product_id + '/edit' }, () => [row.product_title])
            }
            return '—'
        },
        version_number: (f, row) => {
            if (row.version_number && row.id) {
                return h(RouterLink, { to: '/versions/' + row.id + '/view' }, [row.version_number])
            }
            return '—'
        },
        version_status: (f, row) => {
            return h('span', { class: row.version_status ? 'badge bg-success' : 'badge bg-danger' },
                row.version_status ? lang('active') : lang('inactive'))
        },
    },
    headings: {
        product_title: lang('product'),
        version_number: lang('version'),
        version_date: lang('release_date'),
        version_install_count: lang('installs_count'),
        callback_count: lang('callbacks_count'),
        version_status: lang('status'),
        actions: lang('actions')
    },
})
</script>
