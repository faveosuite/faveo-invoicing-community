<template>
  <div class="login-register py-4">
    <div class="row justify-content-center">

      <!-- ─────────────────────────── LOGIN ─────────────────────────── -->
      <div class="col-md-6 col-lg-5 mb-5 mb-lg-0">
        <h2 class="font-weight-bold text-5 mb-3">{{ __('message.login') }}</h2>
        <form @submit.prevent="submitLogin" novalidate>
          <ClientField name="email_username" type="text" :label="__('message.email_address')" required
                       autocomplete="username"
                       v-model="loginForm.email_username" :error="loginErrors.email_username"
                       @update:modelValue="loginSetFieldError('email_username', undefined)"/>

          <ClientField name="password1" type="password" :label="__('message.password')" required
                       autocomplete="current-password"
                       v-model="loginForm.password1" :error="loginErrors.password1"
                       @update:modelValue="loginSetFieldError('password1', undefined)"/>

          <div class="d-flex justify-content-between align-items-center mb-3">
            <ClientCheckbox v-model="loginForm.remember" :label="__('message.remember_me')"/>
            <RouterLink
                class="text-decoration-none text-color-primary text-color-hover-primary font-weight-semibold text-2"
                to="/password/reset">{{ __('message.forgot-password') }} ?
            </RouterLink>
          </div>

          <Honeypot name="login" v-model="loginForm.login" @ready="loginHpReady = $event" />

          <RecaptchaField ref="loginCaptchaRef" action="login" class="mb-3" />

          <button type="submit"
                  class="btn btn-dark btn-modern w-100 text-uppercase rounded-0 font-weight-bold text-3 py-3"
                  :disabled="loggingIn || !loginHpReady" data-loading-text="Loading...">
            <i v-if="loggingIn" class="fas fa-circle-notch fa-spin me-1"></i>{{ __('message.login') }}
          </button>

          <SocialButtons :social="social" :base-url="baseUrl"/>
        </form>
      </div>

      <!-- ────────────────────────── REGISTER ────────────────────────── -->
      <div class="col-md-6 col-lg-5">
        <h2 class="font-weight-bold text-5 mb-3">{{ __('message.register') }}</h2>
        <form @submit.prevent="submitRegister" novalidate>
          <div class="row">
            <div class="col-md-6">
              <ClientField name="first_name" type="text" :label="__('message.first_name')" required
                           v-model="regForm.first_name" :error="regErrors.first_name"
                           @update:modelValue="regSetFieldError('first_name', undefined)"/>
            </div>
            <div class="col-md-6">
              <ClientField name="last_name" type="text" :label="__('message.last_name')" required
                           v-model="regForm.last_name" :error="regErrors.last_name"
                           @update:modelValue="regSetFieldError('last_name', undefined)"/>
            </div>
          </div>

          <ClientField name="email" type="email" :label="__('message.email_address')" required
                       v-model="regForm.email" :error="regErrors.email"
                       @update:modelValue="regSetFieldError('email', undefined)"/>

          <ClientField name="company" type="text" :label="__('message.company')" required
                       v-model="regForm.company" :error="regErrors.company"
                       @update:modelValue="regSetFieldError('company', undefined)"/>

          <ClientField name="address" type="textarea" :label="__('message.address')" required
                       v-model="regForm.address" :error="regErrors.address"
                       @update:modelValue="regSetFieldError('address', undefined)"/>

          <DynamicSelect name="country" :label="__('message.country')" required
                         :apiEndpoint="`${baseUrl}/dependency/countries`"
                         dataKey="countries"
                         :value="regForm.country"
                         :onChange="onCountryChange"
                         :error="regErrors.country"/>

          <PhoneField name="mobile" :label="__('message.mobile')" required
                      :value="regForm.mobile" :error="regErrors.mobile"
                      :onChange="onMobileInput"
                      @countryChange="onMobileCountryChange"/>

          <div class="row">
            <div class="col-md-6">
              <ClientField name="password" type="password" :label="__('message.password')" required
                           autocomplete="new-password"
                           v-model="regForm.password" :error="regErrors.password"
                           @focus="passwordFocused = true" @blur="passwordFocused = false"
                           @update:modelValue="regSetFieldError('password', undefined)"/>
            </div>
            <div class="col-md-6">
              <ClientField name="password_confirmation" type="password" :label="__('message.confirm_password')" required
                           autocomplete="new-password"
                           v-model="regForm.password_confirmation" :error="regErrors.password_confirmation"
                           @update:modelValue="regSetFieldError('password_confirmation', undefined)"/>
            </div>
          </div>

          <div v-if="passwordFocused" class="mb-3 text-1">
            <strong class="d-block mb-1 text-dark">{{ __('message.password_requirements') }}</strong>
            <ul class="list-unstyled mb-0">
              <li v-for="c in checklist" :key="c.key" :class="c.ok ? 'text-success' : 'text-danger'">
                <i class="fas" :class="c.ok ? 'fa-check' : 'fa-times'"></i> {{ c.label }}
              </li>
            </ul>
          </div>

          <ClientCheckbox v-if="termsEnabled" v-model="regForm.terms" :error="regErrors.terms"
                          @update:modelValue="regSetFieldError('terms', undefined)">
            {{ __('message.i_agree_to') }}
            <a :href="termsUrl" target="_blank" class="text-decoration-none">{{
                __('message.terms_and_conditions')
              }}</a>
          </ClientCheckbox>

          <Honeypot name="registerForm" v-model="regForm.registerForm" @ready="regHpReady = $event" />

          <RecaptchaField ref="regCaptchaRef" action="register" class="mb-3" />

          <button type="submit"
                  class="btn btn-dark btn-modern w-100 text-uppercase rounded-0 font-weight-bold text-3 py-3 mt-3"
                  :disabled="registering || !regHpReady" data-loading-text="Loading...">
            <i v-if="registering" class="fas fa-circle-notch fa-spin me-1"></i>{{ __('message.register') }}
          </button>
        </form>
      </div>

    </div>
  </div>
</template>

<script setup>
import {reactive, ref, computed, onMounted} from 'vue'
import { useRoute } from 'vue-router'
import {useForm} from 'vee-validate'
import http from '@/plugins/axios'
import {__} from '@/plugins/i18n'
import {successHandler, errorHandler} from '@/helpers/responseHandler.js'
import {loginSchema, registerSchema, passwordChecks} from '@/validations/client/authSchemas.js'
import { validateForm, scrollToFirstError } from '@/helpers/formUtils.js'
import SocialButtons from './partials/SocialButtons.vue'
import Honeypot from '@/components/Reusable/Honeypot.vue'
import { RecaptchaField } from '@recaptcha'
import { useBaseUrl } from '@/core/composables/useBaseUrl'

const route     = useRoute()
const COMPONENT = 'client-page'
const baseUrl = useBaseUrl()

// Separate vee-validate instances so the two forms don't share error state.
const {errors: loginErrors, setErrors: loginSetErrors, setFieldError: loginSetFieldError} = useForm()
const {errors: regErrors, setErrors: regSetErrors, setFieldError: regSetFieldError} = useForm()

const loginForm = reactive({email_username: '', password1: '', remember: false, login: {}})
const regForm = reactive({
  first_name: '', last_name: '', email: '', company: '', address: '',
  country: null, mobile: '', mobile_code: '', mobile_country_iso: '',
  password: '', password_confirmation: '', terms: false, registerForm: {},
})

const loginCaptchaRef = ref(null)
const regCaptchaRef   = ref(null)
const loggingIn = ref(false)
const registering = ref(false)
const loginHpReady = ref(false)
const regHpReady = ref(false)

const social = ref({google: 0, github: 0, twitter: 0, linkedin: 0})
const termsEnabled = ref(false)
const termsUrl = ref('#')

const checklist = computed(() => {
  const c = passwordChecks(regForm.password)
  return [
    {key: 'length', ok: c.length, label: __('message.pwd_req_length')},
    {key: 'upper', ok: c.upper, label: __('message.pwd_req_upper')},
    {key: 'lower', ok: c.lower, label: __('message.pwd_req_lower')},
    {key: 'number', ok: c.number, label: __('message.pwd_req_number')},
    {key: 'special', ok: c.special, label: __('message.pwd_req_special')},
  ]
})

// Show the requirements list only while the password field is focused.
const passwordFocused = ref(false)

onMounted(async () => {
  try {
    const res = await http.get(`/auth/login-config`)
    const data = res.data?.data ?? {}
    social.value = data.social ?? social.value
    termsEnabled.value = data.status?.terms ?? false
    termsUrl.value = data.apiKeys?.terms_url ?? '#'

    const detectedIso = data.location?.iso_code
    if (detectedIso) prefillCountry(detectedIso, data.location?.country)
  } catch { /* config is best-effort */
  }
})

// Pre-select the geo-detected country as a full {id, name, code} object so it
// both displays correctly and satisfies the required-select validation.
async function prefillCountry(iso, name) {
  if (!name) return
  try {
    const res = await http.get(`/dependency/countries`, {
      params: {'search-query': name, page: 1, paginate: 1},
    })
    const list = res.data?.data?.countries ?? []
    const match = list.find(c => c.code === String(iso).toUpperCase())
    if (match) regForm.country = match
  } catch { /* prefill is best-effort */
  }
}

// DynamicSelect emits the full {id, name, code} option object.
function onCountryChange(value) {
  regSetFieldError('country', undefined)
  regForm.country = value
}

// PhoneField (intl-tel-input) drives the dial code via its own country selector.
function onMobileCountryChange({iso, dialCode}) {
  regForm.mobile_country_iso = iso
  regForm.mobile_code = dialCode
}

function onMobileInput(value) {
  regForm.mobile = String(value).replace(/[^\d]/g, '')
  regSetFieldError('mobile', undefined)
}

async function submitLogin() {
  if (!await validateForm(loginSchema, loginForm, loginSetErrors)) return

  loggingIn.value = true
  try {
    const captchaPayload = await loginCaptchaRef.value?.getPayload()
    if (!loginCaptchaRef.value?.disabled && !captchaPayload?.['g-recaptcha-response']) {
      return
    }
    const res = await http.post(`/login`, {
      email_username: loginForm.email_username,
      password1: loginForm.password1,
      remember: loginForm.remember ? 'on' : '',
      login: loginForm.login,
      ...captchaPayload,
    })
    const redirect = res.data?.data?.redirect
    if (redirect) {
      const redirectPath = route.query.redirect
      if (redirectPath) {
        globalThis.location.href = `${baseUrl}${redirectPath}`
      } else {
        globalThis.location.href = redirect
      }
    } else {
      errorHandler({response: {status: 400, data: {message: __('message.something_wrong')}}}, COMPONENT)
    }
  } catch (e) {
    if (e?.response?.data?.data?.show_v2_recaptcha) {
      loginCaptchaRef.value?.triggerFallback()
      return
    }
    errorHandler(e, COMPONENT, { setErrors: loginSetErrors })
  } finally {
    loggingIn.value = false
  }
}

async function submitRegister() {
  const errs = {}
  try {
    registerSchema.validateSync(regForm, {abortEarly: false})
  } catch (err) {
    err.inner?.forEach(e => {
      if (e.path && !errs[e.path]) errs[e.path] = e.message
    })
  }
  if (termsEnabled.value && !regForm.terms) {
    errs.terms = __('message.login_validation.terms_conditions_required')
  }
  if (Object.keys(errs).length) {
    regSetErrors(errs)
    await scrollToFirstError()
    return
  }

  registering.value = true
  try {
    const captchaPayload = await regCaptchaRef.value?.getPayload()
    if (!regCaptchaRef.value?.disabled && !captchaPayload?.['g-recaptcha-response']) {
      return
    }
    const res = await http.post(`/auth/register`, {
      first_name: regForm.first_name,
      last_name: regForm.last_name,
      email: regForm.email,
      company: regForm.company,
      address: regForm.address,
      country: regForm.country?.code ?? '',
      mobile: regForm.mobile,
      mobile_code: regForm.mobile_code,
      mobile_country_iso: regForm.mobile_country_iso,
      password: regForm.password,
      password_confirmation: regForm.password_confirmation,
      terms: termsEnabled.value ? regForm.terms : undefined,
      registerForm: regForm.registerForm,
      ...captchaPayload,
    })
    successHandler(res, COMPONENT)
    const needVerify = Number(res.data?.data?.need_verify) === 1
    setTimeout(() => {
      globalThis.location.href = `${baseUrl}/${needVerify ? 'verify' : 'login'}`
    }, 1200)
  } catch (e) {
    if (e?.response?.data?.data?.show_v2_recaptcha) {
      regCaptchaRef.value?.triggerFallback()
      return
    }
    errorHandler(e, COMPONENT, { setErrors: regSetErrors })
  } finally {
    registering.value = false
  }
}
</script>
