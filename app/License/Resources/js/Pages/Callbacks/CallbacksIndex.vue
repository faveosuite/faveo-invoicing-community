<template>
    <div>
        <AppAlert componentName="product" />

        <div class="card card-light card-tabs">
            <div class="card-header p-0 pt-1 border-bottom-0">
                <ul class="nav nav-tabs" id="callbacks-tab" role="tablist">
                    <li class="nav-item" @click="updateData('license')">
                        <span class="nav-link clickable" :class="{ active: activeTab === 'license' }" role="tab">
                            {{ lang('license_callbacks') }}
                        </span>
                    </li>
                    <li class="nav-item" @click="updateData('update')">
                        <span class="nav-link clickable" :class="{ active: activeTab === 'update' }" role="tab">
                            {{ lang('update_callbacks') }}
                        </span>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <DataTable v-if="endPoint" :url="endPoint" ref="dataTable" :dataColumns="columns" :option="tableOptions">
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
import { useDateTime } from '@/core/composables/useDateTime'

const { formatDateTime } = useDateTime()

const endPoint = ref('')
const columns = ref([])
const activeTab = ref('license')
const tableOptions = ref({})

function buildRequestAdapter(data) {
    return {
        'sort_field': data.orderBy ? data.orderBy : 'id',
        'sort_order': data.ascending ? 'desc' : 'asc',
        'search_query': data.query.trim(),
        page: data.page,
        perPage: data.limit,
    }
}

function buildResponseAdapter({ data }) {
    return {
        data: data.data.data.map(item => {
            item.keyVal = 'id'
            item.idVal = item.id
            return item
        }),
        count: data.data.total
    }
}

function updateData(value) {
    activeTab.value = value

    if (value === 'license') {
        endPoint.value = '/api/admin/showLicenseCallbacks'
        columns.value = ['product_title', 'license', 'callback_ip', 'callback_domain', 'callback_date_time', 'callback_status']
        tableOptions.value = {
            sortable: ['product_title', 'callback_date_time', 'callback_status'],
            filterable: ['license'],
            requestAdapter: buildRequestAdapter,
            responseAdapter: buildResponseAdapter,
            columnsClasses: {
                license: 'dt-code',
                product_title: 'dt-name',
                callback_ip: 'dt-code',
                callback_domain: 'dt-text',
                callback_date_time: 'dt-date',
                callback_status: 'dt-status',
            },
            templates: {
                callback_date_time: (f, row) => formatDateTime(row.callback_date_time),
                product_title: (f, row) => {
                    if (row.product_title && row.product_id) {
                        return h(RouterLink, { to: '/products/' + row.product_id + '/edit' }, () => [row.product_title])
                    }
                    return '—'
                },
                callback_status: (f, row) => {
                    return h('span', { class: row.callback_status ? 'badge bg-success' : 'badge bg-danger' },
                        row.callback_status ? lang('active') : lang('inactive'))
                },
                callback_domain: (f, row) => {
                    if (row.callback_domain) {
                        return h('a', { href: 'https://' + row.callback_domain, target: '_blank' }, [row.callback_domain])
                    }
                    return '—'
                },
                license: (f, row) => {
                    if (row.license_code && row.license_id) {
                        return h(RouterLink, { to: '/licenses/' + row.license_id + '/view' },
                            [row.license_code.match(/.{1,4}/g).join('-')])
                    }
                    return '—'
                },
            },
            headings: {
                license: lang('license_code'),
                product_title: lang('product'),
                callback_ip: lang('ip_address'),
                callback_domain: lang('domain'),
                callback_date_time: lang('date'),
                callback_status: lang('status')
            },
        }
    } else {
        endPoint.value = '/api/admin/showUpdateCallbacks'
        columns.value = ['product_title', 'version', 'callback_ip', 'callback_types', 'callback_date_time', 'callback_status']
        tableOptions.value = {
            sortable: ['product_title', 'callback_types', 'callback_date_time', 'callback_status'],
            filterable: ['product_title'],
            requestAdapter: buildRequestAdapter,
            responseAdapter: buildResponseAdapter,
            columnsClasses: {
                product_title: 'dt-name',
                version: 'dt-code',
                callback_ip: 'dt-code',
                callback_types: 'dt-name',
                callback_date_time: 'dt-date',
                callback_status: 'dt-status',
            },
            templates: {
                callback_date_time: (f, row) => formatDateTime(row.callback_date_time),
                product_title: (f, row) => {
                    if (row.product_title && row.product_id) {
                        return h(RouterLink, { to: '/products/' + row.product_id + '/edit' }, () => [row.product_title])
                    }
                    return '—'
                },
                version: (f, row) => {
                    if (row.version_number && row.version_id) {
                        return h(RouterLink, { to: '/versions/' + row.version_id + '/view' }, [row.version_number])
                    }
                    return '—'
                },
                callback_status: (f, row) => {
                    return h('span', { class: 'badge bg-success' },
                        row.callback_status ? lang('active') : lang('inactive'))
                },
            },
            headings: {
                product_title: lang('product'),
                version: lang('version'),
                callback_ip: lang('ip_address'),
                callback_types: lang('types'),
                callback_date_time: lang('date'),
                callback_status: lang('status'),
            },
        }
    }
}

onBeforeMount(() => {
    updateData('license')
})
</script>


<style scoped>
.clickable { cursor: pointer; }
</style>
