<template>
    <div>
        <AppAlert :componentName="COMPONENT" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">
                    Open Payments
                    <span class="badge rounded-pill ms-2" :class="enabled ? 'text-bg-success' : 'text-bg-danger'">
                        {{ enabled ? 'Enabled' : 'Disabled' }}
                    </span>
                </h4>
                <div class="card-tools">
                    <button class="btn btn-tool" v-tooltip="'Settings'" @click="openSettingsModal">
                        <i class="fas fa-gear"></i>
                    </button>
                    <button class="btn btn-tool" v-tooltip="'Filters'" @click="showFilter = !showFilter">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </div>

            <div class="card-body">
                <OpenPaymentsFilter
                    :show="showFilter"
                    :baseUrl="baseUrl"
                    @apply="onFilterApply"
                    @reset="onFilterReset"
                    @close="showFilter = false"
                />

                <DataTable
                    ref="dtRef"
                    :url="apiUrl"
                    :dataColumns="columns"
                    :option="tableOptions"
                />
            </div>
        </div>

        <!-- Order detail modal -->
        <AppModal
            v-if="selectedOrder"
            :showModal="!!selectedOrder"
            :onClose="() => selectedOrder = null"
            classname="modal-lg"
            :showControls="false"
        >
            <template #title>
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold">Order #{{ selectedOrder.id }}</span>
                    <span :class="['badge', statusBadgeClass(selectedOrder.payment_status)]">
                        {{ selectedOrder.payment_status }}
                    </span>
                </div>
            </template>

            <template #fields>
                <div class="px-3 pb-3">

                    <!-- Name + Status header -->
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h4 class="fw-bold mb-1">{{ selectedOrder.name }}</h4>
                            <p class="text-muted mb-0">{{ selectedOrder.company }}</p>
                        </div>
                        <div class="text-end">
                            <p class="text-muted small mb-1">{{ selectedOrder.gateway }} Gateway</p>
                            <p class="text-muted small mb-0">{{ formatDateTime(selectedOrder.created_at) }}</p>
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- Contact + Billing Address -->
                    <div class="row mb-4">
                        <div class="col-sm-6">
                            <p class="text-uppercase fw-bold text-muted small mb-3">Contact</p>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="fas fa-envelope text-primary"></i>
                                <span>{{ selectedOrder.email }}</span>
                            </div>
                            <div v-if="selectedOrder.mobile" class="d-flex align-items-center gap-2">
                                <i class="fas fa-phone text-primary"></i>
                                <span>{{ selectedOrder.mobile }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-uppercase fw-bold text-muted small mb-3">Billing Address</p>
                            <div class="d-flex align-items-start gap-2">
                                <i class="fas fa-location-dot text-primary mt-1"></i>
                                <div>
                                    <div>{{ selectedOrder.address }}</div>
                                    <div>{{ selectedOrder.city }}<span v-if="selectedOrder.state">, {{ selectedOrder.state }}</span> {{ selectedOrder.zip }}</div>
                                    <div>{{ selectedOrder.country }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment breakdown -->
                    <p class="text-uppercase fw-bold text-muted small mb-3">Payment Details</p>
                    <div class="rounded border overflow-hidden mb-3">
                        <table class="table table-bordered mb-0">
                            <thead class="visually-hidden"><tr><th>Field</th><th>Value</th></tr></thead>
                            <tbody>
                                <tr v-if="selectedOrder.transaction_id">
                                    <td class="table-secondary fw-semibold col-label-width">Transaction ID</td>
                                    <td class="text-primary font-monospace small">{{ selectedOrder.transaction_id }}</td>
                                </tr>
                                <tr v-if="selectedOrder.description">
                                    <td class="table-secondary fw-semibold">Description</td>
                                    <td class="fst-italic text-muted">{{ selectedOrder.description }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Amount breakdown -->
                    <p class="text-uppercase fw-bold text-muted small mb-3">Amount Breakdown</p>
                    <div class="rounded border overflow-hidden">
                        <table class="table table-bordered mb-0">
                            <thead class="visually-hidden"><tr><th>Field</th><th>Value</th></tr></thead>
                            <tbody>
                                <tr>
                                    <td class="table-secondary fw-semibold col-label-width">Base Amount</td>
                                    <td>{{ selectedOrder.currency_symbol || selectedOrder.currency }} {{ selectedOrder.base_amount }}</td>
                                </tr>
                                <tr>
                                    <td class="table-secondary fw-semibold">
                                        Processing Fee
                                        <span v-if="selectedOrder.processing_fee_rate > 0" class="text-muted fw-normal">
                                            ({{ selectedOrder.processing_fee_rate }}%)
                                        </span>
                                    </td>
                                    <td>{{ selectedOrder.currency_symbol || selectedOrder.currency }} {{ selectedOrder.processing_fee }}</td>
                                </tr>
                                <tr>
                                    <td class="table-secondary fw-bold">Total Charged</td>
                                    <td class="fw-bold text-primary">{{ selectedOrder.currency_symbol || selectedOrder.currency }} {{ selectedOrder.amount }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </template>
        </AppModal>

        <!-- Open Payment settings modal -->
        <AppModal :showModal="showSettingsModal" :onClose="closeSettingsModal" :showCloseBtn="false">
            <template #title>
                <h4>Open Payment Settings</h4>
            </template>

            <template #fields>
                <DynamicSelect
                    name="open_payment_status"
                    label="Open Payment Page"
                    :elements="statusOptions"
                    :value="statusOptions.find(o => o.id === draftStatusId) ?? null"
                    :onChange="(val) => draftStatusId = val?.id ?? 1"
                    :disabled="savingStatus"
                    :clearable="false"
                    :searchable="false"
                />
                <div class="mb-3">
                    <label class="form-label">Page URL</label>
                    <div class="input-group">
                        <input class="form-control" readonly :value="openPaymentUrl" />
                        <span class="input-group-text cursor-pointer" @click="copyUrl">
                            <i :class="copied ? 'fas fa-check text-success' : 'fas fa-copy'"></i>
                            {{ copied ? __('message.copied') : __('message.copy') }}
                        </span>
                    </div>
                </div>
            </template>

            <template #controls>
                <action-button action="save" type="button" :loading="savingStatus" @click="saveOpenPaymentStatus" />
            </template>
        </AppModal>
    </div>
</template>

<script setup>
import { h, reactive, ref, withDirectives, onMounted } from 'vue'
import { vTooltip } from 'floating-vue'
import { __ } from '@/plugins/i18n'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import OpenPaymentsFilter from './OpenPaymentsFilter.vue'
import DynamicSelect from '@/components/Reusable/FormField/DynamicSelect.vue'
import { useDateTime } from '@/core/composables/useDateTime'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import { makeRequestAdapter } from '@/helpers/tableUtils'

const { formatDateTime } = useDateTime()

const COMPONENT = 'open-payments-list'
const baseUrl = useBaseUrl()
const apiUrl  = `/pay/list`

const openPaymentUrl = `${baseUrl}/pay`

const dtRef             = ref(null)
const showFilter        = ref(false)
const showSettingsModal = ref(false)
const activeFilters     = ref({})
const selectedOrder     = ref(null)
const copied            = ref(false)
const enabled           = ref(true)
const draftStatusId     = ref(1)
const savingStatus      = ref(false)
const statusOptions     = [{ id: 1, name: 'Enabled' }, { id: 0, name: 'Disabled' }]

function copyUrl() {
    navigator.clipboard.writeText(openPaymentUrl).then(() => {
        copied.value = true
        setTimeout(() => { copied.value = false }, 2000)
    })
}

onMounted(async () => {
    try {
        const res = await http.get(`/pay/config`)
        enabled.value = !!res.data?.data?.enabled
    } catch (e) {
        errorHandler(e, COMPONENT)
    }
})

function openSettingsModal() {
    draftStatusId.value = enabled.value ? 1 : 0
    showSettingsModal.value = true
}

function closeSettingsModal() {
    showSettingsModal.value = false
}

async function saveOpenPaymentStatus() {
    savingStatus.value = true
    try {
        const res = await http.post(`/licenseStatus`, { open_payment_status: draftStatusId.value })
        successHandler(res, COMPONENT)
        enabled.value = !!draftStatusId.value
        showSettingsModal.value = false
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        savingStatus.value = false
    }
}

function onFilterApply(params) {
    activeFilters.value = params
    showFilter.value    = false
    dtRef.value?.refresh()
}

function onFilterReset() {
    activeFilters.value = {}
    dtRef.value?.refresh()
}

function statusBadgeClass(status) {
    return {
        'bg-success': status === 'completed',
        'bg-danger':  status === 'failed',
        'bg-warning': status === 'pending',
    }
}

const columns = ['name', 'company', 'email', 'mobile', 'amount', 'gateway', 'transaction_id', 'payment_status', 'created_at', 'action']

const tableOptions = reactive({
    headings: {
        name:           'Name',
        company:        'Company',
        email:          'Email',
        mobile:         'Mobile',
        amount:         'Amount',
        gateway:        'Gateway',
        transaction_id: 'Transaction ID',
        payment_status: 'Status',
        created_at:     'Date',
        action:         'Action',
    },
    columnsClasses: {
        name:           'dt-name',
        company:        'dt-name',
        email:          'dt-name',
        mobile:         'dt-name',
        amount:         'dt-amount',
        gateway:        'dt-name',
        transaction_id: 'dt-name',
        payment_status: 'dt-status',
        created_at:     'dt-date',
        action:         'dt-action',
    },
    templates: {
        name:           (f, row) => row.name           || '—',
        company:        (f, row) => row.company        || '—',
        email:          (f, row) => row.email          || '—',
        mobile:         (f, row) => row.mobile         || '—',
        amount: (f, row) => `${row.currency_symbol || row.currency} ${row.amount}`,
        gateway:        (f, row) => row.gateway        || '—',
        transaction_id: (f, row) => row.transaction_id || '—',
        payment_status: (f, row) => h('span', {
            class: ['badge', {
                'bg-success': row.payment_status === 'completed',
                'bg-danger':  row.payment_status === 'failed',
                'bg-warning text-dark': row.payment_status === 'pending',
            }],
        }, __(`message.${row.payment_status}`) || row.payment_status),
        created_at:     (f, row) => formatDateTime(row.created_at),
        action: (f, row) => withDirectives(h('button', {
            class: 'btn btn-light table_btn',
            onClick: () => { selectedOrder.value = row },
        }, h('i', { class: 'fas fa-eye' })), [[vTooltip, __('message.view')]]),
    },
    sortable: ['name', 'company', 'email', 'amount', 'gateway', 'transaction_id', 'payment_status', 'created_at'],
    filterable: true,
    requestAdapter: makeRequestAdapter('created_at', activeFilters),
    orderBy: { column: 'created_at', ascending: false },
})
</script>

<style scoped>
.col-label-width { width: 40%; }
</style>
