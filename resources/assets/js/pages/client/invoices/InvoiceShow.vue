<template>
    <div>

        <div class="card">
            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <div v-else-if="!invoice" class="card-body text-center text-muted py-5">{{ __('message.invoice_not_found') }}</div>

            <template v-else>
                <div class="card-body">

                    <!-- Invoice header: logo (left) | number + date + status (right) -->
                    <div class="row align-items-start mb-4">
                        <div class="col-sm-6">
                            <img v-if="from?.logo" :src="from.logo" :alt="from.company || __('message.invoice')" width="180" height="70" class="mb-3 d-block invoice-logo">
                            <h3 class="mb-0">
                                {{ __('message.invoice') }}
                                <span class="text-muted">#{{ invoice?.number }}</span>
                            </h3>
                        </div>
                        <div class="col-sm-6 text-end">
                            <p class="mb-1">
                                {{ __('message.date') }}: <strong class="text-dark">{{ formatDate(invoice?.date) }}</strong>
                            </p>
                            <span class="badge" :class="statusBadgeClass">{{ capitalize(invoice?.status) }}</span>
                            <div class="d-flex gap-2 justify-content-end mt-2">
                                <RouterLink
                                    v-if="['pending', 'partially paid'].includes(invoice?.status?.toLowerCase())"
                                    :to="{ path: '/checkout', query: { invoice: invoiceId } }"
                                    class="btn btn-success btn-sm"
                                >
                                    <i class="fas fa-credit-card me-1" aria-hidden="true"></i>{{ __('message.pay_now') }}
                                </RouterLink>
                                <!-- Fetched (not a plain link) so a failed PDF shows as an alert instead of raw JSON -->
                                <button
                                    type="button"
                                    class="btn btn-outline-secondary btn-sm"
                                    :title="__('message.download_pdf')"
                                    :disabled="!!downloadingUrl"
                                    @click="downloadFile(pdfUrl, `${__('message.invoice')} #${invoice?.number ?? ''}`)"
                                >
                                    <i :class="['me-1', downloadingUrl ? 'fas fa-spinner fa-spin' : 'fas fa-download']" aria-hidden="true"></i>{{ __('message.download_pdf') }}
                                </button>
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
                                    <i class="fas fa-phone fa-xs me-1 text-muted" aria-hidden="true"></i>+{{ from.phone_code }} {{ from.phone }}<br>
                                </span>
                                <span v-if="from.company_email">
                                    <i class="fas fa-envelope fa-xs me-1 text-muted" aria-hidden="true"></i>{{ from.company_email }}<br>
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
                                    <i class="fas fa-phone fa-xs me-1 text-muted" aria-hidden="true"></i>+{{ to.mobile_code }} {{ to.mobile }}<br>
                                </span>
                                <span v-if="to.email">
                                    <i class="fas fa-envelope fa-xs me-1 text-muted" aria-hidden="true"></i>{{ to.email }}<br>
                                </span>
                                <span v-if="to.gstin">{{ __('message.gstin') }}: {{ to.gstin }}<br></span>
                            </address>
                        </div>
                    </div>

                    <!-- Line items -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-striped invoice-grid">
                            <thead class="table-light">
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
                                        <RouterLink v-if="item.order" :to="`/my-order/${item.order.id}`">
                                            #{{ item.order.number }}
                                        </RouterLink>
                                        <span v-else>—</span>
                                    </td>
                                    <td>{{ item.product_name }}</td>
                                    <td class="tabular-nums">{{ item.regular_price_formatted }}</td>
                                    <td class="tabular-nums">{{ item.agents || __('message.unlimited') }}</td>
                                    <td class="tabular-nums">{{ item.quantity }}</td>
                                    <td class="tabular-nums">{{ item.subtotal_formatted }}</td>
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
                            <table class="table table-sm table-borderless w-100 invoice-totals">
                                <thead class="visually-hidden"><tr><th>{{ __('message.description') }}</th><th>{{ __('message.amount') }}</th></tr></thead>
                                <colgroup>
                                    <col>
                                    <col class="col-label-width">
                                </colgroup>
                                <tbody>
                                    <tr>
                                        <td>{{ totals?.subtotal_label }}</td>
                                        <td class="text-end tabular-nums">{{ totals?.subtotal }}</td>
                                    </tr>
                                    <tr v-if="totals?.credits">
                                        <td>{{ __('message.discount') }} ({{ __('message.credits') }})</td>
                                        <td class="text-end tabular-nums">{{ totals.credits }}</td>
                                    </tr>
                                    <tr v-if="totals?.discount">
                                        <td>{{ __('message.discount') }} ({{ invoice?.coupon_code }})</td>
                                        <td class="text-end tabular-nums">{{ totals.discount }}</td>
                                    </tr>
                                    <tr v-for="(value, name) in totals?.tax" :key="name">
                                        <td>{{ name }}</td>
                                        <td class="text-end tabular-nums">{{ value }}</td>
                                    </tr>
                                    <tr v-if="totals?.processing_fee && invoice?.processing_fee_label">
                                        <td>
                                            {{ __('message.processing_fee') }} ({{ invoice.processing_fee_label }})
                                        </td>
                                        <td class="text-end tabular-nums">{{ totals.processing_fee }}</td>
                                    </tr>
                                    <tr class="border-top">
                                        <td class="fw-bold">{{ __('message.total') }}</td>
                                        <td class="text-end fw-bold fs-5 tabular-nums">{{ totals?.total }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Payment history -->
                    <template v-if="payments?.length">
                        <h5 class="fw-bold text-dark mb-3 mt-4">{{ __('message.payments_section') }}</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered invoice-grid">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('message.transaction_date') }}</th>
                                        <th>{{ __('message.method') }}</th>
                                        <th>{{ __('message.amount') }}</th>
                                        <th>{{ __('message.status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="pay in payments" :key="pay.id">
                                        <td>{{ formatDate(pay.created_at) }}</td>
                                        <td>{{ capitalize(pay.payment_method) }}</td>
                                        <td class="tabular-nums">{{ pay.amount_formatted }}</td>
                                        <td>
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
import { useDownload } from '@/core/composables/useDownload'

const { formatDate } = useDateTime()

const COMPONENT = 'client-page'
const baseUrl = useBaseUrl()

const route = useRoute()
const invoiceId = route.params.id
const pdfUrl = `${baseUrl}/pdf?invoiceid=${invoiceId}`
const { downloadFile, downloadingUrl } = useDownload(COMPONENT)

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
.invoice-logo { max-height: 70px; max-width: 180px; width: auto; height: auto; }
.tabular-nums { font-variant-numeric: tabular-nums; }
.col-label-width { width: 140px; }
/* Default #dee2e6 borders and light headers wash out against the card. */
.invoice-grid { --bs-table-border-color: #adb5bd; }
.invoice-grid thead th { font-weight: 600; color: #212529; }
.invoice-totals td:first-child { font-weight: 600; }
</style>
