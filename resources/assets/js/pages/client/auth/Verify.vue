<template>
    <div>
        <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

        <template v-else>
            <!-- Step progress: numbered circles joined by a line (tick once a step is done) -->
            <ol v-if="progressSteps.length > 1" class="list-unstyled d-flex align-items-center gap-2 mb-4" :aria-label="__('message.verify_account')">
                <template v-for="(s, i) in progressSteps" :key="i">
                    <li v-if="i > 0" class="flex-grow-1 border-top border-2" :class="{ 'border-success': progressSteps[i - 1].done }" aria-hidden="true"></li>
                    <li class="d-flex align-items-center gap-2 text-nowrap text-2 font-weight-semibold"
                        :class="s.active || s.done ? 'text-color-dark' : 'text-muted'" :aria-current="s.active ? 'step' : undefined">
                        <span class="rounded-circle d-inline-flex align-items-center justify-content-center text-1 fw-bold"
                              style="width: 1.75rem; height: 1.75rem"
                              :class="s.done ? 'bg-success text-white' : s.active ? 'bg-color-primary text-white' : 'border'">
                            <i v-if="s.done" class="fas fa-check" aria-hidden="true"></i>
                            <template v-else>{{ i + 1 }}</template>
                        </span>
                        {{ s.label }}
                    </li>
                </template>
            </ol>

            <!-- OTP entry -->
            <form v-if="current" @submit.prevent="verify" novalidate>
                <h1 class="font-weight-bold text-color-dark text-7 line-height-2 mb-1">
                    {{ current.type === 'mobile' ? __('message.verify_mobile') : __('message.verify_email') }}
                </h1>
                <!-- Where the code went, masked (the user may have typed it wrong). -->
                <p class="text-3 mb-4">
                    {{ __('message.otp_enter_code_sent_to', { destination: maskedTarget }) }}
                    <template v-if="current.type === 'email'">{{ __('message.otp_check_spam') }}</template>
                </p>

                <OtpInput :label="__('message.verification_code')" :model-value="form.otp" :error="errors.otp"
                          :disabled="locked" @update:modelValue="onOtpInput" />

                <RecaptchaField ref="captchaRef" :action="captchaAction" class="mb-3" />

                <button type="submit" class="btn btn-primary btn-modern w-100" :disabled="saving || redirecting || locked">
                    <i v-if="saving || redirecting" class="fas fa-circle-notch fa-spin me-1" aria-hidden="true"></i>
                    {{ (saving || redirecting) ? __('message.verifying') : __('message.verify') }}
                </button>

                <!-- Resend (with countdown) and, for mobile, a voice call -->
                <div class="d-flex flex-wrap justify-content-center align-items-center gap-2 mt-3 text-2" aria-live="polite">
                    <span v-if="cooldown === 0">{{ __('message.didnt_get_code') }}</span>
                    <button type="button" class="btn btn-link text-decoration-none font-weight-semibold p-0 text-2"
                            :class="cooldown > 0 ? 'text-muted' : 'text-color-primary'"
                            :disabled="cooldown > 0 || locked" @click="resend('text')">
                        <template v-if="cooldown > 0">
                            <i class="far fa-clock me-1" aria-hidden="true"></i>{{ __('message.resend_code_in', { time: countdown }) }}
                        </template>
                        <template v-else>{{ __('message.resend_code') }}</template>
                    </button>
                    <template v-if="current.type === 'mobile'">
                        <span class="vr" aria-hidden="true"></span>
                        <button type="button" class="btn btn-link text-color-primary text-decoration-none font-weight-semibold p-0 text-2"
                                :disabled="cooldown > 0 || locked" @click="resend('voice')">
                            <i class="fas fa-phone-alt me-1" aria-hidden="true"></i>{{ __('message.otp_call') }}
                        </button>
                    </template>
                </div>
            </form>

            <p class="text-center text-2 mt-4 pt-3 border-top mb-0">
                <i class="far fa-circle-question me-1" aria-hidden="true"></i>{{ __('message.need_help') }}
                <a :href="`${baseUrl}/contact-us`" class="text-color-primary font-weight-semibold text-decoration-none" target="_blank">
                    {{ __('message.contact_support') }}
                </a>
            </p>
        </template>
    </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted, watch } from 'vue'
import { useForm } from 'vee-validate'
import { validateForm } from '@/helpers/formUtils.js'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { otpSchema } from '@/validations/client/authSchemas.js'
import { RecaptchaField } from '@recaptcha'
import OtpInput from './partials/OtpInput.vue'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import { useCooldown } from '@/core/composables/useCooldown'

const COMPONENT = 'client-page'
const COOLDOWN  = 120
const baseUrl = useBaseUrl()

const { errors, setErrors, setFieldError } = useForm()
const captchaRef    = ref(null)
const captchaAction = computed(() => current.value?.type === 'mobile' ? 'mobile_verify' : 'email_verify')
const form      = reactive({ otp: '' })
const loading   = ref(true)
const saving    = ref(false)
const redirecting = ref(false)
const eid       = ref('')
const locked    = ref(false)
const steps     = ref([])
const stepIndex = ref(0)
const { cooldown, start: startCooldown, stop: stopCooldown } = useCooldown(COOLDOWN)

const current = computed(() => steps.value[stepIndex.value] ?? null)

// "jo•••@gmail.com" / "•••• 3210": enough to recognise, not enough to leak.
const maskedTarget = computed(() => {
    const t = String(current.value?.target ?? '')
    if (current.value?.type === 'email') {
        const [user, domain] = t.split('@')
        return domain ? `${user.slice(0, 2)}•••@${domain}` : t
    }
    return t.length > 4 ? `•••• ${t.slice(-4)}` : t
})

// Steps for the progress bar: each pending verification + a final "all set".
const progressSteps = computed(() => steps.value.map((s, i) => ({
    label:  s.type === 'mobile' ? __('message.mobile') : __('message.email'),
    done:   i < stepIndex.value,
    active: i === stepIndex.value,
})))

// Resend cooldown as m:ss.
const countdown = computed(() => `${Math.floor(cooldown.value / 60)}:${String(cooldown.value % 60).padStart(2, '0')}`)

onMounted(async () => {
    try {
        const res  = await http.get(`/auth/verify-config`)
        const data = res.data?.data ?? {}

        // No eid = no verification session (the lockout middleware redirected us).
        if (data.redirect || !data.eid) {
            globalThis.location.href = data.redirect ?? `${baseUrl}/login`
            return
        }

        eid.value = data.eid ?? ''

        const pending = []
        if (!data.isMobileVerified) pending.push({ type: 'mobile', target: data.mobile })
        if (!data.isEmailVerified)  pending.push({ type: 'email',  target: data.email })

        // Honour the configured ordering preference (email-first vs mobile-first).
        if (data.verification_preference === 'email') {
            pending.sort((a, _b) => (a.type === 'email' ? -1 : 1))
        } else {
            pending.sort((a, _b) => (a.type === 'mobile' ? -1 : 1))
        }

        steps.value = pending

        if (!pending.length) {
            globalThis.location.href = `${baseUrl}/login`
            return
        }

        loading.value = false
        sendInitial()
    } catch (e) {
        loading.value = false
        onError(e)
    }
})

watch(stepIndex, () => captchaRef.value?.reset())

// 429 = max attempts reached: show the server's message, lock the form, then back to login.
function onError(e) {
    errorHandler(e, COMPONENT, { setErrors })
    if (e?.response?.status === 429 && !locked.value) {
        locked.value = true
        stopCooldown()
        setTimeout(() => { globalThis.location.href = `${baseUrl}/login` }, 5000)
    }
}

function onOtpInput(value) {
    form.otp = String(value).replace(/\D/g, '').slice(0, 6)
    setFieldError('otp', undefined)
}

// Send the OTP for the active step (initial send / on step change).
async function sendInitial() {
    const url = current.value.type === 'mobile' ? `${baseUrl}/otp/send` : `${baseUrl}/send-email`
    try {
        await http.post(url, { eid: eid.value })
        startCooldown()
    } catch (e) {
        onError(e)
    }
}

async function resend(type) {
    if (cooldown.value > 0) return
    try {
        const res = await http.post(`/resend_otp`, {
            eid: eid.value,
            default_type: current.value.type,
            type: current.value.type === 'mobile' ? type : null,
        })
        successHandler(res, COMPONENT)
        startCooldown()
    } catch (e) {
        onError(e)
    }
}

async function verify() {
    // Lock before the awaits below: a second submit during the captcha check would re-send the
    // already-used code and show "OTP not valid" even though the first request verified it.
    if (saving.value || redirecting.value) return
    saving.value = true
    const url = current.value.type === 'mobile' ? `${baseUrl}/otp/verify` : `${baseUrl}/email/verify`
    try {
        if (!await validateForm(otpSchema, { otp: form.otp }, setErrors)) return

        const captchaPayload = await captchaRef.value?.getPayload()
        if (!captchaRef.value?.disabled && !captchaPayload?.['g-recaptcha-response']) return

        await http.post(url, { eid: eid.value, otp: form.otp, ...captchaPayload })
        form.otp = ''

        if (stepIndex.value < steps.value.length - 1) {
            // No "verified" alert between steps: the progress bar ticks and the next code is sent.
            stepIndex.value++
            stopCooldown()
            sendInitial()
        } else {
            // All steps verified — straight to login (no in-page "all set" flash), which shows the
            // "registration complete" notice. Keep the button locked until the page unloads.
            redirecting.value = true
            stopCooldown()
            globalThis.location.href = `${baseUrl}/login?verified=1`
        }
    } catch (e) {
        if (e?.response?.data?.data?.show_v2_recaptcha) {
            captchaRef.value?.triggerFallback()
            return
        }
        captchaRef.value?.reset()
        onError(e)
    } finally {
        saving.value = false
    }
}
</script>
