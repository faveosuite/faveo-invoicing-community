<template>
    <div>
        <AppAlert componentName="license-view" />

        <div class="card card-header-tabs">
            <div class="card-header card-header-dark card-light">
                <h4 class="card-title">{{ product_title }}</h4>
                <div class="card-tools">
                    <router-link :to="'/licenses/' + license_id + '/edit'" v-tooltip="lang('edit')" class="btn action-btn btn-tool">
                        <i class="fas text-md fa-edit"></i>
                    </router-link>
                    <button class="btn btn-tool action-btn" v-tooltip="lang('delete_btn')" @click="showDeleteModal()">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>

            <div class="row card-body">
                <div class="row p-1 pb-3 pt-3 col-sm-6 ms-2 ps-0">
                    <label class="col-sm-6 fs-7 fw-bold ps-0">{{ lang('client_email') }}:</label>
                    <a :href="baseUrl + '/clients/' + client_id" v-if="client_email" class="col-sm-6 fs-7">{{ client_email }}</a>
                    <span class="col-sm-6 fs-7" v-else>----</span>
                </div>

                <div class="row p-1 pb-3 pt-3 col-sm-6 ms-2 ps-0">
                    <label class="col-sm-6 fs-7 fw-bold ps-0">{{ lang('product_title') }}:</label>
                    <a v-if="product_title" :href="baseUrl + '/products/' + product_id + '/edit'" class="col-sm-6 fs-7">{{ product_title }}</a>
                    <span class="col-sm-6 fs-7" v-else>----</span>
                </div>

                <hr>

                <div class="row p-1 pb-3 pt-3 col-sm-6 ms-2 ps-0">
                    <label class="col-sm-6 fs-7 fw-bold ps-0">{{ lang('installations') }}:</label>
                    <div v-if="installation_counts" class="col-sm-6 fs-7">{{ installation_counts }}</div>
                    <span class="col-sm-6 fs-7" v-else>----</span>
                </div>

                <div class="row p-1 pb-3 pt-3 col-sm-6 ms-2 ps-0">
                    <label class="col-sm-6 fs-7 fw-bold ps-0">{{ lang('callbacks') }}:</label>
                    <div v-if="call_backs_count" class="col-sm-6 fs-7">{{ call_backs_count }}</div>
                    <span class="col-sm-6 fs-7" v-else>----</span>
                </div>

                <hr>

                <div class="row p-1 pb-3 pt-3 col-sm-6 ms-2 ps-0">
                    <label class="col-sm-6 fs-7 fw-bold ps-0">{{ lang('latest_callback') }}:</label>
                    <div v-if="latest_call_backs" class="col-sm-6 fs-7">{{ latest_call_backs }}</div>
                    <span class="col-sm-6 fs-7" v-else>----</span>
                </div>

                <div class="row p-1 pb-3 pt-3 col-sm-6 border-bottom ms-2 ps-0">
                    <label class="col-sm-6 fs-7 fw-bold ps-0">{{ lang('order_number') }}:</label>
                    <a :href="baseUrl + '/orders/license/' + license_order_number" target="_blank" v-if="license_order_number" class="col-sm-6 fs-7">{{ license_order_number }}</a>
                    <div v-else class="col-sm-6 fs-7">----</div>
                </div>

                <hr>

                <div class="row p-1 pb-3 pt-3 col-sm-6 ms-2 ps-0">
                    <label class="col-sm-6 fs-7 fw-bold ps-0">{{ lang('license_ip') }}:</label>
                    <div v-if="license_ip" class="col-sm-6 fs-7">{{ license_ip }}</div>
                    <div v-else class="col-sm-6 fs-7">----</div>
                </div>

                <div class="row p-1 pb-3 pt-3 col-sm-6 ms-2 ps-0">
                    <label class="col-sm-6 fs-7 fw-bold ps-0">{{ lang('license_domain') }}:</label>
                    <a v-if="license_domain" :href="'https://' + license_domain" target="_blank" class="col-sm-6 fs-7">{{ license_domain }}</a>
                    <div v-else class="col-sm-6 fs-7">----</div>
                </div>

                <hr>

                <div class="row p-1 pb-3 pt-3 col-sm-6 ms-2 ps-0">
                    <label class="col-sm-6 fs-7 fw-bold ps-0">{{ lang('installation_limit') }}:</label>
                    <div v-if="installation_limit >= 0" class="col-sm-6 fs-7">{{ installation_limit }}</div>
                    <div v-else class="col-sm-6 fs-7">----</div>
                </div>

                <div class="row p-1 pb-3 pt-3 col-sm-6 ms-2 ps-0">
                    <label class="col-sm-6 fs-7 fw-bold ps-0">{{ lang('license_date') }}:</label>
                    <div v-if="license_date" class="col-sm-6 fs-7">{{ license_date }}</div>
                    <div v-else class="col-sm-6 fs-7">----</div>
                </div>

                <hr>

                <div class="row p-1 pb-3 pt-3 col-sm-6 ms-2 ps-0">
                    <label class="col-sm-6 fs-7 fw-bold ps-0">{{ lang('license_expiry') }}:</label>
                    <div v-if="license_expire_date" class="col-sm-6 fs-7">{{ license_expire_date }}</div>
                    <div v-else class="col-sm-6 fs-7">----</div>
                </div>

                <div class="row p-1 pb-3 pt-3 col-sm-6 ms-2 ps-0">
                    <label class="col-sm-6 fs-7 fw-bold ps-0">{{ lang('updates_expiry') }}:</label>
                    <div v-if="license_updates_date" class="col-sm-6 fs-7">{{ license_updates_date }}</div>
                    <div v-else class="col-sm-6 fs-7">----</div>
                </div>

                <hr>

                <div class="row p-1 pb-3 pt-3 col-sm-6 ms-2 ps-0">
                    <label class="col-sm-6 fs-7 fw-bold ps-0">{{ lang('support_expiry') }}:</label>
                    <div v-if="license_support_date" class="col-sm-6 fs-7">{{ license_support_date }}</div>
                    <div v-else class="col-sm-6 fs-7">----</div>
                </div>

                <div class="row p-1 pb-3 pt-3 col-sm-6 ms-2 ps-0">
                    <label class="col-sm-6 fs-7 fw-bold ps-0">{{ lang('license_status') }}:</label>
                    <div v-if="license_status" class="col-sm-6 text-sm text-success">Active</div>
                    <div v-else class="col-sm-6 text-sm text-danger">Inactive</div>
                </div>

                <hr>

                <div class="row p-1 pb-3 pt-3 col-sm-6 ms-2 ps-0">
                    <label class="col-sm-6 fs-7 fw-bold ps-0">{{ lang('license_code') }}:</label>
                    <div v-if="license_code && license_code !== '----'" class="col-sm-6 fs-7">
                        {{ license_code.match(/.{1,4}/g).join('-') }}
                        <span class="btn ml-2 btn-light" v-tooltip="lang('copy')" style="cursor: pointer" @click="copyCommand()">
                            <i :class="iconClass"></i>
                        </span>
                    </div>
                    <div v-else class="col-sm-6 fs-7">----</div>
                </div>
            </div>
        </div>

        <div class="card card-header-tabs">
            <div class="card-header data-table-header border-0 p-0 pt-1">
                <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                    <li class="nav-item">
                        <span class="nav-link card-header-link cursor-pointer active" data-bs-toggle="pill" role="tab" @click="updateData('installations')">{{ lang('installations') }}</span>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link card-header-link cursor-pointer" data-bs-toggle="pill" role="tab" @click="updateData('callbacks')">{{ lang('callbacks') }}</span>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link card-header-link cursor-pointer" data-bs-toggle="pill" role="tab" @click="updateData('logs')">{{ lang('installation_logs') }}</span>
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
            <delete-modal v-if="showModal" :onClose="onClose" :showModal="showModal" alertComponentName="license-view" deleteUrl="/api/admin/license/delete" redirectUrl="/licenses/list" keyVal="id" :idVal="license_id">
            </delete-modal>
        </transition>
    </div>
</template>

<script setup>
import { ref, reactive, h, onBeforeMount } from 'vue'
import { getIdFromUrl, lang } from '@/helpers/extraLogics'
import axios from '@/plugins/axios'
import copy from 'clipboard-copy'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'

const baseUrl = document.getElementById('app-root')?.dataset?.baseUrl ?? ''

const iconClass = ref('fas fa-copy')
const showModal = ref(false)
const license_id = ref(null)
const client_email = ref('')
const client_id = ref('')
const installation_counts = ref(null)
const latest_call_backs = ref('')
const license_ip = ref('')
const installation_limit = ref(null)
const license_expire_date = ref('')
const license_support_date = ref('')
const license_code = ref('')
const product_title = ref('')
const call_backs_count = ref(null)
const license_order_number = ref('')
const license_domain = ref('')
const license_date = ref('')
const license_updates_date = ref('')
const license_status = ref(null)
const product_id = ref('')
const endPoint = ref('')
const columns = ref([])
const tableOptions = ref({})
const id = ref(null)

function copyCommand() {
    copy(license_code.value)
    iconClass.value = 'fas fa-check'
    setTimeout(() => {
        iconClass.value = 'fas fa-copy'
    }, 2000)
}

function onClose() {
    showModal.value = false
}

function showDeleteModal() {
    showModal.value = !showModal.value
}

function updateStatesWithData(data) {
    product_title.value = data.product_title || ''
    client_email.value = data.client_email || ''
    client_id.value = data.client_id || ''
    if (data.latest_call_backs) latest_call_backs.value = data.latest_call_backs
    if (data.license_date) license_date.value = data.license_date
    if (data.license_limit >= 0) installation_limit.value = data.license_limit
    if (data.license_expire_date) license_expire_date.value = data.license_expire_date
    if (data.license_updates_date) license_updates_date.value = data.license_updates_date
    if (data.license_support_date) license_support_date.value = data.license_support_date
    if (data.license_ip) license_ip.value = data.license_ip
    if (data.license_domain) license_domain.value = data.license_domain
    if (data.license_code) license_code.value = data.license_code
    if (data.license_status !== undefined) license_status.value = data.license_status
    if (data.installation_counts) installation_counts.value = data.installation_counts
    if (data.call_backs_count) call_backs_count.value = data.call_backs_count
    if (data.license_order_number) license_order_number.value = data.license_order_number
    if (data.product_id) product_id.value = data.product_id
    if (data.id) license_id.value = data.id
}

function getInitialValues(licId) {
    axios.get(baseUrl + '/api/admin/licenseView/' + licId).then(res => {
        updateStatesWithData(res.data.data)
    }).catch(() => {})
}

function updateData(value, licenseId) {
    if (licenseId) id.value = licenseId

    if (value === 'installations') {
        endPoint.value = baseUrl + '/api/admin/licenseInstallation/' + id.value
        columns.value = ['installation_domain', 'installation_ip', 'installation_date', 'installation_status', 'actions']
        tableOptions.value = {
            sortIcon: { base: 'glyphicon', up: 'glyphicon-chevron-down', down: 'glyphicon-chevron-up' },
            texts: { filter: '', limit: '' },
            sortable: ['installation_domain', 'installation_date', 'installation_status'],
            filterable: ['installation_domain'],
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
                installation_domain: 'installation_domain',
                installation_ip: 'installation_ip',
                installation_date: 'installation_date',
                installation_status: 'installation_status',
            },
            templates: {
                installation_ip(h, row) {
                    return row.installation_ip ? row.installation_ip : '----'
                },
                installation_date(h, row) {
                    return row.installation_date
                },
                license_date(h, row) {
                    return row.license_date
                },
                installation_domain: (f, row) => {
                    if (row.installation_domain) {
                        return h('a', { href: 'https://' + row.installation_domain, target: '_blank' }, [row.installation_domain])
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
                installation_domain: lang('domain'),
                installation_ip: lang('ip'),
                installation_date: lang('installation_date'),
                installation_status: lang('status'),
                actions: lang('actions')
            },
        }
    } else if (value === 'callbacks') {
        endPoint.value = baseUrl + '/api/admin/licenseCallbacks/' + id.value
        columns.value = ['callback_domain', 'callback_ip', 'callback_date_time', 'callback_status']
        tableOptions.value = {
            sortIcon: { base: 'glyphicon', up: 'glyphicon-chevron-down', down: 'glyphicon-chevron-up' },
            texts: { filter: '', limit: '' },
            sortable: ['callback_domain', 'callback_date', 'callback_status'],
            filterable: ['callback_domain'],
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
                callback_domain: 'callback_domain',
                callback_ip: 'callback_ip',
                callback_date_time: 'callback_date_time',
                callback_status: 'callback_status',
            },
            templates: {
                callback_ip(h, row) {
                    return row.callback_ip ? row.callback_ip : '----'
                },
                callback_date_time(h, row) {
                    return row.callback_date_time
                },
                callback_domain: (f, row) => {
                    if (row.callback_domain) {
                        return h('a', { href: 'https://' + row.callback_domain, target: '_blank' }, [row.callback_domain])
                    } else {
                        return '----'
                    }
                },
                callback_status: (f, row) => {
                    return h('span', {
                        'class': row.callback_status ? 'text-success' : 'text-danger'
                    }, row.callback_status ? lang('success') : lang('error'))
                },
            },
            pagination: { show: false },
            headings: {
                callback_domain: lang('domain'),
                callback_ip: lang('ip'),
                callback_date_time: lang('callback_date_time'),
                callback_status: lang('status'),
            },
        }
    } else {
        endPoint.value = baseUrl + '/api/admin/installationLogs/' + id.value
        columns.value = ['installation_domain', 'installation_ip', 'version_number', 'installation_last_active_date', 'installation_status']
        tableOptions.value = {
            sortIcon: { base: 'glyphicon', up: 'glyphicon-chevron-down', down: 'glyphicon-chevron-up' },
            texts: { filter: '', limit: '' },
            sortable: ['installation_domain', 'installation_last_active_date', 'installation_status'],
            filterable: ['installation_domain'],
            requestAdapter(data) {
                return {
                    'sort_field': data.orderBy ? data.orderBy : 'installation_last_active_date',
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
                installation_domain: 'installation_domain',
                installation_ip: 'installation_ip',
                version: 'version',
                installation_last_active_date: 'installation_last_active_date',
                installation_status: 'installation_status',
            },
            templates: {
                installation_ip(h, row) {
                    return row.installation_ip ? row.installation_ip : '----'
                },
                version_number(h, row) {
                    return row.version_number ? row.version_number : '----'
                },
                installation_last_active_date(h, row) {
                    return row.installation_last_active_date
                },
                installation_domain: (f, row) => {
                    if (row.installation_domain) {
                        return h('a', { href: 'https://' + row.installation_domain, target: '_blank' }, [row.installation_domain])
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
                installation_domain: lang('domain'),
                installation_ip: lang('ip'),
                version_number: lang('version'),
                installation_last_active_date: lang('last_active_date'),
                installation_status: lang('status'),
            },
        }
    }
}

onBeforeMount(() => {
    const path = window.location.pathname
    const licenseId = getIdFromUrl(path)
    id.value = licenseId
    license_id.value = licenseId
    getInitialValues(licenseId)
    updateData('installations', licenseId)
})
</script>
