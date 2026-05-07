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
import { lang } from '@/helpers/extraLogics'

const baseUrl = document.getElementById('app-root')?.dataset?.baseUrl ?? ''

const endPoint = baseUrl + '/api/admin/reportUpdate'

const columns = ['report_text', 'product', 'report_date_time', 'report_status']

const options = reactive({
    sortIcon: {
        base: 'glyphicon',
        up: 'glyphicon-chevron-down',
        down: 'glyphicon-chevron-up'
    },
    texts: { filter: '', limit: '' },
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
        product: 'license_product_title',
        report_date_time: 'report_date_time',
        report_text: 'report_text',
        report_status: 'status',
    },
    templates: {
        report_date_time(h, row) {
            return row.report_date_time
        },
        product: (f, row) => {
            if (row.product_title && row.product_id) {
                return h('a', {
                    href: baseUrl + '/products/' + row.product_id + '/edit'
                }, [row.product_title])
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
        product: lang('product'),
        report_text: lang('report'),
        report_date_time: lang('report_date_time'),
        report_status: lang('status'),
    },
})
</script>
