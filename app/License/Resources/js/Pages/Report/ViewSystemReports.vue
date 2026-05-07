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
import { lang } from '@/helpers/extraLogics'

const baseUrl = document.getElementById('app-root')?.dataset?.baseUrl ?? ''

const endPoint = baseUrl + '/api/admin/reportSystem'

const columns = ['report_text', 'user_formatted', 'report_date_time', 'report_status']

const options = reactive({
    sortIcon: {
        base: 'glyphicon',
        up: 'glyphicon-chevron-down',
        down: 'glyphicon-chevron-up'
    },
    texts: { filter: '', limit: '' },
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
        report_date_time: 'report_date_time',
        report_text: 'report_text',
        report_status: 'status',
        user_formatted: 'format'
    },
    templates: {
        license_code(h, row) {
            return row.license_code ? row.license_code : '---'
        },
        report_date_time(h, row) {
            return row.report_date_time
        },
        license_date(h, row) {
            return row.license_date
        },
        latest_callback_date_time(h, row) {
            return row.latest_callback_date_time
        },
        user_formatted: (f, row) => {
            if (row.user_formatted && row.user_formatted !== 'System') {
                return h('a', {
                    href: baseUrl + '/clients/' + row.account_id
                }, [row.user_formatted])
            } else if (row.user_formatted && row.user_formatted === 'System') {
                return row.user_formatted
            } else {
                return '----'
            }
        },
        report_status: (f, row) => {
            return h('span', {
                'class': row.report_status ? 'text-success' : 'text-danger'
            }, row.report_status ? lang('success') : lang('error'))
        },
    },
    pagination: { show: false },
    headings: {
        report_text: lang('report'),
        report_date_time: lang('report_date_time'),
        report_status: lang('status'),
        user_formatted: lang('user'),
    },
})
</script>
