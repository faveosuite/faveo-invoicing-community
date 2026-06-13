<template>
    <div>
        <AppAlert :componentName="COMPONENT" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.order_details') }}</h4>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">

                    <!-- ── Overview ──────────────────────────────────────── -->
                    <div class="card card-light mb-3">
                        <div class="card-body py-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-muted small mb-1">{{ __('message.date') }}</div>
                                    <div class="fw-bold">{{ order?.created_at || '—' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-muted small mb-1">{{ __('message.order_no') }}</div>
                                    <div class="fw-bold">#{{ order?.number }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-muted small mb-1">{{ __('message.status') }}</div>
                                    <div class="fw-bold">{{ order?.order_status || '—' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── User Details ──────────────────────────────────── -->
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card card-light">
                                <div class="card-header">
                                    <h5 class="card-title">{{ __('message.user_details') }}</h5>
                                </div>
                                <div class="card-body table-responsive">
                                    <table class="table table-hover">
                                        <tbody>
                                            <tr>
                                                <td><b>{{ __('message.name') }}:</b></td>
                                                <td>{{ userName }}</td>
                                            </tr>
                                            <tr>
                                                <td><b>{{ __('message.email') }}:</b></td>
                                                <td>{{ order?.user?.email || '—' }}</td>
                                            </tr>
                                            <tr>
                                                <td><b>{{ __('message.mobile') }}:</b></td>
                                                <td>
                                                    <span v-if="order?.user?.mobile_code">
                                                        (<b>+</b>{{ order.user.mobile_code }})&nbsp;
                                                    </span>
                                                    {{ order?.user?.mobile || '—' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><b>{{ __('message.country') }}:</b></td>
                                                <td>{{ order?.user?.country || '—' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                <!-- ── License Details ─────────────────────────────────── -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="card card-light">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('message.license_details') }}</h4>
                            </div>
                            <div class="card-body table-responsive">
                                <table class="table table-hover">
                                    <tbody>
                                        <!-- License Code -->
                                        <tr>
                                            <td><b>{{ __('message.license_code') }}:</b></td>
                                            <td>{{ licenseDetails?.licence_code || '—' }}</td>
                                            <td>
                                                <action-button
                                                    variant="secondary"
                                                    size="sm"
                                                    class="btn-xs"
                                                    :icon="copied ? 'fas fa-check' : 'fas fa-clipboard'"
                                                    :icon-only="true"
                                                    v-tooltip="__('message.copy')"
                                                    @click="copyLicenseCode"
                                                />
                                                <action-button
                                                    variant="secondary"
                                                    size="sm"
                                                    class="btn-xs ms-1"
                                                    icon="fas fa-credit-card"
                                                    :icon-only="true"
                                                    :loading="saving.reissue"
                                                    v-tooltip="__('message.reissue_license')"
                                                    @click="reissueLicense"
                                                />
                                            </td>
                                        </tr>

                                        <!-- Updates Expiry -->
                                        <tr>
                                            <td><b>{{ __('message.updates_expiry') }}:</b></td>
                                            <td>
                                                <span :class="expiryStatus('update_end') ? 'text-danger' : ''">
                                                    {{ expiryDate('update_end') || '—' }}
                                                </span>
                                                <span v-if="expiryStatus('update_end')" class="badge bg-danger ms-1">
                                                    {{ expiryStatus('update_end') }}
                                                </span>
                                            </td>
                                            <td>
                                                <action-button
                                                    v-if="expiryDate('update_end')"
                                                    variant="secondary"
                                                    size="sm"
                                                    class="btn-xs"
                                                    icon="fas fa-pen"
                                                    :icon-only="true"
                                                    v-tooltip="__('message.edit')"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#updateExpiryModal"
                                                    @click="modal.date = expiryRaw('update_end')"
                                                />
                                            </td>
                                        </tr>

                                        <!-- License Expiry -->
                                        <tr>
                                            <td><b>{{ __('message.license_expiry') }}:</b></td>
                                            <td>
                                                <span :class="expiryStatus('subscription_end') ? 'text-danger' : ''">
                                                    {{ expiryDate('subscription_end') || '—' }}
                                                </span>
                                                <span v-if="expiryStatus('subscription_end')" class="badge bg-danger ms-1">
                                                    {{ expiryStatus('subscription_end') }}
                                                </span>
                                            </td>
                                            <td>
                                                <action-button
                                                    v-if="expiryDate('subscription_end')"
                                                    variant="secondary"
                                                    size="sm"
                                                    class="btn-xs"
                                                    icon="fas fa-pen"
                                                    :icon-only="true"
                                                    v-tooltip="__('message.edit')"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#licenseExpiryModal"
                                                    @click="modal.date = expiryRaw('subscription_end')"
                                                />
                                            </td>
                                        </tr>

                                        <!-- Support Expiry -->
                                        <tr>
                                            <td><b>{{ __('message.support_expiry') }}:</b></td>
                                            <td>
                                                <span :class="expiryStatus('support_end') ? 'text-danger' : ''">
                                                    {{ expiryDate('support_end') || '—' }}
                                                </span>
                                                <span v-if="expiryStatus('support_end')" class="badge bg-danger ms-1">
                                                    {{ expiryStatus('support_end') }}
                                                </span>
                                            </td>
                                            <td>
                                                <action-button
                                                    v-if="expiryDate('support_end')"
                                                    variant="secondary"
                                                    size="sm"
                                                    class="btn-xs"
                                                    icon="fas fa-pen"
                                                    :icon-only="true"
                                                    v-tooltip="__('message.edit')"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#supportExpiryModal"
                                                    @click="modal.date = expiryRaw('support_end')"
                                                />
                                            </td>
                                        </tr>

                                        <!-- Localized License -->
                                        <tr>
                                            <td><b>{{ __('message.switch_localized_license') }}</b></td>
                                            <td>
                                                <Switch
                                                    name="license_mode"
                                                    :value="order?.license_mode === 'File'"
                                                    :disabled="saving.licenseMode"
                                                    :onChange="toggleLicenseMode"
                                                />
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Installation Details ────────────────────────────── -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="card card-light">
                            <div class="card-header">
                                <h4 class="card-title">
                                    {{ __('message.installation_details') }}
                                </h4>
                            </div>
                            <div class="card-body table-responsive">
                                <DataTable
                                    :url="`${baseUrl}/get-installation-details/${orderId}`"
                                    :dataColumns="installColumns"
                                    :option="installTableOptions"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Invoice List ────────────────────────────────────── -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="card card-light">
                            <div class="card-header">
                                <h4 class="card-title">
                                    {{ __('message.invoice_list') }}
                                </h4>
                            </div>
                            <div class="card-body table-responsive">
                                <DataTable
                                    :url="`${baseUrl}/getOrderInvoices/${orderId}`"
                                    :dataColumns="invoiceColumns"
                                    :option="invoiceTableOptions"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Payment Receipts ────────────────────────────────── -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="card card-light">
                            <div class="card-header">
                                <h4 class="card-title">
                                    {{ __('message.payment_receipts') }}
                                </h4>
                            </div>
                            <div class="card-body table-responsive">
                                <DataTable
                                    :url="`${baseUrl}/getOrderPayments/${orderId}`"
                                    :dataColumns="paymentColumns"
                                    :option="paymentTableOptions"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Auto Renewal ────────────────────────────────────── -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="card card-light">
                            <div class="card-header">
                                <h4 class="card-title">
                                    {{ __('message.auto_renewal') }}
                                </h4>
                            </div>
                            <div class="card-body">
                                <table class="table table-hover">
                                    <tbody>
                                        <tr>
                                            <td><b>{{ __('message.auto_renewal_subscription') }}</b></td>
                                            <td>
                                                <Switch
                                                    name="auto_renewal"
                                                    :value="autorenewal == 1"
                                                    :disabled="autorenewal != 1 || saving.renewal"
                                                    :onChange="disableRenewal"
                                                />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><b>{{ __('message.status') }}:</b></td>
                                            <td>
                                                <span v-if="isSubscribed" class="text-success fw-bold">{{ __('message.active') }}</span>
                                                <span v-else class="text-danger fw-bold">{{ __('message.inactive') }}</span>
                                            </td>
                                        </tr>
                                        <template v-if="isSubscribed && paymentLog">
                                            <tr>
                                                <td><b>{{ __('message.payment-method') }}:</b></td>
                                                <td>{{ paymentLog?.payment_method ? capitalize(paymentLog.payment_method) : '—' }}</td>
                                            </tr>
                                            <tr>
                                                <td><b>{{ __('message.subscription_start_date') }}:</b></td>
                                                <td>{{ paymentLog?.date ? formatDate(paymentLog.date) : '—' }}</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                </div><!-- /card-body -->
            </template>
        </div><!-- /card -->

        <!-- ── Modals ──────────────────────────────────────────────────── -->
        <ExpiryModal
            id="updateExpiryModal"
            :title="__('message.edit_updates_expiry')"
            :orderId="orderId"
            :initialDate="modal.date"
            endpoint="edit-update-expiry"
            :baseUrl="baseUrl"
            @saved="reload"
        />
        <ExpiryModal
            id="licenseExpiryModal"
            :title="__('message.edit_license_expiry')"
            :orderId="orderId"
            :initialDate="modal.date"
            endpoint="edit-license-expiry"
            :baseUrl="baseUrl"
            @saved="reload"
        />
        <ExpiryModal
            id="supportExpiryModal"
            :title="__('message.edit_support_expiry')"
            :orderId="orderId"
            :initialDate="modal.date"
            endpoint="edit-support-expiry"
            :baseUrl="baseUrl"
            @saved="reload"
        />
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, h } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import http from '@/plugins/axios'
import { errorHandler } from '@/helpers/responseHandler.js'
import ExpiryModal from './components/ExpiryModal.vue'
import Switch from '@/components/Reusable/FormField/Switch.vue'
import { useDateTime } from '@/core/composables/useDateTime'

const { formatDate } = useDateTime()

const COMPONENT = 'orders-show'

const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const route   = useRoute()
const orderId = route.params.id

const loading        = ref(true)
const copied         = ref(false)
const order          = ref(null)
const licenseDetails = ref(null)
const autorenewal    = ref(0)
const isSubscribed   = ref(0)
const paymentLog     = ref(null)

const saving = reactive({
    reissue:     false,
    renewal:     false,
    licenseMode: false,
})

const modal = reactive({ date: null })

// ── Helpers ────────────────────────────────────────────────────────────────
const userName = computed(() => {
    const u = order.value?.user
    if (!u) return '—'
    return `${u.first_name ?? ''} ${u.last_name ?? ''}`.trim() || '—'
})

function capitalize(str) {
    return str ? str.charAt(0).toUpperCase() + str.slice(1) : ''
}


function expiryEntry(key) {
    return licenseDetails.value?.expiry_dates?.[key] ?? null
}

function expiryDate(key) {
    const d = expiryEntry(key)?.date
    return d ? formatDate(d) : null
}

function expiryRaw(key) {
    const d = expiryEntry(key)?.date
    if (!d) return null
    const dt = new Date(d)
    const mm   = String(dt.getMonth() + 1).padStart(2, '0')
    const dd   = String(dt.getDate()).padStart(2, '0')
    const yyyy = dt.getFullYear()
    return `${mm}/${dd}/${yyyy}`
}

function expiryStatus(key) {
    return expiryEntry(key)?.status ?? null
}

// ── Actions ────────────────────────────────────────────────────────────────
async function copyLicenseCode(e) {
    const code = licenseDetails.value?.licence_code
    if (!code) return
    await navigator.clipboard.writeText(code)
    copied.value = true
    e.target.blur()
    setTimeout(() => { copied.value = false }, 2000)
}

async function reissueLicense() {
    saving.reissue = true
    try {
        await http.patch(`${baseUrl}/reissue-license`, { id: orderId })
        await reload()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.reissue = false
    }
}

async function disableRenewal() {
    saving.renewal = true
    try {
        await http.post(`${baseUrl}/renewal-disable`, { order_id: orderId })
        await reload()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.renewal = false
    }
}

async function toggleLicenseMode(checked) {
    saving.licenseMode = true
    try {
        const choose = checked ? 1 : 0
        await http.post(`${baseUrl}/switch-license-mode`, { choose, orderNo: order.value?.number })
        await reload()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.licenseMode = false
    }
}

// ── Data loading ───────────────────────────────────────────────────────────
async function reload() {
    const res = await http.get(`${baseUrl}/order/${orderId}`)
    const d   = res.data?.data ?? res.data
    order.value          = d.order
    licenseDetails.value = d.license_details
    autorenewal.value    = d.autorenewal
    isSubscribed.value   = d.is_subscribed
    paymentLog.value     = d.payment_log
}

onMounted(async () => {
    try {
        await reload()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

// ── Installation table ─────────────────────────────────────────────────────
const installColumns = ['path', 'ip', 'version', 'status', 'last_active_date']
const installTableOptions = reactive({
    headings: {
        path:             __('message.installation_path'),
        ip:               __('message.installation_ip'),
        version:          'Version',
        status:           __('message.status'),
        last_active_date: __('message.last_active'),
    },
    columnsClasses: {
        path: 'dt-text',
        ip: 'dt-code',
        version: 'dt-code',
        status: 'dt-status',
        last_active_date: 'dt-date',
    },
    templates: {
        path:             (f, row) => row.path || '—',
        ip:               (f, row) => row.ip || '—',
        version:          (f, row) => row.version || '—',
        status:           (f, row) => row.status || '—',
        last_active_date: (f, row) => row.last_active_date || '—',
    },
    sortable:   [],
    filterable: false,
    requestAdapter(data) {
        return { page: data.page, limit: data.limit }
    },
    responseAdapter({ data }) {
        const rows = data?.data ?? []
        return { data: rows, count: rows.length }
    },
})

// ── Invoice table ──────────────────────────────────────────────────────────
const invoiceColumns = ['number', 'products', 'date', 'amount', 'status']
const invoiceTableOptions = reactive({
    headings: {
        number:   __('message.invoice_no'),
        products: __('message.products'),
        date:     __('message.date'),
        amount:   __('message.total'),
        status:   __('message.status'),
    },
    columnsClasses: {
        number: 'dt-number',
        products: 'dt-name',
        date: 'dt-date',
        amount: 'dt-amount',
        status: 'dt-status',
    },
    templates: {
        number:   (f, row) => row.number && row.id ? h(RouterLink, { to: '/invoices/' + row.id }, () => row.number) : (row.number || '—'),
        products: (f, row) => (row.products ?? []).join(', ') || '—',
        status: (f, row) => h('span', {
            class: row.status === 'Success' ? 'badge bg-success' : 'badge bg-secondary',
        }, row.status),
    },
    sortable:   ['date', 'number', 'amount', 'status'],
    filterable: false,
    requestAdapter(data) {
        const columnMap = { amount: 'grand_total' }
        return {
            'sort-field':   (columnMap[data.orderBy] ?? data.orderBy) || 'date',
            'sort-order':   data.ascending ? 'asc' : 'desc',
            'search-query': '',
            page:           data.page,
            limit:          data.limit,
        }
    },
    orderBy: { column: 'date', ascending: false },
})

// ── Payment table ──────────────────────────────────────────────────────────
const paymentColumns = ['invoice_number', 'amount', 'payment_method', 'payment_status', 'created_at']
const paymentTableOptions = reactive({
    headings: {
        invoice_number: __('message.invoice_no'),
        amount:         __('message.total'),
        payment_method: __('message.method'),
        payment_status: __('message.status'),
        created_at:     __('message.payment_date'),
    },
    columnsClasses: {
        invoice_number: 'dt-number',
        amount: 'dt-amount',
        payment_method: 'dt-name',
        payment_status: 'dt-status',
        created_at: 'dt-date',
    },
    templates: {
        payment_status: (f, row) => h('span', {
            class: row.payment_status === 'Success' ? 'badge bg-success' : 'badge bg-secondary',
        }, row.payment_status),
        created_at: (f, row) => row.created_at ? formatDate(row.created_at) : '—',
    },
    sortable:   ['created_at', 'amount', 'payment_method', 'payment_status'],
    filterable: false,
    requestAdapter(data) {
        return {
            'sort-field':   data.orderBy || 'created_at',
            'sort-order':   data.ascending ? 'asc' : 'desc',
            'search-query': '',
            page:           data.page,
            limit:          data.limit,
        }
    },
    orderBy: { column: 'created_at', ascending: false },
})
</script>
