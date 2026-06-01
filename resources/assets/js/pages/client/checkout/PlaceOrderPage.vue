<template>
  <div role="main" class="main shop pb-4">
    <div class="container pt-4">
      <!-- Loading -->
      <inline-loader v-if="loading" />

      <!-- Not found -->
      <div v-else-if="!invoice.id" class="text-center py-6">
        <i class="fas fa-file-invoice fa-3x text-color-grey-lighten mb-3 d-block"></i>
        <p class="text-color-grey mb-0">{{ error || __('message.err_msg') }}</p>
      </div>

      <div v-else class="row pb-4 mb-5">

        <!-- Items -->
        <div class="col-lg-7 mb-4 mb-lg-0">
          <div class="table-responsive">
            <table class="shop_table cart">
              <thead>
                <tr class="text-color-dark">
                  <th class="product-thumbnail" width="15%">&nbsp;</th>
                  <th class="product-name text-uppercase" width="35%">{{ __('message.product') }}</th>
                  <th class="product-quantity text-uppercase" width="15%">{{ __('message.quantity') }}</th>
                  <th class="product-quantity text-uppercase" width="20%">{{ __('message.agents') }}</th>
                  <th class="product-subtotal text-uppercase text-end" width="15%">{{ __('message.total') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="it in items" :key="it.id" class="cart_table_item">
                  <td class="product-thumbnail">
                    <span class="product-thumbnail-image d-inline-block">
                      <img v-if="it.image" :src="it.image" width="90" height="90" alt="" class="img-fluid" />
                      <span v-else class="d-inline-flex align-items-center justify-content-center bg-light" style="width:90px;height:90px;">
                        <i class="fas fa-box text-color-grey fa-2x"></i>
                      </span>
                    </span>
                  </td>
                  <td class="product-name"><span class="font-weight-semi-bold text-color-dark">{{ it.product_name }}</span></td>
                  <td class="product-quantity"><span class="text-color-dark">{{ it.quantity }}</span></td>
                  <td class="product-quantity"><span class="text-color-grey">{{ it.agents ? it.agents : __('message.unlimited_agents') }}</span></td>
                  <td class="product-subtotal text-end">
                    <span class="amount text-color-dark font-weight-bold text-4">{{ symbol }}{{ it.subtotal }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Order summary + payment -->
        <div class="col-lg-5 position-relative">
          <div class="card border-width-3 border-radius-0">
            <div class="card-body">
              <h4 class="font-weight-bold text-uppercase text-4 mb-0 pb-3 border-bottom">{{ __('message.your_order') }}</h4>

              <div v-if="invoice.number" class="d-flex justify-content-between py-3 border-bottom">
                <strong class="text-color-dark">{{ __('message.invoice') }}</strong>
                <span class="font-weight-medium">#{{ invoice.number }}</span>
              </div>

              <div v-if="summary.subtotal_ex_tax !== undefined" class="d-flex justify-content-between py-3 border-bottom">
                <strong class="text-color-dark">
                  {{ __('message.sub_total') }}
                  <small v-if="summary.tax_total > 0 && summary.tax_label" class="text-color-grey fw-normal">(ex. {{ summary.tax_label }})</small>
                </strong>
                <span class="amount font-weight-medium">{{ symbol }}{{ summary.subtotal_ex_tax }}</span>
              </div>

              <div v-for="(t, i) in (summary.taxes || [])" :key="i" class="d-flex justify-content-between py-3 border-bottom">
                <strong class="text-color-dark">{{ t.label }}<span v-if="t.rate" class="text-color-grey fw-normal"> ({{ t.rate }}%)</span></strong>
                <span class="amount font-weight-medium">{{ symbol }}{{ t.amount }}</span>
              </div>

              <div v-if="feeAmount > 0" class="d-flex justify-content-between py-3 border-bottom">
                <strong class="text-color-dark">{{ __('message.processing_fee') }}<span v-if="feePercent" class="text-color-grey fw-normal"> ({{ feePercent }}%)</span></strong>
                <span class="amount font-weight-medium">{{ symbol }}{{ feeAmount }}</span>
              </div>

              <div class="d-flex justify-content-between py-3 border-bottom">
                <strong class="text-color-dark text-4">{{ __('message.total') }}</strong>
                <strong class="text-color-dark text-4">{{ symbol }}{{ payable }}</strong>
              </div>

              <div class="py-3">
                <button type="button"
                        class="btn btn-dark btn-modern w-100 text-uppercase border-radius-0 text-3 py-3"
                        :disabled="!selectedGateway || busy"
                        @click="continuePay">
                  {{ busy ? __('message.please_wait') : __('message.pay_now') }}
                  <i v-if="!busy" class="fas fa-arrow-right ms-2"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Stripe card modal (custom card fields) -->
  <AppModal
    :showModal="showStripeModal"
    :onClose="onStripeModalClose"
    :showCloseBtn="true"
    :showControls="false"
    classname="modal-md"
  >
    <template #title>
      <div class="d-flex align-items-center justify-content-between w-100 me-3">
        <span class="fw-bold fs-5">{{ __('message.enter_card_details') }}</span>
      </div>
    </template>

    <template #fields>
      <div class="px-2 pb-3">
        <AppAlert componentName="stripe-modal" />

        <!-- Card Number -->
        <div class="mb-4">
          <label class="form-label text-color-grey mb-1">{{ __('message.card_number') }}</label>
          <div id="card-number" class="form-control h-auto py-3" :class="{ 'is-invalid': cardErrors.number }"></div>
          <div v-if="cardErrors.number" class="invalid-feedback d-block">{{ cardErrors.number }}</div>
        </div>

        <!-- Expiry + CVC -->
        <div class="row g-3 mb-4">
          <div class="col-6">
            <label class="form-label text-color-grey mb-1">{{ __('message.expiry_date') }}</label>
            <div id="card-expiry" class="form-control h-auto py-3" :class="{ 'is-invalid': cardErrors.expiry }"></div>
            <div v-if="cardErrors.expiry" class="invalid-feedback d-block">{{ cardErrors.expiry }}</div>
          </div>
          <div class="col-6">
            <label class="form-label text-color-grey mb-1">CVC</label>
            <div id="card-cvc" class="form-control h-auto py-3" :class="{ 'is-invalid': cardErrors.cvc }"></div>
            <div v-if="cardErrors.cvc" class="invalid-feedback d-block">{{ cardErrors.cvc }}</div>
          </div>
        </div>

        <!-- Total row -->
        <div class="d-flex justify-content-between align-items-center border rounded px-2 py-2 mb-3">
          <span class="fw-bold text-color-dark">{{ __('message.total') }}</span>
          <span class="fw-bold text-color-dark">{{ symbol }}{{ payable }}</span>
        </div>

        <!-- Pay Now button -->
        <button class="btn btn-primary w-100 py-2 fw-bold text-uppercase"
                :disabled="busy" @click="payStripe">
          {{ busy ? __('message.please_wait') : __('message.pay_now') }}
        </button>

      </div>
    </template>
  </AppModal>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAlertStore } from '@/core/stores/alert'
import http, { parseErrorMessage } from '@/plugins/axios'
import { __ } from '@/plugins/i18n'

const el = document.getElementById('app-client')
const baseUrl = el?.dataset?.baseUrl ?? ''

const route = useRoute()
const router = useRouter()
const invoiceId = route.query.invoice
const gatewayFromUrl = route.query.gateway ?? null

const alertStore = useAlertStore()

const loading = ref(true)
const busy = ref(false)
const error = ref('')
const showStripeModal = ref(false)

const invoice = ref({})
const items = ref([])
const summary = ref({})
const gateways = ref([])
const amountDue = ref(0)
const symbol = ref('')
const selectedGateway = ref(gatewayFromUrl)

// Processing fee + payable total for the chosen gateway (from payInit).
const selectedGatewayData = computed(() =>
  gateways.value.find(g => (g.name || '').toLowerCase() === (selectedGateway.value || '').toLowerCase()) ?? null
)
const feeAmount = computed(() => selectedGatewayData.value?.fee_amount ?? 0)
const feePercent = computed(() => selectedGatewayData.value?.processing_fee ?? 0)
const payable = computed(() => selectedGatewayData.value?.payable ?? amountDue.value)

const cardErrors = reactive({ number: '', expiry: '', cvc: '' })
const cardComplete = reactive({ number: false, expiry: false, cvc: false })

let stripe = null
let cardNumber = null
let clientSecret = null
let paymentIntentId = null

function logoFallback(event, name) {
  const span = document.createElement('span')
  span.className = 'font-weight-bold text-color-dark'
  span.textContent = name
  event.target.replaceWith(span)
}

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

onMounted(async () => {
  if (!invoiceId) { loading.value = false; error.value = __('message.err_msg'); return }
  try {
    const { data } = await http.get(`${baseUrl}/invoice/${invoiceId}/pay-init`)
    invoice.value = data.data.invoice
    items.value = data.data.items
    summary.value = data.data.summary ?? {}
    amountDue.value = data.data.amount
    symbol.value = data.data.currency_symbol
    gateways.value = data.data.gateways
    if (!selectedGateway.value && gateways.value.length) selectedGateway.value = gateways.value[0].name
  } catch (e) {
    error.value = parseErrorMessage(e)
  } finally {
    loading.value = false
  }
})

async function continuePay() {
  if (!selectedGateway.value || busy.value) return
  busy.value = true
  alertStore.unsetAlert()
  try {
    if (selectedGateway.value.toLowerCase() === 'razorpay') {
      const { data } = await http.post(`${baseUrl}/invoice/${invoiceId}/razorpay/order`)
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
  const { data } = await http.post(`${baseUrl}/invoice/${invoiceId}/stripe/session`)
  clientSecret = data.data.client_secret
  paymentIntentId = data.data.payment_intent_id

  // An earlier (idempotent) attempt may have already completed this intent —
  // don't show the card form again, just verify + fulfil server-side.
  if (data.data.status === 'succeeded') {
    await finalizeStripe()
    return
  }

  await loadScript('https://js.stripe.com/v3/')
  stripe = window.Stripe(data.data.publishable_key)
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

  bindStripeField(cardNumber, 'number')
  bindStripeField(cardExpiry, 'expiry')
  bindStripeField(cardCvc, 'cvc')
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
  if (!cardComplete.number || !cardComplete.expiry || !cardComplete.cvc) return

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
  try {
    const { data } = await http.post(`${baseUrl}/invoice/${invoiceId}/stripe/confirm`, { payment_intent: paymentIntentId })
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
  }
}

async function payRazorpay(config) {
  await loadScript('https://checkout.razorpay.com/v1/checkout.js')
  const options = { ...config }
  options.handler = async (response) => {
    try {
      await http.post(`${baseUrl}/payment/${invoiceId}`, {
        razorpay_payment_id: response.razorpay_payment_id,
        razorpay_order_id: response.razorpay_order_id,
        razorpay_signature: response.razorpay_signature,
      })
      onPaid()
    } catch (e) {
      alertStore.setAlert({ message: parseErrorMessage(e), type: 'danger', component_name: 'client-page' })
    }
  }
  options.modal = { ondismiss: () => { busy.value = false } }
  const rzp = new window.Razorpay(options)
  rzp.open()
}

function onPaid() {
  router.push({ path: '/payment-success', query: { invoice: invoiceId } })
}
</script>
