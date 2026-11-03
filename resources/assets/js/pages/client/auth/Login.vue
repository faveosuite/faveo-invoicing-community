<template>
  <div>
    <form @submit.prevent="submit" novalidate>
      <ClientField name="email_username" type="text" :label="__('message.email_or_username')" required
                   autocomplete="username" :spellcheck="false"
                   v-model="form.email_username" :error="errors.email_username"
                   @update:modelValue="setFieldError('email_username', undefined)"/>

      <ClientField name="password1" type="password" :label="__('message.password')" required
                   autocomplete="current-password"
                   v-model="form.password1" :error="errors.password1"
                   @update:modelValue="setFieldError('password1', undefined)"/>

      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <ClientCheckbox v-model="form.remember" :label="__('message.remember_me')"/>
        <RouterLink class="text-decoration-none text-color-primary font-weight-semibold text-2" to="/password/reset">
          {{ __('message.forgot-password') }}
        </RouterLink>
      </div>

      <Honeypot name="login" v-model="form.login" @ready="hpReady = $event" />

      <RecaptchaField ref="captchaRef" action="login" class="mb-3" />

      <button type="submit" class="btn btn-primary btn-modern w-100"
              :disabled="saving || !hpReady">
        <i v-if="saving" class="fas fa-circle-notch fa-spin me-1" aria-hidden="true"></i>{{ __('message.log_in') }}
      </button>
    </form>

    <SocialButtons :social="social" :base-url="baseUrl" :redirect="redirect"/>

    <p class="text-center text-3 mt-4 mb-0">
      {{ __('message.dont_have_account') }}
      <RouterLink to="/register" class="text-color-primary font-weight-semibold text-decoration-none">{{ __('message.create_account') }}</RouterLink>
    </p>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { errorHandler } from '@/helpers/responseHandler.js'
import { loginSchema } from '@/validations/client/authSchemas.js'
import { validateForm } from '@/helpers/formUtils.js'
import SocialButtons from './partials/SocialButtons.vue'
import Honeypot from '@/components/Reusable/Honeypot.vue'
import { RecaptchaField } from '@recaptcha'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import { useAlertStore } from '@/core/stores/alert.js'

const COMPONENT = 'client-page'
const route = useRoute()
const baseUrl = useBaseUrl()
const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : ''

const { errors, setErrors, setFieldError } = useForm()
const form = reactive({ email_username: '', password1: '', remember: false, login: {} })
const captchaRef = ref(null)
const saving = ref(false)
const hpReady = ref(false)
const social = ref({ google: 0, github: 0, twitter: 0, linkedin: 0 })

// Other pages send a one-off message here as a query flag (wording stays translated):
// the axios interceptor (session_expired), ResetPassword (reset_expired),
// Verify (verified), Register (registered) and the OAuth callback (social_error=<reason>).
const FLASH = {
  session_expired: ['warning', () => __('message.session_expired')],
  reset_expired:   ['warning', () => __('message.reset_link_expired')],
  verified:        ['success', () => __('message.registration_complete')],
  registered:      ['success', () => __('message.account_created_login')],
  social_error:    ['danger',  (reason) => __(`message.social_error.${reason}`)],
}

function showFlash() {
  const params = new URLSearchParams(globalThis.location.search)
  const key = Object.keys(FLASH).find(k => params.has(k))
  if (!key) return
  const [type, message] = FLASH[key]
  useAlertStore().setAlert({ type, message: message(params.get(key)), component_name: COMPONENT })
  // Strip the flag so a refresh or a failed login doesn't show it again.
  globalThis.history.replaceState({}, '', globalThis.location.pathname)
}

onMounted(async () => {
  showFlash()
  globalThis.document.querySelector('[name="email_username"]')?.focus()
  try {
    const res = await http.get('/auth/login-config')
    social.value = res.data?.data?.social ?? social.value
  } catch { /* config is best-effort */ }
})

async function submit() {
  if (!await validateForm(loginSchema, form, setErrors)) return

  saving.value = true
  try {
    const captchaPayload = await captchaRef.value?.getPayload()
    if (!captchaRef.value?.disabled && !captchaPayload?.['g-recaptcha-response']) {
      return
    }
    const res = await http.post('/login', {
      email_username: form.email_username,
      password1: form.password1,
      remember: form.remember ? 'on' : '',
      login: form.login,
      // The backend checks it's a same-site path and sends the user there once fully logged in (after any verify/2FA step).
      redirect: route.query.redirect,
      ...captchaPayload,
    })
    const target = res.data?.data?.redirect
    if (target) {
      globalThis.location.href = target
    } else {
      errorHandler({ response: { status: 400, data: { message: __('message.something_wrong') } } }, COMPONENT)
    }
  } catch (e) {
    if (e?.response?.data?.data?.show_v2_recaptcha) {
      captchaRef.value?.triggerFallback()
      return
    }
    captchaRef.value?.reset()
    errorHandler(e, COMPONENT, { setErrors, excludeFields: ['login'] })
  } finally {
    saving.value = false
  }
}
</script>
