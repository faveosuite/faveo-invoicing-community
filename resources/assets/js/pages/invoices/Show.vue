<template>
    <div>
        <AppAlert componentName="invoices-show" />

        <div v-if="loading" class="text-center py-5">
            <span class="spinner-border text-secondary"></span>
        </div>

        <div v-else class="card card-light">
            <div class="card-header">
                <h4 class="card-title">Invoice #{{ invoice?.number }}</h4>
                <div class="card-tools d-flex gap-2">
                    <router-link to="/invoices" class="btn btn-tool" title="Back to Invoices" v-tooltip>
                        <i class="fas fa-arrow-left"></i> Back
                    </router-link>
                    <a :href="`${baseUrl}/pdf?invoiceid=${invoiceId}`" class="btn btn-tool" title="Download PDF" v-tooltip>
                        <i class="fas fa-download"></i>
                    </a>
                </div>
            </div>

            <div class="card-body">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col-sm-6">
                        <img v-if="from?.logo" :src="from.logo" alt="Logo" width="150" height="100" class="mb-3">
                        <h2 class="mb-0 text-secondary">Invoice <span class="text-dark">#{{ invoice?.number }}</span></h2>
                    </div>
                    <div class="col-sm-6 text-end">
                        <h4 class="mb-1">Date: {{ formatDate(invoice?.date) }}</h4>
                        <h2 :class="statusClass"><strong>{{ capitalize(invoice?.status) }}</strong></h2>
                    </div>
                </div>

                <!-- Addresses -->
                <div class="row mb-4">
                    <!-- From -->
                    <div class="col-sm-6">
                        <h5 class="fw-bold mb-2">From</h5>
                        <address v-if="from" class="text-muted">
                            <strong>{{ from.company }}</strong><br>
                            {{ from.address }}<br>
                            {{ from.city }}<br>
                            {{ from.state }} {{ from.zip }}<br>
                            {{ from.country }}<br>
                            <strong>Mobile:</strong> +{{ from.phone_code }} {{ from.phone }}<br>
                            <strong>Email:</strong> {{ from.company_email }}<br>
                            <template v-if="from.gstin"><strong>GSTIN:</strong> {{ from.gstin }}<br></template>
                            <template v-if="from.cin_no"><strong>CIN:</strong> {{ from.cin_no }}<br></template>
                        </address>
                    </div>
                    <!-- To -->
                    <div class="col-sm-6">
                        <h5 class="fw-bold mb-2">To</h5>
                        <address v-if="to" class="text-muted">
                            <strong>{{ to.first_name }} {{ to.last_name }}</strong><br>
                            {{ to.address }}<br>
                            {{ to.town }}<br>
                            {{ to.state }} {{ to.zip }}<br>
                            {{ to.country }}<br>
                            <strong>Mobile:</strong> +{{ to.mobile_code }} {{ to.mobile }}<br>
                            <strong>Email:</strong> {{ to.email }}<br>
                            <template v-if="to.gstin"><strong>GSTIN:</strong> {{ to.gstin }}<br></template>
                        </address>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="table-responsive mb-4">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Order No</th>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Agents</th>
                                <th>Qty</th>
                                <th>Sub Total</th>
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
                                <td>{{ item.agents || 'Unlimited' }}</td>
                                <td>{{ item.quantity }}</td>
                                <td>{{ formatCurrency(item.subtotal) }}</td>
                            </tr>
                            <tr v-if="!items?.length">
                                <td colspan="6" class="text-center text-muted">No items found</td>
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
                                    <th class="text-start">Sub Total</th>
                                    <td class="text-end">{{ totals?.subtotal }}</td>
                                </tr>
                                <tr v-if="totals?.credits">
                                    <th class="text-start">Discount (Credits)</th>
                                    <td class="text-end">{{ totals.credits }}</td>
                                </tr>
                                <tr v-if="totals?.discount">
                                    <th class="text-start">Discount ({{ invoice?.coupon_code }})</th>
                                    <td class="text-end">{{ totals.discount }}</td>
                                </tr>
                                <tr v-for="(value, name) in totals?.tax" :key="name">
                                    <th class="text-start">{{ name }}</th>
                                    <td class="text-end">{{ value }}</td>
                                </tr>
                                <tr v-if="totals?.processing_fee && invoice?.processing_fee_label">
                                    <th class="text-start">Processing Fee ({{ invoice.processing_fee_label }})</th>
                                    <td class="text-end">{{ totals.processing_fee }}</td>
                                </tr>
                                <tr class="border-top">
                                    <th class="text-start fs-5">Total</th>
                                    <td class="text-end fs-5 fw-bold">{{ totals?.total }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Payments Table -->
                <div class="mt-4" v-if="payments?.length">
                    <h5 class="fw-bold mb-3">Payments</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Transaction Date</th>
                                    <th>Method</th>
                                    <th>Total</th>
                                    <th>Status</th>
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
    return new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

function formatCurrency(val) {
    if (val === null || val === undefined) return '—'
    const cur = invoice.value?.currency || 'USD'
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: cur }).format(val)
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
