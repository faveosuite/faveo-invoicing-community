<template>

  <div class="op-page">

    <div class="op-wrapper">

      <!-- Stepper -->
      <div class="d-flex justify-content-center align-items-start mb-4">
        <template v-for="(s, i) in progressSteps" :key="s.key">
          <div v-if="i > 0" class="flex-grow-1 border-top border-2 mt-4 step-connector"
               :class="(s.done || s.active) ? 'border-primary' : ''"></div>
          <div class="text-center px-2 step-item">
            <span class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2 step-circle"
                  :class="(s.done || s.active) ? 'bg-primary text-white' : 'bg-light text-muted border'">
              <i class="fas fa-lg" :class="s.icon"></i>
            </span>
            <div class="fw-semibold" :class="(s.done || s.active) ? 'text-primary' : 'text-muted'">
              {{ s.label }}
            </div>
          </div>
        </template>
      </div>

      <!-- ── STEP 1: Details + Payment (two columns) ── -->
      <transition name="op-slide" mode="out-in">
        <div v-if="step === 'form'" key="form" class="card shadow border-0 rounded-3">

          <div class="card-header bg-white border-bottom px-4 py-3">
            <h5 class="fw-bold mb-0">{{ __('message.op_payment_details') }}</h5>
            <p class="text-muted mb-0 mt-1">{{ __('message.op_pay_for', { app: appTitle }) }}</p>
          </div>

          <div class="card-body px-0 pt-0">
            <form @submit.prevent="submitForm" novalidate>
              <div class="row g-0">

                <!-- LEFT: Personal + Address -->
                <div class="col-md-7 p-4 border-end">

                  <p class="op-section-label text-uppercase fw-semibold text-muted mb-3">{{ __('message.op_personal_info') }}</p>

                  <ClientField name="name" :label="__('message.full_name')" placeholder="John Doe"
                               v-model="form.name" :error="errors.name" :required="true"
                               @update:modelValue="setFieldError('name', undefined)" />

                  <div class="row g-3">
                    <div class="col-md-6">
                      <ClientField name="email" type="email" :label="__('message.email')" placeholder="john@example.com"
                                   v-model="form.email" :error="errors.email" :required="true"
                                   @update:modelValue="setFieldError('email', undefined)" />
                    </div>
                    <div class="col-md-6">
                      <PhoneField name="mobile" :label="__('message.mobile')"
                                  :value="form.mobile" :error="errors.mobile" :required="true"
                                  :onChange="(val) => { form.mobile = val; setFieldError('mobile', undefined) }"
                                  @countryChange="({ dialCode }) => form.mobile_code = dialCode" />
                    </div>
                  </div>

                  <ClientField name="company" :label="__('message.company')" placeholder="Acme Inc."
                               v-model="form.company" :error="errors.company" :required="true"
                               @update:modelValue="setFieldError('company', undefined)" />

                  <p class="op-section-label text-uppercase fw-semibold text-muted mb-3">{{ __('message.billing_address') }}</p>

                  <ClientField name="address" :label="__('message.op_street_address')" placeholder="123 Main Street"
                               v-model="form.address" :error="errors.address" :required="true"
                               @update:modelValue="setFieldError('address', undefined)" />

                  <div class="row g-3">
                    <div class="col-md-6">
                      <ClientField name="city" :label="__('message.city')" placeholder="New York"
                                   v-model="form.city" :error="errors.city" :required="true"
                                   @update:modelValue="setFieldError('city', undefined)" />
                    </div>
                    <div class="col-md-6">
                      <DynamicSelect name="country" :label="__('message.country')"
                                    :apiEndpoint="`${baseUrl}/dependency/countries`"
                                    dataKey="countries"
                                    :value="selectedCountryObj"
                                    :onChange="onCountryChange" :required="true"
                                    placeholder="Search country..."
                                    :error="errors.country" />
                    </div>
                    <div class="col-md-6">
                      <DynamicSelect name="state" :label="__('message.state')"
                                    :apiEndpoint="selectedCountryObj ? `${baseUrl}/dependency/states` : null"
                                    dataKey="states"
                                    :apiParams="selectedCountryObj ? { country: selectedCountryObj.code } : {}"
                                    :value="selectedStateObj"
                                    :onChange="onStateChange" :required="true"
                                    placeholder="Search state..."
                                    :error="errors.state"
                                    :disabled="!selectedCountryObj" />
                    </div>
                    <div class="col-md-6">
                      <ClientField name="zip" :label="__('message.op_zip_code')" placeholder="10001"
                                   v-model="form.zip" :error="errors.zip" :required="true"
                                   @update:modelValue="setFieldError('zip', undefined)" />
                    </div>
                  </div>

                </div>

                <!-- RIGHT: Payment -->
                <div class="col-md-5 p-4 d-flex flex-column op-right-bg">

                  <p class="op-section-label text-uppercase fw-semibold text-muted mb-3">{{ __('message.op_payment_info') }}</p>

                  <ClientField name="amount" type="number" :label="__('message.amount')" placeholder="0.00"
                               v-model="form.amount" :error="errors.amount" :required="true"
                               @update:modelValue="setFieldError('amount', undefined)" />

                  <SelectField name="currency" :label="__('message.currency')"
                             :elements="currencyOptions" :value="selectedCurrency"
                             :onChange="onCurrencyChange"
                             :clearable="false" :searchable="false" />

                  <ClientField name="description" type="textarea" :label="__('message.op_description_optional')"
                               :placeholder="__('message.op_what_payment_for')"
                               v-model="form.description" :rows="2" />

                  <div class="mb-3">
                    <label class="form-label text-dark">{{ __('message.op_payment_gateway') }}</label>
                    <div v-for="gw in enabledGateways" :key="gw.name" class="mb-3">
                      <label class="d-flex align-items-center mb-0 clickable" :for="`gw_${gw.name}`">
                        <input :id="`gw_${gw.name}`" v-model="form.gateway" type="radio"
                               class="me-2" name="payment_gateway" :value="gw.name" />
                        <img :src="`${baseUrl}/images/logo/${gw.name}.png`" :alt="gw.name" height="22"
                             @error="onLogoError($event, gw.name)" />
                      </label>
                      <p v-if="form.gateway === gw.name && gw.processing_fee"
                         class="text-muted small mt-2 mb-0 ms-4">
                        {{ __('message.processing_fee_note', { fee: gw.processing_fee }) }}
                      </p>
                    </div>
                  </div>

                  <!-- push button to bottom -->
                  <div class="mt-auto">
                    <AppAlert componentName="open-payment-form" />
                    <button type="submit" class="btn btn-dark w-100 py-3 fw-semibold">
                      {{ __('message.op_continue_to_review') }} <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                  </div>

                </div>
              </div>
            </form>
          </div>
        </div>
      </transition>

      <!-- ── STEP 2: Review ── -->
      <transition name="op-slide" mode="out-in">
        <div v-if="step === 'summary'" key="summary" class="card shadow border-0 rounded-3">

          <div class="card-header bg-white border-bottom px-4 py-3">
            <h5 class="fw-bold mb-0">{{ __('message.op_review_order') }}</h5>
            <p class="text-muted mb-0 mt-1">{{ __('message.op_confirm_details') }}</p>
          </div>

          <div class="card-body px-0 pt-0">
            <div class="row g-0">

              <!-- LEFT: Payer info -->
              <div class="col-md-7 p-5 border-end">

                <!-- Profile row -->
                <div class="d-flex align-items-center gap-3 mb-4">
                  <div class="op-avatar flex-shrink-0">{{ avatarInitials }}</div>
                  <div>
                    <div class="fw-bold fs-5 text-dark">{{ form.name }}</div>
                    <div class="text-muted">{{ form.email }}</div>
                  </div>
                </div>

                <!-- Mobile + Company -->
                <div class="row g-4 mb-4">
                  <div class="col-6">
                    <div class="op-detail-label text-uppercase fw-semibold text-muted mb-1">{{ __('message.mobile') }}</div>
                    <div class="fw-semibold text-dark">{{ form.mobile_code ? `+${form.mobile_code} ${form.mobile}` : form.mobile }}</div>
                  </div>
                  <div class="col-6">
                    <div class="op-detail-label text-uppercase fw-semibold text-muted mb-1">{{ __('message.company') }}</div>
                    <div class="fw-semibold text-dark">{{ form.company }}</div>
                  </div>
                </div>

                <!-- Billing address -->
                <div class="border-top pt-4">
                  <div class="op-detail-label text-uppercase fw-semibold text-muted mb-3">{{ __('message.billing_address') }}</div>
                  <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">{{ __('message.op_street_address') }}</span>
                    <span class="fw-semibold text-end">{{ form.address }}</span>
                  </div>
                  <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">{{ __('message.city') }}</span>
                    <span class="fw-semibold">{{ form.city }}</span>
                  </div>
                  <div v-if="form.state" class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">{{ __('message.state') }}</span>
                    <span class="fw-semibold">{{ form.state }}</span>
                  </div>
                  <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">{{ __('message.op_zip_code') }}</span>
                    <span class="fw-semibold">{{ form.zip }}</span>
                  </div>
                  <div class="d-flex justify-content-between py-2">
                    <span class="text-muted">{{ __('message.country') }}</span>
                    <span class="fw-semibold">{{ selectedCountryObj?.name || form.country }}</span>
                  </div>
                </div>

              </div>

              <!-- RIGHT: Payment summary -->
              <div class="col-md-5 p-5 d-flex flex-column op-right-bg">

                <div class="fw-bold mb-3">{{ __('message.op_payment_summary') }}</div>

                <!-- Gateway / Currency / Note -->
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                  <span class="text-muted">{{ __('message.op_gateway') }}</span>
                  <span class="fw-semibold">{{ form.gateway }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                  <span class="text-muted">{{ __('message.currency') }}</span>
                  <span class="fw-semibold">{{ selectedCurrency?.name ?? form.currency }}</span>
                </div>
                <div v-if="form.description" class="py-2 border-bottom">
                  <div class="text-muted small mb-1">{{ __('message.op_note') }}</div>
                  <div class="op-note-text">{{ form.description }}</div>
                </div>

                <!-- Cart summary — server-calculated -->
                <div class="rounded-3 border bg-white p-3 mt-3">
                  <div v-if="calcLoading" class="text-center py-2 text-muted small">
                    <i class="fas fa-circle-notch fa-spin me-1"></i> Calculating…
                  </div>
                  <template v-else>
                    <div class="d-flex justify-content-between py-2">
                      <span class="text-muted">{{ __('message.amount_due') }}</span>
                      <span class="fw-semibold">{{ selectedCurrencySymbol }} {{ calculation.base_amount }}</span>
                    </div>
                    <div v-if="calculation.processing_fee_rate > 0" class="d-flex justify-content-between py-2">
                      <span class="text-muted">{{ __('message.processing_fee') }} ({{ calculation.processing_fee_rate }}%)</span>
                      <span class="fw-semibold">{{ selectedCurrencySymbol }} {{ calculation.processing_fee }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 mt-1 border-top">
                      <span class="fw-bold">{{ __('message.op_total_due') }}</span>
                      <span class="fw-bold">{{ selectedCurrencySymbol }} {{ calculation.total }}</span>
                    </div>
                  </template>
                </div>

                <div class="mt-auto pt-4">
                  <AppAlert componentName="open-payment-review" />
                  <RecaptchaField ref="captchaRef" action="open_payment" class="mb-3" />
                  <div v-if="showPayBtn || showBackBtn" class="d-flex gap-2">
                    <button v-if="showBackBtn" class="btn btn-light fw-semibold px-3 py-3" @click="backToForm" :disabled="paying">
                      <i class="fas fa-arrow-left"></i>
                    </button>
                    <button v-if="showPayBtn" class="btn btn-dark fw-semibold w-100 py-3" @click="payNow" :disabled="paying">
                      <span v-if="paying"><i class="fas fa-circle-notch fa-spin me-2"></i>{{ __('message.op_processing') }}</span>
                      <span v-else><i class="fas fa-lock me-2"></i>{{ __('message.op_pay') }} {{ selectedCurrencySymbol }} {{ calculation.total }}</span>
                    </button>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>
      </transition>

      <!-- ── STEP 3: Result ── -->
      <transition name="op-slide" mode="out-in">
        <div v-if="step === 'result'" key="result" class="row justify-content-center">
          <div class="col-lg-8">

          <!-- Success -->
          <template v-if="result.success">

            <!-- Thanks banner -->
            <div class="card border-width-3 border-radius-0 border-color-success mb-0">
              <div class="card-body text-center">
                <p class="text-color-dark font-weight-bold text-4-5 mb-0">
                  <i class="fas fa-check text-color-success me-1"></i> {{ __('message.thanks_order_received') }}
                </p>
              </div>
            </div>

            <!-- Summary row -->
            <div class="d-flex flex-column flex-md-row justify-content-between py-3 px-4 my-4">
              <div class="text-center">
                <span><strong class="text-color-dark">{{ __('message.op_transaction_id') }}</strong><br>
                  <span class="font-monospace small">{{ result.transactionId || '—' }}</span>
                </span>
              </div>
              <div class="text-center mt-4 mt-md-0">
                <span><strong class="text-color-dark">{{ __('message.status') }}</strong><br>{{ __('message.success') }}</span>
              </div>
              <div class="text-center mt-4 mt-md-0">
                <span><strong class="text-color-dark">{{ __('message.payment-method') }}</strong><br>{{ result.gateway }}</span>
              </div>
              <div class="text-center mt-4 mt-md-0">
                <span><strong class="text-color-dark">{{ __('message.total') }}</strong><br>{{ result.currency }} {{ result.amount }}</span>
              </div>
            </div>

            <!-- Details card -->
            <div class="card border-width-3 border-radius-0 mb-4">
              <div class="card-body">
                <h4 class="font-weight-bold text-uppercase text-4 mb-3">{{ __('message.op_payment_details') }}</h4>
                <table class="shop_table cart-totals mb-0">
                  <thead class="visually-hidden"><tr><th>{{ __("message.op_description_optional") }}</th><th>{{ __("message.value") }}</th></tr></thead>
                  <tbody>
                    <tr>
                      <td class="border-top-0"><strong class="text-color-dark">{{ __('message.op_description_optional') }}</strong></td>
                      <td class="border-top-0 text-end text-color-grey">{{ result.description || '—' }}</td>
                    </tr>
                    <tr>
                      <td><strong class="text-color-dark">{{ __('message.op_gateway') }}</strong></td>
                      <td class="text-end text-color-grey">{{ result.gateway }}</td>
                    </tr>
                    <tr class="total">
                      <td><strong class="text-color-dark text-3-5">{{ __('message.op_amount_paid') }}</strong></td>
                      <td class="text-end">
                        <strong class="text-color-dark"><span class="amount text-color-dark text-5">{{ result.currency }} {{ result.amount }}</span></strong>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Action -->
            <div class="d-flex justify-content-center">
              <button class="btn btn-dark btn-modern text-uppercase border-radius-0 px-5 py-2 fw-semibold" @click="reset">
                {{ __('message.op_make_another') }}
              </button>
            </div>

          </template>

          <!-- Failed -->
          <template v-else>
            <div class="card border-width-3 border-radius-0 border-color-danger">
              <div class="card-body text-center py-5">
                <i class="fas fa-times-circle fa-3x text-danger mb-3 d-block"></i>
                <h4 class="fw-bold mb-2">{{ __('message.op_payment_failed') }}</h4>
                <p class="text-muted mb-4">{{ result.message || __('message.op_something_went_wrong') }}</p>
                <button class="btn btn-dark btn-modern text-uppercase border-radius-0 px-5 py-2 fw-semibold" @click="reset">
                  {{ __('message.op_try_again') }}
                </button>
              </div>
            </div>
          </template>

          </div>
        </div>
      </transition>

    </div>
  </div>

  <!-- ── Stripe Card Modal ── -->
  <AppModal
    :showModal="showStripeModal"
    :onClose="onStripeModalClose"
    :showControls="false"
    classname="modal-md"
  >
    <template #title>
      <div class="d-flex align-items-center gap-2">
        <div class="op-brand-lock op-brand-lock-sm">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
        </div>
        <span class="fw-bold">{{ __('message.card_details') }}</span>
      </div>
    </template>
    <template #fields>
      <div class="px-2 pb-3">

        <AppAlert componentName="open-payment-stripe" />

        <div v-if="stripeLoading" class="d-flex align-items-center justify-content-center py-4 gap-2 text-muted">
          <i class="fas fa-circle-notch fa-spin"></i>
          <span class="small">Loading secure form…</span>
        </div>

        <template v-else>
          <!-- Card Number -->
          <div class="mb-3">
            <label class="form-label text-muted small mb-1">{{ __('message.card_number') }}</label>
            <div id="card-number" class="form-control h-auto py-3" :class="{ 'is-invalid': cardErrors.number }"></div>
            <div v-if="cardErrors.number" class="invalid-feedback d-block">{{ cardErrors.number }}</div>
          </div>

          <!-- Expiry + CVC -->
          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label text-muted small mb-1">{{ __('message.expiry_date') }}</label>
              <div id="card-expiry" class="form-control h-auto py-3" :class="{ 'is-invalid': cardErrors.expiry }"></div>
              <div v-if="cardErrors.expiry" class="invalid-feedback d-block">{{ cardErrors.expiry }}</div>
            </div>
            <div class="col-6">
              <label class="form-label text-muted small mb-1">{{ __('message.cvc') }}</label>
              <div id="card-cvc" class="form-control h-auto py-3" :class="{ 'is-invalid': cardErrors.cvc }"></div>
              <div v-if="cardErrors.cvc" class="invalid-feedback d-block">{{ cardErrors.cvc }}</div>
            </div>
          </div>

          <!-- Total row -->
          <div class="d-flex justify-content-between align-items-center border rounded px-3 py-2 mb-3 bg-light">
            <span class="fw-semibold small text-muted">{{ __('message.op_total_due') }}</span>
            <span class="fw-bold">{{ order?.currency }} {{ order?.amount }}</span>
          </div>

          <!-- Pay button -->
          <button class="btn btn-success w-100 py-2 fw-semibold" :disabled="stripeSubmitting" @click="payStripe">
            <span v-if="stripeSubmitting"><i class="fas fa-circle-notch fa-spin me-2"></i>{{ __('message.op_processing') }}</span>
            <span v-else><i class="fas fa-lock me-2"></i>{{ __('message.op_pay') }} {{ order?.currency }} {{ order?.amount }}</span>
          </button>

        </template>

      </div>
    </template>
  </AppModal>

  <GlobalLoader />

</template>

<script setup>
import { ref, reactive, computed, onMounted, nextTick } from 'vue'
import { useForm } from 'vee-validate'
import { validateForm } from '@/helpers/formUtils.js'
import http from '@/plugins/axios'
import { useAlertStore } from '@/core/stores/alert'
import { RecaptchaField } from '@recaptcha'
import { openPaymentSchema } from '@/validations/client/openPaymentSchema'
import { __ } from '@/plugins/i18n'
import GlobalLoader from '@/components/Reusable/GlobalLoader.vue'
import { useLoaderStore } from '@/core/stores/loader'
import { useBaseUrl } from '@/core/composables/useBaseUrl'

const baseUrl = useBaseUrl()
const API     = `/pay`

const steps = computed(() => [
  { key: 'form',    label: __('message.op_step_details'), icon: 'fa-user'           },
  { key: 'summary', label: __('message.op_step_review'),  icon: 'fa-clipboard-list' },
  { key: 'result',  label: __('message.op_step_done'),    icon: 'fa-check'          },
])

const step        = ref('form')
const alertStore  = useAlertStore()
const loaderStore = useLoaderStore()
const { errors, setErrors, setFieldError } = useForm()
const stepIndex  = computed(() => steps.value.findIndex(s => s.key === step.value))
const progressSteps = computed(() => steps.value.map((s, i) => ({
  ...s,
  active: stepIndex.value === i,
  done:   stepIndex.value > i,
})))
const avatarInitials = computed(() => {
  const parts = (form.name ?? '').trim().split(/\s+/)
  return parts.length >= 2
    ? (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
    : (parts[0]?.[0] ?? '?').toUpperCase()
})

const captchaRef = ref(null)
const paying     = ref(false)
const showPayBtn = ref(true)
const showBackBtn  = ref(true)
const order        = ref(null)
const result       = reactive({ success: false, message: '', transactionId: '', currency: '', amount: '', gateway: '', description: '' })
const showStripeModal  = ref(false)
const stripeLoading    = ref(false)
const stripeSubmitting = ref(false)
const cardErrors       = reactive({ number: '', expiry: '', cvc: '' })
const cardComplete     = reactive({ number: false, expiry: false, cvc: false })
let   stripeInstance   = null
let   cardNumberEl     = null
let   clientSecret     = null

const form = reactive({
  name: '', email: '', mobile: '', mobile_code: '', company: '',
  address: '', city: '', state: '', zip: '', country: '',
  amount: '', currency: '', gateway: '', description: '',
})

// ── Currency ────────────────────────────────────────────────────────
const appTitle        = ref('')
const currencyOptions = ref([])
const enabledGateways = ref([])
const selectedCurrency = computed(() => currencyOptions.value.find(c => c.code === form.currency) ?? null)
const selectedCurrencySymbol = computed(() => selectedCurrency.value?.symbol ?? '')
const onCurrencyChange = (val) => { form.currency = val?.code ?? '' }

const onLogoError = (event, name) => {
  const span = document.createElement('span')
  span.className = 'fw-semibold text-dark'
  span.textContent = name
  event.target.replaceWith(span)
}

// ── Payment summary — server-calculated to avoid any rounding mismatch ──
const calculation = reactive({ base_amount: '0.00', processing_fee: '0.00', processing_fee_rate: 0, total: '0.00' })
const calcLoading  = ref(false)

const fetchCalculation = async () => {
  if (!form.amount || !form.gateway) return
  calcLoading.value = true
  try {
    const { data } = await http.get(`${API}/calculate`, { params: { amount: form.amount, gateway: form.gateway } })
    Object.assign(calculation, data.data)
  } catch {
    // fallback: keep zeros, user will see correct values after retry
  } finally {
    calcLoading.value = false
  }
}

// ── Countries / States ──────────────────────────────────────────────
const selectedCountryObj = ref(null)
const selectedStateObj = ref(null)

const onCountryChange = (val) => {
  selectedCountryObj.value = val
  selectedStateObj.value = null
  form.country = val?.code ?? ''   // ISO 3166-1 alpha-2 — required by Stripe
  form.state = ''
  setFieldError('country', undefined)
}

const onStateChange = (val) => {
  selectedStateObj.value = val
  form.state = val?.name ?? ''
  setFieldError('state', undefined)
}

const loadScript = (src) => new Promise((resolve, reject) => {
  if (document.querySelector(`script[src="${src}"]`)) { resolve(); return }
  const s = document.createElement('script')
  s.src = src; s.onload = resolve; s.onerror = reject
  document.head.appendChild(s)
})

const reset = () => {
  step.value = 'form'
  order.value = null
  showPayBtn.value = true
  showBackBtn.value = true
  showStripeModal.value = false
  stripeInstance = null
  cardNumberEl   = null
  clientSecret   = null
  selectedCountryObj.value = null
  selectedStateObj.value = null
  Object.assign(result, { success: false, message: '', transactionId: '', currency: '', amount: '', gateway: '', description: '' })
  alertStore.unsetAlert()
}

const backToForm = () => {
  step.value  = 'form'
  order.value = null
  setErrors({})
  alertStore.unsetAlert()
}

const showResult = (success, paidOrder, message = '') => {
  Object.assign(result, {
    success,
    message,
    transactionId: paidOrder?.transaction_id || paidOrder?.id || '',
    currency: paidOrder?.currency || '',
    amount: paidOrder?.amount || '',
    gateway: paidOrder?.gateway || '',
    description: paidOrder?.description || '',
  })
  step.value = 'result'
}

const submitForm = async () => {
  alertStore.unsetAlert()

  if (!await validateForm(openPaymentSchema, form, setErrors)) return

  setErrors({})
  step.value = 'summary'
  fetchCalculation()
}

const createOrder = async () => {
  const captchaPayload = await captchaRef.value?.getPayload() ?? {}
  // If reCAPTCHA is active but token is null, RecaptchaField already shows the
  // inline error — abort here so the payment flow doesn't continue
  if (captchaRef.value && !captchaRef.value.disabled && !captchaPayload['g-recaptcha-response']) {
    const err = new Error('captcha_failed')
    err.captchaFailed = true
    throw err
  }
  const mobile = form.mobile_code ? `+${form.mobile_code} ${form.mobile}`.trim() : form.mobile
  const { data } = await http.post(`${API}/create`, { ...form, mobile, ...captchaPayload })
  order.value = data.data.order
}

const payNow = async () => {
  alertStore.unsetAlert()

  // Create the order on first Pay attempt; reuse it on retry
    if (!order.value) {
      paying.value = true
      try {
        await createOrder()
        // If the server-confirmed total differs from the frontend estimate
        // (rate changed between config load and Pay click), alert the user
        const confirmedTotal = parseFloat(order.value?.amount)
        const estimatedTotal = parseFloat(calculation.total)
        if (!Number.Number.isNaN(confirmedTotal) && Math.abs(confirmedTotal - estimatedTotal) > 0.01) {
          alertStore.setAlert({
            message: `The total has been updated to ${selectedCurrencySymbol.value} ${confirmedTotal} due to a fee rate change.`,
            type: 'warning',
            component_name: 'open-payment-review',
          })
          paying.value = false
          return
        }
      } catch (err) {
        if (err.captchaFailed) {
          paying.value = false
          return
        }
        const res = err.response?.data
        if (res?.data?.show_v2_recaptcha) {
          captchaRef.value?.triggerFallback()
          alertStore.setAlert({ message: 'Please complete the reCAPTCHA and try again.', type: 'warning', component_name: 'open-payment-review' })
        } else if ((err.response?.status === 422 || err.response?.status === 412) && res?.errors) {
          const map = Object.fromEntries(Object.entries(res.errors).map(([k, v]) => [k, v[0]]))
          setErrors(map)
          step.value = 'form'
          alertStore.setAlert({ message: 'Please fix the highlighted fields and try again.', type: 'warning', component_name: 'open-payment-form' })
        } else if (err.response?.status === 429) {
          alertStore.setAlert({ message: 'Too many attempts. Please wait a moment and try again.', type: 'danger', component_name: 'open-payment-review' })
        } else {
          alertStore.setAlert({ message: res?.message || 'Failed to create order', type: 'danger', component_name: 'open-payment-review' })
        }
        paying.value = false
        return
      }
      paying.value = false
    }

    if (form.gateway === 'Stripe') {
      await initStripe()
      return
    }

  paying.value = true
  try {
    const { data } = await http.post(`${API}/prepare`, { order_id: order.value.id })
    await initRazorpay(data.data)
  } catch (err) {
    alertStore.setAlert({ message: err.response?.data?.message || 'Failed to initialize payment', type: 'danger', component_name: 'open-payment-review' })
    paying.value = false
  }
}

const initRazorpay = async (cfg) => {
  await loadScript('https://checkout.razorpay.com/v1/checkout.js')
  const rzp = new globalThis.Razorpay({ ...cfg, handler: verifyRazorpay })
  rzp.on('payment.failed', (e) => {
    alertStore.setAlert({ message: e.error.description, type: 'danger', component_name: 'open-payment-review' })
  })
  rzp.open()
  paying.value = false
}

const initStripe = async () => {
  paying.value = true
  alertStore.unsetAlert()
  try {
    const { data } = await http.post(`${API}/stripe/card-session`, { order_id: order.value.id })
    const cfg = data.data

    if (cfg.status === 'succeeded') {
      await verifyStripe(cfg.payment_intent_id)
      return
    }

    await loadScript('https://js.stripe.com/v3/')
    stripeInstance = globalThis.Stripe(cfg.publishable_key)
    clientSecret  = cfg.client_secret

    Object.assign(cardErrors,   { number: '', expiry: '', cvc: '' })
    Object.assign(cardComplete, { number: false, expiry: false, cvc: false })

    showStripeModal.value = true
    stripeLoading.value   = true
    await nextTick()

    const elements = stripeInstance.elements()
    const style = {
      base: { fontSize: '15px', color: '#32325d', fontFamily: 'inherit', '::placeholder': { color: '#aab7c4' } },
      invalid: { color: '#dc3545' },
    }

    cardNumberEl = elements.create('cardNumber', { showIcon: true, style })
    const cardExpiry = elements.create('cardExpiry', { style })
    const cardCvc    = elements.create('cardCvc', { style })

    const bind = (el, key) => el.on('change', (e) => {
      cardErrors[key]   = e.error?.message ?? ''
      cardComplete[key] = e.complete
    })
    bind(cardNumberEl, 'number')
    bind(cardExpiry,   'expiry')
    bind(cardCvc,      'cvc')

    // Hide spinner first so the card divs render in the DOM, then mount
    stripeLoading.value = false
    await nextTick()

    cardNumberEl.mount('#card-number')
    cardExpiry.mount('#card-expiry')
    cardCvc.mount('#card-cvc')
  } catch (err) {
    alertStore.setAlert({ message: err.response?.data?.message || 'Failed to initialise payment', type: 'danger', component_name: 'open-payment-stripe' })
    showStripeModal.value = true
    stripeLoading.value = false
  } finally {
    paying.value = false
  }
}

const payStripe = async () => {
  if (stripeSubmitting.value) return

  if (!cardComplete.number) cardErrors.number = cardErrors.number || 'Card number is required'
  if (!cardComplete.expiry) cardErrors.expiry = cardErrors.expiry || 'Expiry date is required'
  if (!cardComplete.cvc)    cardErrors.cvc    = cardErrors.cvc    || 'CVC is required'
  if (!cardComplete.number || !cardComplete.expiry || !cardComplete.cvc) return

  stripeSubmitting.value = true
  alertStore.unsetAlert()
  try {
    const { paymentIntent, error: confirmError } = await stripeInstance.confirmCardPayment(clientSecret, {
      payment_method: { card: cardNumberEl },
    })

    if (confirmError) {
      if (confirmError.code === 'payment_intent_unexpected_state' || confirmError.payment_intent?.status === 'succeeded') {
        await verifyStripe(confirmError.payment_intent.id)
        return
      }
      alertStore.setAlert({ message: confirmError.message, type: 'danger', component_name: 'open-payment-stripe' })
      return
    }

    if (paymentIntent?.status === 'succeeded') {
      showStripeModal.value = false
      await verifyStripe(paymentIntent.id)
    } else {
      alertStore.setAlert({ message: 'Payment was not completed. Please try again.', type: 'danger', component_name: 'open-payment-stripe' })
    }
  } catch (err) {
    alertStore.setAlert({ message: err.response?.data?.message || 'Payment failed', type: 'danger', component_name: 'open-payment-stripe' })
  } finally {
    stripeSubmitting.value = false
  }
}

const onStripeModalClose = () => {
  showStripeModal.value  = false
  stripeLoading.value    = false
  stripeSubmitting.value = false
  alertStore.unsetAlert()
  stripeInstance = null
  cardNumberEl   = null
  clientSecret   = null
  showPayBtn.value  = true
  showBackBtn.value = true
}

const verifyRazorpay = async (response) => {
  loaderStore.startLoader('op-verify')
  try {
    const { data } = await http.post(`${API}/verify/razorpay`, {
      order_id: order.value.id,
      razorpay_payment_id: response.razorpay_payment_id,
      razorpay_order_id: response.razorpay_order_id,
      razorpay_signature: response.razorpay_signature,
    })
    if (data.success && data.data?.order) showResult(true, data.data.order)
    else showResult(false, null, data.message || 'Payment verification failed')
  } catch (err) {
    showResult(false, null, err.response?.data?.message || 'Verification failed')
  } finally {
    loaderStore.stopLoader('op-verify')
  }
}

const verifyStripe = async (paymentIntentId) => {
  loaderStore.startLoader('op-verify')
  try {
    const { data } = await http.post(`${API}/verify/stripe`, {
      order_id: order.value.id,
      payment_intent_id: paymentIntentId,
    })
    if (data.success && data.data?.order) showResult(true, data.data.order)
    else showResult(false, null, data.message || 'Payment verification failed')
  } catch (err) {
    showResult(false, null, err.response?.data?.message || 'Verification failed')
  } finally {
    loaderStore.stopLoader('op-verify')
  }
}

const autoDetectCountry = async () => {
  try {
    // Primary: backend detects from request IP (avoids CORS/rate-limit)
    const { data } = await http.get(`${API}/detect-country`)
    const country = data?.data?.country
    if (country) { onCountryChange(country); return }

    // Fallback: browser-side lookup for localhost/private IP environments
    const geo  = await fetch('https://ipapi.co/json').then(r => r.json())
    const code = geo?.country_code
    if (!code) return

    const res   = await http.get(`/dependency/countries`)
    const match = (res.data?.data?.countries ?? []).find(c => c.code === code)
    if (match) onCountryChange(match)
  } catch { /* best-effort */ }
}

onMounted(async () => {
  autoDetectCountry()

  // Load enabled gateways + active currencies from the backend
  try {
    const { data } = await http.get(`${API}/config`)
    const cfg = data.data
    appTitle.value        = cfg.app_title ?? ''
    currencyOptions.value = (cfg.currencies ?? []).map(c => ({ code: c.code, symbol: c.symbol, name: `${c.name} (${c.code})` }))
    enabledGateways.value = cfg.gateways ?? []
    if (!form.currency && currencyOptions.value.length) form.currency = currencyOptions.value[0].code
    if (!form.gateway  && enabledGateways.value.length)  form.gateway  = enabledGateways.value[0].name
  } catch {
    currencyOptions.value = [{ code: 'USD', symbol: '$', name: 'United States dollar (USD)' }]
    enabledGateways.value = [{ name: 'Razorpay', processing_fee: 0 }, { name: 'Stripe', processing_fee: 0 }]
    form.currency = 'USD'
    form.gateway  = 'Razorpay'
  }

  const params  = new URLSearchParams(globalThis.location.search)
  const status  = params.get('status')
  const orderId = params.get('order_id')
  const message = params.get('message')

  if (status === 'success' && orderId) {
    try {
      const { data } = await http.get(`${API}/order/${orderId}`)
      showResult(true, data.data?.order ?? { id: orderId })
    } catch { showResult(true, { id: orderId }) }
    globalThis.history.replaceState({}, document.title, globalThis.location.pathname)
  } else if (status === 'failed' || status === 'error') {
    showResult(false, null, message ? decodeURIComponent(message) : 'Payment failed')
    globalThis.history.replaceState({}, document.title, globalThis.location.pathname)
  } else if (status === 'pending') {
    showResult(false, null, message ? decodeURIComponent(message) : 'Payment is still processing. Please check back later.')
    globalThis.history.replaceState({}, document.title, globalThis.location.pathname)
  }
})
</script>

<style scoped>
/* ── Shared layout ──────────────────────────────────────────────── */
.op-right-bg     { background: #f8f9ff; }
.op-section-label { font-size: 0.7rem; letter-spacing: 0.08em; }
.op-detail-label  { font-size: 0.65rem; letter-spacing: 0.08em; }
.op-address       { font-style: normal; line-height: 1.8; color: #374151; }
.op-total-label   { font-size: 0.7rem; letter-spacing: 0.06em; text-transform: uppercase; }
.op-amount-display { font-size: 2rem; color: #111827; letter-spacing: -0.02em; line-height: 1.15; }
.op-note-text     { color: #374151; line-height: 1.5; }
.op-result-msg    { max-width: 400px; margin-inline: auto; }
.op-txn-box       { background: var(--primary-rgba-10); border: 1.5px solid var(--primary-rgba-30); }
.op-txn-id        { font-family: monospace; font-size: 0.95rem; letter-spacing: 0.05em; color: var(--primary); }
.op-brand-lock-sm { width: 26px; height: 26px; flex-shrink: 0; }

/* ── Page ───────────────────────────────────────────────────────── */
.op-page {
  display: flex;
  align-items: flex-start;
  justify-content: center;
}


.op-wrapper {
  width: 100%;
  max-width: 980px;
  position: relative;
  z-index: 1;
}

/* ── Brand lock (Stripe modal icon) ─────────────────────────────── */
.op-brand-lock {
  width: 28px; height: 28px;
  background: var(--primary);
  color: white;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* ── Stepper (matches Verify page) ──────────────────────────────── */
.step-circle    { width: 50px; height: 50px; }
.step-item      { min-width: 96px; }
.step-connector { max-width: 90px; }


/* ── Review: avatar ─────────────────────────────────────────────── */
.op-avatar {
  width: 52px; height: 52px;
  border-radius: 50%;
  background: var(--primary);
  color: white;
  font-weight: 700;
  font-size: 1.1rem;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 4px 12px var(--primary-rgba-30);
}

/* ── Review: info list ──────────────────────────────────────────── */
.op-info-list { display: flex; flex-direction: column; gap: 0.75rem; }
.op-info-item { display: flex; align-items: center; gap: 0.85rem; }
.op-info-icon {
  width: 32px; height: 32px;
  background: var(--primary-rgba-10);
  color: var(--primary);
  border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.75rem;
  flex-shrink: 0;
}
.op-info-label { font-size: 0.72rem; color: #9ca3af; font-weight: 500; line-height: 1; margin-bottom: 0.15rem; }
.op-info-value { font-size: 0.875rem; color: #111827; font-weight: 500; }

/* ── Amount banner ──────────────────────────────────────────────── */
.op-amount-banner {
  background: var(--primary);
  color: white;
}



/* ── Transition ─────────────────────────────────────────────────── */
.op-slide-enter-active, .op-slide-leave-active { transition: all 0.25s ease; }
.op-slide-enter-from { opacity: 0; transform: translateX(20px); }
.op-slide-leave-to   { opacity: 0; transform: translateX(-20px); }

/* ── Mobile ─────────────────────────────────────────────────────── */
@media (max-width: 767px) {
  .op-step-line { width: 28px; }
  .op-step-label { display: none; }
  .border-end { border-right: none !important; border-bottom: 1px solid #dee2e6 !important; }
}

</style>

<style scoped>
.clickable { cursor: pointer; }
</style>
