<template>
    <div>
        <AppAlert componentName="installations-view" />

        <div class="card card-header-tabs card-outline">
            <div class="card-header card-header-dark card-light">
                <h4 class="card-title">{{ product_title }}</h4>
                <div class="card-tools">
                    <router-link :to="'/installations/' + id + '/edit'" v-tooltip="lang('edit')" class="btn btn-tool action-btn">
                        <i class="fas fa-edit"></i>
                    </router-link>
                    <button class="btn btn-tool action-btn" v-tooltip="lang('delete_btn')" @click="showDeleteModal()">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>

            <div class="row card-body col-md-12 ms-2 ps-0">
                <div class="row pt-2 pb-2 border-bottom col-sm-6 ms-2 ps-0">
                    <label class="col-sm-6 fs-7 fw-bold ps-1">{{ lang('license_code') }}:</label>
                    <router-link v-if="license_code" :to="'/licenses/' + license_id + '/view'" class="col-sm-6 fs-7">{{ license_code.match(/.{1,4}/g).join('-') }}</router-link>
                    <span class="col-sm-6" v-else>----</span>
                </div>

                <div class="row pt-2 pb-2 border-bottom col-sm-6 ms-2 ps-0">
                    <label class="col-sm-6 fs-7 fw-bold ps-1">{{ lang('installation_date') }}:</label>
                    <div v-if="installation_date" class="col-sm-6 fs-7">{{ installation_date }}</div>
                    <span class="col-sm-6" v-else>----</span>
                </div>

                <div class="row pt-2 pb-2 border-bottom col-sm-6 ms-2 ps-0">
                    <label class="col-sm-6 fs-7 fw-bold ps-1">{{ lang('installation_domain') }}:</label>
                    <a :href="'https://' + installation_domain" target="_blank" v-if="installation_domain" class="col-sm-6 fs-7">{{ installation_domain }}</a>
                    <span class="col-sm-6" v-else>----</span>
                </div>

                <div class="row pt-2 pb-2 border-bottom col-sm-6 ms-2 ps-0">
                    <label class="col-sm-6 fs-7 fw-bold ps-1">{{ lang('installation_ip') }}:</label>
                    <div v-if="installation_ip" class="col-sm-6 fs-7">{{ installation_ip }}</div>
                    <span class="col-sm-6" v-else>----</span>
                </div>

                <div class="row pt-2 pb-2 col-sm-6 ms-2 ps-0">
                    <label class="col-sm-6 fs-7 fw-bold ps-1">{{ lang('ip_address_verification') }}</label>
                    <div v-if="installation_disable_ip_verification" class="col-sm-6 text-sm text-success">{{ lang('enabled') }}</div>
                    <div v-else class="col-sm-6 text-sm text-danger">{{ lang('disabled') }}</div>
                </div>

                <div class="row pt-2 pb-2 col-sm-6 ms-2 ps-0">
                    <label class="col-sm-6 fs-7 fw-bold ps-1">{{ lang('status') }}:</label>
                    <div v-if="installation_status" class="col-sm-6 text-sm text-success">{{ lang('active') }}</div>
                    <div v-else class="col-sm-6 text-sm text-danger">{{ lang('inactive') }}</div>
                </div>
            </div>
        </div>

        <div class="card card-header-tabs">
            <div class="card-header data-table-header border-0 p-0 pt-1">
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

        <transition name="modal">
            <delete-modal v-if="showModal" :onClose="onClose" :showModal="showModal" alertComponentName="installations-view" deleteUrl="/api/admin/installations/delete" redirectUrl="/installations/list" keyVal="id" :idVal="id">
            </delete-modal>
        </transition>
    </div>
</template>

<script setup>
import { ref, h, onBeforeMount } from 'vue'
import { getIdFromUrl, lang } from '@/helpers/extraLogics'
import axios from '@/plugins/axios'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'

const baseUrl = document.getElementById('app-root')?.dataset?.baseUrl ?? ''

const showModal = ref(false)
const endPoint = ref('')
const product_title = ref('')
const license_code = ref('')
const license_id = ref('')
const installation_date = ref('')
const installation_domain = ref('')
const installation_ip = ref('')
const installation_disable_ip_verification = ref(null)
const installation_status = ref(null)
const columns = ref([])
const tableOptions = ref({})
const id = ref('')

function showDeleteModal() {
    showModal.value = !showModal.value
}

function onClose() {
    showModal.value = false
}

function updateStatesWithData(data) {
    product_title.value = data.product_title || ''
    installation_date.value = data.installation_date || ''
    license_code.value = data.license_code || ''
    license_id.value = data.license_id || ''
    if (data.installation_domain) installation_domain.value = data.installation_domain
    if (data.installation_ip) installation_ip.value = data.installation_ip
    if (data.installation_disable_ip_verification !== undefined) installation_disable_ip_verification.value = data.installation_disable_ip_verification
    if (data.installation_status !== undefined) installation_status.value = data.installation_status
    if (data.id) id.value = data.id
}

function getInitialValues(instId) {
    axios.get(baseUrl + '/api/admin/installationView/' + instId).then(res => {
        updateStatesWithData(res.data.data)
    }).catch(() => {})
}

function updateData(value, productId) {
    if (productId) id.value = productId

    endPoint.value = baseUrl + '/api/admin/installationCallbacks/' + id.value
    columns.value = ['callback_domain', 'callback_ip', 'callback_date_time', 'callback_status']
    tableOptions.value = {
        sortIcon: { base: 'glyphicon', up: 'glyphicon-chevron-down', down: 'glyphicon-chevron-up' },
        texts: { filter: '', limit: '' },
        sortable: ['callback_date_time', 'callback_status'],
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
                    data.keyVal = 'id'
                    data.idVal = data.id
                    return data
                }),
                count: data.data.total
            }
        },
        columnsClasses: {
            callback_ip: 'ip_address',
            callback_domain: 'callback_domain',
            order_number: 'order_number',
            callback_date_time: 'callback_date_time',
            callback_status: 'status',
        },
        templates: {
            callback_ip(h, row) {
                return row.callback_ip ? row.callback_ip : '----'
            },
            callback_date_time(h, row) {
                return row.callback_date_time
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
        },
        pagination: { show: false },
        headings: {
            callback_ip: lang('ip'),
            callback_domain: lang('domain'),
            order_number: lang('order_number'),
            callback_date_time: lang('date'),
            callback_status: lang('status')
        },
    }
}

onBeforeMount(() => {
    const path = window.location.pathname
    const installationId = getIdFromUrl(path)
    id.value = installationId
    getInitialValues(installationId)
    updateData('callbacks', installationId)
})
</script>
