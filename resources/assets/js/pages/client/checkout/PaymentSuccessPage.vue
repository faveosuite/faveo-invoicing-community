<template>
  <div class="main shop pb-4">
    <div class="container pt-4 pb-5">
      <div v-if="loading" class="row justify-content-center py-3"><loader /></div>
      <div v-else-if="error" class="text-center py-6">
        <i class="fas fa-triangle-exclamation fa-3x text-color-grey-lighten mb-3 d-block" aria-hidden="true"></i>
        <p class="text-color-grey mb-0">{{ error }}</p>
      </div>

      <template v-else>
        <!-- Confirmation. The page title bar already says "Payment Successful", so no second heading here. -->
        <div class="ok-hero text-center">
          <!-- Confetti + tick, plays 3 times then holds on the tick (LottieFiles "success confetti", Lottie Simple License) -->
          <img class="ok-anim" :src="`${baseUrl}/images/success-confetti.svg`" alt="" width="200" height="200" />
          <!-- Still tick for people who turned animations off -->
          <span class="ok-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="34" height="34" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5" /></svg>
          </span>
          <p class="ok-message mb-0" role="status">{{ __('message.co_payment_confirmed') }}</p>
          <span class="ok-pill"><span class="ok-dot" aria-hidden="true"></span>{{ __('message.co_payment_completed') }}</span>
        </div>

        <Alert componentName="payment-success-download" />

        <div class="row g-4">
          <div class="col-lg-8">
            <!-- Key facts -->
            <section class="co-card" aria-labelledby="info-h">
              <div class="co-card-header"><h2 id="info-h" class="co-title">{{ __('message.co_order_information') }}</h2></div>
              <dl class="facts mb-0">
                <div>
                  <dt class="co-note fw-normal">{{ __('message.invoice_number') }}</dt>
                  <dd class="fw-semibold mb-0 d-flex align-items-center gap-2">
                    <router-link :to="`/my-invoice/${invoice.id}`" class="co-link">{{ invoice.number }}</router-link>
                    <button type="button" class="btn btn-sm border-0 p-0 text-color-grey" :aria-label="copied ? __('message.copied') : __('message.copy')"
                            v-tooltip="copied ? __('message.copied') : __('message.copy')" @click="copyNumber">
                      <i :class="copied ? 'fas fa-check text-success' : 'far fa-copy'" aria-hidden="true"></i>
                    </button>
                  </dd>
                </div>
                <div><dt class="co-note fw-normal">{{ __('message.date') }}</dt><dd class="fw-semibold text-color-dark mb-0">{{ formatDate(invoice.date) }}</dd></div>
                <div v-if="paymentMethod"><dt class="co-note fw-normal">{{ __('message.payment-method') }}</dt><dd class="fw-semibold text-color-dark mb-0">{{ paymentMethod }}</dd></div>
              </dl>
            </section>

            <!-- What was bought -->
            <section class="co-card" aria-labelledby="ord-h">
              <div class="co-card-header"><h2 id="ord-h" class="co-title">{{ __('message.co_purchased_products', { count: orders.length }) }}</h2></div>
              <ul class="list-unstyled mb-0">
                <li v-for="o in orders" :key="o.number" class="co-item align-items-center flex-wrap">
                  <span class="co-thumb" aria-hidden="true">
                    <img v-if="o.image" :src="o.image" alt="" width="56" height="56" />
                    <i v-else class="fas fa-box"></i>
                  </span>
                  <div class="flex-grow-1 min-w-0 item-name">
                    <div class="fw-semibold text-color-dark">{{ o.product_name }}<template v-if="o.qty > 1"> × {{ o.qty }}</template></div>
                    <div class="co-note">{{ __('message.order_number') }}: <router-link :to="`/my-order/${o.id}`" class="co-link">{{ o.number }}</router-link></div>
                  </div>
                  <!-- Price + download: beside the name, or on their own line on phones -->
                  <div class="d-flex align-items-center gap-3 ms-auto item-actions">
                  <span class="co-price">{{ toNumber(o.price) > 0 ? symbol + o.price : __('message.free') }}</span>
                  <button v-if="o.downloadable" type="button" class="btn btn-outline-primary btn-sm text-nowrap"
                          :disabled="downloadingUrl === o.download_url"
                          @click="downloadFile(o.download_url, o.product_name)">
                    <i :class="downloadingUrl === o.download_url ? 'fas fa-circle-notch fa-spin' : 'fa fa-download'" class="me-1" aria-hidden="true"></i>{{ __('message.download') }}
                    <span class="visually-hidden"> {{ o.product_name }}</span>
                  </button>
                  </div>
                </li>
              </ul>
            </section>
          </div>

          <!-- Summary + invoice -->
          <div class="col-lg-4">
            <aside class="co-card co-summary" aria-labelledby="sum-h">
              <div class="co-card-header"><h2 id="sum-h" class="co-title">{{ __('message.cart_totals') }}</h2></div>
              <div class="co-card-body">
                <div v-if="summary.subtotal_ex_tax !== undefined" class="co-row">
                  <span>{{ __('message.subtotal') }} <span v-if="toNumber(summary.tax_total) > 0 && summary.tax_label" class="co-note">(ex. {{ summary.tax_label }})</span></span>
                  <span class="co-amt">{{ symbol }}{{ summary.subtotal_ex_tax }}</span>
                </div>
                <div v-if="toNumber(summary.discount) > 0" class="co-row is-discount">
                  <span>{{ summary.coupon_code ? __('message.coupon') + ':' : __('message.discount') }} <strong v-if="summary.coupon_code">{{ summary.coupon_code }}</strong></span>
                  <span class="co-amt">−{{ symbol }}{{ summary.discount }}</span>
                </div>
                <div v-for="(t, i) in (summary.taxes || [])" :key="i" class="co-row">
                  <span>{{ t.rate ? `${t.label} ${t.rate}%` : t.label }}</span>
                  <span class="co-amt">{{ symbol }}{{ t.amount }}</span>
                </div>
                <div v-if="summary.processing_fee_rate" class="co-row">
                  <span class="d-inline-flex align-items-center gap-1">
                    {{ __('message.processing_fee') }} ({{ summary.processing_fee_rate }})
                    <button type="button" class="btn btn-sm border-0 p-0 text-color-grey" :aria-label="__('message.co_fee_info', { gateway: paymentMethod })"
                            v-tooltip="__('message.co_fee_info', { gateway: paymentMethod })">
                      <i class="fas fa-info-circle" aria-hidden="true"></i>
                    </button>
                  </span>
                  <span class="co-amt">{{ symbol }}{{ summary.processing_fee }}</span>
                </div>
                <div class="co-row is-total">
                  <span>{{ __('message.total') }}</span>
                  <span class="co-amt">{{ symbol }}{{ invoice.grand_total }}</span>
                </div>
                <button type="button" class="btn btn-primary btn-modern w-100 text-uppercase py-3 mt-3" :disabled="downloadingUrl === pdfUrl"
                        @click="downloadFile(pdfUrl, `${__('message.invoice')} #${invoice.number}`)">
                  <i :class="downloadingUrl === pdfUrl ? 'fas fa-spinner fa-spin' : 'fas fa-file-download'" class="me-2" aria-hidden="true"></i>{{ __('message.co_download_invoice') }}
                </button>
                <div class="d-flex justify-content-center gap-4 mt-3">
                  <router-link to="/my-orders" class="co-link">{{ __('message.my_orders') }}</router-link>
                  <router-link to="/my-invoices" class="co-link">{{ __('message.my_invoices') }}</router-link>
                </div>
              </div>
            </aside>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import http, { parseErrorMessage } from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { useDateTime } from '@/core/composables/useDateTime'
import { useDownload } from '@/core/composables/useDownload'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import Alert from '@/components/Reusable/Alert.vue'

const { formatDate } = useDateTime()
const { downloadFile, downloadingUrl } = useDownload('payment-success-download')

const route = useRoute()
const invoiceId = route.query.invoice
const baseUrl = useBaseUrl()
const pdfUrl = `${baseUrl}/pdf?invoiceid=${invoiceId}`

const loading = ref(true)
const error = ref('')

const invoice = ref({})
const orders = ref([])
const paymentMethod = ref('')
const symbol = ref('')
const summary = ref({})

// Money comes formatted ("1,000.00"): strip separators before comparing.
const toNumber = v => parseFloat(String(v).replace(/,/g, '')) || 0

// Copy the invoice number (for support emails, accounts); the icon turns into a tick for 2s.
const copied = ref(false)
async function copyNumber() {
  try {
    await navigator.clipboard.writeText(String(invoice.value.number))
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
  } catch {
    // Clipboard blocked (insecure origin / permissions): nothing to copy into, leave the icon as is.
  }
}

onMounted(async () => {
  if (!invoiceId) { loading.value = false; error.value = __('message.err_msg'); return }
  try {
    const { data } = await http.get(`/invoice/${invoiceId}/pay-success`)
    invoice.value = data.data.invoice
    orders.value = data.data.orders
    paymentMethod.value = data.data.payment_method
    symbol.value = data.data.invoice.currency_symbol
    summary.value = data.data.summary ?? {}
  } catch (e) {
    error.value = parseErrorMessage(e)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.min-w-0 { min-width: 0; }
/* The SVG has empty space around its circle: pull the neighbours in so the gaps look even */
.ok-anim { display: block; margin: -2.5rem auto -3.75rem; }
.ok-icon { display: none; width: 72px; height: 72px; border-radius: 50%; align-items: center; justify-content: center; background: #d1e7dd; color: #0f5132; box-shadow: 0 0 0 10px rgba(25, 135, 84, .08); }
@media (prefers-reduced-motion: reduce) {
  .ok-anim { display: none; }
  .ok-icon { display: inline-flex; }
}
.ok-hero { margin-bottom: 2.5rem; }
.ok-message { margin-top: 1.25rem; font-size: 1.125rem; font-weight: 500; line-height: 1.5; color: var(--dark); }
.ok-pill { display: inline-flex; align-items: center; gap: .5rem; margin-top: 1rem; padding: .4rem 1rem; border-radius: 999px; background: #e3f3ea; color: #0f5132; font-size: .875rem; font-weight: 600; }
.ok-dot { width: 8px; height: 8px; border-radius: 50%; background: #198754; }
@media (max-width: 575px) {
  .item-name { flex-basis: calc(100% - 72px); }
  .item-actions { flex-basis: 100%; justify-content: space-between; padding-left: 72px; }
}
.facts { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; padding: 1.25rem; }
</style>
