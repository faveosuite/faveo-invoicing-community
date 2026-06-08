<template>

  <!-- Navbar -->
  <nav class="navbar navbar-light bg-white border-bottom">
    <div class="op-container mx-auto px-3 w-100 d-flex align-items-center justify-content-between">
      <a href="javascript:;" class="navbar-brand py-2 m-0">
        <img v-if="logoUrl" :src="logoUrl" alt="Logo" class="op-nav-logo">
        <span v-else class="fw-bold">{{ company }}</span>
      </a>

      <!-- Language selector -->
      <div v-if="languages.length" class="dropdown">
        <button class="btn btn-link text-dark text-decoration-none p-0 d-flex align-items-center gap-2"
                type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <span :class="`fi fi-${flagCodeFor(currentLocale)} op-flag`"></span>
          <span class="fw-semibold">{{ currentLocale.toUpperCase() }}</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end op-lang-menu">
          <li v-for="lang in languages" :key="lang.locale">
            <a class="dropdown-item d-flex align-items-center gap-2"
               :class="{ active: lang.locale.toLowerCase() === currentLocale }"
               href="javascript:;"
               @click.prevent="selectLang(lang)">
              <span :class="`fi fi-${flagCodeFor(lang.locale)}`"></span>
              <span>{{ lang.name }}</span>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

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
            <p class="text-muted small mb-0 mt-1">{{ __('message.op_fill_info') }}</p>
          </div>

          <div class="card-body p-0">
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
                                  :onChange="(val) => { form.mobile = val; setFieldError('mobile', undefined) }" />
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
                    <div class="d-flex flex-column gap-2">
                      <label v-for="gw in enabledGateways" :key="gw.name"
                             :class="['op-gateway-card', form.gateway === gw.name ? 'op-gateway-selected' : '']">
                        <input type="radio" v-model="form.gateway" :value="gw.name" />
                        <div class="d-flex align-items-center gap-2">
                          <div class="op-gateway-radio"></div>
                          <div>
                            <div class="fw-semibold small">{{ gw.name }}</div>
                            <div class="text-muted op-gw-desc">
                              {{ gw.name === 'Razorpay' ? __('message.op_razorpay_methods') : __('message.op_stripe_methods') }}
                            </div>
                          </div>
                        </div>
                      </label>
                    </div>
                  </div>

                  <!-- Processing fee note -->
                  <p v-if="processingFeeRate > 0" class="text-muted small mt-2 mb-3">
                    <i class="fas fa-info-circle me-1"></i>
                    {{ __('message.processing_fee_note', { fee: processingFeeRate }) }}
                  </p>

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
            <p class="text-muted small mb-0 mt-1">{{ __('message.op_confirm_details') }}</p>
          </div>

          <div class="card-body p-0">
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
                    <div class="fw-semibold text-dark">{{ form.mobile }}</div>
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
        <div v-if="step === 'result'" key="result" class="card shadow border-0 rounded-3">
          <div class="card-body p-5 text-center">

            <div :class="['op-result-icon mx-auto mb-4', result.success ? 'op-result-success' : 'op-result-failed']">
              <i :class="['fas fa-2x', result.success ? 'fa-check' : 'fa-times']"></i>
            </div>

            <h4 class="fw-bold mb-2">{{ result.success ? __('message.op_payment_successful') : __('message.op_payment_failed') }}</h4>
            <p class="op-result-msg text-muted mb-4">
              {{ result.success
                ? __('message.op_success_msg')
                : (result.message || __('message.op_something_went_wrong')) }}
            </p>

            <template v-if="result.success && result.transactionId">
              <div class="rounded-3 border bg-white p-3 mb-4 text-start">
                <div class="d-flex justify-content-between py-2 border-bottom">
                  <span class="text-muted">{{ __('message.op_transaction_id') }}</span>
                  <span class="op-txn-id fw-bold">{{ result.transactionId }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                  <span class="text-muted">{{ __('message.op_amount_paid') }}</span>
                  <span class="fw-semibold">{{ result.currency }} {{ result.amount }}</span>
                </div>
                <div class="d-flex justify-content-between py-2">
                  <span class="text-muted">{{ __('message.op_gateway') }}</span>
                  <span class="fw-semibold">{{ result.gateway }}</span>
                </div>
              </div>
            </template>

            <button class="btn btn-dark px-5 py-2 fw-semibold" @click="reset">
              {{ result.success ? __('message.op_make_another') : __('message.op_try_again') }}
            </button>
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

  <!-- Footer -->
  <footer class="bg-white border-top py-3">
    <div class="op-container mx-auto px-3 w-100 d-flex flex-wrap justify-content-between align-items-center gap-2">
      <span class="text-muted">
        <strong>{{ __('message.copyright') }} © 2015 - {{ currentYear }}
          <a v-if="website" :href="website" target="_blank" class="text-primary text-decoration-none">{{ company }}</a>
          <span v-else class="text-primary">{{ company }}</span>.
        </strong>
        {{ __('message.all_rights') }} <strong><a href="https://www.faveohelpdesk.com/" target="_blank" class="text-decoration-none">Faveo</a></strong>
      </span>
      <span v-if="appName || appVersion" class="text-muted d-none d-sm-inline">{{ appName }} {{ appVersion }}</span>
    </div>
  </footer>

</template>

<script setup>
import { ref, reactive, computed, onMounted, nextTick } from 'vue'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { useAlertStore } from '@/core/stores/alert'
import { RecaptchaField } from '@recaptcha'
import { openPaymentSchema } from '@/validations/client/openPaymentSchema'
import { __ } from '@/plugins/i18n'
import GlobalLoader from '@/components/Reusable/GlobalLoader.vue'
import { useLoaderStore } from '@/core/stores/loader'

const el      = document.getElementById('app-client')
const baseUrl = el?.dataset?.baseUrl ?? ''
const API     = `${baseUrl}/open-payment`

const logoUrl     = el?.dataset?.appLogo    ?? ''
const company     = el?.dataset?.company    ?? ''
const website     = el?.dataset?.website    ?? ''
const appName     = el?.dataset?.appName    ?? ''
const appVersion  = el?.dataset?.appVersion ?? ''
const currentYear = new Date().getFullYear()

// ── Language dropdown ────────────────────────────────────────────────
const languages    = ref([])
const currentLocale = computed(() => (el?.dataset?.locale ?? 'en').toLowerCase())

const localeMap = {
  ar: 'sa', bsn: 'ba', de: 'de', en: 'us', 'en-gb': 'gb', es: 'es', fr: 'fr',
  he: 'il', hi: 'in', id: 'id', it: 'it', ja: 'jp', kr: 'kr', mt: 'mt',
  nl: 'nl', no: 'no', pt: 'pt', ru: 'ru', ta: 'in', tr: 'tr', vi: 'vn',
  'zh-hans': 'cn', 'zh-hant': 'tw',
}
function flagCodeFor(loc) {
  const lc = String(loc ?? '').toLowerCase()
  return localeMap[lc] ?? localeMap[lc.slice(0, 2)] ?? 'un'
}
async function loadLanguages() {
  try {
    const { data } = await http.get(`${baseUrl}/language/control`)
    languages.value = (data?.data ?? []).filter(l => Number(l.status) === 1)
  } catch { /* best-effort */ }
}
async function selectLang(lang) {
  try {
    await http.post(`${baseUrl}/lang/update`, { language: lang.locale })
    window.location.reload()
  } catch (e) {
    console.error('Language switch failed', e)
  }
}

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

const submitting = ref(false)
const captchaRef = ref(null)
const paying     = ref(false)
const showPayBtn = ref(true)
const showBackBtn  = ref(true)
const order        = ref(null)
const result       = reactive({ success: false, message: '', transactionId: '', currency: '', amount: '', gateway: '' })
const showStripeModal  = ref(false)
const stripeLoading    = ref(false)
const stripeSubmitting = ref(false)
const cardErrors       = reactive({ number: '', expiry: '', cvc: '' })
const cardComplete     = reactive({ number: false, expiry: false, cvc: false })
let   stripeInstance   = null
let   cardNumberEl     = null
let   clientSecret     = null

const form = reactive({
  name: '', email: '', mobile: '', company: '',
  address: '', city: '', state: '', zip: '', country: '',
  amount: '', currency: '', gateway: '', description: '',
})

// ── Currency ────────────────────────────────────────────────────────
const currencyOptions = ref([])
const enabledGateways = ref([])
const selectedCurrency = computed(() => currencyOptions.value.find(c => c.code === form.currency) ?? null)
const selectedCurrencySymbol = computed(() => selectedCurrency.value?.symbol ?? '')
const onCurrencyChange = (val) => { form.currency = val?.code ?? '' }

// ── Payment summary — server-calculated to avoid any rounding mismatch ──
const selectedGateway   = computed(() => enabledGateways.value.find(g => g.name === form.gateway) ?? null)
const processingFeeRate = computed(() => selectedGateway.value?.processing_fee ?? 0)

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
  Object.assign(result, { success: false, message: '', transactionId: '', currency: '', amount: '', gateway: '' })
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
  })
  step.value = 'result'
}

const submitForm = () => {
  alertStore.unsetAlert()

  try {
    openPaymentSchema.validateSync(form, { abortEarly: false })
  } catch (err) {
    const map = {}
    err.inner?.forEach(e => { if (e.path && !map[e.path]) map[e.path] = e.message })
    setErrors(map)
    return
  }

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
  const { data } = await http.post(`${API}/create`, { ...form, ...captchaPayload })
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
        if (!isNaN(confirmedTotal) && Math.abs(confirmedTotal - estimatedTotal) > 0.01) {
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
  const rzp = new window.Razorpay({ ...cfg, handler: verifyRazorpay })
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
    stripeInstance = window.Stripe(cfg.publishable_key)
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

    const res   = await http.get(`${baseUrl}/dependency/countries`)
    const match = (res.data?.data?.countries ?? []).find(c => c.code === code)
    if (match) onCountryChange(match)
  } catch { /* best-effort */ }
}

onMounted(async () => {
  loadLanguages()
  autoDetectCountry()

  // Load enabled gateways + active currencies from the backend
  try {
    const { data } = await http.get(`${API}/config`)
    const cfg = data.data
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

  const params  = new URLSearchParams(window.location.search)
  const status  = params.get('status')
  const orderId = params.get('order_id')
  const message = params.get('message')

  if (status === 'success' && orderId) {
    try {
      const { data } = await http.get(`${API}/order/${orderId}`)
      showResult(true, data.data?.order ?? { id: orderId })
    } catch { showResult(true, { id: orderId }) }
    window.history.replaceState({}, document.title, window.location.pathname)
  } else if (status === 'failed' || status === 'error') {
    showResult(false, null, message ? decodeURIComponent(message) : 'Payment failed')
    window.history.replaceState({}, document.title, window.location.pathname)
  } else if (status === 'pending') {
    showResult(false, null, message ? decodeURIComponent(message) : 'Payment is still processing. Please check back later.')
    window.history.replaceState({}, document.title, window.location.pathname)
  }
})
</script>

<style scoped>
/* ── Shared layout ──────────────────────────────────────────────── */
.op-container    { max-width: 980px; }
.op-nav-logo     { height: 60px; max-width: 220px; object-fit: contain; }
.op-lang-menu    { max-height: 320px; overflow-y: auto; }
.op-flag         { width: 1.5em; height: 1.5em; }
.op-right-bg     { background: #f8f9ff; }
.op-section-label { font-size: 0.7rem; letter-spacing: 0.08em; }
.op-detail-label  { font-size: 0.65rem; letter-spacing: 0.08em; }
.op-gw-desc       { font-size: 0.72rem; }
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
  min-height: calc(100vh - 114px);
  background: #f4f6f9;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 2rem 1rem 3rem;
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

/* ── Gateway radio cards ────────────────────────────────────────── */
.op-gateway-card {
  display: block;
  cursor: pointer;
  border: 1.5px solid #e5e7eb;
  border-radius: 8px;
  padding: 0.7rem 0.9rem;
  background: #fff;
  transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
}
.op-gateway-card input { display: none; }
.op-gateway-selected {
  border-color: var(--primary);
  background: var(--primary-rgba-10);
  box-shadow: 0 0 0 3px var(--primary-rgba-20);
}
.op-gateway-radio {
  width: 16px; height: 16px; border-radius: 50%;
  border: 2px solid #d1d5db; flex-shrink: 0;
  transition: border-color 0.2s, background 0.2s;
}
.op-gateway-selected .op-gateway-radio {
  border-color: var(--primary); background: var(--primary);
  box-shadow: inset 0 0 0 3px var(--primary-rgba-10);
}

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

/* ── Result icon ────────────────────────────────────────────────── */
.op-result-icon {
  width: 80px; height: 80px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center; color: white;
}
.op-result-success { background: var(--primary); box-shadow: 0 8px 24px var(--primary-rgba-40); }
.op-result-failed  { background: linear-gradient(135deg, #ef4444, #dc2626); box-shadow: 0 8px 24px rgba(239,68,68,0.35); }


/* ── Transition ─────────────────────────────────────────────────── */
.op-slide-enter-active, .op-slide-leave-active { transition: all 0.25s ease; }
.op-slide-enter-from { opacity: 0; transform: translateX(20px); }
.op-slide-leave-to   { opacity: 0; transform: translateX(-20px); }

/* ── Mobile ─────────────────────────────────────────────────────── */
@media (max-width: 767px) {
  .op-page { padding: 1.5rem 0.75rem 3rem; }
  .op-step-line { width: 28px; }
  .op-step-label { display: none; }
  .border-end { border-right: none !important; border-bottom: 1px solid #dee2e6 !important; }
}

</style>
