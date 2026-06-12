<template>
    <div>
        <AppAlert componentName="installations-view" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ product_title }}</h4>
                <div class="card-tools">
                    <router-link :to="'/installations/' + id + '/edit'" v-tooltip="lang('edit')" class="btn btn-tool">
                        <i class="fas fa-edit"></i>
                    </router-link>
                    <action-button action="delete" icon-only class="btn-tool" v-tooltip="lang('delete_btn')" @click="showDeleteModal()" />
                </div>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <template v-else>
                <div class="card-body">
                    <div class="row g-0">
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex">
                                <span class="fw-bold me-2">{{ lang('license_code') }}:</span>
                                <router-link v-if="license_code" :to="'/licenses/' + license_id + '/view'">
                                    {{ license_code.match(/.{1,4}/g).join('-') }}
                                </router-link>
                                <span v-else class="text-muted">—</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex">
                                <span class="fw-bold me-2">{{ lang('installation_date') }}:</span>
                                <span>{{ installation_date || '—' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex">
                                <span class="fw-bold me-2">{{ lang('installation_domain') }}:</span>
                                <a v-if="installation_domain" :href="'https://' + installation_domain" target="_blank">{{ installation_domain }}</a>
                                <span v-else class="text-muted">—</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex">
                                <span class="fw-bold me-2">{{ lang('installation_ip') }}:</span>
                                <span>{{ installation_ip || '—' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex align-items-center">
                                <span class="fw-bold me-2">{{ lang('ip_address_verification') }}:</span>
                                <span :class="installation_disable_ip_verification ? 'badge bg-success' : 'badge bg-danger'">
                                    {{ installation_disable_ip_verification ? lang('enabled') : lang('disabled') }}
                                </span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex align-items-center">
                                <span class="fw-bold me-2">{{ lang('status') }}:</span>
                                <span :class="installation_status ? 'badge bg-success' : 'badge bg-danger'">
                                    {{ installation_status ? lang('active') : lang('inactive') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">
                    {{ lang('callbacks') }}
                </h4>
            </div>
            <div class="card-body">
                <DataTable v-if="endPoint" :url="endPoint" ref="dataTable" :dataColumns="columns" :option="tableOptions">
                    <template #actions="props"><table-actions :data="props.row" /></template>
                </DataTable>
            </div>
        </div>

        <transition name="modal">
            <DeleteModal v-if="showModal" :onClose="onClose" :showModal="showModal"
                :deleteUrl="`${baseUrl}/api/admin/installations/delete`"
                :deleteData="{ id: id }"
                componentName="installations-view"
                method="post"
                @deleted="onDeleted" />
        </transition>
    </div>
</template>

<script setup>
import { ref, h, onBeforeMount } from 'vue'
import { useRouter } from 'vue-router'
import { getIdFromUrl, lang } from '@/helpers/extraLogics'
import axios from '@/plugins/axios'
import DeleteModal from '@/themes/adminlte/components/common/DeleteModal.vue'
import { useDateTime } from '@/core/composables/useDateTime'

const router = useRouter()
const { formatDate, formatDateTime } = useDateTime()
const baseUrl = document.getElementById('app-root')?.dataset?.baseUrl ?? ''

const loading = ref(true)
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

function onDeleted() {
    setTimeout(() => router.push('/installations/list'), 2000)
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
    loading.value = true
    axios.get(baseUrl + '/api/admin/installationView/' + instId).then(res => {
        updateStatesWithData(res.data.data)
    }).catch(() => {}).finally(() => {
        loading.value = false
    })
}

function updateData(value, productId) {
    if (productId) id.value = productId

    endPoint.value = baseUrl + '/api/admin/installationCallbacks/' + id.value
    columns.value = ['callback_domain', 'callback_ip', 'callback_date_time', 'callback_status']
    tableOptions.value = {
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
            callback_ip: 'dt-code',
            callback_domain: 'dt-text',
            callback_date_time: 'dt-date',
            callback_status: 'dt-status',
        },
        templates: {
            callback_ip: (f, row) => row.callback_ip || '—',
            callback_date_time: (f, row) => formatDateTime(row.callback_date_time),
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
        },
        headings: {
            callback_ip: lang('ip'),
            callback_domain: lang('domain'),
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
