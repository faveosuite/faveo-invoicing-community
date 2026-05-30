<template>
  <div role="main" class="main shop pb-4">
    <div class="container pt-4">
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary"></div>
      </div>

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
                  <th class="product-name text-uppercase">{{ __('message.product') }}</th>
                  <th class="product-quantity text-uppercase">{{ __('message.quantity') }}</th>
                  <th class="product-quantity text-uppercase">{{ __('message.agents') }}</th>
                  <th class="product-subtotal text-uppercase text-end">{{ __('message.total') }}</th>
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

              <div class="d-flex justify-content-between py-3 border-bottom">
                <strong class="text-color-dark text-4">{{ __('message.amount_due') }}</strong>
                <strong class="text-color-dark text-4">{{ symbol }}{{ amountDue }}</strong>
              </div>

              <!-- Gateway pre-selected via URL: show Pay Now button directly -->
              <div v-if="gatewayFromUrl" class="py-3">
                <button class="btn btn-dark btn-modern w-100 text-uppercase text-nowrap border-radius-0 text-3 py-3"
                        :disabled="busy" @click="continuePay">
                  {{ busy ? __('message.please_wait') : `${__('message.pay_now')} ${symbol}${amountDue}` }}
                </button>
              </div>

              <!-- No pre-selected gateway: show gateway selection -->
              <div v-else class="py-3">
                <strong class="d-block text-color-dark text-uppercase mb-3">{{ __('message.payment_methods') }}</strong>
                <p v-if="!gateways.length" class="text-color-grey text-2 mb-0">{{ __('message.no_payment_gateway') }}</p>
                <div v-for="g in gateways" :key="g.name" class="mb-3">
                  <label class="d-flex align-items-center mb-0" style="cursor:pointer">
                    <input type="radio" v-model="selectedGateway" :value="g.name" name="gw" class="me-2" />
                    <img :src="`${baseUrl}/images/logo/${g.name}.png`" :alt="g.name" height="22" @error="logoFallback($event, g.name)" />
                    <span v-if="g.processing_fee" class="text-color-grey text-2 ms-2">(+{{ g.processing_fee }}%)</span>
                  </label>
                </div>
                <button class="btn btn-dark btn-modern w-100 text-uppercase text-nowrap border-radius-0 text-3 py-3 mt-2"
                        :disabled="!selectedGateway || busy" @click="continuePay">
                  {{ busy ? __('message.please_wait') : __('message.place_order_and_pay') }}
                </button>
              </div>

            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Stripe card modal -->
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
          <span class="fw-bold text-color-dark">{{ symbol }}{{ amountDue }}</span>
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
import { ref, reactive, onMounted } from 'vue'
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
const gateways = ref([])
const amountDue = ref(0)
const symbol = ref('')
const stripeKey = ref('')
const selectedGateway = ref(gatewayFromUrl)

const cardErrors = reactive({ number: '', expiry: '', cvc: '' })
const cardComplete = reactive({ number: false, expiry: false, cvc: false })

let stripe = null
let cardNumber = null

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
    amountDue.value = data.data.amount
    symbol.value = data.data.currency_symbol
    gateways.value = data.data.gateways
    stripeKey.value = data.data.stripe_key
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
    // Razorpay needs a server-built order (select-gateway). Stripe is charged
    // directly against the invoice id, so it only needs the publishable key
    // already returned by pay-init — no select-gateway round-trip.
    if (selectedGateway.value.toLowerCase() === 'razorpay') {
      const { data } = await http.post(`${baseUrl}/invoice/${invoiceId}/select-gateway`, { gateway: selectedGateway.value })
      await payRazorpay(data.data.razorpay)
    } else {
      await openStripeModal(stripeKey.value)
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

async function openStripeModal(publishableKey) {
  await loadScript('https://js.stripe.com/v3/')
  stripe = window.Stripe(publishableKey)
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
    const { token, error: tokenError } = await stripe.createToken(cardNumber)
    if (tokenError) {
      alertStore.setAlert({ message: tokenError.message, type: 'danger', component_name: 'stripe-modal' })
      busy.value = false
      return
    }
    const { data } = await http.post(`${baseUrl}/invoice/${invoiceId}/charge-stripe`, { stripeToken: token.id })
    if (data?.success) {
      showStripeModal.value = false
      onPaid()
    } else if (data?.data?.redirectUrl) {
      window.location.href = data.data.redirectUrl
    } else {
      alertStore.setAlert({ message: data?.message ?? __('message.err_msg'), type: 'danger', component_name: 'stripe-modal' })
      busy.value = false
    }
  } catch (e) {
    const redirect = e.response?.data?.data?.redirectUrl
    if (redirect) { window.location.href = redirect; return }
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
  router.push('/invoices')
}
</script>
