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
    sortable: ['whitelist_host_comments', 'whitelist_host_date'],
    filterable: ['whitelist_host_ip'],
    columnsClasses: {
        whitelist_host_ip: 'dt-code',
        whitelist_host_comments: 'dt-text',
        whitelist_host_date: 'dt-date',
        actions: 'dt-action',
    },
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
        whitelist_host_ip: (f, row) => row.whitelist_host_ip || '—',
        whitelist_host_comments: (f, row) => row.whitelist_host_comments || '—',
        whitelist_host_date: (f, row) => row.whitelist_host_date || '—',
    },
    headings: {
        whitelist_host_ip: lang('ip_address'),
        whitelist_host_comments: lang('comments'),
        whitelist_host_date: lang('date'),
        actions: lang('actions')
    },
})
</script>
