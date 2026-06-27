<template>
    <div>
        <AppAlert componentName="dataTableModal" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ lang('view_license_reports') }}</h4>
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
import { useDateTime } from '@/core/composables/useDateTime'

const { formatDateTime } = useDateTime()

const baseUrl = document.getElementById('app-root')?.dataset?.baseUrl ?? ''

const endPoint = baseUrl + '/api/admin/reportLicense'

const columns = ['report_text', 'user', 'license', 'report_date_time', 'report_status']

const options = reactive({
    sortable: ['product_title', 'report_text', 'report_date_time', 'report_status'],
    filterable: ['product_title', 'report_text'],
    requestAdapter(data) {
        return {
            'sort_field': data.orderBy ? data.orderBy : 'report_date_time',
            'sort_order': data.orderBy ? (data.ascending ? 'asc' : 'desc') : 'desc',
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
        license: 'dt-code',
        user: 'dt-email',
        report_date_time: 'dt-date',
        report_text: 'dt-text',
        report_status: 'dt-status',
    },
    templates: {
        report_date_time: (f, row) => formatDateTime(row.report_date_time),
        user: (f, row) => {
            if (row.client_email) {
                return h(RouterLink, { to: '/users/' + row.client_id },
                    [row.client_email])
            }
            return '—'
        },
        license: (f, row) => {
            if (row.license_code && row.license_id) {
                return h(RouterLink, { to: '/licenses/' + row.license_id + '/view' },
                    [row.license_code.match(/.{1,4}/g).join('-')])
            }
            return '—'
        },
        report_status: (f, row) => {
            return h('span', { class: row.report_status ? 'badge bg-success' : 'badge bg-danger' },
                row.report_status ? lang('success') : lang('error'))
        },
    },
    headings: {
        license: lang('license_code'),
        user: lang('email'),
        report_text: lang('report'),
        report_date_time: lang('report_date_time'),
        report_status: lang('status'),
    },
})
</script>
