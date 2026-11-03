<template>
    <div>
        <!-- Sent: same wording whether or not the account exists (the server never says). -->
        <div v-if="sentTo" class="text-center" role="status">
            <i class="fas fa-envelope-open-text text-color-primary text-10 mb-3" aria-hidden="true"></i>
            <h2 class="font-weight-bold text-color-dark text-5 mb-2">{{ __('message.check_your_email') }}</h2>
            <p class="text-3 mb-1">{{ __('message.reset_link_sent_to', { email: sentTo }) }}</p>
            <p class="text-2 mb-4">{{ __('message.otp_check_spam') }}</p>
            <button type="button" class="btn btn-primary btn-modern w-100" @click="sentTo = ''">
                {{ __('message.resend_link') }}
            </button>
        </div>

        <form v-else @submit.prevent="submit" novalidate>
            <ClientField name="email" type="email" :label="__('message.email_address')" required
                         autocomplete="email" :spellcheck="false"
                         v-model="form.email" :error="errors.email"
                         @update:modelValue="setFieldError('email', undefined)" />

            <Honeypot name="forgot" v-model="form.forgot" @ready="hpReady = $event" />

            <RecaptchaField ref="captchaRef" action="forgot" class="mb-3" />

            <button type="submit"
                    class="btn btn-primary btn-modern w-100"
                    :disabled="saving || !hpReady">
                <i v-if="saving" class="fas fa-circle-notch fa-spin me-1" aria-hidden="true"></i>
                {{ saving ? __('message.sending') : __('message.send_reset_link') }}
            </button>
        </form>

        <p class="text-center text-3 mt-4 mb-0">
            <RouterLink to="/login" class="text-color-primary font-weight-semibold text-decoration-none">
                <i class="fas fa-arrow-left me-1" aria-hidden="true"></i>{{ __('message.back_to_login') }}
            </RouterLink>
        </p>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useForm } from 'vee-validate'
import { validateForm } from '@/helpers/formUtils.js'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { errorHandler } from '@/helpers/responseHandler.js'
import { forgotSchema } from '@/validations/client/authSchemas.js'
import Honeypot from '@/components/Reusable/Honeypot.vue'
import { RecaptchaField } from '@recaptcha'

const COMPONENT = 'client-page'

const { errors, setErrors, setFieldError } = useForm()
const captchaRef = ref(null)
const form   = reactive({ email: '', forgot: {} })
const saving = ref(false)
const hpReady = ref(false)
// Email the link was requested for; set = show the "Check your email" state.
const sentTo = ref('')

async function submit() {
    if (!await validateForm(forgotSchema, form, setErrors)) return

    saving.value = true
    try {
        const captchaPayload = await captchaRef.value?.getPayload()
        if (!captchaRef.value?.disabled && !captchaPayload?.['g-recaptcha-response']) {
            return
        }
        await http.post(`/password/email`, {
            email: form.email,
            forgot: form.forgot,
            ...captchaPayload,
        })
        // Keep form.email so "Resend link" goes back to a prefilled form.
        sentTo.value = form.email
    } catch (e) {
        if (e?.response?.data?.data?.show_v2_recaptcha) {
            captchaRef.value?.triggerFallback()
            return
        }
        captchaRef.value?.reset()
        const serverErrors = e?.response?.data?.errors
        if (serverErrors?.email) {
            setErrors({ email: Array.isArray(serverErrors.email) ? serverErrors.email[0] : serverErrors.email })
        } else {
            errorHandler(e, COMPONENT, { setErrors, excludeFields: ['forgot'] })
        }
    } finally {
        saving.value = false
    }
}
</script>
