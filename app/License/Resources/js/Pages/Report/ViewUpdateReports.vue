<template>
    <div>
        <AppAlert componentName="dataTableModal" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ lang('view_update_reports') }}</h4>
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

const endPoint = baseUrl + '/api/admin/reportUpdate'

const columns = ['report_text', 'product', 'report_date_time', 'report_status']

const options = reactive({
    sortable: ['report_text', 'report_date_time', 'report_status'],
    filterable: ['report_text'],
    requestAdapter(data) {
        return {
            'sort_field': data.orderBy ? data.orderBy : 'report_date_time',
            'sort_order': data.ascending ? 'asc' : 'desc',
            'search_query': data.query.trim(),
            perPage: data.limit,
            page: data.page,
        }
    },
    responseAdapter({ data }) {
        return {
            data: data.data.data.map(data => {
                data.keyVal = 'id'
                data.idVal = data.id
                return data
            }),
            count: data.data.total
        }
    },
    columnsClasses: {
        product: 'dt-name',
        report_date_time: 'dt-date',
        report_text: 'dt-text',
        report_status: 'dt-status',
    },
    templates: {
        report_date_time: (f, row) => row.report_date_time || '—',
        product: (f, row) => {
            if (row.product_title && row.product_id) {
                return h(RouterLink, { to: '/products/' + row.product_id + '/edit' }, () => [row.product_title])
            }
            return '—'
        },
        report_status: (f, row) => {
            return h('span', { class: row.report_status ? 'badge bg-success' : 'badge bg-danger' },
                row.report_status ? lang('success') : lang('error'))
        },
    },
    headings: {
        product: lang('product'),
        report_text: lang('report'),
        report_date_time: lang('report_date_time'),
        report_status: lang('status'),
    },
})
</script>
