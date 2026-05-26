<template>
    <div>
        <AppAlert componentName="client-orders-show" />

        <inline-loader v-if="loading" />

        <template v-else-if="!order">
            <div class="alert alert-warning">{{ __('message.no_records_found') }}</div>
        </template>

        <template v-else>

            <!-- Summary Bar -->
            <div class="row justify-content-center mb-4">
                <div class="col-lg-12 alert bg-color-grey">
                    <div class="d-flex flex-column flex-md-row justify-content-between plan-features text-center">
                        <div>
                            <strong>{{ __('message.order_number') }}</strong><br>
                            #{{ order.number }}
                        </div>
                        <div class="mt-3 mt-md-0">
                            <strong>{{ __('message.date') }}</strong><br>
                            {{ formatDate(order.order_date) }}
                        </div>
                        <div class="mt-3 mt-md-0">
                            <strong>{{ __('message.status') }}</strong><br>
                            {{ order.status || '—' }}
                        </div>
                        <div class="mt-3 mt-md-0">
                            <strong>{{ __('message.expiry_date') }}</strong><br>
                            {{ formatDate(order.update_ends_at) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two-column layout -->
            <div class="row pt-2">

                <!-- Left mini-nav -->
                <div class="col-lg-3 mt-4 mt-lg-0">
                    <aside class="sidebar mt-2 mb-5">
                        <ul class="nav nav-list flex-column">
                            <li class="nav-item">
                                <a class="nav-link text-3" :class="{ active: activeTab === 'license' }"
                                   href="javascript:;" @click="activeTab = 'license'">
                                    {{ __('message.license_details') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-3" :class="{ active: activeTab === 'users' }"
                                   href="javascript:;" @click="activeTab = 'users'">
                                    {{ __('message.user_details') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-3" :class="{ active: activeTab === 'invoice' }"
                                   href="javascript:;" @click="activeTab = 'invoice'">
                                    {{ __('message.invoice_list') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-3" :class="{ active: activeTab === 'receipt' }"
                                   href="javascript:;" @click="activeTab = 'receipt'">
                                    {{ __('message.payment_receipts') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-3" :class="{ active: activeTab === 'auto-renew' }"
                                   href="javascript:;" @click="activeTab = 'auto-renew'">
                                    {{ __('message.auto_renewal') }}
                                </a>
                            </li>
                        </ul>
                    </aside>
                </div>

                <!-- Right content -->
                <div class="col-lg-9 mt-2">

                    <!-- ── License Details ──────────────────────────────── -->
                    <div v-show="activeTab === 'license'">

                        <div class="row align-items-center">
                            <div class="col-sm-5">
                                <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">
                                    <span class="fw-bold">{{ __('message.license_code') }}</span>
                                </div>
                            </div>
                            <div class="col-sm-7 d-flex align-items-center gap-2">
                                <span>{{ order.serial_key || '—' }}</span>
                                <button v-if="order.serial_key"
                                        class="btn btn-light btn-sm ms-2"
                                        v-tooltip :title="copied ? __('message.copied') : __('message.copy')"
                                        @click="copyLicense">
                                    <i :class="copied ? 'fas fa-check text-success' : 'fas fa-copy'"></i>
                                </button>
                            </div>
                        </div>

                        <div class="row"><div class="col"><hr class="solid my-3"></div></div>

                        <div class="row align-items-center">
                            <div class="col-sm-5">
                                <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">
                                    <span class="fw-bold">{{ __('message.license_expiry_date') }}</span>
                                </div>
                            </div>
                            <div class="col-sm-7">{{ formatDate(order.license_ends_at) }}</div>
                        </div>

                        <div class="row"><div class="col"><hr class="solid my-3"></div></div>

                        <div class="row align-items-center">
                            <div class="col-sm-5">
                                <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">
                                    <span class="fw-bold">{{ __('message.update_expiry_date') }}</span>
                                </div>
                            </div>
                            <div class="col-sm-7">{{ formatDate(order.update_ends_at) }}</div>
                        </div>

                        <div class="row"><div class="col"><hr class="solid my-3"></div></div>

                        <!-- Installations table -->
                        <DataTable :url="installationsUrl" :dataColumns="installColumns" :option="installOptions">
                            <template #last_active="{ row }">{{ formatDate(row.last_active) }}</template>
                            <template #version="{ row }">{{ row.version || '—' }}</template>
                        </DataTable>
                    </div>

                    <!-- ── User Details ─────────────────────────────────── -->
                    <div v-show="activeTab === 'users'">
                        <template v-if="order.user">
                            <div class="row align-items-center">
                                <div class="col-sm-5">
                                    <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">
                                        <span class="fw-bold">{{ __('message.client_name') }}</span>
                                    </div>
                                </div>
                                <div class="col-sm-7">{{ order.user.name || '—' }}</div>
                            </div>
                            <div class="row"><div class="col"><hr class="solid my-3"></div></div>

                            <div class="row align-items-center">
                                <div class="col-sm-5">
                                    <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">
                                        <span class="fw-bold">{{ __('message.email') }}</span>
                                    </div>
                                </div>
                                <div class="col-sm-7">{{ order.user.email || '—' }}</div>
                            </div>
                            <div class="row"><div class="col"><hr class="solid my-3"></div></div>

                            <div class="row align-items-center">
                                <div class="col-sm-5">
                                    <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">
                                        <span class="fw-bold">{{ __('message.mobile') }}</span>
                                    </div>
                                </div>
                                <div class="col-sm-7">{{ order.user.mobile || '—' }}</div>
                            </div>
                            <div class="row"><div class="col"><hr class="solid my-3"></div></div>

                            <div class="row align-items-center">
                                <div class="col-sm-5">
                                    <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">
                                        <span class="fw-bold">{{ __('message.address') }}</span>
                                    </div>
                                </div>
                                <div class="col-sm-7">{{ order.user.address || '—' }}</div>
                            </div>
                        </template>
                    </div>

                    <!-- ── Invoice List ─────────────────────────────────── -->
                    <div v-if="activeTab === 'invoice'">
                        <DataTable :url="invoicesUrl" :dataColumns="invoiceColumns" :option="invoiceOptions">
                            <template #number="{ row }">
                                <RouterLink :to="'/invoices/' + row.id" class="fw-semibold">{{ row.number || '—' }}</RouterLink>
                            </template>
                            <template #date="{ row }">{{ formatDate(row.date) }}</template>
                            <template #status="{ row }">
                                <span class="badge" :class="invoiceBadge(row.status)">{{ row.status || '—' }}</span>
                            </template>
                            <template #action="{ row }">
                                <RouterLink :to="'/invoices/' + row.id" class="btn btn-sm btn-light"
                                            v-tooltip :title="__('message.view')">
                                    <i class="fas fa-eye"></i>
                                </RouterLink>
                            </template>
                        </DataTable>
                    </div>

                    <!-- ── Payment Receipts ─────────────────────────────── -->
                    <div v-if="activeTab === 'receipt'">
                        <DataTable :url="paymentsUrl" :dataColumns="paymentColumns" :option="paymentOptions">
                            <template #payment_status="{ row }">
                                <span :class="paymentBadge(row.payment_status)">{{ row.payment_status || '—' }}</span>
                            </template>
                            <template #created_at="{ row }">{{ formatDate(row.created_at) }}</template>
                        </DataTable>
                    </div>

                    <!-- ── Auto Renewal ─────────────────────────────────── -->
                    <div v-show="activeTab === 'auto-renew'">
                        <div class="alert alert-info">
                            {{ __('message.auto_renewal') }}
                        </div>
                    </div>

                </div>
            </div>

        </template>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { errorHandler } from '@/helpers/responseHandler.js'

const el      = document.getElementById('app-client')
const baseUrl = el?.dataset?.baseUrl ?? ''
const userId  = el?.dataset?.userId  ?? ''

const route   = useRoute()
const orderId = route.params.id

const loading   = ref(true)
const copied    = ref(false)
const activeTab = ref('license')
const order     = ref(null)

const installationsUrl = `${baseUrl}/get-my-installations/${orderId}`
const invoicesUrl      = `${baseUrl}/get-my-invoices/${orderId}/${userId}`
const paymentsUrl      = `${baseUrl}/get-my-payment-client/${orderId}/${userId}`


const installColumns = ['installation_path', 'installation_ip', 'version', 'last_active']
const installOptions = reactive({
    headings: {
        installation_path: () => __('message.installation_path'),
        installation_ip:   () => __('message.installation_ip'),
        version:           () => __('message.version'),
        last_active:       () => __('message.last_active'),
    },
    sortable:   ['installation_path', 'installation_ip', 'last_active'],
    filterable: true,
})

const invoiceColumns = ['number', 'date', 'grand_total', 'status', 'action']
const invoiceOptions = reactive({
    headings: {
        number:      () => __('message.invoice_no'),
        date:        () => __('message.date'),
        grand_total: () => __('message.grand_total'),
        status:      () => __('message.status'),
        action:      () => __('message.actions'),
    },
    sortable:   ['number', 'date'],
    filterable: true,
})

const paymentColumns = ['invoice_number', 'amount', 'payment_method', 'payment_status', 'created_at']
const paymentOptions = reactive({
    headings: {
        invoice_number: () => __('message.invoice_no'),
        amount:         () => __('message.total'),
        payment_method: () => __('message.method'),
        payment_status: () => __('message.status'),
        created_at:     () => __('message.payment_date'),
    },
    sortable:   ['payment_status', 'created_at'],
    filterable: true,
})

function formatDate(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
}

function invoiceBadge(status) {
    const s = (status ?? '').toLowerCase()
    if (s === 'paid' || s === 'success')   return 'bg-success'
    if (s === 'pending' || s === 'unpaid') return 'bg-warning text-dark'
    if (s === 'partially paid')            return 'bg-info text-dark'
    if (s === 'cancelled')                 return 'bg-danger'
    if (s === 'overdue')                   return 'bg-danger'
    return 'bg-secondary'
}

function paymentBadge(status) {
    const s = (status ?? '').toLowerCase()
    if (s === 'success') return 'badge bg-success'
    if (s === 'pending') return 'badge bg-warning text-dark'
    if (s === 'failed')  return 'badge bg-danger'
    return 'badge bg-secondary'
}

async function copyLicense() {
    const code = order.value?.serial_key
    if (!code) return
    try {
        await navigator.clipboard.writeText(code)
        copied.value = true
        setTimeout(() => { copied.value = false }, 2000)
    } catch {}
}

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/get-my-orders`, { params: { id: orderId } })
        order.value = res.data?.data ?? null
    } catch (e) {
        errorHandler(e, 'client-orders-show')
    } finally {
        loading.value = false
    }
})
</script>
