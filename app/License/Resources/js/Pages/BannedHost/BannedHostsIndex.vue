<template>
    <div>
        <div class="alert alert-info">
            <span>View existing banned hosts. If any banned host needs to be modified, click the IP address. If any banned
                host needs to be deleted, check the IP address and click the 'Submit' button.</span>
        </div>

        <AppAlert componentName="dataTableModal" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ lang('view_banned_hosts') }}</h4>
                <div class="card-tools">
                    <router-link to="/banned-hosts/create" class="btn btn-tool" v-tooltip="lang('create_banned_host')">
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

const endPoint = baseUrl + '/api/admin/viewBannedHost'

const columns = ['banned_host_ip', 'comments', 'banned_host_date', 'actions']

const options = reactive({
    sortIcon: {
        base: 'glyphicon',
        up: 'glyphicon-chevron-down',
        down: 'glyphicon-chevron-up'
    },
    texts: { filter: '', limit: '' },
    sortable: ['banned_host_ip', 'comments', 'banned_host_date', 'banned_host_blocks', 'banned_host_last_block_date'],
    filterable: ['banned_host_ip'],
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
                data.edit_url = '/banned-hosts/' + data.id + '/edit'
                data.delete_url = (document.getElementById('app-root')?.dataset?.baseUrl ?? '') + '/api/admin/bannedHosts/delete'
                data.keyVal = 'id'
                data.idVal = data.id
                return data
            }),
            count: data.data.total
        }
    },
    columnsClasses: {
        banned_host_ip: 'banned_host_ip',
        comments: 'comments',
        banned_host_date: 'banned_host_date',
    },
    templates: {
        banned_host_ip(h, row) {
            return row.banned_host_ip ? row.banned_host_ip : '---'
        },
        comments(h, row) {
            return row.comments ? row.comments : '---'
        },
        banned_host_date(h, row) {
            return row.banned_host_date
        },
    },
    pagination: { show: false },
    headings: {
        banned_host_ip: lang('ip_address'),
        comments: lang('comments'),
        banned_host_date: lang('date'),
        actions: lang('actions')
    },
})
</script>
