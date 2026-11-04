<template>
  <div>
    <form @submit.prevent="submit" novalidate>
      <div class="row">
        <div class="col-sm-6">
          <ClientField name="first_name" type="text" :label="__('message.first_name')" required
                       autocomplete="given-name"
                       v-model="form.first_name" :error="errors.first_name"
                       @update:modelValue="setFieldError('first_name', undefined)"/>
        </div>
        <div class="col-sm-6">
          <ClientField name="last_name" type="text" :label="__('message.last_name')" required
                       autocomplete="family-name"
                       v-model="form.last_name" :error="errors.last_name"
                       @update:modelValue="setFieldError('last_name', undefined)"/>
        </div>
      </div>

      <!-- Full width: side by side, the email and phone number get cut off in the form column. -->
      <ClientField name="email" type="email" :label="__('message.email_address')" required
                   autocomplete="email" :spellcheck="false"
                   v-model="form.email" :error="errors.email"
                   @update:modelValue="setFieldError('email', undefined)"/>

      <PhoneField name="mobile" :label="__('message.mobile')" required
                  :value="form.mobile" :error="errors.mobile"
                  :onChange="onMobileInput"
                  @countryChange="onMobileCountryChange"/>

      <div class="row">
        <div class="col-sm-6">
          <div class="position-relative">
            <ClientField name="password" type="password" :label="__('message.password')" required
                         autocomplete="new-password"
                         v-model="form.password" :error="errors.password"
                         @focus="passwordFocused = true" @blur="passwordFocused = false"
                         @update:modelValue="setFieldError('password', undefined)"/>
            <PasswordHints :password="form.password" :show="passwordFocused" :strict="!!errors.password"/>
          </div>
        </div>
        <div class="col-sm-6">
          <ClientField name="password_confirmation" type="password" :label="__('message.confirm_password')" required
                       autocomplete="new-password"
                       v-model="form.password_confirmation" :error="errors.password_confirmation"
                       @update:modelValue="setFieldError('password_confirmation', undefined)"/>
        </div>
      </div>

      <ClientCheckbox v-if="termsEnabled" v-model="form.terms" :error="errors.terms" class="mb-3"
                      @update:modelValue="setFieldError('terms', undefined)">
        {{ __('message.i_agree_to') }}
        <a :href="termsUrl" target="_blank" class="text-decoration-none">{{ __('message.terms_and_conditions') }}</a>
      </ClientCheckbox>

      <Honeypot name="honeypot" v-model="form.honeypot" @ready="hpReady = $event" />

      <RecaptchaField ref="captchaRef" action="register" class="mb-3" />

      <button type="submit" class="btn btn-primary w-100 py-2 fw-medium"
              :disabled="saving || !hpReady">
        <i v-if="saving" class="fas fa-circle-notch fa-spin me-1" aria-hidden="true"></i>{{ __('message.create_account') }}
      </button>
    </form>

    <SocialButtons :social="social" :base-url="baseUrl"/>

    <p class="text-center mt-4 mb-0">
      {{ __('message.already_have_account') }}
      <RouterLink to="/login" class="text-color-primary fw-medium text-decoration-none">{{ __('message.log_in') }}</RouterLink>
    </p>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { errorHandler } from '@/helpers/responseHandler.js'
import { registerSchema } from '@/validations/client/authSchemas.js'
import { scrollToFirstError } from '@/helpers/formUtils.js'
import SocialButtons from './partials/SocialButtons.vue'
import PasswordHints from './partials/PasswordHints.vue'
import Honeypot from '@/components/Reusable/Honeypot.vue'
import { RecaptchaField } from '@recaptcha'
import { useBaseUrl } from '@/core/composables/useBaseUrl'

const COMPONENT = 'client-page'
const baseUrl = useBaseUrl()
const router = useRouter()

const { errors, setErrors, setFieldError } = useForm()
const form = reactive({
  first_name: '', last_name: '', email: '',
  mobile: '', mobile_code: '', mobile_country_iso: '',
  password: '', password_confirmation: '', terms: false, honeypot: {},
})
const captchaRef = ref(null)
const saving = ref(false)
const hpReady = ref(false)
const passwordFocused = ref(false)

const social = ref({ google: 0, github: 0, twitter: 0, linkedin: 0 })
const termsEnabled = ref(false)
const termsUrl = ref('#')

onMounted(async () => {
  try {
    const res = await http.get('/auth/login-config')
    const data = res.data?.data ?? {}
    social.value = data.social ?? social.value
    termsEnabled.value = data.status?.terms ?? false
    termsUrl.value = data.apiKeys?.terms_url ?? '#'
  } catch { /* config is best-effort */ }
})

// PhoneField (intl-tel-input) drives the dial code via its own country selector.
function onMobileCountryChange({ iso, dialCode }) {
  form.mobile_country_iso = iso
  form.mobile_code = dialCode
}

function onMobileInput(value) {
  form.mobile = String(value).replace(/[^\d]/g, '')
  setFieldError('mobile', undefined)
}

async function submit() {
  const errs = {}
  try {
    registerSchema.validateSync(form, { abortEarly: false })
  } catch (err) {
    err.inner?.forEach(e => {
      if (e.path && !errs[e.path]) errs[e.path] = e.message
    })
  }
  if (termsEnabled.value && !form.terms) {
    errs.terms = __('message.login_validation.terms_conditions_required')
  }
  if (Object.keys(errs).length) {
    setErrors(errs)
    await scrollToFirstError()
    return
  }

  saving.value = true
  try {
    const captchaPayload = await captchaRef.value?.getPayload()
    if (!captchaRef.value?.disabled && !captchaPayload?.['g-recaptcha-response']) {
      return
    }
    const res = await http.post('/auth/register', {
      first_name: form.first_name,
      last_name: form.last_name,
      email: form.email,
      mobile: form.mobile,
      mobile_code: form.mobile_code,
      mobile_country_iso: form.mobile_country_iso,
      password: form.password,
      password_confirmation: form.password_confirmation,
      terms: termsEnabled.value ? form.terms : undefined,
      honeypot: form.honeypot,
      ...captchaPayload,
    })
    // "You're all set!" contradicts a verify step that follows — the verify page is the next step itself.
    if (Number(res.data?.data?.need_verify) === 1) {
      router.push('/verify')
      return
    }
    // No verification step: go straight to log in, which says the account was created.
    globalThis.location.href = `${baseUrl}/login?registered=1`
  } catch (e) {
    if (e?.response?.data?.data?.show_v2_recaptcha) {
      captchaRef.value?.triggerFallback()
      return
    }
    captchaRef.value?.reset()
    errorHandler(e, COMPONENT, { setErrors, excludeFields: ['honeypot'] })
  } finally {
    saving.value = false
  }
}
</script>
