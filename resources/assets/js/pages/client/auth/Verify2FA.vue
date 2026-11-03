<template>
    <div>
        <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

        <template v-else>
            <!-- Icon + heading change with the mode (authenticator app / recovery code) -->
            <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-color-light text-color-primary border mb-3"
                  style="width: 3rem; height: 3rem" aria-hidden="true">
                <i class="fas text-5" :class="useRecovery ? 'fa-key' : 'fa-mobile-screen'"></i>
            </span>
            <h1 class="font-weight-bold text-color-dark text-7 line-height-2 mb-1">
                {{ useRecovery ? __('message.use_recovery_code_title') : __('message.two_factor_authentication') }}
            </h1>
            <p class="text-3 mb-4">{{ useRecovery ? __('message.enter_recovery_code_hint') : __('message.open_two_factor') }}</p>

            <!-- Authenticator code -->
            <form v-if="!useRecovery" @submit.prevent="submitTotp" novalidate>
                <OtpInput :label="__('message.authentication_code')" :model-value="form.totp" :error="errors.totp"
                          @update:modelValue="onTotpInput" />

                <Honeypot name="2fa_code" v-model="form.code2fa" @ready="totpHpReady = $event" />

                <RecaptchaField ref="totpCaptchaRef" action="login_2fa" class="mb-3" />

                <button type="submit" class="btn btn-primary btn-modern w-100" :disabled="saving || !totpHpReady">
                    <i v-if="saving" class="fas fa-circle-notch fa-spin me-1" aria-hidden="true"></i>{{ __('message.verify') }}
                </button>
            </form>

            <!-- Recovery code -->
            <form v-else @submit.prevent="submitRecovery" novalidate>
                <ClientField name="rec_code" type="text" :label="__('message.recovery_code_label')"
                             autocomplete="off" :spellcheck="false"
                             v-model="form.rec_code" :error="errors.rec_code"
                             @update:modelValue="setFieldError('rec_code', undefined)" />
                <p class="text-2 text-muted mt-n2 mb-3">{{ __('message.recovery_code_once') }}</p>

                <Honeypot name="recovery_code" v-model="form.recovery" @ready="recHpReady = $event" />

                <RecaptchaField ref="recCaptchaRef" action="login_recovery" class="mb-3" />

                <button type="submit" class="btn btn-primary btn-modern w-100" :disabled="saving || !recHpReady">
                    <i v-if="saving" class="fas fa-circle-notch fa-spin me-1" aria-hidden="true"></i>{{ __('message.verify') }}
                </button>
            </form>

            <div class="d-flex flex-column align-items-center gap-2 mt-4 text-3">
                <button type="button" class="btn btn-link text-color-primary text-decoration-none font-weight-semibold p-0"
                        @click="toggleMode(!useRecovery)">
                    <i class="fas me-1" :class="useRecovery ? 'fa-mobile-screen' : 'fa-key'" aria-hidden="true"></i>
                    {{ useRecovery ? __('message.use_authentication_code') : __('message.login_recovery_code') }}
                </button>
                <a :href="`${baseUrl}/login`" class="text-decoration-none">{{ __('message.back_to_login') }}</a>
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
const form        = reactive({ totp: '', rec_code: '', code2fa: {}, recovery: {} })
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
    form.totp = String(value).replace(/\D/g, '').slice(0, 6)
    setFieldError('totp', undefined)
}

function toggleMode(recovery) {
    useRecovery.value = recovery
    setErrors({})
}

async function submitTotp() {
    if (!await validateForm(twoFaSchema, { totp: form.totp }, setErrors)) return
    const captchaPayload = await totpCaptchaRef.value?.getPayload()
    if (!totpCaptchaRef.value?.disabled && !captchaPayload?.['g-recaptcha-response']) return
    await postVerify(`${baseUrl}/2fa/loginValidate`, { totp: form.totp, '2fa_code': form.code2fa, ...captchaPayload }, totpCaptchaRef)
}

async function submitRecovery() {
    if (!await validateForm(recoverySchema, { rec_code: form.rec_code }, setErrors)) return
    const captchaPayload = await recCaptchaRef.value?.getPayload()
    if (!recCaptchaRef.value?.disabled && !captchaPayload?.['g-recaptcha-response']) return
    await postVerify(`${baseUrl}/verify-recovery-code`, { rec_code: form.rec_code, recovery_code: form.recovery, ...captchaPayload }, recCaptchaRef)
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
