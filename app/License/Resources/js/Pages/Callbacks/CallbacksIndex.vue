<template>
    <div>
        <div class="alert alert-info">
            <span>{{ lang('callbacks_description') }}</span>
        </div>

        <AppAlert componentName="product" />

        <div class="card">
            <div class="card-header data-table-header border-0 p-0 pt-1">
                <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                    <li class="nav-item" @click="updateData('license')">
                        <span class="nav-link card-header-link" :class="{ active: activeTab === 'license' }" data-bs-toggle="pill" role="tab">{{ lang('license_callbacks') }}</span>
                    </li>
                    <li class="nav-item" @click="updateData('update')">
                        <span class="nav-link card-header-link" :class="{ active: activeTab === 'update' }" data-bs-toggle="pill" role="tab">{{ lang('update_callbacks') }}</span>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <DataTable v-if="!loading" :url="endPoint" ref="dataTable" :dataColumns="columns" :option="tableOptions">
                    <template #actions="props"><table-actions :data="props.row" /></template>
                </DataTable>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, h, onBeforeMount } from 'vue'
import { RouterLink } from 'vue-router'
import { lang } from '@/helpers/extraLogics'

const baseUrl = document.getElementById('app-root')?.dataset?.baseUrl ?? ''

const endPoint = ref('')
const columns = ref([])
const loading = ref(true)
const activeTab = ref('license')
const tableOptions = ref({})

function updateData(value) {
    activeTab.value = value

    if (value === 'license') {
        loading.value = true
        endPoint.value = baseUrl + '/api/admin/showLicenseCallbacks'
        columns.value = ['product_title', 'license', 'callback_ip', 'callback_domain', 'callback_date_time', 'callback_status']
        tableOptions.value = {
            sortIcon: { base: 'glyphicon', up: 'glyphicon-chevron-down', down: 'glyphicon-chevron-up' },
            texts: { filter: '', limit: '' },
            sortable: ['product_title', 'callback_date_time', 'callback_status'],
            filterable: ['license'],
            requestAdapter(data) {
                return {
                    'sort_field': data.orderBy ? data.orderBy : 'id',
                    'sort_order': data.ascending ? 'desc' : 'asc',
                    'search_query': data.query.trim(),
                    perPage: data.limit,
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
                license: 'license',
                product: 'product_title',
                callback_ip: 'callback_ip',
                callback_domain: 'callback_domain',
                callback_date_time: 'callback_date_time',
                callback_status: 'callback_status',
            },
            templates: {
                callback_date_time(h, row) {
                    return row.callback_date_time
                },
                product_title: (f, row) => {
                    if (row.product_title && row.product_id) {
                        return h('a', { href: baseUrl + '/products/' + row.product_id + '/edit' }, [row.product_title])
                    } else {
                        return '----'
                    }
                },
                callback_status: (f, row) => {
                    return h('span', {
                        'class': row.callback_status ? 'text-success' : 'text-danger'
                    }, row.callback_status ? lang('active') : lang('inactive'))
                },
                callback_domain: (f, row) => {
                    if (row.callback_domain) {
                        return h('a', { href: 'https://' + row.callback_domain, target: '_blank' }, [row.callback_domain])
                    } else {
                        return '----'
                    }
                },
                license: (f, row) => {
                    if (row.license_code && row.license_id) {
                        return h(RouterLink, { to: '/licenses/' + row.license_id + '/view' }, [row.license_code.match(/.{1,4}/g).join('-')])
                    } else {
                        return '----'
                    }
                },
            },
            pagination: { show: false },
            headings: {
                license: lang('license_code'),
                product_title: lang('product'),
                callback_ip: lang('ip_address'),
                callback_domain: lang('domain'),
                callback_date_time: lang('date'),
                callback_status: lang('status')
            },
        }
        loading.value = false
    } else {
        loading.value = true
        endPoint.value = baseUrl + '/api/admin/showUpdateCallbacks'
        columns.value = ['product_title', 'version', 'callback_ip', 'callback_types', 'callback_date_time', 'callback_status']
        tableOptions.value = {
            sortIcon: { base: 'glyphicon', up: 'glyphicon-chevron-down', down: 'glyphicon-chevron-up' },
            texts: { filter: '', limit: '' },
            sortable: ['product_title', 'callback_types', 'callback_date_time', 'callback_status'],
            filterable: ['product_title'],
            requestAdapter(data) {
                return {
                    'sort_field': data.orderBy ? data.orderBy : 'id',
                    'sort_order': data.ascending ? 'desc' : 'asc',
                    'search_query': data.query.trim(),
                    perPage: data.limit,
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
                product_title: 'product_title',
                version: 'version_number',
                callback_ip: 'callback_ip',
                callback_types: 'callback_types',
                callback_date_time: 'callback_date_time',
                callback_status: 'callback_status',
            },
            templates: {
                callback_date_time(h, row) {
                    return row.callback_date_time
                },
                product_title: (f, row) => {
                    if (row.product_title && row.product_id) {
                        return h('a', { href: baseUrl + '/products/' + row.product_id + '/edit' }, [row.product_title])
                    } else {
                        return '----'
                    }
                },
                version: (f, row) => {
                    if (row.version_number && row.version_id) {
                        return h(RouterLink, { to: '/versions/' + row.version_id + '/view' }, [row.version_number])
                    } else {
                        return '----'
                    }
                },
                callback_status: (f, row) => {
                    return h('span', {
                        'class': row.callback_status ? 'text-success' : 'text-success'
                    }, row.callback_status ? lang('active') : lang('inactive'))
                },
            },
            pagination: { show: false },
            headings: {
                product_title: lang('product'),
                version: lang('version'),
                callback_ip: lang('ip_address'),
                callback_types: lang('types'),
                callback_date_time: lang('date'),
                callback_status: lang('status'),
            },
        }
        loading.value = false
    }
}

onBeforeMount(() => {
    updateData('license')
})
</script>
