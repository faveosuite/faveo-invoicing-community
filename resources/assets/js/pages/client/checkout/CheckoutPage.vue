<template>
  <div role="main" class="main shop pb-4">
    <div class="container pt-4">
      <!-- Loading -->
      <div v-if="loading && !displayItems.length" class="row justify-content-center py-3"><loader /></div>

      <!-- Empty / not found -->
      <div v-else-if="isEmpty" class="d-flex flex-column align-items-center justify-content-center text-center py-6">
        <template v-if="mode === 'invoice'">
          <i class="fas fa-file-invoice fa-3x text-color-grey-lighten mb-3 d-block"></i>
          <p class="text-color-grey mb-0">{{ invError || __('message.err_msg') }}</p>
        </template>
        <template v-else>
          <i class="fas fa-shopping-cart fa-3x text-color-grey-lighten mb-3 d-block"></i>
          <p class="text-color-grey mb-4">{{ __('message.cart_empty') }}</p>
          <a :href="`${baseUrl}/store`" class="btn btn-dark btn-modern text-uppercase border-radius-0 btn-px-4 py-3">
            {{ __('message.browse_products') }}
          </a>
        </template>
      </div>

      <!-- Checkout -->
      <div v-else class="row pb-4 mb-5">

        <!-- Items + coupon -->
        <div class="col-lg-7 mb-4 mb-lg-0">
          <div class="table-responsive">
            <table class="shop_table cart">
              <thead>
                <tr class="text-color-dark">
                  <th class="product-thumbnail" style="width:15%">&nbsp;</th>
                  <th class="product-name text-uppercase" style="width:35%">{{ __('message.product') }}</th>
                  <th class="product-quantity text-uppercase" style="width:15%">{{ __('message.quantity') }}</th>
                  <th class="product-quantity text-uppercase" style="width:20%">{{ __('message.agents') }}</th>
                  <th class="product-subtotal text-uppercase text-end" style="width:15%">{{ __('message.total') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in displayItems" :key="item.id" class="cart_table_item">
                  <td class="product-thumbnail">
                    <div class="product-thumbnail-wrapper">
                      <a v-if="mode === 'cart'" href="#" class="product-thumbnail-remove" :title="__('message.remove')" @click.prevent="remove(item.id)">
                        <i class="fas fa-times"></i>
                      </a>
                      <span class="product-thumbnail-image d-inline-block">
                        <img v-if="item.image" :src="item.image" alt="" class="img-fluid" />
                        <span v-else class="d-inline-flex align-items-center justify-content-center bg-light product-placeholder">
                          <i class="fas fa-box text-color-grey fa-2x"></i>
                        </span>
                      </span>
                    </div>
                  </td>
                  <td class="product-name">
                    <span class="font-weight-semi-bold text-color-dark">{{ item.name }}</span>
                  </td>
                  <td class="product-quantity">
                    <span class="text-color-dark">{{ item.quantity }}</span>
                  </td>
                  <td class="product-quantity">
                    <span class="text-color-grey">{{ item.agents ? item.agents : __('message.unlimited_agents') }}</span>
                  </td>
                  <td class="product-subtotal text-end">
                    <span class="amount text-color-dark font-weight-bold text-4">{{ symbol }}{{ item.line_total }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Coupon input (cart mode only; once applied it shows in the Discount
               summary row, where it can also be removed — an invoice's totals are final) -->
          <div v-if="mode === 'cart' && !cartStore.couponCode" class="d-flex align-items-center mt-3">
            <input v-model="couponInput" type="text"
                   class="form-control h-auto border-radius-0 line-height-1 py-3 coupon-input"
                   :placeholder="__('message.coupon_code')" @keyup.enter="applyCode" />
            <button type="button"
                    class="btn btn-light btn-modern text-2 text-uppercase ms-2 btn-apply-coupon"
                    :disabled="!couponInput || cartStore.loading"
                    @click="applyCode">
              {{ __('message.apply') }}
            </button>
          </div>

          <!-- Credit balance toggle. A single yes/no choice, like ticking a box —
               not an amount to type. Re-fetches the totals below when changed
               (invoice mode) or is simply carried forward to checkout (cart mode). -->
          <div v-if="availableCreditNumber > 0" class="form-check mt-3">
            <input id="use-credit" v-model="useCredit" type="checkbox" class="form-check-input" @change="onToggleCredit" />
            <label for="use-credit" class="form-check-label">
              {{ __('message.use_credit_balance', { amount: symbol + availableCredit }) }}
            </label>
          </div>
        </div>

        <!-- Order summary -->
        <div class="col-lg-5 position-relative">
          <div class="card border-width-3 border-radius-0">
            <div class="card-body">
              <h4 class="font-weight-bold text-uppercase text-4 mb-0 pb-3 border-bottom">{{ __('message.your_order') }}</h4>

              <!-- Cart-mode breakdown -->
              <template v-if="mode === 'cart'">
                <div class="d-flex justify-content-between py-3 border-bottom">
                  <strong class="text-color-dark">
                    {{ __('message.cart_subtotal') }}
                    <small v-if="cartStore.taxTotal > 0 && cartStore.taxLabel" class="text-color-grey fw-normal">(ex. {{ cartStore.taxLabel }})</small>
                  </strong>
                  <span class="amount font-weight-medium">{{ symbol }}{{ cartStore.subtotalExTax }}</span>
                </div>

                <div v-if="cartStore.couponDiscount > 0" class="d-flex justify-content-between align-items-center py-3 border-bottom">
                  <strong class="text-color-dark">{{ __('message.coupon') }} :
                    <span class="text-color-grey fw-normal"> {{ cartStore.couponCode }} </span>
                  </strong>
                  <span class="d-flex align-items-center">
                    <span class="amount text-success">−{{ symbol }}{{ cartStore.couponDiscount }}</span>
                    <button type="button"
                            v-tooltip="__('message.remove')"
                            class="btn ms-2 p-0 lh-1 border-0"
                            @click="removeCoupon">
                      <i class="fas fa-times-circle text-color-dark fa-lg"></i>
                    </button>
                  </span>
                </div>

                <div v-for="tax in cartStore.taxes" :key="tax.label" class="d-flex justify-content-between py-3 border-bottom">
                  <strong class="text-color-dark">{{ tax.label }}<span v-if="tax.rate" class="text-color-grey fw-normal"> ({{ tax.rate }}%)</span></strong>
                  <span class="amount font-weight-medium">{{ symbol }}{{ tax.amount }}</span>
                </div>

                <div v-if="cartCreditPreview > 0" class="d-flex justify-content-between py-3 border-bottom">
                  <strong class="text-color-dark">{{ __('message.credit_to_apply') }}</strong>
                  <span class="amount text-success">−{{ symbol }}{{ cartCreditPreview.toFixed(2) }}</span>
                </div>

                <div class="d-flex justify-content-between py-3 border-bottom">
                  <strong class="text-color-dark text-4">{{ __('message.total') }}</strong>
                  <strong class="text-color-dark text-4">{{ symbol }}{{ cartTotalPreview }}</strong>
                </div>
              </template>

              <!-- Invoice-mode: totals already finalised on the invoice -->
              <template v-else>
                <div v-if="invoice.number" class="d-flex justify-content-between py-3 border-bottom">
                  <strong class="text-color-dark">{{ __('message.invoice') }}</strong>
                  <span class="font-weight-medium">#{{ invoice.number }}</span>
                </div>
                <div v-if="invSummary.subtotal_ex_tax !== undefined" class="d-flex justify-content-between py-3 border-bottom">
                  <strong class="text-color-dark">
                    {{ __('message.sub_total') }}
                    <small v-if="invSummary.tax_total > 0 && invSummary.tax_label" class="text-color-grey fw-normal">(ex. {{ invSummary.tax_label }})</small>
                  </strong>
                  <span class="amount font-weight-medium">{{ symbol }}{{ invSummary.subtotal_ex_tax }}</span>
                </div>
                <div v-for="(tax, i) in (invSummary.taxes || [])" :key="i" class="d-flex justify-content-between py-3 border-bottom">
                  <strong class="text-color-dark">{{ tax.label }}<span v-if="tax.rate" class="text-color-grey fw-normal"> ({{ tax.rate }}%)</span></strong>
                  <span class="amount font-weight-medium">{{ symbol }}{{ tax.amount }}</span>
                </div>
                <div v-if="invSummary.discount > 0" class="d-flex justify-content-between py-3 border-bottom">
                  <strong class="text-color-dark">{{ __('message.coupon') }}<span v-if="invSummary.coupon_code" class="text-color-grey fw-normal"> : {{ invSummary.coupon_code }}</span></strong>
                  <span class="amount text-success">−{{ symbol }}{{ invSummary.discount }}</span>
                </div>
                <div v-if="invPaidNumber > 0" class="d-flex justify-content-between py-3 border-bottom">
                  <strong class="text-color-dark">{{ __('message.credit_applied') }}</strong>
                  <span class="amount text-success">−{{ symbol }}{{ invPaid }}</span>
                </div>
                <div v-if="invCreditAppliedNumber > 0" class="d-flex justify-content-between py-3 border-bottom">
                  <strong class="text-color-dark">{{ __('message.credit_to_apply') }}</strong>
                  <span class="amount text-success">−{{ symbol }}{{ invCreditApplied }}</span>
                </div>
                <div class="d-flex justify-content-between py-3 border-bottom">
                  <strong class="text-color-dark text-4">{{ __('message.amount_due') }}</strong>
                  <strong class="text-color-dark text-4">{{ symbol }}{{ grandTotal }}</strong>
                </div>
              </template>

              <!-- Payment methods -->
              <div class="py-3">
                <strong class="d-block text-color-dark text-uppercase mb-3">{{ __('message.payment_methods') }}</strong>

                <p v-if="!gateways.length" class="text-color-grey text-2 mb-0">
                  {{ __('message.no_payment_gateway') }}
                </p>

                <div v-for="gateway in gateways" :key="gateway.name" class="mb-3">
                  <label class="d-flex align-items-center mb-0 clickable" :for="`gw_${gateway.name}`">
                    <input :id="`gw_${gateway.name}`" v-model="selectedGateway" type="radio"
                           class="me-2" name="payment_gateway" :value="gateway.name" />
                    <img :src="`${baseUrl}/images/logo/${gateway.name}.png`" :alt="gateway.name" height="22"
                         @error="onLogoError($event, gateway.name)" />
                  </label>
                  <p v-if="selectedGateway === gateway.name && gateway.processing_fee"
                     class="text-color-grey text-2 mt-2 mb-0 ms-4">
                    {{ __('message.processing_fee_note', { fee: gateway.processing_fee }) }}
                  </p>
                </div>
              </div>

              <button type="button"
                      class="btn btn-dark btn-modern w-100 text-uppercase border-radius-0 text-3 py-3"
                      :disabled="!gateways.length || placing || (requiresGateway && !selectedGateway)"
                      @click="proceed">
                {{ placing ? __('message.please_wait') : __('message.proceed') }} <i v-if="!placing" class="fas fa-arrow-right ms-2"></i>
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCartStore } from '@/core/stores/cart'
import { useAlertStore } from '@/core/stores/alert'
import http, { parseErrorMessage } from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { useBaseUrl } from '@/core/composables/useBaseUrl'

const baseUrl = useBaseUrl()

const route = useRoute()
const router = useRouter()
const cartStore = useCartStore()
const alertStore = useAlertStore()

// Two modes:
//  - cart    : checkout the current cart (creates the invoice on "proceed")
//  - invoice : pay an existing invoice reached via /checkout?invoice=ID
//              (e.g. the Pay action on the invoices list)
// Both are reactive (not a one-time snapshot) because the SPA reuses this
// component instance when navigating from one invoice's checkout straight to
// another's (e.g. Pay on invoice A, back to the list, Pay on invoice B) —
// without that, the page would keep showing invoice A forever.
const invoiceId = computed(() => route.query.invoice ?? null)
const mode = computed(() => (invoiceId.value ? 'invoice' : 'cart'))

const couponInput = ref('')
const selectedGateway = ref(null)
const placing = ref(false)
// Credit is a single yes/no choice — when on, the full available balance
// applies (no partial amount to pick). One toggle shared by both modes.
const useCredit = ref(false)

// Invoice-mode state (populated from pay-init).
const invoiceLoading = ref(false)
const invoice = ref({})
const invItems = ref([])
const invSummary = ref({})
const invGateways = ref([])
const invAmount = ref(0)
const invPaid = ref(0)
// credit_applied/available_credit are what the server actually used/has on
// file for display — invAmount already has credit_applied netted out.
const invCreditApplied = ref(0)
const invAvailableCredit = ref(0)
const invSymbol = ref('')
const invError = ref('')

// Money fields from the backend are currency-formatted for display (e.g. "3,000.00")
// — strip thousands separators before treating them as numbers.
function toNumber(v) {
  return parseFloat(String(v).replace(/,/g, '')) || 0
}

const invPaidNumber = computed(() => toNumber(invPaid.value))
const invCreditAppliedNumber = computed(() => toNumber(invCreditApplied.value))

const loading = computed(() => (mode.value === 'invoice' ? invoiceLoading.value : cartStore.loading))
const symbol = computed(() => (mode.value === 'invoice' ? invSymbol.value : cartStore.currencySymbol))
const gateways = computed(() => (mode.value === 'invoice' ? invGateways.value : cartStore.gateways))
// In invoice mode this is the amount due (already net of any auto-applied
// credit); in cart mode the cart's grand total.
const grandTotal = computed(() => (mode.value === 'invoice' ? invAmount.value : cartStore.grandTotal))
const availableCredit = computed(() => (mode.value === 'invoice' ? invAvailableCredit.value : cartStore.availableCredit))
const availableCreditNumber = computed(() => toNumber(availableCredit.value))
// Cart mode has no invoice yet to ask the server for a real total, so this is
// a display-only preview (both numbers already came from the server as-is;
// this just takes their min) — the real amount is always recomputed once an
// invoice exists, on the next (place-order) step.
const cartCreditPreview = computed(() =>
  useCredit.value ? Math.min(toNumber(cartStore.grandTotal), availableCreditNumber.value) : 0
)
// Keep the server's own comma-formatted string when there's nothing to
// subtract, so the common case (no credit) doesn't lose thousands separators.
const cartTotalPreview = computed(() => cartCreditPreview.value > 0
  ? (toNumber(cartStore.grandTotal) - cartCreditPreview.value).toFixed(2)
  : cartStore.grandTotal)
// A gateway is only required when there's actually something left to charge —
// credit alone can cover an invoice entirely, with nothing to pay via gateway.
const requiresGateway = computed(() => mode.value !== 'invoice' || toNumber(invAmount.value) > 0)

// One unified item shape for the table, regardless of source.
const displayItems = computed(() => {
  if (mode.value === 'invoice') {
    return invItems.value.map((it) => ({
      id: it.id,
      name: it.product_name,
      image: it.image,
      quantity: it.quantity,
      agents: it.agents,
      line_total: it.subtotal,
    }))
  }
  return cartStore.items.map((it) => ({
    id: it.id,
    name: it.name,
    image: it.image,
    quantity: it.quantity,
    agents: it.agents,
    line_total: it.line_total,
  }))
})

const isEmpty = computed(() =>
  mode.value === 'invoice' ? !invoice.value.id : cartStore.items.length === 0
)

// Re-run whenever the invoice id changes — including switching from one
// invoice straight to another, or from an invoice back to plain cart checkout.
watch(invoiceId, (id) => {
  selectedGateway.value = null
  useCredit.value = false
  if (id) {
    loadInvoice()
    return
  }
  cartStore.fetchCheckout()
}, { immediate: true })

async function loadInvoice() {
  invoiceLoading.value = true
  invError.value = ''
  try {
    const { data } = await http.get(`/invoice/${invoiceId.value}/pay-init`, {
      params: { use_credit: useCredit.value ? 1 : 0 },
    })
    invoice.value = data.data.invoice
    invItems.value = data.data.items
    invSummary.value = data.data.summary ?? {}
    invAmount.value = data.data.amount
    invPaid.value = data.data.paid
    invCreditApplied.value = data.data.credit_applied
    invAvailableCredit.value = data.data.available_credit
    invSymbol.value = data.data.currency_symbol
    invGateways.value = data.data.gateways
  } catch (e) {
    invError.value = parseErrorMessage(e)
  } finally {
    invoiceLoading.value = false
  }
}

// Toggling credit only needs a re-fetch in invoice mode (it changes the
// amount due there); in cart mode it's just carried forward to checkout.
function onToggleCredit() {
  if (mode.value === 'invoice') loadInvoice()
}

// Default to the first available gateway once they load (either mode).
watch(gateways, (gws) => {
  if (gws.length && !selectedGateway.value) selectedGateway.value = gws[0].name
}, { immediate: true })

async function applyCode() {
  if (!couponInput.value) return
  alertStore.unsetAlert()
  try {
    await cartStore.applyCoupon(couponInput.value.trim())
    couponInput.value = ''
    await cartStore.fetchCheckout()
  } catch {
    alertStore.setAlert({ message: cartStore.error, type: 'danger', component_name: 'client-page' })
  }
}

async function removeCoupon() {
  await cartStore.removeCoupon()
  await cartStore.fetchCheckout()
}

async function remove(itemId) {
  await cartStore.removeItem(itemId)
  await cartStore.fetchCheckout()
}

function onLogoError(event, name) {
  // Fall back to the gateway name text if its logo image is missing.
  const span = document.createElement('span')
  span.className = 'font-weight-bold text-color-dark'
  span.textContent = name
  event.target.replaceWith(span)
}

// Hand off to the pay page (/place-order) with the chosen gateway. The pay page
// then shows full details and a single "Pay Now" that triggers that gateway.
//  - invoice mode: the invoice already exists, just carry its id forward.
//  - cart mode   : create (or reuse) the invoice from the cart first.
async function proceed() {
  if ((requiresGateway.value && !selectedGateway.value) || placing.value) return
  placing.value = true
  alertStore.unsetAlert()
  try {
    const credit = useCredit.value ? '1' : '0'
    if (mode.value === 'invoice') {
      router.push({ path: '/place-order', query: { invoice: invoiceId.value, gateway: selectedGateway.value, use_credit: credit } })
      return
    }
    const { data } = await http.post(`/cart/place-order`, { gateway: selectedGateway.value })
    router.push({ path: '/place-order', query: { invoice: data.data.invoice_id, gateway: selectedGateway.value, use_credit: credit } })
  } catch (e) {
    alertStore.setAlert({ message: parseErrorMessage(e), type: 'danger', component_name: 'client-page' })
    placing.value = false
  }
}
</script>

<style scoped>
.product-placeholder { width: 90px; height: 90px; }
.coupon-input { max-width: 280px; }
.btn-apply-coupon { background: #F4F4F4; }
.clickable { cursor: pointer; }
</style>
