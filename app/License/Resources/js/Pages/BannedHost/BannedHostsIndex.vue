<template>
    <div>
        <AppAlert componentName="dataTableModal" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ lang('view_banned_hosts') }}</h4>
                <div class="card-tools">
                    <router-link to="/banned-hosts/settings" class="btn btn-tool" v-tooltip="lang('security_settings')">
                        <i class="fas fa-gear"></i>
                    </router-link>
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
import { useDateTime } from '@/core/composables/useDateTime'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import { makeRequestAdapter } from '@/helpers/tableUtils'

const { formatDate } = useDateTime()

const baseUrl = useBaseUrl()

const endPoint = '/api/admin/viewBannedHost'

const columns = ['banned_host_ip', 'comments', 'banned_host_date', 'actions']

const options = reactive({
    sortable: ['banned_host_ip', 'comments', 'banned_host_date', 'banned_host_blocks', 'banned_host_last_block_date'],
    filterable: ['banned_host_ip'],
    requestAdapter: makeRequestAdapter('id'),
    responseAdapter({ data }) {
        return {
            data: data.data.data.map(data => {
                data.edit_url = '/banned-hosts/' + data.id + '/edit'
                data.delete_url = baseUrl + '/api/admin/bannedHosts/delete'
                data.keyVal = 'id'
                data.idVal = data.id
                return data
            }),
            count: data.data.total
        }
    },
    columnsClasses: {
        banned_host_ip: 'dt-code',
        comments: 'dt-text',
        banned_host_date: 'dt-date',
        actions: 'dt-action',
    },
    templates: {
        banned_host_ip: (f, row) => row.banned_host_ip || '—',
        comments: (f, row) => row.comments || '—',
        banned_host_date: (f, row) => formatDate(row.banned_host_date),
    },
    headings: {
        banned_host_ip: lang('ip_address'),
        comments: lang('comments'),
        banned_host_date: lang('date'),
        actions: lang('actions')
    },
})
</script>
