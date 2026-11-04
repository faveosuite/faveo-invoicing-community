<template>
    <div>
        <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

        <template v-else>
            <!-- Step progress: icon circles (Mobile / Email / Complete) joined by a line; a tick once done -->
            <ol v-if="steps.length > 1" class="list-unstyled d-flex align-items-start mx-auto mb-5" style="max-width: 18rem"
                :aria-label="__('message.verify_account')">
                <template v-for="(s, i) in progressSteps" :key="s.key">
                    <li v-if="i > 0" class="flex-grow-1 rounded-pill" style="height: 2px; margin-top: 1.0625rem"
                        :class="progressSteps[i - 1].done ? 'bg-success' : 'bg-secondary-subtle'" aria-hidden="true"></li>
                    <li class="d-flex flex-column align-items-center text-center flex-shrink-0" style="width: 4.5rem"
                        :aria-current="s.active ? 'step' : undefined">
                        <span class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2"
                              style="width: 2.25rem; height: 2.25rem"
                              :style="s.active ? { boxShadow: '0 0 0 4px rgba(var(--bs-primary-rgb), .18)' } : undefined"
                              :class="s.done ? 'bg-success text-white' : s.active ? 'bg-color-primary text-white' : 'border border-2 text-body-secondary'">
                            <i :class="s.done ? 'fas fa-check' : s.icon" aria-hidden="true"></i>
                        </span>
                        <span class="small fw-semibold lh-sm" :class="s.done ? 'text-success' : s.active ? 'text-body-emphasis' : 'text-body-secondary'">{{ s.label }}</span>
                    </li>
                </template>
            </ol>

            <!-- OTP entry -->
            <form v-if="current" @submit.prevent="verify" novalidate>
                <h1 class="fw-bold text-body-emphasis fs-3 lh-sm mb-1">
                    {{ current.type === 'mobile' ? __('message.verify_mobile') : __('message.verify_email') }}
                </h1>
                <!-- Where the code went, masked (the user may have typed it wrong). -->
                <p class="text-body-secondary mb-4">
                    {{ sentTo[0] }}<span class="fw-medium text-body-emphasis text-nowrap" dir="ltr">{{ maskedTarget }}</span>{{ sentTo[1] }}
                    <template v-if="current.type === 'email'">{{ __('message.otp_check_spam') }}</template>
                </p>

                <OtpInput :label="__('message.verification_code')" :model-value="form.otp" :error="errors.otp"
                          :disabled="locked" @update:modelValue="onOtpInput" />

                <RecaptchaField ref="captchaRef" :action="captchaAction" class="mb-3" />

                <button type="submit" class="btn btn-primary w-100 py-2 fw-medium" :disabled="saving || redirecting || locked">
                    <i v-if="saving || redirecting" class="fas fa-circle-notch fa-spin me-1" aria-hidden="true"></i>
                    {{ (saving || redirecting) ? __('message.verifying') : __('message.verify') }}
                </button>

                <!-- Resend: one muted line while waiting; once allowed, the question and its actions on one line -->
                <p class="text-center small mt-4 mb-0" aria-live="polite">
                    <span v-if="cooldown > 0" class="text-body-secondary">
                        <i class="far fa-clock me-1" aria-hidden="true"></i>{{ __('message.resend_code_in', { time: countdown }) }}
                    </span>
                    <span v-else class="d-inline-flex flex-wrap justify-content-center align-items-center column-gap-2 row-gap-1">
                        <span class="text-body-secondary">{{ __('message.didnt_get_code') }}</span>
                        <button type="button" class="btn btn-link text-color-primary text-decoration-none fw-medium p-0 small"
                                :disabled="locked" @click="resend('text')">{{ __('message.resend_code') }}</button>
                        <template v-if="current.type === 'mobile'">
                            <span class="text-body-secondary">{{ __('message.or') }}</span>
                            <button type="button" class="btn btn-link text-color-primary text-decoration-none fw-medium p-0 small"
                                    :disabled="locked" @click="resend('voice')">
                                <i class="fas fa-phone-alt me-1" aria-hidden="true"></i>{{ __('message.otp_call') }}
                            </button>
                        </template>
                    </span>
                </p>
            </form>

            <!-- Closing row: leave, or get help -->
            <div class="d-flex flex-wrap justify-content-center align-items-center gap-2 mt-4 small">
                <a :href="`${baseUrl}/login`" class="text-body-secondary text-decoration-none">{{ __('message.back_to_login') }}</a>
                <span class="vr" aria-hidden="true"></span>
                <span>
                    {{ __('message.need_help') }}
                    <a :href="`${baseUrl}/contact-us`" class="text-color-primary fw-medium text-decoration-none" target="_blank">
                        {{ __('message.contact_support') }}
                    </a>
                </span>
            </div>
        </template>
    </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted, watch } from 'vue'
import { useForm } from 'vee-validate'
import { validateForm } from '@/helpers/formUtils.js'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { errorHandler } from '@/helpers/responseHandler.js'
import { otpSchema } from '@/validations/client/authSchemas.js'
import { RecaptchaField } from '@recaptcha'
import OtpInput from './partials/OtpInput.vue'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import { useCooldown } from '@/core/composables/useCooldown'

const COMPONENT = 'client-page'
const COOLDOWN  = 60 // fallback only; the server sends retry_after
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

// "Enter the 6-digit code we sent to " + [destination] + "." — split around it so it can be bold.
const sentTo = computed(() => __('message.otp_enter_code_sent_to', { destination: '\u0000' }).split('\u0000'))

// Progress: each required channel, then a final "Complete" step (reached once all are verified).
const progressSteps = computed(() => [
    ...steps.value.map((s, i) => ({
        key:    s.type,
        icon:   s.type === 'mobile' ? 'fas fa-mobile-alt' : 'far fa-envelope',
        label:  s.type === 'mobile' ? __('message.mobile') : __('message.email'),
        done:   i < stepIndex.value,
        active: i === stepIndex.value,
    })),
    { key: 'complete', icon: 'fas fa-flag-checkered', label: __('message.complete'), done: redirecting.value, active: false },
])

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

        // Every required channel, so progress survives a reload (a verified one shows as done).
        const all = []
        if (data.mobileRequired ?? !data.isMobileVerified) all.push({ type: 'mobile', target: data.mobile, done: !!data.isMobileVerified })
        if (data.emailRequired ?? !data.isEmailVerified)   all.push({ type: 'email',  target: data.email,  done: !!data.isEmailVerified })

        // Configured preference (email-first vs mobile-first), then verified ones first so the
        // active step is always the first not-done one.
        const first = data.verification_preference === 'email' ? 'email' : 'mobile'
        all.sort((a, b) => (a.type === first ? -1 : 1) - (b.type === first ? -1 : 1))
        all.sort((a, b) => b.done - a.done)

        steps.value = all
        stepIndex.value = all.findIndex(s => !s.done)

        if (stepIndex.value === -1) {
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
    // Countdown starts right away (sending the mail/SMS can take a few seconds),
    // then follows the server's exact retry_after; cleared if the send fails.
    startCooldown(COOLDOWN)
    try {
        const res = await http.post(url, { eid: eid.value })
        startCooldown(res.data?.data?.retry_after ?? COOLDOWN)
    } catch (e) {
        // A code went out recently (e.g. page reloaded): just show the remaining wait.
        if (!waitFromServer(e)) { stopCooldown(); onError(e) }
    }
}

// 429 with retry_after = the server's resend wait; returns true when handled.
function waitFromServer(e) {
    const seconds = e?.response?.status === 429 ? e.response.data?.data?.retry_after : null
    if (!seconds) return false
    startCooldown(seconds)
    return true
}

async function resend(type) {
    if (cooldown.value > 0) return
    startCooldown(COOLDOWN) // immediate feedback; corrected from the server below
    try {
        const res = await http.post(`/resend_otp`, {
            eid: eid.value,
            channel: current.value.type,                              // email | mobile
            method: current.value.type === 'mobile' ? type : null,    // text | voice (mobile only)
        })
        startCooldown(res.data?.data?.retry_after ?? COOLDOWN)
    } catch (e) {
        if (waitFromServer(e)) errorHandler(e, COMPONENT) // show "Please wait N seconds…" too
        else { stopCooldown(); onError(e) }
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
