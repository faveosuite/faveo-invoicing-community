<template>
    <div>
        <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

        <form v-else @submit.prevent="submit" novalidate>
            <div class="position-relative">
                <ClientField name="password" type="password" :label="__('message.new_password')" required
                             autocomplete="new-password"
                             v-model="form.password" :error="errors.password"
                             @focus="passwordFocused = true" @blur="passwordFocused = false"
                             @update:modelValue="setFieldError('password', undefined)" />
                <PasswordHints :password="form.password" :show="passwordFocused" :strict="!!errors.password" />
            </div>

            <ClientField name="password_confirmation" type="password" :label="__('message.confirm_password')" required
                         autocomplete="new-password"
                         v-model="form.password_confirmation" :error="errors.password_confirmation"
                         @update:modelValue="setFieldError('password_confirmation', undefined)" />

            <Honeypot name="reset" v-model="form.reset" @ready="hpReady = $event" />

            <RecaptchaField ref="captchaRef" action="reset" class="mb-3" />

            <button type="submit"
                    class="btn btn-primary btn-modern w-100"
                    :disabled="saving || !hpReady">
                <i v-if="saving" class="fas fa-circle-notch fa-spin me-1" aria-hidden="true"></i>
                {{ __('message.reset_password') }}
            </button>
        </form>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import { validateForm } from '@/helpers/formUtils.js'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { resetSchema } from '@/validations/client/authSchemas.js'
import Honeypot from '@/components/Reusable/Honeypot.vue'
import PasswordHints from './partials/PasswordHints.vue'
import { RecaptchaField } from '@recaptcha'
import { useBaseUrl } from '@/core/composables/useBaseUrl'

const COMPONENT = 'client-page'
const baseUrl = useBaseUrl()
const route   = useRoute()
const router  = useRouter()

const { errors, setErrors, setFieldError } = useForm()
const captchaRef = ref(null)
const form = reactive({ token: route.params.token, email: '', password: '', password_confirmation: '', reset: {} })

const loading        = ref(true)
const saving         = ref(false)
const hpReady        = ref(false)

// Password hints popover is shown while the password field has focus.
const passwordFocused = ref(false)

onMounted(async () => {
    try {
        const res  = await http.get(`/auth/reset-validate/${route.params.token}`)
        const data = res.data?.data

        // 2FA-protected reset → backend tells us to verify first.
        if (data?.redirect) {
            globalThis.location.href = data.redirect
            return
        }

        // showResetForm() returns the payload wrapped in an array: [{ reset_token, email }]
        const payload = Array.isArray(data) ? data[0] : data
        form.email = payload?.email ?? ''
        form.token = payload?.reset_token ?? route.params.token
        loading.value = false
    } catch {
        // Expired/invalid link: the login page shows why (layout clears alerts on navigation).
        router.replace({ path: '/login', query: { reset_expired: 1 } })
    }
})

async function submit() {
    if (!await validateForm(resetSchema, form, setErrors)) return

    saving.value = true
    try {
        const captchaPayload = await captchaRef.value?.getPayload()
        if (!captchaRef.value?.disabled && !captchaPayload?.['g-recaptcha-response']) {
            return
        }
        const res = await http.post(`/password/reset`, {
            token: form.token,
            email: form.email,
            password: form.password,
            password_confirmation: form.password_confirmation,
            reset: form.reset,
            ...captchaPayload,
        })
        successHandler(res, COMPONENT)
        const redirect = res.data?.data?.redirect
        setTimeout(() => { globalThis.location.href = redirect || `${baseUrl}/login` }, 1500)
    } catch (e) {
        if (e?.response?.data?.data?.show_v2_recaptcha) {
            captchaRef.value?.triggerFallback()
            return
        }
        captchaRef.value?.reset()
        errorHandler(e, COMPONENT, { setErrors, excludeFields: ['reset'] })
    } finally {
        saving.value = false
    }
}
</script>
