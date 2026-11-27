<template>
  <div class="main shop pb-4">
    <div class="container pt-4">
      <!-- Loading -->
      <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

      <!-- Not found -->
      <div v-else-if="!invoice.id" class="text-center py-6">
        <i class="fas fa-file-invoice fa-3x text-color-grey-lighten mb-3 d-block" aria-hidden="true"></i>
        <p class="text-color-grey mb-0">{{ error || __('message.err_msg') }}</p>
      </div>

      <div v-else class="pb-5">
        <div class="row g-4">
          <div class="col-lg-8">
            <!-- Invoice items -->
            <section class="co-card" aria-labelledby="inv-h">
              <div class="co-card-header">
                <h2 id="inv-h" class="co-title">{{ __('message.co_invoice_number', { number: invoice.number }) }}</h2>
                <span class="badge rounded-pill" :class="invoice.status === 'partially paid' ? 'text-bg-warning' : 'text-bg-light border'">
                  {{ invoice.status === 'partially paid' ? __('message.partially_paid') : __('message.unpaid') }}
                </span>
              </div>
              <ul class="list-unstyled mb-0">
                <li v-for="it in items" :key="it.id" class="co-item align-items-center">
                  <span class="co-thumb" aria-hidden="true">
                    <img v-if="it.image" :src="it.image" alt="" width="56" height="56" />
                    <i v-else class="fas fa-box"></i>
                  </span>
                  <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold text-color-dark">{{ it.product_name }}</div>
                    <div v-for="e in (it.extras || [])" :key="e.entitlement_id" class="co-note">{{ e.label }}<template v-if="e.quantity > 1"> × {{ e.quantity }}</template></div>
                    <div class="co-meta">
                      <span v-if="it.quantity > 1">{{ __('message.cart_qty', { count: it.quantity }) }}</span>
                    </div>
                  </div>
                  <span class="co-price">{{ symbol }}{{ it.subtotal }}</span>
                </li>
              </ul>
            </section>

            <!-- Chosen payment method (picked on checkout) -->
            <section v-if="amountDueNumber > 0 && selectedGateway" class="co-card" aria-labelledby="pm-h">
              <div class="co-card-header"><h2 id="pm-h" class="co-title">{{ __('message.co_payment_method') }}</h2></div>
              <div class="co-card-body d-flex align-items-center gap-3">
                <img :src="`${baseUrl}/images/logo/${selectedGateway}.png`" :alt="selectedGateway" height="24" class="gateway-logo" @error="$event.target.replaceWith(selectedGateway)" />
                <span class="flex-grow-1 co-note">{{ gatewayNote(selectedGateway) }}</span>
                <router-link :to="{ path: '/checkout', query: { invoice: invoice.id } }" class="co-link">{{ __('message.co_change') }}</router-link>
              </div>
            </section>
          </div>

          <!-- Summary + pay -->
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
                <div v-if="paidAmountNumber > 0" class="co-row is-discount">
                  <span>{{ __('message.co_already_paid') }}</span>
                  <span class="co-amt">−{{ symbol }}{{ paidAmount }}</span>
                </div>
                <div v-if="creditAppliedNumber > 0" class="co-row is-discount">
                  <span>{{ __('message.co_credit_balance') }}</span>
                  <span class="co-amt">−{{ symbol }}{{ creditApplied }}</span>
                </div>
                <div v-if="toNumber(feeAmount) > 0" class="co-row">
                  <span>{{ __('message.processing_fee') }}<template v-if="feePercent"> ({{ feePercent }}%)</template></span>
                  <span class="co-amt">{{ symbol }}{{ feeAmount }}</span>
                </div>
                <div class="co-row is-total">
                  <span>{{ __('message.co_total_payable') }}</span>
                  <span class="co-amt">{{ symbol }}{{ payable }}</span>
                </div>
                <button type="button" class="btn btn-primary btn-modern w-100 text-uppercase py-3 mt-3"
                        :disabled="busy || (amountDueNumber > 0 && !selectedGateway)" @click="continuePay">
                  <span v-if="busy" class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
                  <i v-else class="fas fa-lock me-2" aria-hidden="true"></i>{{ busy ? __('message.please_wait') : __('message.pay_now') }}
                </button>
                <p v-if="amountDueNumber > 0 && selectedGateway" class="co-note text-center mt-3 mb-0">{{ __('message.co_secure_note', { gateway: selectedGateway }) }}</p>
              </div>
            </aside>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Stripe card modal: card fields are Stripe iframes, so card data never touches our server -->
  <AppModal :showModal="showStripeModal" :onClose="onStripeModalClose" :showCloseBtn="false" classname="modal-dialog-centered co-card-modal" footerClass="gap-2">
    <template #title>
      <h2 class="modal-title h5 mb-0">{{ __('message.co_pay_by_card') }}</h2>
      <p class="co-note mb-0">{{ __('message.co_invoice_number', { number: invoice.number }) }}</p>
    </template>

    <template #fields>
      <AppAlert componentName="stripe-modal" />

      <div class="d-flex align-items-center justify-content-between mb-1">
        <label id="card-number-label" for="card-number" class="form-label fw-semibold mb-0" @click="focusCard('number')">{{ __('message.card_number') }}</label>
        <span class="d-flex gap-1">
          <img :src="`${baseUrl}/images/logo/cards/visa.svg`" alt="Visa" height="20" width="30">
          <img :src="`${baseUrl}/images/logo/cards/mastercard.svg`" alt="Mastercard" height="20" width="30">
          <img :src="`${baseUrl}/images/logo/cards/amex.svg`" alt="American Express" height="20" width="30">
        </span>
      </div>
      <div id="card-number" class="form-control h-auto py-3" :class="{ 'is-invalid': cardErrors.number }"></div>
      <div class="invalid-feedback d-block" aria-live="polite">{{ cardErrors.number }}</div>

      <div class="row g-3 mt-0">
        <div class="col-6">
          <label id="card-expiry-label" for="card-expiry" class="form-label fw-semibold mb-1" @click="focusCard('expiry')">{{ __('message.expiry_date') }}</label>
          <div id="card-expiry" class="form-control h-auto py-3" :class="{ 'is-invalid': cardErrors.expiry }"></div>
          <div class="invalid-feedback d-block" aria-live="polite">{{ cardErrors.expiry }}</div>
        </div>
        <div class="col-6">
          <label id="card-cvc-label" for="card-cvc" class="form-label fw-semibold mb-1" @click="focusCard('cvc')">{{ __('message.cvc') }}</label>
          <div id="card-cvc" class="form-control h-auto py-3" :class="{ 'is-invalid': cardErrors.cvc }"></div>
          <div class="invalid-feedback d-block" aria-live="polite">{{ cardErrors.cvc }}</div>
        </div>
      </div>
      <p class="co-note mt-3 mb-0"><i class="fas fa-lock me-1" aria-hidden="true"></i>{{ __('message.co_card_note') }}</p>
    </template>

    <template #controls>
      <button type="button" class="btn btn-light btn-modern text-uppercase" :disabled="busy" @click="onStripeModalClose">{{ __('message.cancel') }}</button>
      <button type="button" class="btn btn-primary btn-modern text-uppercase" :disabled="busy" @click="payStripe">
        <span v-if="busy" class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
        {{ busy ? __('message.co_processing') : __('message.co_pay_amount', { amount: symbol + payable }) }}
      </button>
    </template>
  </AppModal>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAlertStore } from '@/core/stores/alert'
import { useLoaderStore } from '@/core/stores/loader'
import { useCartStore } from '@/core/stores/cart'
import http, { parseErrorMessage } from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { useBaseUrl } from '@/core/composables/useBaseUrl'

const route = useRoute()
const baseUrl = useBaseUrl()
const router = useRouter()
// Reactive (not a one-time snapshot) because the SPA reuses this component
// instance when navigating from one invoice's pay page straight to another's
// — without that, the page would keep showing the first invoice forever.
const invoiceId = computed(() => route.query.invoice)

const alertStore = useAlertStore()
const loaderStore = useLoaderStore()
const cartStore = useCartStore()

const loading = ref(true)
const busy = ref(false)
const error = ref('')
const showStripeModal = ref(false)

const invoice = ref({})
const items = ref([])
const summary = ref({})
const gateways = ref([])
const amountDue = ref(0)
const paidAmount = ref(0)
// Whether to use credit was already decided on the checkout page — this page
// only carries that choice through, it doesn't offer the toggle itself.
const useCredit = ref(route.query.use_credit === '1')
// credit_applied is what the server actually used, for display only.
const creditApplied = ref(0)
const symbol = ref('')
const selectedGateway = ref(route.query.gateway ?? null)

// Money fields from the backend are currency-formatted for display (e.g. "3,000.00")
// — strip thousands separators before treating them as numbers.
function toNumber(v) {
  return parseFloat(String(v).replace(/,/g, '')) || 0
}

const paidAmountNumber = computed(() => toNumber(paidAmount.value))
const creditAppliedNumber = computed(() => toNumber(creditApplied.value))
const amountDueNumber = computed(() => toNumber(amountDue.value))

// Processing fee + payable total for the chosen gateway (from payInit) — already
// computed server-side net of any auto-applied credit.
const selectedGatewayData = computed(() =>
  gateways.value.find(g => (g.name || '').toLowerCase() === (selectedGateway.value || '').toLowerCase()) ?? null
)
const feeAmount = computed(() => selectedGatewayData.value?.fee_amount ?? 0)
const feePercent = computed(() => selectedGatewayData.value?.processing_fee ?? 0)
const payable = computed(() => selectedGatewayData.value?.payable ?? amountDue.value)

// One line next to the gateway logo.
const GATEWAY_NOTES = { stripe: 'message.co_gateway_stripe', razorpay: 'message.co_gateway_razorpay' }
const gatewayNote = name => (GATEWAY_NOTES[name.toLowerCase()] ? __(GATEWAY_NOTES[name.toLowerCase()]) : '')

const cardErrors = reactive({ number: '', expiry: '', cvc: '' })
const cardComplete = reactive({ number: false, expiry: false, cvc: false })

let stripe = null
let cardNumber = null
let cardFields = {}
let clientSecret = null
let paymentIntentId = null


function loadScript(src) {
  return new Promise((resolve, reject) => {
    if (document.querySelector(`script[src="${src}"]`)) return resolve()
    const s = document.createElement('script')
    s.src = src
    s.onload = resolve
    s.onerror = () => reject(new Error('Failed to load ' + src))
    document.head.appendChild(s)
  })
}

async function refreshPayInit() {
  const { data } = await http.get(`/invoice/${invoiceId.value}/pay-init`, {
    params: { use_credit: useCredit.value ? 1 : 0 },
  })
  invoice.value = data.data.invoice
  items.value = data.data.items
  summary.value = data.data.summary ?? {}
  amountDue.value = data.data.amount
  paidAmount.value = data.data.paid
  creditApplied.value = data.data.credit_applied
  symbol.value = data.data.currency_symbol
  gateways.value = data.data.gateways
  if (!selectedGateway.value && gateways.value.length) selectedGateway.value = gateways.value[0].name
}

// Re-run whenever the invoice id changes — including switching from one
// invoice's pay page straight to another's without a full page reload.
watch(invoiceId, async (id) => {
  selectedGateway.value = route.query.gateway ?? null
  useCredit.value = route.query.use_credit === '1'
  clientSecret = null
  paymentIntentId = null
  if (!id) { loading.value = false; error.value = __('message.err_msg'); return }
  loading.value = true
  error.value = ''
  try {
    await refreshPayInit()
  } catch (e) {
    error.value = parseErrorMessage(e)
  } finally {
    loading.value = false
  }
}, { immediate: true })

async function continuePay() {
  if (busy.value) return
  busy.value = true
  alertStore.unsetAlert()
  try {
    // Credit alone covers it (server already reflects this in amountDue): pay
    // it off directly, no gateway involved at all.
    if (amountDueNumber.value <= 0) {
      const { data } = await http.post(`/invoice/${invoiceId.value}/apply-credit`)
      if (data.data?.paid_in_full) { onPaid(); return }
      await refreshPayInit()
      return
    }

    if (!selectedGateway.value) return
    if (selectedGateway.value.toLowerCase() === 'razorpay') {
      const { data } = await http.post(`/invoice/${invoiceId.value}/razorpay/order`, { use_credit: useCredit.value })
      await payRazorpay(data.data.razorpay)
    } else {
      await openStripeModal()
    }
  } catch (e) {
    alertStore.setAlert({ message: parseErrorMessage(e), type: 'danger', component_name: 'client-page' })
  } finally {
    busy.value = false
  }
}

function bindStripeField(element, key) {
  element.on('change', (e) => {
    cardErrors[key] = e.error ? e.error.message : ''
    cardComplete[key] = e.complete
  })
}

// Create a PaymentIntent for the invoice, then mount our own card fields. The
// card data is confirmed straight to Stripe (confirmCardPayment) — never our server.
async function openStripeModal() {
  const { data } = await http.post(`/invoice/${invoiceId.value}/stripe/session`, { use_credit: useCredit.value })
  clientSecret = data.data.client_secret
  paymentIntentId = data.data.payment_intent_id

  // An earlier (idempotent) attempt may have already completed this intent —
  // don't show the card form again, just verify + fulfil server-side.
  if (data.data.status === 'succeeded') {
    await finalizeStripe()
    return
  }

  await loadScript('https://js.stripe.com/v3/')
  stripe = globalThis.Stripe(data.data.publishable_key)
  const elements = stripe.elements()

  // Reset state from any previous open
  Object.assign(cardErrors, { number: '', expiry: '', cvc: '' })
  Object.assign(cardComplete, { number: false, expiry: false, cvc: false })

  showStripeModal.value = true

  // Wait one tick for teleported DOM to render
  await new Promise(r => setTimeout(r, 100))

  const stripeStyle = {
    base: {
      fontSize: '15px',
      color: '#32325d',
      fontFamily: 'inherit',
      '::placeholder': { color: '#aab7c4' },
    },
    invalid: { color: '#dc3545' },
  }

  cardNumber = elements.create('cardNumber', { showIcon: true, style: stripeStyle })
  const cardExpiry = elements.create('cardExpiry', { style: stripeStyle })
  const cardCvc = elements.create('cardCvc', { style: stripeStyle })

  cardNumber.mount('#card-number')
  cardExpiry.mount('#card-expiry')
  cardCvc.mount('#card-cvc')

  cardFields = { number: cardNumber, expiry: cardExpiry, cvc: cardCvc }
  bindStripeField(cardNumber, 'number')
  bindStripeField(cardExpiry, 'expiry')
  bindStripeField(cardCvc, 'cvc')
}

// Stripe fields are iframes, so a <label for> can't target them — focus the element instead.
function focusCard(key) {
  cardFields[key]?.focus()
}

function onStripeModalClose() {
  showStripeModal.value = false
  busy.value = false
  alertStore.unsetAlert()
}

async function payStripe() {
  if (busy.value) return

  // Trigger validation on any untouched empty fields
  if (!cardComplete.number) cardErrors.number = cardErrors.number || __('message.card_number_required')
  if (!cardComplete.expiry) cardErrors.expiry = cardErrors.expiry || __('message.expiry_required')
  if (!cardComplete.cvc) cardErrors.cvc = cardErrors.cvc || __('message.cvc_required')
  const firstInvalid = ['number', 'expiry', 'cvc'].find(k => !cardComplete[k])
  if (firstInvalid) { focusCard(firstInvalid); return }

  busy.value = true
  alertStore.unsetAlert()
  try {
    // Confirm the card against the PaymentIntent. Stripe handles 3D Secure here.
    const { paymentIntent, error: confirmError } = await stripe.confirmCardPayment(clientSecret, {
      payment_method: { card: cardNumber },
    })

    if (confirmError) {
      // The intent was already completed by a previous attempt — don't show an
      // error, just verify + fulfil (the server confirm is idempotent).
      if (confirmError.code === 'payment_intent_unexpected_state' || confirmError.payment_intent?.status === 'succeeded') {
        await finalizeStripe()
        return
      }
      alertStore.setAlert({ message: confirmError.message, type: 'danger', component_name: 'stripe-modal' })
      busy.value = false
      return
    }

    if (paymentIntent && paymentIntent.status === 'succeeded') {
      await finalizeStripe()
    } else {
      alertStore.setAlert({ message: __('message.err_msg'), type: 'danger', component_name: 'stripe-modal' })
      busy.value = false
    }
  } catch (e) {
    alertStore.setAlert({ message: parseErrorMessage(e), type: 'danger', component_name: 'stripe-modal' })
    busy.value = false
  }
}

// Authoritatively verify the PaymentIntent + fulfil the invoice server-side.
// Idempotent: safe to call for an intent a prior attempt already completed.
async function finalizeStripe() {
  loaderStore.startLoader('pay-verify')
  try {
    const { data } = await http.post(`/invoice/${invoiceId.value}/stripe/confirm`, { payment_intent: paymentIntentId })
    if (data?.success) {
      showStripeModal.value = false
      onPaid()
    } else {
      alertStore.setAlert({ message: data?.message ?? __('message.err_msg'), type: 'danger', component_name: 'stripe-modal' })
      busy.value = false
    }
  } catch (e) {
    alertStore.setAlert({ message: parseErrorMessage(e), type: 'danger', component_name: 'stripe-modal' })
    busy.value = false
  } finally {
    loaderStore.stopLoader('pay-verify')
  }
}

async function payRazorpay(config) {
  await loadScript('https://checkout.razorpay.com/v1/checkout.js')
  const options = { ...config }
  options.handler = async (response) => {
    loaderStore.startLoader('pay-verify')
    try {
      const { data } = await http.post(`/invoice/${invoiceId.value}/razorpay/confirm`, {
        razorpay_payment_id: response.razorpay_payment_id,
        razorpay_order_id: response.razorpay_order_id,
        razorpay_signature: response.razorpay_signature,
      })
      if (data?.success) {
        onPaid()
      } else {
        alertStore.setAlert({ message: data?.message ?? __('message.err_msg'), type: 'danger', component_name: 'client-page' })
      }
    } catch (e) {
      alertStore.setAlert({ message: parseErrorMessage(e), type: 'danger', component_name: 'client-page' })
    } finally {
      loaderStore.stopLoader('pay-verify')
    }
  }
  options.modal = { ondismiss: () => { busy.value = false } }
  const rzp = new globalThis.Razorpay(options)
  rzp.open()
}

function onPaid() {
  // The server emptied the cart on payment, but this is an in-page navigation — reload it
  // so the navbar count doesn't keep showing what was just bought until a refresh.
  cartStore.fetchCart()
  router.push({ path: '/payment-success', query: { invoice: invoiceId.value } })
}
</script>

<style scoped>
.min-w-0 { min-width: 0; }
.gateway-logo { width: auto; }
</style>

<style>
.modal-dialog.co-card-modal { max-width: 460px; }
</style>
