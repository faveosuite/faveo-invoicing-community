<template>
  <div>
    <form @submit.prevent="submit" novalidate>
      <ClientField name="login" type="text" :label="__('message.email_or_username')" required
                   autocomplete="username" :spellcheck="false"
                   v-model="form.login" :error="errors.login"
                   @update:modelValue="setFieldError('login', undefined)"/>

      <ClientField name="password" type="password" :label="__('message.password')" required
                   autocomplete="current-password"
                   v-model="form.password" :error="errors.password"
                   @update:modelValue="setFieldError('password', undefined)"/>

      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <ClientCheckbox v-model="form.remember" :label="__('message.remember_me')"/>
        <RouterLink class="text-decoration-none text-color-primary fw-medium small" to="/password/reset">
          {{ __('message.forgot_password_link') }}
        </RouterLink>
      </div>

      <Honeypot name="honeypot" v-model="form.honeypot" @ready="hpReady = $event" />

      <RecaptchaField ref="captchaRef" action="login" class="mb-3" />

      <button type="submit" class="btn btn-primary w-100 py-2 fw-medium"
              :disabled="saving || !hpReady">
        <i v-if="saving" class="fas fa-circle-notch fa-spin me-1" aria-hidden="true"></i>{{ __('message.log_in') }}
      </button>
    </form>

    <SocialButtons :social="social" :base-url="baseUrl" :redirect="redirect"/>

    <p class="text-center mt-4 mb-0">
      {{ __('message.dont_have_account') }}
      <RouterLink to="/register" class="text-color-primary fw-medium text-decoration-none">{{ __('message.create_account') }}</RouterLink>
    </p>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
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
const router = useRouter()
const baseUrl = useBaseUrl()
const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : ''

const { errors, setErrors, setFieldError } = useForm()
// login = email or username
const form = reactive({ login: '', password: '', remember: false, honeypot: {} })
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
  verified:        ['success', () => __('message.account_created_login')],
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
  globalThis.document.querySelector('[name="login"]')?.focus()
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
      login: form.login,
      password: form.password,
      remember: form.remember,
      honeypot: form.honeypot,
      // The backend checks it's a same-site path and sends the user there once fully logged in (after any verify/2FA step).
      redirect: route.query.redirect,
      ...captchaPayload,
    })
    const target = res.data?.data?.redirect
    // Verify / 2FA pages are part of this app and nobody is signed in yet: switch in place.
    // A finished login reloads, so the app boots with the signed-in user.
    const step = target?.startsWith(baseUrl) ? target.slice(baseUrl.length) : ''
    if (step === '/verify' || step === '/verify-2fa') {
      router.push(step)
    } else if (target) {
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
    errorHandler(e, COMPONENT, { setErrors, excludeFields: ['honeypot'] })
  } finally {
    saving.value = false
  }
}
</script>
