<template>
    <div>
        <AppAlert componentName="dataTableModal" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ lang('view_whitelist_ip') }}</h4>
                <div class="card-tools">
                    <router-link to="/whitelist/create" class="btn btn-tool" v-tooltip="lang('create_whitelist_ip')">
                        <i class="fas fa-plus"></i>
                    </router-link>
                </div>
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
import { reactive } from 'vue'
import { lang } from '@/helpers/extraLogics'

const baseUrl = document.getElementById('app-root')?.dataset?.baseUrl ?? ''

const endPoint = baseUrl + '/api/admin/view-Whitelist'

const columns = ['whitelist_host_ip', 'whitelist_host_comments', 'whitelist_host_date', 'actions']

const options = reactive({
    sortIcon: {
        base: 'glyphicon',
        up: 'glyphicon-chevron-down',
        down: 'glyphicon-chevron-up'
    },
    texts: { filter: '', limit: '' },
    sortable: ['whitelist_host_comments', 'whitelist_host_date'],
    filterable: ['whitelist_host_ip'],
    columnsClasses: {
        whitelist_host_ip: 'whitelist_host_ip',
        whitelist_host_comments: 'whitelist_host_comments',
        whitelist_host_date: 'whitelist_host_date',
    },
    pagination: { show: false },
    requestAdapter(data) {
        return {
            'sort_field': data.orderBy ? data.orderBy : 'id',
            'sort_order': data.ascending ? 'asc' : 'desc',
            'search_query': data.query,
            perPage: data.limit,
            page: data.page,
        }
    },
    responseAdapter({ data }) {
        return {
            data: data.data.data.map(data => {
                data.edit_url = '/whitelist/' + data.id + '/edit'
                data.delete_url = (document.getElementById('app-root')?.dataset?.baseUrl ?? '') + '/api/admin/delete-whitelist-ip'
                data.keyVal = 'id'
                data.idVal = data.id
                return data
            }),
            count: data.data.total
        }
    },
    templates: {
        whitelist_host_ip(h, row) {
            return row.whitelist_host_ip ? row.whitelist_host_ip : '---'
        },
        whitelist_host_comments(h, row) {
            return row.whitelist_host_comments ? row.whitelist_host_comments : '---'
        },
        whitelist_host_date(h, row) {
            return row.whitelist_host_date
        },
    },
    headings: {
        whitelist_host_ip: 'IP Address',
        whitelist_host_comments: 'Comments',
        whitelist_host_date: 'Date',
        actions: 'Actions'
    },
})
</script>
