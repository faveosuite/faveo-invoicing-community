<template>
    <div>
        <AppAlert componentName="dataTableModal" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ lang('view_cracking_reports') }}</h4>
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

const endPoint = baseUrl + '/api/admin/reportCracking'

const columns = ['report_text', 'license_code', 'report_date_time', 'report_status']

const options = reactive({
    sortIcon: {
        base: 'glyphicon',
        up: 'glyphicon-chevron-down',
        down: 'glyphicon-chevron-up'
    },
    texts: { filter: '', limit: '' },
    sortable: ['report_text', 'license_code', 'report_date_time', 'report_status'],
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
        license_code: 'license_code',
        report_date_time: 'report_date_time',
        report_status: 'report_status',
        report_text: 'report_text',
    },
    templates: {
        license_date(h, row) {
            return row.license_date
        },
        latest_callback_date_time(h, row) {
            return row.latest_callback_date_time
        },
        report_date_time(h, row) {
            return row.report_date_time
        },
        license_code: (f, row) => {
            if (row.license_code && row.license_id) {
                return h(RouterLink, {
                    to: '/licenses/' + row.license_id + '/view'
                }, [row.license_code.match(/.{1,4}/g).join('-')])
            } else if (row.license_code) {
                return row.license_code.match(/.{1,4}/g).join('-')
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
        report_status: lang('status'),
        license_code: lang('license_code'),
        report_text: lang('report'),
        report_date_time: lang('report_date_time'),
    },
})
</script>
