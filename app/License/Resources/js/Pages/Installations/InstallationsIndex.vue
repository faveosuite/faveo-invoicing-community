<template>
    <div>
        <AppAlert componentName="dataTableModal" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ lang('all_installations') }}</h4>
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

const endPoint = baseUrl + '/api/admin/viewInstallations'

const columns = [
    'product_title', 'license', 'client_email', 'installation_domain', 'installation_ip',
    'installation_date', 'installation_status', 'actions'
]

const options = reactive({
    sortIcon: {
        base: 'glyphicon',
        up: 'glyphicon-chevron-down',
        down: 'glyphicon-chevron-up'
    },
    texts: { filter: '', limit: '' },
    sortable: ['product_title', 'installation_status'],
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
                data.edit_url = '/installations/' + data.id + '/edit'
                data.delete_url = (document.getElementById('app-root')?.dataset?.baseUrl ?? '') + '/api/admin/installations/delete'
                data.view_url = '/installations/' + data.id + '/view'
                data.keyVal = 'id'
                data.idVal = data.id
                return data
            }),
            count: data.data.total
        }
    },
    columnsClasses: {
        product_title: 'product_title',
        license: 'i_license_code',
        client_email: 'client_email',
        installation_domain: 'installation_domain',
        installation_ip: 'installation_ip',
        installation_date: 'i_latest_installation',
        installation_status: 'i_installation_status',
    },
    templates: {
        installation_date(h, row) {
            return row.installation_date
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
        license: (f, row) => {
            if (row.license_code && row.license_id) {
                return h(RouterLink, {
                    to: '/licenses/' + row.license_id + '/view'
                }, [row.license_code.match(/.{1,4}/g).join('-')])
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
        installation_domain: (f, row) => {
            if (row.installation_domain) {
                return h('a', {
                    href: 'https://' + row.installation_domain,
                    target: '_blank'
                }, [row.installation_domain])
            } else {
                return '----'
            }
        },
        installation_status: (f, row) => {
            return h('span', {
                'class': row.installation_status ? 'text-success' : 'text-danger'
            }, row.installation_status ? lang('active') : lang('inactive'))
        },
    },
    pagination: { show: false },
    headings: {
        product_title: lang('product'),
        license: lang('license_code'),
        client_email: lang('email'),
        installation_domain: lang('domain'),
        installation_ip: lang('ip'),
        installation_date: lang('installation_date'),
        installation_status: lang('status'),
        actions: lang('actions')
    },
})
</script>
