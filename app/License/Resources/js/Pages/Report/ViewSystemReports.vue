<template>
    <div>
        <AppAlert componentName="dataTableModal" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ lang('view_system_reports') }}</h4>
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

const endPoint = baseUrl + '/api/admin/reportSystem'

const columns = ['report_text', 'user_formatted', 'report_date_time', 'report_status']

const options = reactive({
    sortable: ['report_text', 'user_formatted', 'report_date_time', 'report_status'],
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
        report_date_time: 'dt-date',
        report_text: 'dt-text',
        report_status: 'dt-status',
        user_formatted: 'dt-name',
    },
    templates: {
        report_date_time: (f, row) => formatDateTime(row.report_date_time),
        user_formatted: (f, row) => {
            if (row.user_formatted && row.user_formatted !== 'System') {
                return h(RouterLink, { to: '/users/' + row.account_id }, () => [row.user_formatted])
            } else if (row.user_formatted) {
                return row.user_formatted
            }
            return '—'
        },
        report_status: (f, row) => {
            return h('span', { class: row.report_status ? 'badge bg-success' : 'badge bg-danger' },
                row.report_status ? lang('success') : lang('error'))
        },
    },
    headings: {
        report_text: lang('report'),
        report_date_time: lang('report_date_time'),
        report_status: lang('status'),
        user_formatted: lang('user'),
    },
})
</script>
