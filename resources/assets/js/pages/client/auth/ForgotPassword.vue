<template>
    <AuthLayout>
        <form @submit.prevent="submit" novalidate>
            <ClientField name="email" type="email" :label="__('message.email_address')" required
                         autocomplete="email"
                         v-model="form.email" :error="errors.email"
                         @update:modelValue="setFieldError('email', undefined)" />

            <div class="mb-3">
                <RouterLink to="/login" class="text-color-primary fw-semibold text-2 text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i>{{ __('message.know_password') }}
                </RouterLink>
            </div>

            <Honeypot name="forgot" v-model="form.forgot" @ready="hpReady = $event" />

            <RecaptchaField ref="captchaRef" action="forgot" class="mb-3" />

            <button type="submit"
                    class="btn btn-dark btn-modern w-100 text-uppercase rounded-0 font-weight-bold text-3 py-3"
                    :disabled="saving || !hpReady">
                <i v-if="saving" class="fas fa-circle-notch fa-spin me-1"></i>
                {{ saving ? __('message.sending') : __('message.send_mail') }}
            </button>
        </form>
    </AuthLayout>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { forgotSchema } from '@/validations/client/authSchemas.js'
import AuthLayout from './partials/AuthLayout.vue'
import Honeypot from '@/components/Reusable/Honeypot.vue'
import { RecaptchaField } from '@recaptcha'

const COMPONENT = 'client-page'
const el      = document.getElementById('app-client')
const baseUrl = el?.dataset?.baseUrl ?? ''

const { errors, setErrors, setFieldError } = useForm()
const captchaRef = ref(null)
const form   = reactive({ email: '', forgot: {} })
const saving = ref(false)
const hpReady = ref(false)

async function submit() {
    try {
        forgotSchema.validateSync(form, { abortEarly: false })
    } catch (err) {
        const map = {}
        err.inner?.forEach(e => { if (e.path && !map[e.path]) map[e.path] = e.message })
        setErrors(map)
        return
    }

    saving.value = true
    try {
        const captchaPayload = await captchaRef.value?.getPayload()
        if (!captchaRef.value?.disabled && !captchaPayload?.['g-recaptcha-response']) {
            return
        }
        const res = await http.post(`${baseUrl}/password/email`, {
            email: form.email,
            forgot: form.forgot,
            ...captchaPayload,
        })
        successHandler(res, COMPONENT)
        form.email = ''
        captchaRef.value?.reset()
    } catch (e) {
        if (e?.response?.data?.data?.show_v2_recaptcha) {
            captchaRef.value?.triggerFallback()
            return
        }
        const serverErrors = e?.response?.data?.errors
        if (serverErrors?.email) {
            setErrors({ email: Array.isArray(serverErrors.email) ? serverErrors.email[0] : serverErrors.email })
        } else {
            errorHandler(e, COMPONENT)
        }
    } finally {
        saving.value = false
    }
}
</script>
