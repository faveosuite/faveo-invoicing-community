<template>
    <div>

        <div class="card">
            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">

                    <!-- Invoice header: logo (left) | number + date + status (right) -->
                    <div class="row align-items-start mb-4">
                        <div class="col-sm-6">
                            <img v-if="from?.logo" :src="from.logo" alt="Logo" class="mb-3 d-block invoice-logo">
                            <h3 class="mb-0">
                                {{ __('message.invoice') }}
                                <span class="text-muted">#{{ invoice?.number }}</span>
                            </h3>
                        </div>
                        <div class="col-sm-6 text-end">
                            <p class="mb-1 text-muted">
                                {{ __('message.date') }}: <strong class="text-dark">{{ formatDate(invoice?.date) }}</strong>
                            </p>
                            <span class="badge" :class="statusBadgeClass">{{ capitalize(invoice?.status) }}</span>
                            <div class="d-flex gap-2 justify-content-end mt-2">
                                <RouterLink
                                    v-if="invoice?.status?.toLowerCase() === 'pending'"
                                    :to="{ path: '/checkout', query: { invoice: invoiceId } }"
                                    class="btn btn-success btn-sm"
                                >
                                    <i class="fas fa-credit-card me-1"></i>{{ __('message.pay_now') }}
                                </RouterLink>
                                <a
                                    :href="`${baseUrl}/pdf?invoiceid=${invoiceId}`"
                                    class="btn btn-outline-secondary btn-sm"
                                    :title="__('message.download_pdf')"
                                >
                                    <i class="fas fa-download me-1"></i>{{ __('message.download_pdf') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- From / To -->
                    <div class="row mb-4 mt-2">
                        <div class="col-sm-6">
                            <p class="text-muted text-uppercase fw-bold small mb-2">{{ __('message.from') }}</p>
                            <address v-if="from" class="mb-0">
                                <strong>{{ from.company }}</strong><br>
                                <span v-if="from.address">{{ from.address }}<br></span>
                                <span v-if="from.city">{{ from.city }}<br></span>
                                <span v-if="from.state || from.zip">{{ from.state }} {{ from.zip }}<br></span>
                                <span v-if="from.country">{{ from.country }}<br></span>
                                <span v-if="from.phone">
                                    <i class="fas fa-phone fa-xs me-1 text-muted"></i>+{{ from.phone_code }} {{ from.phone }}<br>
                                </span>
                                <span v-if="from.company_email">
                                    <i class="fas fa-envelope fa-xs me-1 text-muted"></i>{{ from.company_email }}<br>
                                </span>
                                <span v-if="from.gstin">{{ __('message.gstin') }}: {{ from.gstin }}<br></span>
                                <span v-if="from.cin_no">{{ __('message.cin') }}: {{ from.cin_no }}<br></span>
                            </address>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted text-uppercase fw-bold small mb-2">{{ __('message.to') }}</p>
                            <address v-if="to" class="mb-0">
                                <strong>{{ to.first_name }} {{ to.last_name }}</strong><br>
                                <span v-if="to.address">{{ to.address }}<br></span>
                                <span v-if="to.town">{{ to.town }}<br></span>
                                <span v-if="to.state || to.zip">{{ to.state }} {{ to.zip }}<br></span>
                                <span v-if="to.country">{{ to.country }}<br></span>
                                <span v-if="to.mobile">
                                    <i class="fas fa-phone fa-xs me-1 text-muted"></i>+{{ to.mobile_code }} {{ to.mobile }}<br>
                                </span>
                                <span v-if="to.email">
                                    <i class="fas fa-envelope fa-xs me-1 text-muted"></i>{{ to.email }}<br>
                                </span>
                                <span v-if="to.gstin">{{ __('message.gstin') }}: {{ to.gstin }}<br></span>
                            </address>
                        </div>
                    </div>

                    <!-- Line items -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('message.order_no') }}</th>
                                    <th>{{ __('message.product') }}</th>
                                    <th class="text-end">{{ __('message.price') }}</th>
                                    <th class="text-center">{{ __('message.agents') }}</th>
                                    <th class="text-center">{{ __('message.qty') }}</th>
                                    <th class="text-end">{{ __('message.sub_total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in items" :key="item.id">
                                    <td>
                                        <RouterLink v-if="item.order" :to="`/my-order/${item.order.id}`">
                                            #{{ item.order.number }}
                                        </RouterLink>
                                        <span v-else>—</span>
                                    </td>
                                    <td>{{ item.product_name }}</td>
                                    <td class="text-end">{{ formatCurrency(item.regular_price) }}</td>
                                    <td class="text-center">{{ item.agents || __('message.unlimited') }}</td>
                                    <td class="text-center">{{ item.quantity }}</td>
                                    <td class="text-end">{{ formatCurrency(item.subtotal) }}</td>
                                </tr>
                                <tr v-if="!items?.length">
                                    <td colspan="6" class="text-center text-muted">{{ __('message.no_items_found') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals -->
                    <div class="row">
                        <div class="col-lg-5 ms-auto">
                            <table class="table table-sm table-borderless w-100">
                                <thead class="visually-hidden"><tr><th>Description</th><th>Amount</th></tr></thead>
                                <colgroup>
                                    <col>
                                    <col class="col-label-width">
                                </colgroup>
                                <tbody>
                                    <tr>
                                        <td class="text-muted">{{ __('message.sub_total') }}</td>
                                        <td class="text-end">{{ totals?.subtotal }}</td>
                                    </tr>
                                    <tr v-if="totals?.credits">
                                        <td class="text-muted">{{ __('message.discount') }} (Credits)</td>
                                        <td class="text-end">{{ totals.credits }}</td>
                                    </tr>
                                    <tr v-if="totals?.discount">
                                        <td class="text-muted">{{ __('message.discount') }} ({{ invoice?.coupon_code }})</td>
                                        <td class="text-end">{{ totals.discount }}</td>
                                    </tr>
                                    <tr v-for="(value, name) in totals?.tax" :key="name">
                                        <td class="text-muted">{{ name }}</td>
                                        <td class="text-end">{{ value }}</td>
                                    </tr>
                                    <tr v-if="totals?.processing_fee && invoice?.processing_fee_label">
                                        <td class="text-muted">
                                            {{ __('message.processing_fee') }} ({{ invoice.processing_fee_label }})
                                        </td>
                                        <td class="text-end">{{ totals.processing_fee }}</td>
                                    </tr>
                                    <tr class="border-top">
                                        <td class="fw-bold">{{ __('message.total') }}</td>
                                        <td class="text-end fw-bold fs-5">{{ totals?.total }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Payment history -->
                    <template v-if="payments?.length">
                        <p class="text-muted text-uppercase fw-bold small mb-3 mt-2">{{ __('message.payments_section') }}</p>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('message.transaction_date') }}</th>
                                        <th>{{ __('message.method') }}</th>
                                        <th class="text-end">{{ __('message.total') }}</th>
                                        <th class="text-center">{{ __('message.status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="pay in payments" :key="pay.id">
                                        <td>{{ formatDate(pay.created_at) }}</td>
                                        <td>{{ capitalize(pay.payment_method) }}</td>
                                        <td class="text-end">{{ formatCurrency(pay.amount) }}</td>
                                        <td class="text-center">
                                            <span class="badge" :class="pay.payment_status === 'success' ? 'bg-success' : 'bg-secondary'">
                                                {{ capitalize(pay.payment_status) }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </template>

                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { errorHandler } from '@/helpers/responseHandler.js'
import { useDateTime } from '@/core/composables/useDateTime'
import { useBaseUrl } from '@/core/composables/useBaseUrl'

const { formatDate } = useDateTime()

const COMPONENT = 'client-page'
const baseUrl = useBaseUrl()

const route = useRoute()
const invoiceId = route.params.id

const loading = ref(true)
const invoice = ref(null)
const from = ref(null)
const to = ref(null)
const items = ref([])
const totals = ref(null)
const payments = ref([])

const statusBadgeClass = computed(() => {
    const s = invoice.value?.status?.toLowerCase()
    if (s === 'success' || s === 'paid') return 'bg-success'
    if (s === 'pending') return 'bg-warning text-dark'
    if (s === 'partially paid') return 'bg-info text-dark'
    if (s === 'cancelled') return 'bg-danger'
    if (s === 'overdue') return 'bg-danger'
    return 'bg-secondary'
})

function capitalize(str) {
    return str ? String(str).charAt(0).toUpperCase() + String(str).slice(1) : '—'
}


function formatCurrency(val) {
    if (val === null || val === undefined) return '—'
    const cur = invoice.value?.currency || 'USD'
    return new Intl.NumberFormat(undefined, { style: 'currency', currency: cur }).format(val)
}

async function fetchInvoice() {
    try {
        const res = await http.get(`/invoice/${invoiceId}`)
        const data = res.data?.data ?? res.data

        invoice.value = data.invoice
        from.value = data.from
        to.value = data.to
        items.value = data.items ?? []
        totals.value = data.totals
        payments.value = data.payments ?? []
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
}

onMounted(fetchInvoice)
</script>

<style scoped>
.invoice-logo { max-height: 70px; max-width: 180px; }
.col-label-width { width: 140px; }
</style>
