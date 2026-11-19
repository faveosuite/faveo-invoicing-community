<template>
  <div class="main shop pb-4">
    <div class="container pt-4">
      <!-- Loading -->
      <div v-if="loading && !displayItems.length" class="row justify-content-center py-3"><loader /></div>

      <!-- Empty / not found -->
      <div v-else-if="isEmpty" class="d-flex flex-column align-items-center justify-content-center text-center py-6">
        <template v-if="mode === 'invoice'">
          <i class="fas fa-file-invoice fa-3x text-color-grey-lighten mb-3 d-block" aria-hidden="true"></i>
          <p class="text-color-grey mb-0">{{ invError || __('message.err_msg') }}</p>
        </template>
        <template v-else>
          <i class="fas fa-shopping-cart fa-3x text-color-grey-lighten mb-3 d-block" aria-hidden="true"></i>
          <p class="text-color-grey mb-4">{{ __('message.cart_empty') }}</p>
          <a :href="`${baseUrl}/store`" class="btn btn-primary btn-modern btn-px-4 py-3">
            {{ __('message.browse_products') }}
          </a>
        </template>
      </div>

      <!-- Checkout -->
      <div v-else class="pb-5">
        <div class="row g-4">
          <div class="col-lg-8">
            <!-- Items -->
            <section class="co-card" aria-labelledby="items-h">
              <div class="co-card-header">
                <div v-if="mode === 'invoice'">
                  <h2 id="items-h" class="co-title">{{ __('message.co_pay_invoice', { number: invoice.number }) }}</h2>
                </div>
                <h2 v-else id="items-h" class="co-title">{{ __('message.checkout') }}</h2>
                <span v-if="mode === 'invoice'" class="badge rounded-pill" :class="statusBadge.cls">{{ statusBadge.label }}</span>
                <router-link v-else to="/cart" class="co-link">{{ __('message.co_edit_cart') }}</router-link>
              </div>
              <ul class="list-unstyled mb-0">
                <li v-for="item in displayItems" :key="item.id" class="co-item align-items-center">
                  <span class="co-thumb" aria-hidden="true">
                    <img v-if="item.image" :src="item.image" alt="" width="56" height="56" />
                    <i v-else class="fas fa-box"></i>
                  </span>
                  <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold text-color-dark">{{ item.name }}</div>
                    <div v-if="item.meta.length" class="co-meta">{{ item.meta.join(' · ') }}</div>
                    <div v-for="e in (item.extras || [])" :key="e.entitlement_id" class="co-note">{{ e.label }}<template v-if="e.quantity > 1"> × {{ e.quantity }}</template></div>
                  </div>
                  <span class="co-price">{{ symbol }}{{ item.line_total }}</span>
                  <button v-if="mode === 'cart'" type="button" class="btn btn-sm border-0 text-color-grey px-2"
                          :aria-label="`${__('message.remove')} ${item.name}`" :disabled="cartStore.loading" @click="remove(item.id)">
                    <i class="fas fa-times" aria-hidden="true"></i>
                  </button>
                </li>
              </ul>
              <div v-if="mode === 'invoice'" class="co-card-body border-top py-3">
                <p class="co-note mb-0"><i class="fas fa-lock me-1" aria-hidden="true"></i>{{ __('message.co_invoice_final') }}</p>
              </div>
            </section>

            <!-- Payment method: hidden when nothing is left to charge (free / trial / credit covers it) -->
            <section v-if="requiresGateway" class="co-card" aria-labelledby="pay-h">
              <div class="co-card-header"><h2 id="pay-h" class="co-title">{{ __('message.co_payment_method') }}</h2></div>
              <fieldset class="co-card-body">
                <legend class="visually-hidden">{{ __('message.co_choose_payment') }}</legend>
                <p v-if="!gateways.length" class="co-note mb-0">{{ __('message.no_payment_gateway') }}</p>
                <label v-for="gateway in gateways" :key="gateway.name" class="co-gateway" :class="{ 'is-selected': selectedGateway === gateway.name }">
                  <input v-model="selectedGateway" type="radio" class="form-check-input" name="payment_gateway" :value="gateway.name" />
                  <img :src="`${baseUrl}/images/logo/${gateway.name}.png`" :alt="gateway.name" @error="onLogoError($event, gateway.name)" />
                  <span class="flex-grow-1 co-note">{{ gatewayNote(gateway.name) }}</span>
                </label>
                <p v-if="selectedFee" class="co-note mt-3 mb-0" role="status">
                  <i class="fas fa-info-circle me-1" aria-hidden="true"></i>{{ __('message.co_fee_note', { fee: selectedFee }) }}
                </p>
              </fieldset>
            </section>
          </div>

          <!-- Order summary -->
          <div class="col-lg-4">
            <aside class="co-card co-summary" aria-labelledby="sum-h">
              <div class="co-card-header"><h2 id="sum-h" class="co-title">{{ __('message.cart_totals') }}</h2></div>
              <div class="co-card-body">
                <!-- Coupon (cart mode; once applied it shows as a summary row, removable there) -->
                <div v-if="mode === 'cart' && !cartStore.couponCode && toNumber(cartStore.subtotal) > 0" class="mb-3">
                  <label for="coupon" class="form-label fw-semibold text-2 mb-1">{{ __('message.coupon_code') }}</label>
                  <div class="input-group has-validation">
                    <input id="coupon" v-model="couponInput" type="text" name="coupon_code" autocomplete="off" :spellcheck="false"
                           class="form-control" :class="{ 'is-invalid': couponError }" :placeholder="__('message.co_enter_code')"
                           :aria-invalid="!!couponError" aria-describedby="coupon-err" :disabled="couponBusy"
                           @input="couponError = ''" @keyup.enter="applyCode" />
                    <button type="button" class="btn btn-outline-primary" :disabled="!couponInput || couponBusy" @click="applyCode">
                      <span v-if="couponBusy" class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
                      {{ couponBusy ? __('message.co_applying') : __('message.apply') }}
                    </button>
                    <div id="coupon-err" class="invalid-feedback">{{ couponError }}</div>
                  </div>
                </div>

                <!-- Credit balance: all or nothing -->
                <label v-if="availableCreditNumber > 0 && toNumber(grandTotal) > 0" class="co-credit" for="use-credit">
                  <input id="use-credit" v-model="useCredit" type="checkbox" class="form-check-input" :disabled="loading" @change="onToggleCredit" />
                  <span>{{ __('message.co_use_credit') }} <span class="co-note d-block">{{ __('message.co_available', { amount: symbol + availableCredit }) }}</span></span>
                </label>

                <div aria-live="polite" :aria-busy="loading" :class="{ 'co-busy': loading }">
                  <!-- Cart mode -->
                  <template v-if="mode === 'cart'">
                    <div class="co-row">
                      <span>{{ __('message.subtotal') }} <span v-if="toNumber(cartStore.taxTotal) > 0 && cartStore.taxLabel" class="co-note">(ex. {{ cartStore.taxLabel }})</span></span>
                      <span class="co-amt">{{ symbol }}{{ cartStore.subtotalExTax }}</span>
                    </div>
                    <div v-if="toNumber(cartStore.couponDiscount) > 0" class="co-row is-discount align-items-center">
                      <span class="d-inline-flex align-items-center gap-1">
                        {{ __('message.coupon') }}: <strong>{{ cartStore.couponCode }}</strong>
                        <button type="button" class="btn btn-sm p-0 px-1 border-0 text-color-grey" :aria-label="`${__('message.remove')} ${cartStore.couponCode}`" @click="removeCoupon">
                          <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                      </span>
                      <span class="co-amt">−{{ symbol }}{{ cartStore.couponDiscount }}</span>
                    </div>
                    <div v-for="tax in cartStore.taxes" :key="tax.label" class="co-row">
                      <span>{{ tax.rate ? `${tax.label} ${tax.rate}%` : tax.label }}</span>
                      <span class="co-amt">{{ symbol }}{{ tax.amount }}</span>
                    </div>
                    <div v-if="toNumber(cartCreditPreview) > 0" class="co-row is-discount">
                      <span>{{ __('message.co_credit_balance') }}</span>
                      <span class="co-amt">−{{ symbol }}{{ cartCreditPreview }}</span>
                    </div>
                    <div class="co-row is-total">
                      <span>{{ __('message.total') }}</span>
                      <span class="co-amt">{{ symbol }}{{ cartTotalPreview }}</span>
                    </div>
                  </template>

                  <!-- Invoice mode: totals are final -->
                  <template v-else>
                    <div v-if="invSummary.subtotal_ex_tax !== undefined" class="co-row">
                      <span>{{ __('message.subtotal') }} <span v-if="toNumber(invSummary.tax_total) > 0 && invSummary.tax_label" class="co-note">(ex. {{ invSummary.tax_label }})</span></span>
                      <span class="co-amt">{{ symbol }}{{ invSummary.subtotal_ex_tax }}</span>
                    </div>
                    <div v-if="toNumber(invSummary.discount) > 0" class="co-row is-discount">
                      <span>{{ invSummary.coupon_code ? __('message.coupon') + ':' : __('message.discount') }} <strong v-if="invSummary.coupon_code">{{ invSummary.coupon_code }}</strong></span>
                      <span class="co-amt">−{{ symbol }}{{ invSummary.discount }}</span>
                    </div>
                    <div v-for="(tax, i) in (invSummary.taxes || [])" :key="i" class="co-row">
                      <span>{{ tax.rate ? `${tax.label} ${tax.rate}%` : tax.label }}</span>
                      <span class="co-amt">{{ symbol }}{{ tax.amount }}</span>
                    </div>
                    <div v-if="invPaidNumber > 0" class="co-row is-discount">
                      <span>{{ __('message.co_already_paid') }}</span>
                      <span class="co-amt">−{{ symbol }}{{ invPaid }}</span>
                    </div>
                    <div v-if="invCreditAppliedNumber > 0" class="co-row is-discount">
                      <span>{{ __('message.co_credit_balance') }}</span>
                      <span class="co-amt">−{{ symbol }}{{ invCreditApplied }}</span>
                    </div>
                    <div class="co-row is-total">
                      <span>{{ __('message.amount_due') }}</span>
                      <span class="co-amt">{{ symbol }}{{ grandTotal }}</span>
                    </div>
                  </template>
                </div>

                <button type="button" class="btn btn-primary btn-modern w-100 text-uppercase py-3 mt-3"
                        :disabled="!canProceed" :aria-describedby="needsGatewayChoice ? 'proceed-hint' : undefined" @click="proceed">
                  <span v-if="placing" class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
                  {{ placing ? __('message.please_wait') : __('message.proceed') }}
                </button>
                <p v-if="needsGatewayChoice" id="proceed-hint" class="co-note text-center mt-2 mb-0">{{ __('message.co_choose_payment_hint') }}</p>
                <p v-if="mode === 'cart'" class="co-note text-center mt-3 mb-0">
                  <i class="fas fa-lock me-1" aria-hidden="true"></i>{{ __('message.co_invoice_on_proceed') }}
                </p>
              </div>
            </aside>
          </div>
        </div>

      </div>
    </div>

    <!-- Only for a new cart purchase where auto-renew can be offered — the
         existing "Enable auto-renewal" flow on the order page covers this
         for orders placed without opting in here. -->
    <Modal :showModal="showAutoRenewModal" :onClose="closeAutoRenewModal" :showCloseBtn="false">
      <template #title>
        <h5 class="modal-title fw-bold">{{ __('message.auto_renew_modal_title') }}</h5>
      </template>
      <template #fields>
        <p class="mb-0">{{ __('message.auto_renew_modal_body') }}</p>
      </template>
      <template #controls>
        <action-button action="confirm" variant="light" icon="fas fa-arrow-right" :label="__('message.decline_and_proceed')" @click="submitOrder(false)" />
        <action-button action="confirm" :label="__('message.enable_and_proceed')" @click="submitOrder(true)" />
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCartStore } from '@/core/stores/cart'
import { useAlertStore } from '@/core/stores/alert'
import Modal from '@/themes/porto/components/common/Modal.vue'
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
const couponBusy = ref(false)
const couponError = ref('')
const selectedGateway = ref(null)
const placing = ref(false)
// Credit is a single yes/no choice — when on, the full available balance
// applies (no partial amount to pick). One toggle shared by both modes.
const useCredit = ref(false)
// Cart mode (new purchase) only — asked via a modal right before submitting.
const showAutoRenewModal = ref(false)

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
// Cart mode has no invoice yet, so this is a display-only preview — both numbers
// come pre-formatted from the server; the real amount is recomputed once an
// invoice exists, on the next (place-order) step.
const cartCreditPreview = computed(() => (useCredit.value ? cartStore.cart?.credit_preview ?? 0 : 0))
const cartTotalPreview = computed(() => (useCredit.value
  ? cartStore.cart?.grand_total_after_credit ?? cartStore.grandTotal
  : cartStore.grandTotal))
// A gateway is only required when there's actually something left to charge —
// credit alone can cover an invoice entirely (or a cart item can be free/trial),
// with nothing to pay via gateway.
const requiresGateway = computed(() =>
  toNumber(mode.value === 'invoice' ? invAmount.value : cartTotalPreview.value) > 0
)

// "1 agent" / "12 agents" / "Unlimited agents"; null = not priced per agent.
function agentsLabel(n) {
  if (n === null || n === undefined) return null
  if (!n) return __('message.plan_agents_unlimited')
  return n === 1 ? __('message.cart_one_agent') : __('message.cart_n_agents', { count: n })
}

// One unified item shape for the list, regardless of source; meta = the grey line under the name.
const displayItems = computed(() => {
  if (mode.value === 'invoice') {
    return invItems.value.map((it) => ({
      id: it.id,
      name: it.product_name,
      image: it.image,
      line_total: it.subtotal,
      extras: it.extras ?? [],
      meta: [agentsLabel(it.agents), it.quantity > 1 ? __('message.cart_qty', { count: it.quantity }) : null].filter(Boolean),
    }))
  }
  return cartStore.items.map((it) => {
    const addons = (it.extras ?? []).filter(e => e.quantity > 0).length
    return {
      id: it.id,
      name: it.name,
      image: it.image,
      line_total: it.line_total,
      meta: [
        it.period,
        it.show_agent ? agentsLabel(it.agents) : null,
        addons ? __('message.cart_n_addons', { count: addons }) : null,
        it.quantity > 1 ? __('message.cart_qty', { count: it.quantity }) : null,
      ].filter(Boolean),
    }
  })
})

// Invoice status pill (invoice mode).
const statusBadge = computed(() => ({
  success: { label: __('message.paid'), cls: 'text-bg-success' },
  'partially paid': { label: __('message.partially_paid'), cls: 'text-bg-warning' },
}[invoice.value.status] ?? { label: __('message.unpaid'), cls: 'text-bg-warning' }))

// One line under each gateway logo.
const GATEWAY_NOTES = { stripe: 'message.co_gateway_stripe', razorpay: 'message.co_gateway_razorpay' }
const gatewayNote = name => (GATEWAY_NOTES[name.toLowerCase()] ? __(GATEWAY_NOTES[name.toLowerCase()]) : '')

// Processing fee % of the picked gateway (0 = none).
const selectedFee = computed(() => gateways.value.find(g => g.name === selectedGateway.value)?.processing_fee || 0)

const needsGatewayChoice = computed(() => requiresGateway.value && !selectedGateway.value)
const canProceed = computed(() => !placing.value && !loading.value && !couponBusy.value && !needsGatewayChoice.value)

const isEmpty = computed(() =>
  mode.value === 'invoice' ? !invoice.value.id : cartStore.items.length === 0
)

// Removing the last item swaps the page for the short "empty" message — bring it into view
// instead of leaving the user scrolled down past it.
watch(isEmpty, (empty) => { if (empty) window.scrollTo({ top: 0 }) })

// Re-run whenever the invoice id changes — including switching from one
// invoice straight to another, or from an invoice back to plain cart checkout.
const ownPath = route.path
watch(invoiceId, (id) => {
  // Navigating away also clears ?invoice (before unmount) — don't reload the cart for that.
  if (route.path !== ownPath) return
  selectedGateway.value = null
  useCredit.value = false
  showAutoRenewModal.value = false
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

// Errors show under the coupon field, not as a page alert.
async function applyCode() {
  if (!couponInput.value || couponBusy.value) return
  couponBusy.value = true
  couponError.value = ''
  try {
    await cartStore.applyCoupon(couponInput.value.trim())
    couponInput.value = ''
    await cartStore.fetchCheckout()
  } catch {
    couponError.value = cartStore.error
  } finally {
    couponBusy.value = false
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

function closeAutoRenewModal() {
  showAutoRenewModal.value = false
  placing.value = false
}

// Hand off to the pay page (/place-order) with the chosen gateway. The pay page
// then shows full details and a single "Pay Now" that triggers that gateway.
//  - invoice mode: the invoice already exists, just carry its id forward.
//  - cart mode   : create (or reuse) the invoice from the cart first — if the
//                  plan can auto-renew, ask via a modal before submitting.
function proceed() {
  if ((requiresGateway.value && !selectedGateway.value) || placing.value) return
  placing.value = true

  if (mode.value === 'cart' && cartStore.autoRenewGateways.includes(selectedGateway.value)) {
    showAutoRenewModal.value = true
    return
  }
  submitOrder(false)
}

async function submitOrder(autoRenewOptIn) {
  showAutoRenewModal.value = false
  alertStore.unsetAlert()
  try {
    const credit = useCredit.value ? '1' : '0'
    if (mode.value === 'invoice') {
      router.push({ path: '/place-order', query: { invoice: invoiceId.value, gateway: selectedGateway.value, use_credit: credit } })
      return
    }
    const { data } = await http.post(`/my-cart/place-order`, {
      // Gateway is only used for an auto-renew check there — a free /
      // credit-covered cart may have none selected (or none configured at all).
      gateway: selectedGateway.value || null,
      auto_renew_opt_in: autoRenewOptIn,
    })
    router.push({ path: '/place-order', query: { invoice: data.data.invoice_id, gateway: selectedGateway.value, use_credit: credit } })
  } catch (e) {
    alertStore.setAlert({ message: parseErrorMessage(e), type: 'danger', component_name: 'client-page' })
    placing.value = false
  }
}
</script>

<style scoped>
.min-w-0 { min-width: 0; }
</style>
