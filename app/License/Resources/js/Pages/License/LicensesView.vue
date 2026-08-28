<template>
    <div>
        <AppAlert componentName="license-view" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ product_title }}</h4>
                <div class="card-tools">
                    <router-link :to="'/licenses/' + license_id + '/edit'" v-tooltip="lang('edit')" class="btn btn-tool">
                        <i class="fas fa-edit"></i>
                    </router-link>
                    <button class="btn btn-tool" v-tooltip="lang('delete_btn')" @click="showDeleteModal()"><i class="fas fa-trash"></i></button>
                </div>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <template v-else>
                <div class="card-body">
                    <div class="row g-0">
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex">
                                <span class="fw-bold me-2">{{ lang('client_email') }}:</span>
                                <router-link v-if="client_email && client_id" :to="'/users/' + client_id">{{ client_email }}</router-link>
                                <span v-else class="text-muted">—</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex">
                                <span class="fw-bold me-2">{{ lang('product_title') }}:</span>
                                <router-link v-if="product_title && product_id" :to="'/products/' + product_id + '/edit'">{{ product_title }}</router-link>
                                <span v-else class="text-muted">—</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex">
                                <span class="fw-bold me-2">{{ lang('installations') }}:</span>
                                <span>{{ installation_counts || '—' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex">
                                <span class="fw-bold me-2">{{ lang('callbacks') }}:</span>
                                <span>{{ call_backs_count || '—' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex">
                                <span class="fw-bold me-2">{{ lang('latest_callback') }}:</span>
                                <span>{{ latest_call_backs || '—' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex">
                                <span class="fw-bold me-2">{{ lang('order_number') }}:</span>
                                <a v-if="license_order_number" :href="baseUrl + '/admin/orders/license/' + license_order_number" target="_blank">
                                    {{ license_order_number }}
                                </a>
                                <span v-else class="text-muted">—</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex">
                                <span class="fw-bold me-2">{{ lang('license_ip') }}:</span>
                                <span>{{ license_ip || '—' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex">
                                <span class="fw-bold me-2">{{ lang('license_domain') }}:</span>
                                <a v-if="license_domain" :href="'https://' + license_domain" target="_blank">{{ license_domain }}</a>
                                <span v-else class="text-muted">—</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex">
                                <span class="fw-bold me-2">{{ lang('licensed_machine_id') }}:</span>
                                <span>{{ license_machine_id || '—' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex">
                                <span class="fw-bold me-2">{{ lang('installation_limit') }}:</span>
                                <span>{{ installation_limit >= 0 ? installation_limit : '—' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex">
                                <span class="fw-bold me-2">{{ lang('license_date') }}:</span>
                                <span>{{ license_date || '—' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex">
                                <span class="fw-bold me-2">{{ lang('license_expiry') }}:</span>
                                <span>{{ license_expire_date || '—' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex">
                                <span class="fw-bold me-2">{{ lang('updates_expiry') }}:</span>
                                <span>{{ license_updates_date || '—' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex">
                                <span class="fw-bold me-2">{{ lang('support_expiry') }}:</span>
                                <span>{{ license_support_date || '—' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex align-items-center">
                                <span class="fw-bold me-2">{{ lang('license_status') }}:</span>
                                <span :class="license_status ? 'badge bg-success' : 'badge bg-danger'">
                                    {{ license_status ? lang('active') : lang('inactive') }}
                                </span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="d-flex align-items-center">
                                <span class="fw-bold me-2">{{ lang('license_code') }}:</span>
                                <template v-if="license_code && license_code !== '----'">
                                    <span class="me-2">{{ license_code.match(/.{1,4}/g).join('-') }}</span>
                                    <button class="btn btn-light table_btn" v-tooltip="lang('copy')" @click="copyCommand()">
                                        <i :class="copied ? 'fas fa-check' : 'fas fa-clipboard'"></i>
                                    </button>
                                </template>
                                <span v-else class="text-muted">—</span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div class="card card-light">
            <div class="card-body px-0 pt-0">
                <ul class="nav nav-tabs px-3 pt-2" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link" :class="{ active: activeTab === 'installations' }"
                            href="#" @click.prevent="updateData('installations')">
                            <i class="fas fa-server me-1"></i>{{ lang('installations') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" :class="{ active: activeTab === 'callbacks' }"
                            href="#" @click.prevent="updateData('callbacks')">
                            <i class="fas fa-phone-alt me-1"></i>{{ lang('callbacks') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" :class="{ active: activeTab === 'logs' }"
                            href="#" @click.prevent="updateData('logs')">
                            <i class="fas fa-clipboard-list me-1"></i>{{ lang('installation_logs') }}
                        </a>
                    </li>
                </ul>
                <div class="p-3">
                    <DataTable v-if="endPoint" :key="activeTab" :url="endPoint" ref="dataTable" :dataColumns="columns" :option="tableOptions">
                        <template #actions="props"><table-actions :data="props.row" /></template>
                    </DataTable>
                </div>
            </div>
        </div>

        <transition name="modal">
            <DeleteModal v-if="showModal" :onClose="onClose" :showModal="showModal"
                :deleteUrl="`${baseUrl}/api/admin/license/delete`"
                :deleteData="{ id: license_id }"
                componentName="license-view"
                method="post"
                @deleted="onDeleted" />
        </transition>
    </div>
</template>

<script setup>
import { ref, h, onBeforeMount } from 'vue'
import { getIdFromUrl, lang } from '@/helpers/extraLogics'
import axios from '@/plugins/axios'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'
import { useRouter } from 'vue-router'
import { useDateTime } from '@/core/composables/useDateTime'
import { useBaseUrl } from '@/core/composables/useBaseUrl'

const router = useRouter()
const { formatDate, formatDateTime } = useDateTime()
const baseUrl = useBaseUrl()

const loading = ref(true)
const copied = ref(false)
const showModal = ref(false)
const activeTab = ref('installations')
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
const license_machine_id = ref('')
const license_date = ref('')
const license_updates_date = ref('')
const license_status = ref(null)
const product_id = ref('')
const endPoint = ref('')
const columns = ref([])
const tableOptions = ref({})
const id = ref(null)

function copyCommand() {
    navigator.clipboard.writeText(license_code.value)
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
}

function onClose() {
    showModal.value = false
}

function onDeleted() {
    setTimeout(() => router.push('/licenses/list'), 2000)
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
    if (data.license_machine_id) license_machine_id.value = data.license_machine_id
    if (data.license_code) license_code.value = data.license_code
    if (data.license_status !== undefined) license_status.value = data.license_status
    if (data.installation_counts) installation_counts.value = data.installation_counts
    if (data.call_backs_count) call_backs_count.value = data.call_backs_count
    if (data.license_order_number) license_order_number.value = data.license_order_number
    if (data.product_id) product_id.value = data.product_id
    if (data.id) license_id.value = data.id
}

function getInitialValues(licId) {
    loading.value = true
    axios.get('/api/admin/licenseView/' + licId).then(res => {
        updateStatesWithData(res.data.data)
    }).catch(() => {}).finally(() => {
        loading.value = false
    })
}

function buildInstallationOptions() {
    return {
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
                    data.delete_url = baseUrl + '/api/admin/installations/delete'
                    data.view_url = '/installations/' + data.id + '/view'
                    data.keyVal = 'id'
                    data.idVal = data.id
                    data.method = 'post'
                    return data
                }),
                count: data.data.total
            }
        },
        columnsClasses: {
            installation_domain: 'dt-text',
            installation_ip: 'dt-code',
            installation_date: 'dt-date',
            installation_status: 'dt-status',
            actions: 'dt-action',
        },
        templates: {
            installation_ip: (f, row) => row.installation_ip || '—',
            installation_date: (f, row) => formatDate(row.installation_date),
            installation_domain: (f, row) => {
                if (row.installation_domain) {
                    return h('a', { href: 'https://' + row.installation_domain, target: '_blank' }, [row.installation_domain])
                }
                return '—'
            },
            installation_status: (f, row) => {
                return h('span', { class: row.installation_status ? 'badge bg-success' : 'badge bg-danger' },
                    row.installation_status ? lang('active') : lang('inactive'))
            },
        },
        headings: {
            installation_domain: lang('domain'),
            installation_ip: lang('ip'),
            installation_date: lang('installation_date'),
            installation_status: lang('status'),
            actions: lang('actions')
        },
    }
}

function buildCallbackOptions() {
    return {
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
            callback_domain: 'dt-text',
            callback_ip: 'dt-code',
            callback_date_time: 'dt-date',
            callback_status: 'dt-status',
        },
        templates: {
            callback_ip: (f, row) => row.callback_ip || '—',
            callback_date_time: (f, row) => formatDateTime(row.callback_date_time),
            callback_domain: (f, row) => {
                if (row.callback_domain) {
                    return h('a', { href: 'https://' + row.callback_domain, target: '_blank' }, [row.callback_domain])
                }
                return '—'
            },
            callback_status: (f, row) => {
                return h('span', { class: row.callback_status ? 'badge bg-success' : 'badge bg-danger' },
                    row.callback_status ? lang('success') : lang('error'))
            },
        },
        headings: {
            callback_domain: lang('domain'),
            callback_ip: lang('ip'),
            callback_date_time: lang('callback_date_time'),
            callback_status: lang('status'),
        },
    }
}

function buildLogsOptions() {
    return {
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
            installation_domain: 'dt-text',
            installation_ip: 'dt-code',
            version_number: 'dt-code',
            installation_last_active_date: 'dt-date',
            installation_status: 'dt-status',
        },
        templates: {
            installation_ip: (f, row) => row.installation_ip || '—',
            version_number: (f, row) => row.version_number || '—',
            installation_last_active_date: (f, row) => formatDate(row.installation_last_active_date),
            installation_domain: (f, row) => {
                if (row.installation_domain) {
                    return h('a', { href: 'https://' + row.installation_domain, target: '_blank' }, [row.installation_domain])
                }
                return '—'
            },
            installation_status: (f, row) => {
                return h('span', { class: row.installation_status ? 'badge bg-success' : 'badge bg-danger' },
                    row.installation_status ? lang('active') : lang('inactive'))
            },
        },
        headings: {
            installation_domain: lang('domain'),
            installation_ip: lang('ip'),
            version_number: lang('version'),
            installation_last_active_date: lang('last_active_date'),
            installation_status: lang('status'),
        },
    }
}

function updateData(value, licenseId) {
    if (licenseId) id.value = licenseId
    activeTab.value = value

    if (value === 'installations') {
        endPoint.value = '/api/admin/licenseInstallation/' + id.value
        columns.value = ['installation_domain', 'installation_ip', 'installation_date', 'installation_status', 'actions']
        tableOptions.value = buildInstallationOptions()
    } else if (value === 'callbacks') {
        endPoint.value = '/api/admin/licenseCallbacks/' + id.value
        columns.value = ['callback_domain', 'callback_ip', 'callback_date_time', 'callback_status']
        tableOptions.value = buildCallbackOptions()
    } else {
        endPoint.value = '/api/admin/installationLogs/' + id.value
        columns.value = ['installation_domain', 'installation_ip', 'version_number', 'installation_last_active_date', 'installation_status']
        tableOptions.value = buildLogsOptions()
    }
}

onBeforeMount(() => {
    const path = globalThis.location.pathname
    const licenseId = getIdFromUrl(path)
    id.value = licenseId
    license_id.value = licenseId
    getInitialValues(licenseId)
    updateData('installations', licenseId)
})
</script>

<style scoped>
/* Domain is the one column every tab shares as an "identity" column, so it
   stays a fixed, identical px everywhere. Every other column is left at
   width:auto — under table-layout:fixed, the browser splits whatever space
   remains after Domain EQUALLY across the auto columns of THAT table. Tabs
   have different column counts (installations/logs: 5, callbacks: 4), so
   each one's equal-share differs — that's expected; only Domain is pinned. */
:deep(.VueTables__table) {
    table-layout: fixed;
}
:deep(.dt-text) { width: 250px; }
:deep(.dt-code),
:deep(.dt-date),
:deep(.dt-status),
:deep(.dt-action) {
    width: auto;
}

/* table-layout:fixed only ever looks at the width property — min-width is
   silently ignored for it, so on a narrow screen the auto columns above have
   no floor at all and get crushed to a couple px, wrapping their header text
   one letter per line. Below md, give them back a real width floor; that
   makes the table wider than the viewport, and the existing .table-responsive
   wrapper (Bootstrap, overflow-x:auto) scrolls it — the standard, legible
   mobile pattern instead of squished columns. */
@media (max-width: 768px) {
    :deep(.dt-code)   { width: 120px; }
    :deep(.dt-date)   { width: 160px; }
    :deep(.dt-status) { width: 130px; }
    :deep(.dt-action) { width: 200px; }
}

/* Sort icon: float:right participates in the header's flow, so anything
   changing the header's content width nudges it. position:absolute takes
   it out of flow entirely; reserved padding-right keeps it clear of text. */
:deep(.VueTables__table th.VueTables__sortable) {
    position: relative;
    padding-right: 1.75rem;
}
:deep(.VueTables__sort-icon) {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    float: none !important;
    transform: translateY(-50%) !important;
}
</style>
