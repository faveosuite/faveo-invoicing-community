<template>
    <div>
        <!-- Sent: same wording whether or not the account exists (the server never says). -->
        <div v-if="sentTo" class="text-center" role="status">
            <i class="fas fa-envelope-open-text text-color-primary fs-1 mb-3" aria-hidden="true"></i>
            <h1 class="fw-bold text-body-emphasis fs-4 mb-2">{{ __('message.check_your_email') }}</h1>
            <p class="mb-1">{{ __('message.reset_link_sent_to', { email: sentTo }) }}</p>
            <p class="small mb-4">{{ __('message.otp_check_spam') }}</p>
            <button type="button" class="btn btn-primary w-100 py-2 fw-medium" @click="sentTo = ''">
                {{ __('message.resend_link') }}
            </button>
        </div>

        <template v-else>
            <!-- Own heading (not the route titleKey) so it goes away with the form once the link is sent. -->
            <h1 class="fw-bold text-body-emphasis fs-3 lh-sm mb-1">{{ __('message.forgot_password_title') }}</h1>
            <p class="text-body-secondary mb-4">{{ __('message.forgot_password_subtitle') }}</p>

            <form @submit.prevent="submit" novalidate>
                <ClientField name="email" type="email" :label="__('message.email_address')" required
                             autocomplete="email" :spellcheck="false"
                             v-model="form.email" :error="errors.email"
                             @update:modelValue="setFieldError('email', undefined)" />

                <Honeypot name="honeypot" v-model="form.honeypot" @ready="hpReady = $event" />

                <RecaptchaField ref="captchaRef" action="forgot" class="mb-3" />

                <button type="submit"
                        class="btn btn-primary w-100 py-2 fw-medium"
                        :disabled="saving || !hpReady">
                    <i v-if="saving" class="fas fa-circle-notch fa-spin me-1" aria-hidden="true"></i>
                    {{ saving ? __('message.sending') : __('message.send_reset_link') }}
                </button>
            </form>
        </template>

        <p class="text-center mt-4 mb-0">
            {{ __('message.remember_password') }}
            <RouterLink to="/login" class="text-color-primary fw-medium text-decoration-none">{{ __('message.log_in') }}</RouterLink>
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
const form   = reactive({ email: '', honeypot: {} })
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
            honeypot: form.honeypot,
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
            errorHandler(e, COMPONENT, { setErrors, excludeFields: ['honeypot'] })
        }
    } finally {
        saving.value = false
    }
}
</script>
