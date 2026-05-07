<template>
    <div>
        <AppAlert componentName="version-view" />

        <div class="card card-header-tabs card-outline">
            <div class="card-header card-header-dark card-light border-bottom">
                <h4 class="card-title">{{ product_heading }}</h4>
                <div class="card-tools"></div>
            </div>

            <div class="row card-body col-md-12">
                <div class="row pt-2 pb-2 border-bottom col-sm-6 ms-2 ps-0">
                    <label class="col-sm-6 fs-7 fw-bold">{{ lang('product_name') }}:</label>
                    <a :href="baseUrl + '/products/' + product_id + '/edit'" v-if="product_title" class="col-sm-6 fs-7">{{ product_title }}</a>
                    <span class="col-sm-6" v-else>----</span>
                </div>

                <div class="row pt-2 pb-2 border-bottom col-sm-6 ms-2 ps-0">
                    <label class="col-sm-6 fs-7 fw-bold">{{ lang('version_date') }}:</label>
                    <div v-if="version_date" class="col-sm-6 fs-7">{{ version_date }}</div>
                    <span class="col-sm-6" v-else>----</span>
                </div>

                <div class="row pt-2 pb-2 col-sm-6 ms-2 ps-0">
                    <label class="col-sm-6 fs-7 fw-bold">{{ lang('installs_count') }}:</label>
                    <div v-if="version_install_count" class="col-sm-6 fs-7">{{ version_install_count }}</div>
                    <span class="col-sm-6" v-else>----</span>
                </div>

                <div class="row pt-2 pb-2 col-sm-6 ms-2 ps-0">
                    <label class="col-sm-6 fs-7 fw-bold">{{ lang('version_status') }}:</label>
                    <div v-if="version_status" class="col-sm-6 text-sm text-success">{{ lang('active') }}</div>
                    <div v-else class="col-sm-6 text-sm text-danger">{{ lang('inactive') }}</div>
                </div>
            </div>
        </div>

        <div class="card card-header-tabs">
            <div class="card-header data-table-header p-0 pt-1">
                <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                    <li class="nav-item">
                        <span class="nav-link active" data-bs-toggle="pill" role="tab">{{ lang('callbacks') }}</span>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <DataTable :url="endPoint" ref="dataTable" :dataColumns="columns" :option="tableOptions">
                    <template #actions="props"><table-actions :data="props.row" /></template>
                </DataTable>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, h, onBeforeMount } from 'vue'
import { getIdFromUrl, lang } from '@/helpers/extraLogics'
import axios from '@/plugins/axios'

const baseUrl = document.getElementById('app-root')?.dataset?.baseUrl ?? ''

const endPoint = ref('')
const product_heading = ref('')
const product_title = ref('')
const version_date = ref('')
const version_install_count = ref('')
const version_status = ref(null)
const columns = ref([])
const tableOptions = ref({})
const id = ref(null)
const product_id = ref(null)

function updateStatesWithData(data) {
    product_title.value = data.product_title || data.product?.name || ''
    product_id.value = data.product_id || data.product?.id || null
    product_heading.value = `${product_title.value} - ${data.version_number ?? ''}`
    if (data.version_date) version_date.value = data.version_date
    if (data.version_install_count) version_install_count.value = data.version_install_count
    if (data.version_status !== undefined) version_status.value = data.version_status
}

function getInitialValues(verId) {
    axios.get(baseUrl + '/api/admin/versionView/' + verId).then(res => {
        updateStatesWithData(res.data.data)
    }).catch(() => {})
}

function updateData(versionId) {
    if (versionId) id.value = versionId

    endPoint.value = baseUrl + '/api/admin/versionCallbacks/' + id.value
    columns.value = ['callback_ip', 'callback_type', 'callback_date_time', 'callback_status']
    tableOptions.value = {
        sortIcon: { base: 'glyphicon', up: 'glyphicon-chevron-down', down: 'glyphicon-chevron-up' },
        texts: { filter: '', limit: '' },
        sortable: ['callback_type', 'callback_date_time', 'callback_status'],
        filterable: ['callback_date_time'],
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
                    data.keyVal = 'version_id'
                    data.idVal = data.version_id
                    return data
                }),
                count: data.data.total
            }
        },
        columnsClasses: {
            callback_ip: 'callback_ip',
            callback_type: 'type',
            callback_date_time: 'callback_date_time',
            callback_status: 'callback_status',
        },
        templates: {
            callback_date_time(h, row) {
                return row.callback_date_time
            },
            callback_status: (f, row) => {
                return h('span', {
                    'class': row.callback_status ? 'text-success' : 'text-danger'
                }, row.callback_status ? lang('active') : lang('inactive'))
            },
        },
        pagination: { show: false },
        headings: {
            callback_ip: lang('ip_address'),
            callback_type: lang('type'),
            callback_date_time: lang('date'),
            callback_status: lang('status'),
            actions: lang('actions')
        },
    }
}

onBeforeMount(() => {
    const path = window.location.pathname
    const versionId = getIdFromUrl(path)
    id.value = versionId
    getInitialValues(versionId)
    updateData(versionId)
})
</script>
