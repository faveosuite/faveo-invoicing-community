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

const baseUrl = document.getElementById('app-root')?.dataset?.baseUrl ?? ''

const endPoint = baseUrl + '/api/admin/reportLicense'

const columns = ['report_text', 'user', 'license', 'report_date_time', 'report_status']

const options = reactive({
    sortIcon: {
        base: 'glyphicon',
        up: 'glyphicon-chevron-down',
        down: 'glyphicon-chevron-up',
    },
    texts: { filter: '', limit: '' },
    sortable: ['product_title', 'report_text', 'report_date_time', 'report_status'],
    filterable: ['product_title', 'report_text'],
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
        product_title: 'license_product_title',
        license: 'license_code',
        user: 'client_email',
        report_date_time: 'report_date_time',
        report_text: 'report_text',
        report_status: 'status',
    },
    pagination: { show: false },
    headings: {
        license: lang('license_code'),
        user: lang('email'),
        report_text: lang('report'),
        report_date_time: lang('report_date_time'),
        report_status: lang('status'),
    },
    templates: {
        user(f, row) {
            if (row.client_email) {
                return h('a', {
                    href: (document.getElementById('app-root')?.dataset?.baseUrl ?? '') + '/clients/' + row.client_id
                }, [row.client_email])
            } else {
                return '----'
            }
        },
        report_date_time(h, row) {
            return row.report_date_time
        },
        license: (f, row) => {
            if (row.license_code && row.license_id) {
                return h(RouterLink, {
                    to: '/licenses/' + row.license_id + '/view'
                }, [row.license_code.match(/.{1,4}/g).join('-')])
            } else {
                return '----'
            }
        },
        report_status: (f, row) => {
            return h('span', {
                'class': row.report_status ? 'text-success' : 'text-danger'
            }, row.report_status ? lang('success') : lang('error'))
        },
    }
})
</script>
