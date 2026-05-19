<template>
    <div>
        <AppAlert componentName="invoices-show" />

        <inline-loader v-if="loading" />

        <div v-else class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.invoice') }} #{{ invoice?.number }}</h4>
                <div class="card-tools d-flex gap-2">
                    <router-link to="/invoices" class="btn btn-tool" :title="__('message.back_to_invoices')" v-tooltip>
                        <i class="fas fa-arrow-left"></i> {{ __('message.back') }}
                    </router-link>
                    <a :href="`${baseUrl}/pdf?invoiceid=${invoiceId}`" class="btn btn-tool" :title="__('message.download_pdf')" v-tooltip>
                        <i class="fas fa-download"></i>
                    </a>
                </div>
            </div>

            <div class="card-body">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col-sm-6">
                        <img v-if="from?.logo" :src="from.logo" alt="Logo" width="150" height="100" class="mb-3">
                        <h2 class="mb-0 text-secondary">{{ __('message.invoice') }} <span class="text-dark">#{{ invoice?.number }}</span></h2>
                    </div>
                    <div class="col-sm-6 text-end">
                        <h4 class="mb-1">{{ __('message.date') }}: {{ formatDate(invoice?.date) }}</h4>
                        <h2 :class="statusClass"><strong>{{ capitalize(invoice?.status) }}</strong></h2>
                    </div>
                </div>

                <!-- Addresses -->
                <div class="row mb-4">
                    <!-- From -->
                    <div class="col-sm-6">
                        <h5 class="fw-bold mb-2">{{ __('message.from') }}</h5>
                        <address v-if="from" class="text-muted">
                            <strong>{{ from.company }}</strong><br>
                            {{ from.address }}<br>
                            {{ from.city }}<br>
                            {{ from.state }} {{ from.zip }}<br>
                            {{ from.country }}<br>
                            <strong>{{ __('message.mobile') }}:</strong> +{{ from.phone_code }} {{ from.phone }}<br>
                            <strong>{{ __('message.email') }}:</strong> {{ from.company_email }}<br>
                            <template v-if="from.gstin"><strong>{{ __('message.gstin') }}:</strong> {{ from.gstin }}<br></template>
                            <template v-if="from.cin_no"><strong>{{ __('message.cin') }}:</strong> {{ from.cin_no }}<br></template>
                        </address>
                    </div>
                    <!-- To -->
                    <div class="col-sm-6">
                        <h5 class="fw-bold mb-2">{{ __('message.to') }}</h5>
                        <address v-if="to" class="text-muted">
                            <strong>{{ to.first_name }} {{ to.last_name }}</strong><br>
                            {{ to.address }}<br>
                            {{ to.town }}<br>
                            {{ to.state }} {{ to.zip }}<br>
                            {{ to.country }}<br>
                            <strong>{{ __('message.mobile') }}:</strong> +{{ to.mobile_code }} {{ to.mobile }}<br>
                            <strong>{{ __('message.email') }}:</strong> {{ to.email }}<br>
                            <template v-if="to.gstin"><strong>{{ __('message.gstin') }}:</strong> {{ to.gstin }}<br></template>
                        </address>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="table-responsive mb-4">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('message.order_no') }}</th>
                                <th>{{ __('message.product') }}</th>
                                <th>{{ __('message.price') }}</th>
                                <th>{{ __('message.agents') }}</th>
                                <th>{{ __('message.qty') }}</th>
                                <th>{{ __('message.sub_total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in items" :key="item.id">
                                <td>
                                    <router-link v-if="item.order" :to="`/orders/${item.order.id}`">#{{ item.order.number }}</router-link>
                                    <span v-else>—</span>
                                </td>
                                <td>{{ item.product_name }}</td>
                                <td>{{ formatCurrency(item.regular_price) }}</td>
                                <td>{{ item.agents || __('message.unlimited') }}</td>
                                <td>{{ item.quantity }}</td>
                                <td>{{ formatCurrency(item.subtotal) }}</td>
                            </tr>
                            <tr v-if="!items?.length">
                                <td colspan="6" class="text-center text-muted">{{ __('message.no_items_found') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Summary -->
                <div class="row">
                    <div class="col-lg-6"></div>
                    <div class="col-lg-6">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th class="text-start">{{ __('message.sub_total') }}</th>
                                    <td class="text-end">{{ totals?.subtotal }}</td>
                                </tr>
                                <tr v-if="totals?.credits">
                                    <th class="text-start">{{ __('message.discount') }} (Credits)</th>
                                    <td class="text-end">{{ totals.credits }}</td>
                                </tr>
                                <tr v-if="totals?.discount">
                                    <th class="text-start">{{ __('message.discount') }} ({{ invoice?.coupon_code }})</th>
                                    <td class="text-end">{{ totals.discount }}</td>
                                </tr>
                                <tr v-for="(value, name) in totals?.tax" :key="name">
                                    <th class="text-start">{{ name }}</th>
                                    <td class="text-end">{{ value }}</td>
                                </tr>
                                <tr v-if="totals?.processing_fee && invoice?.processing_fee_label">
                                    <th class="text-start">{{ __('message.processing_fee') }} ({{ invoice.processing_fee_label }})</th>
                                    <td class="text-end">{{ totals.processing_fee }}</td>
                                </tr>
                                <tr class="border-top">
                                    <th class="text-start fs-5">{{ __('message.total') }}</th>
                                    <td class="text-end fs-5 fw-bold">{{ totals?.total }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Payments Table -->
                <div class="mt-4" v-if="payments?.length">
                    <h5 class="fw-bold mb-3">{{ __('message.payments_section') }}</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('message.transaction_date') }}</th>
                                    <th>{{ __('message.method') }}</th>
                                    <th>{{ __('message.total') }}</th>
                                    <th>{{ __('message.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="pay in payments" :key="pay.id">
                                    <td>{{ formatDate(pay.created_at) }}</td>
                                    <td>{{ capitalize(pay.payment_method) }}</td>
                                    <td>{{ formatCurrency(pay.amount) }}</td>
                                    <td>
                                        <span class="badge" :class="pay.payment_status === 'success' ? 'bg-success' : 'bg-secondary'">
                                            {{ capitalize(pay.payment_status) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import http from '@/plugins/axios'
import { errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'invoices-show'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const route = useRoute()
const invoiceId = route.params.id

const loading = ref(true)

const invoice = ref(null)
const from = ref(null)
const to = ref(null)
const items = ref([])
const totals = ref(null)
const payments = ref([])

const statusClass = computed(() => {
    const s = invoice.value?.status?.toLowerCase()
    if (s === 'success' || s === 'paid') return 'text-success'
    if (s === 'pending' || s === 'unpaid') return 'text-warning text-dark'
    return 'text-secondary'
})

function capitalize(str) {
    return str ? String(str).charAt(0).toUpperCase() + String(str).slice(1) : '—'
}

function formatDate(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
}

function formatCurrency(val) {
    if (val === null || val === undefined) return '—'
    const cur = invoice.value?.currency || 'USD'
    return new Intl.NumberFormat(undefined, { style: 'currency', currency: cur }).format(val)
}

async function fetchInvoice() {
    try {
        const res = await http.get(`${baseUrl}/invoice/${invoiceId}`)
        const data = res.data?.data ?? res.data

        invoice.value = data.invoice
        from.value = data.from
        to.value = data.to
        items.value = data.items
        totals.value = data.totals
        payments.value = data.payments

    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    fetchInvoice()
})
</script>
