<template>
    <div>
        <AppAlert componentName="version-view" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ product_heading }}</h4>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <template v-else>
                <div class="card-body">
                    <div class="row g-0">
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex">
                                <span class="fw-bold me-2">{{ lang('product_name') }}:</span>
                                <router-link v-if="product_title && product_id" :to="'/products/' + product_id + '/edit'">{{ product_title }}</router-link>
                                <span v-else class="text-muted">—</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex">
                                <span class="fw-bold me-2">{{ lang('version_date') }}:</span>
                                <span>{{ version_date || '—' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex">
                                <span class="fw-bold me-2">{{ lang('installs_count') }}:</span>
                                <span>{{ version_install_count || '—' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex align-items-center">
                                <span class="fw-bold me-2">{{ lang('version_status') }}:</span>
                                <span :class="version_status ? 'badge bg-success' : 'badge bg-danger'">
                                    {{ version_status ? lang('active') : lang('inactive') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div class="card card-light">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <span class="nav-link active" role="tab">{{ lang('callbacks') }}</span>
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
import { getIdFromUrl, lang } from '@/helpers/extraLogics'
import axios from '@/plugins/axios'

const baseUrl = document.getElementById('app-root')?.dataset?.baseUrl ?? ''

const loading = ref(true)
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
    loading.value = true
    axios.get(baseUrl + '/api/admin/versionView/' + verId).then(res => {
        updateStatesWithData(res.data.data)
    }).catch(() => {}).finally(() => {
        loading.value = false
    })
}

function updateData(versionId) {
    if (versionId) id.value = versionId

    endPoint.value = baseUrl + '/api/admin/versionCallbacks/' + id.value
    columns.value = ['callback_ip', 'callback_type', 'callback_date_time', 'callback_status']
    tableOptions.value = {
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
            callback_ip: 'dt-code',
            callback_type: 'dt-name',
            callback_date_time: 'dt-date',
            callback_status: 'dt-status',
        },
        templates: {
            callback_date_time: (f, row) => row.callback_date_time || '—',
            callback_status: (f, row) => {
                return h('span', { class: row.callback_status ? 'badge bg-success' : 'badge bg-danger' },
                    row.callback_status ? lang('active') : lang('inactive'))
            },
        },
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
