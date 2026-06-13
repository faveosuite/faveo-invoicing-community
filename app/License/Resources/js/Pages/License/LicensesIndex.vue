<template>
    <div>
        <AppAlert componentName="dataTableModal" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ lang('all_licenses') }}</h4>
                <div class="card-tools">
                    <router-link to="/licenses/create" class="btn btn-tool" v-tooltip="lang('create_license')">
                        <i class="fas fa-plus"></i>
                    </router-link>
                </div>
            </div>
            <div class="card-body">
                <DataTable :url="endPoint" :dataColumns="dataColumns" :option="options">
                    <template #table-tools>
                        <ColumnSelector
                            :entityType="'licenses'"
                            :pinStart="[]"
                            :pinEnd="['actions']"
                            :labels="columnLabels"
                            componentName="dataTableModal"
                            @change="onColumnsChange"
                        />
                    </template>
                    <template #actions="props"><table-actions :data="props.row" /></template>
                </DataTable>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, h } from 'vue'
import { RouterLink } from 'vue-router'
import { lang } from '@/helpers/extraLogics'
import { useDateTime } from '@/core/composables/useDateTime'
import ColumnSelector from '@/components/Reusable/ColumnSelector.vue'

const { formatDate } = useDateTime()

const baseUrl = document.getElementById('app-root')?.dataset?.baseUrl ?? ''

const endPoint = baseUrl + '/api/admin/viewLicenses'

// report_columns keys (type 'licenses') equal these column names 1:1, so no
// key map is needed — the ColumnSelector emits the names this table uses.
const DEFAULT_COLUMNS = [
    'license_code', 'client_email', 'product_title', 'license_order_number', 'license_domain', 'license_ip',
    'license_date', 'installation_counts', 'call_backs_count', 'latest_call_backs',
    'license_limit', 'license_expire_date', 'license_updates_date', 'license_support_date', 'license_status', 'actions'
]
const dataColumns = ref([...DEFAULT_COLUMNS])

// Labels shown in the ColumnSelector dropdown (keyed by column key).
const columnLabels = {
    license_code:         lang('license_code'),
    client_email:         lang('email'),
    product_title:        lang('product'),
    license_order_number: lang('order_number'),
    license_domain:       lang('license_domain'),
    license_ip:           lang('license_ip'),
    license_date:         lang('date'),
    installation_counts:  lang('installations_count'),
    call_backs_count:     lang('callbacks_count'),
    latest_call_backs:    lang('latest_callbacks'),
    license_limit:        lang('license_limit'),
    license_expire_date:  lang('license_expiry'),
    license_updates_date: lang('updates_expiry'),
    license_support_date: lang('support_expiry'),
    license_status:       lang('status'),
}

function onColumnsChange(reportKeys) {
    dataColumns.value = reportKeys.length ? [...reportKeys] : [...DEFAULT_COLUMNS]
}

const options = reactive({
    sortable: ['product_title', 'client_email', 'license_code', 'license_limit', 'license_order_number', 'license_expire_date', 'license_support_date', 'license_updates_date', 'license_status'],
    filterable: ['product_title'],
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
                data.edit_url = '/licenses/' + data.id + '/edit'
                data.delete_url = (document.getElementById('app-root')?.dataset?.baseUrl ?? '') + '/api/admin/license/delete'
                data.view_url = '/licenses/' + data.id + '/view'
                data.keyVal = 'id'
                data.idVal = data.id
                return data
            }),
            count: data.data.total
        }
    },
    columnsClasses: {
        product_title: 'dt-name',
        license_ip: 'dt-code',
        license_domain: 'dt-text',
        license_code: 'dt-code',
        client_email: 'dt-email',
        license_order_number: 'dt-number',
        installation_counts: 'dt-number',
        call_backs_count: 'dt-number',
        latest_call_backs: 'dt-date',
        license_limit: 'dt-number',
        license_expire_date: 'dt-date',
        license_updates_date: 'dt-date',
        license_support_date: 'dt-date',
        license_date: 'dt-date',
        license_status: 'dt-status',
        actions: 'dt-action',
    },
    templates: {
        license_ip: (f, row) => row.license_ip || '—',
        license_updates_date: (f, row) => formatDate(row.license_updates_date),
        latest_call_backs: (f, row) => row.latest_call_backs || '—',
        license_support_date: (f, row) => formatDate(row.license_support_date),
        license_date: (f, row) => formatDate(row.license_date),
        license_expire_date: (f, row) => row.license_expire_date || '—',
        license_code: (f, row) => {
            if (row.license_code && row.id) {
                return h(RouterLink, { to: '/licenses/' + row.id + '/view' },
                    [row.license_code.match(/.{1,4}/g).join('-')])
            }
            return '—'
        },
        product_title: (f, row) => {
            if (row.product_title && row.product_id) {
                return h(RouterLink, { to: '/products/' + row.product_id + '/edit' }, () => [row.product_title])
            }
            return '—'
        },
        client_email: (f, row) => {
            if (row.client_email) {
                return h(RouterLink, { to: '/users/' + row.client_id }, () => [row.client_email])
            }
            return '—'
        },
        license_domain: (f, row) => {
            if (row.license_domain) {
                return h('a', { href: 'https://' + row.license_domain, target: '_blank' }, [row.license_domain])
            }
            return '—'
        },
        license_status: (f, row) => {
            return h('span', { class: row.license_status ? 'badge bg-success' : 'badge bg-danger' },
                row.license_status ? lang('active') : lang('inactive'))
        },
        license_order_number: (f, row) => {
            if (row.license_order_number) {
                return h('a', { href: baseUrl + '/admin/orders/license/' + row.license_order_number, target: '_blank' },
                    [row.license_order_number])
            }
            return '—'
        }
    },
    headings: {
        product_title: lang('product'),
        license_ip: lang('license_ip'),
        license_domain: lang('license_domain'),
        client_email: lang('email'),
        license_code: lang('license_code'),
        license_order_number: lang('order_number'),
        installation_counts: lang('installations_count'),
        call_backs_count: lang('callbacks_count'),
        latest_call_backs: lang('latest_callbacks'),
        license_limit: lang('license_limit'),
        license_expire_date: lang('license_expiry'),
        license_updates_date: lang('updates_expiry'),
        license_support_date: lang('support_expiry'),
        license_status: lang('status'),
        actions: lang('actions')
    },
})
</script>
