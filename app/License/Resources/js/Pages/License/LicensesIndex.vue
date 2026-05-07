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

const endPoint = baseUrl + '/api/admin/viewLicenses'

const dataColumns = [
    'license_code', 'client_email', 'product_title', 'license_order_number', 'license_domain', 'license_ip',
    'license_date', 'installation_counts', 'call_backs_count', 'latest_call_backs',
    'license_limit', 'license_expire_date', 'license_updates_date', 'license_support_date', 'license_status', 'actions'
]

const options = reactive({
    sortIcon: {
        base: 'glyphicon',
        up: 'glyphicon-chevron-down',
        down: 'glyphicon-chevron-up'
    },
    texts: { filter: '', limit: '' },
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
        product_title: 'product_title',
        license_ip: 'license_ip',
        license_domain: 'license_domain',
        license_code: 'license_code',
        client_email: 'client_email',
        license_order_number: 'license_order_number',
        installation_counts: 'installation_counts',
        call_backs_count: 'license_callbacks',
        latest_call_backs: 'latest_call_backs',
        license_limit: 'license_limit',
        license_expire_date: 'license_expire_date',
        license_updates_date: 'license_updates_date',
        license_support_date: 'license_support_date',
        license_status: 'license_status',
        actions: 'actions',
    },
    templates: {
        license_ip(h, row) {
            return row.license_ip ? row.license_ip : '----'
        },
        license_updates_date(h, row) {
            return row.license_updates_date ? row.license_updates_date : '----'
        },
        latest_call_backs(h, row) {
            return row.latest_call_backs ? row.latest_call_backs : '----'
        },
        license_support_date(h, row) {
            return row.license_support_date ? row.license_support_date : '----'
        },
        license_date(h, row) {
            return row.license_date ? row.license_date : '----'
        },
        license_expire_date(h, row) {
            return row.license_expire_date ? row.license_expire_date : '----'
        },
        license_code: (f, row) => {
            if (row.license_code && row.id) {
                return h(RouterLink, {
                    to: '/licenses/' + row.id + '/view'
                }, [row.license_code.match(/.{1,4}/g).join('-')])
            } else {
                return '----'
            }
        },
        product_title: (f, row) => {
            if (row.product_title && row.product_id) {
                return h('a', {
                    href: baseUrl + '/products/' + row.product_id + '/edit'
                }, [row.product_title])
            } else {
                return '----'
            }
        },
        client_email: (f, row) => {
            if (row.client_email) {
                return h('a', {
                    href: baseUrl + '/clients/' + row.client_id
                }, [row.client_email])
            } else {
                return '----'
            }
        },
        license_domain: (f, row) => {
            if (row.license_domain) {
                return h('a', {
                    href: 'https://' + row.license_domain,
                    target: '_blank'
                }, [row.license_domain])
            } else {
                return '----'
            }
        },
        license_status: (f, row) => {
            return h('span', {
                'class': row.license_status ? 'text-success' : 'text-danger'
            }, row.license_status ? lang('active') : lang('inactive'))
        },
        license_order_number: (f, row) => {
            if (row.license_order_number) {
                return h('a', {
                    href: baseUrl + '/orders/license/' + row.license_order_number,
                    target: '_blank'
                }, [row.license_order_number])
            } else {
                return '----'
            }
        }
    },
    pagination: { show: false },
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
