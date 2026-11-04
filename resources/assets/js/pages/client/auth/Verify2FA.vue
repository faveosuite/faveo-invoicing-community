<template>
    <div>
        <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

        <template v-else>
            <!-- Heading changes with the mode (authenticator app / recovery code) -->
            <h1 class="fw-bold text-body-emphasis fs-3 lh-sm mb-1">
                {{ useRecovery ? __('message.use_recovery_code_title') : __('message.two_factor_check') }}
            </h1>
            <p class="text-body-secondary mb-4">{{ useRecovery ? __('message.enter_recovery_code_hint') : __('message.open_two_factor') }}</p>

            <!-- Authenticator code -->
            <form v-if="!useRecovery" @submit.prevent="submitTotp" novalidate>
                <OtpInput :label="__('message.authentication_code')" :model-value="form.code" :error="errors.code"
                          @update:modelValue="onTotpInput" />

                <Honeypot name="honeypot" v-model="form.honeypot" @ready="totpHpReady = $event" />

                <RecaptchaField ref="totpCaptchaRef" action="login_2fa" class="mb-3" />

                <button type="submit" class="btn btn-primary w-100 py-2 fw-medium" :disabled="saving || !totpHpReady">
                    <i v-if="saving" class="fas fa-circle-notch fa-spin me-1" aria-hidden="true"></i>{{ __('message.verify') }}
                </button>
            </form>

            <!-- Recovery code -->
            <form v-else @submit.prevent="submitRecovery" novalidate>
                <ClientField name="recovery_code" type="text" :label="__('message.recovery_code_label')"
                             autocomplete="off" :spellcheck="false"
                             v-model="form.recovery_code" :error="errors.recovery_code"
                             @update:modelValue="setFieldError('recovery_code', undefined)" />

                <Honeypot name="honeypot" v-model="form.honeypot" @ready="recHpReady = $event" />

                <RecaptchaField ref="recCaptchaRef" action="login_recovery" class="mb-3" />

                <button type="submit" class="btn btn-primary w-100 py-2 fw-medium" :disabled="saving || !recHpReady">
                    <i v-if="saving" class="fas fa-circle-notch fa-spin me-1" aria-hidden="true"></i>{{ __('message.verify') }}
                </button>
            </form>

            <!-- Secondary actions, one quiet row -->
            <div class="d-flex flex-wrap justify-content-center align-items-center gap-2 mt-4 small">
                <button type="button" class="border-0 bg-transparent p-0 text-color-primary fw-medium"
                        @click="toggleMode(!useRecovery)">
                    {{ useRecovery ? __('message.use_authentication_code') : __('message.use_recovery_code_title') }}
                </button>
                <span class="vr" aria-hidden="true"></span>
                <a :href="`${baseUrl}/login`" class="text-body-secondary text-decoration-none">{{ __('message.back_to_login') }}</a>
            </div>
        </template>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useForm } from 'vee-validate'
import { validateForm } from '@/helpers/formUtils.js'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { twoFaSchema, recoverySchema } from '@/validations/client/authSchemas.js'
import Honeypot from '@/components/Reusable/Honeypot.vue'
import { RecaptchaField } from '@recaptcha'
import OtpInput from './partials/OtpInput.vue'
import { useBaseUrl } from '@/core/composables/useBaseUrl'

const COMPONENT = 'client-page'
const baseUrl = useBaseUrl()

const { errors, setErrors, setFieldError } = useForm()
const totpCaptchaRef = ref(null)
const recCaptchaRef  = ref(null)
// code = 6-digit authenticator code; one honeypot (only one form is shown at a time)
const form        = reactive({ code: '', recovery_code: '', honeypot: {} })
const totpHpReady = ref(false)
const recHpReady  = ref(false)
const loading     = ref(true)
const saving      = ref(false)
const useRecovery = ref(false)

onMounted(async () => {
    try {
        const res = await http.get(`/auth/2fa-check`)
        // No active 2FA session → backend hands back a login redirect.
        const redirect = res.data?.data?.redirect
        if (redirect) {
            globalThis.location.href = redirect
            return
        }
    } catch (e) {
        globalThis.location.href = `${baseUrl}/login`
        return
    } finally {
        loading.value = false
    }
})

function onTotpInput(value) {
    form.code = String(value).replace(/\D/g, '').slice(0, 6)
    setFieldError('code', undefined)
}

function toggleMode(recovery) {
    useRecovery.value = recovery
    setErrors({})
}

async function submitTotp() {
    if (!await validateForm(twoFaSchema, { code: form.code }, setErrors)) return
    const captchaPayload = await totpCaptchaRef.value?.getPayload()
    if (!totpCaptchaRef.value?.disabled && !captchaPayload?.['g-recaptcha-response']) return
    await postVerify(`${baseUrl}/2fa/loginValidate`, { code: form.code, honeypot: form.honeypot, ...captchaPayload }, totpCaptchaRef)
}

async function submitRecovery() {
    if (!await validateForm(recoverySchema, { recovery_code: form.recovery_code }, setErrors)) return
    const captchaPayload = await recCaptchaRef.value?.getPayload()
    if (!recCaptchaRef.value?.disabled && !captchaPayload?.['g-recaptcha-response']) return
    await postVerify(`${baseUrl}/verify-recovery-code`, { recovery_code: form.recovery_code, honeypot: form.honeypot, ...captchaPayload }, recCaptchaRef)
}

async function postVerify(url, payload, captchaRef) {
    saving.value = true
    try {
        const res = await http.post(url, payload)
        const redirect = res.data?.data?.redirect
        if (redirect) {
            globalThis.location.href = redirect
        } else {
            captchaRef?.value?.reset()
            successHandler(res, COMPONENT)
        }
    } catch (e) {
        if (e?.response?.data?.data?.show_v2_recaptcha) {
            captchaRef?.value?.triggerFallback()
            return
        }
        captchaRef?.value?.reset()
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        saving.value = false
    }
}
</script>
