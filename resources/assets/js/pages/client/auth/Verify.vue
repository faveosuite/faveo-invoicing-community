<template>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-11 col-md-10 col-lg-9 col-xl-8 mt-3 mb-2">
                <div class="card border-0 rounded-3">
                    <div class="card-body p-4 p-md-5">

                        <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

                        <template v-else>
                            <!-- Step progress -->
                            <div class="d-flex justify-content-center align-items-start mb-4">
                                <template v-for="(s, i) in progressSteps" :key="i">
                                    <!-- connector -->
                                    <div v-if="i > 0" class="flex-grow-1 border-top border-2 mt-4 step-connector"
                                         :class="(s.done || s.active) ? 'border-primary' : ''"></div>

                                    <div class="text-center px-2 step-item">
                                        <span class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2 step-circle"
                                              :class="(s.done || s.active) ? 'bg-primary text-white' : 'bg-light text-muted border'">
                                            <i class="fas fa-lg" :class="s.icon"></i>
                                        </span>
                                        <div class="fw-semibold"
                                             :class="(s.done || s.active) ? 'text-primary' : 'text-muted'">
                                            {{ s.label }}
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- OTP entry -->
                            <form v-if="current && !done" @submit.prevent="verify" novalidate>
                                <ClientField name="otp" type="text" :label="__('message.enter_code')" required
                                             :placeholder="__('message.otp_placeholder')"
                                             :model-value="form.otp" :error="errors.otp"
                                             @update:modelValue="onOtpInput" />

                                <p class="text-muted mt-3 mb-4">
                                    {{ current.type === 'mobile' ? __('message.otp_description') : __('message.email_otp_description') }}
                                </p>

                                <div class="row align-items-center g-2">
                                    <div class="col-6">
                                        <div class="d-flex align-items-center gap-2">
                                            <button type="button"
                                                    class="btn btn-link text-primary text-decoration-none fw-semibold p-0"
                                                    :disabled="cooldown > 0" @click="resend('text')">
                                                <i class="fas fa-sync-alt me-1"></i>{{ current.type === 'mobile' ? __('message.resend_otp') : __('message.resend_email') }}
                                            </button>
                                            <span v-if="cooldown > 0" class="text-muted fw-semibold">
                                                {{ String(cooldown).padStart(2, '0') }}s
                                            </span>
                                        </div>
                                        <button v-if="current.type === 'mobile'" type="button"
                                                class="btn btn-link text-primary text-decoration-none fw-semibold p-0 mt-1"
                                                :disabled="cooldown > 0" @click="resend('voice')">
                                            <i class="fas fa-phone-alt me-1"></i>{{ __('message.otp_call') }}
                                        </button>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <RecaptchaField ref="captchaRef" :action="captchaAction" />
                                    </div>
                                    <div class="col-6 text-end">
                                        <button type="submit" class="btn btn-primary btn-lg" :disabled="saving">
                                            <i v-if="saving" class="fas fa-circle-notch fa-spin me-1"></i>
                                            {{ saving ? __('message.verifying') : __('message.verify') }}
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <!-- Success -->
                            <div v-else-if="done" class="text-center py-4">
                                <h2 class="text-primary fw-bold mb-0">{{ __('message.all_success') }}</h2>
                            </div>

                            <div class="mt-3 text-2">
                                {{ __('message.trouble_logging_in') }}
                                <a :href="`${baseUrl}/contact-us`" class="text-decoration-none" target="_blank">
                                    {{ __('message.click_here') }}
                                </a>
                            </div>
                        </template>

                    </div>
                </div>
            </div>
        </div>
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
const done      = ref(false)
const eid       = ref('')
const steps     = ref([])
const stepIndex = ref(0)
const { cooldown, start: startCooldown, stop: stopCooldown } = useCooldown(COOLDOWN)

const current = computed(() => steps.value[stepIndex.value] ?? null)

// Steps for the progress bar: each pending verification + a final "all set".
const progressSteps = computed(() => {
    const list = steps.value.map((s, i) => ({
        label:  s.type === 'mobile' ? __('message.verify_mobile') : __('message.verify_email'),
        icon:   s.type === 'mobile' ? 'fa-mobile-alt' : 'fa-envelope',
        done:   done.value || i < stepIndex.value,
        active: !done.value && i === stepIndex.value,
    }))
    list.push({ label: __('message.all_set'), icon: 'fa-check', done: done.value, active: done.value })
    return list
})

onMounted(async () => {
    try {
        const res  = await http.get(`/auth/verify-config`)
        const data = res.data?.data ?? {}

        if (data.redirect) {
            globalThis.location.href = data.redirect
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
        errorHandler(e, COMPONENT, { setErrors })
    }
})

watch(stepIndex, () => captchaRef.value?.reset())

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
        errorHandler(e, COMPONENT, { setErrors })
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
        errorHandler(e, COMPONENT, { setErrors })
    }
}

async function verify() {
    if (!await validateForm(otpSchema, { otp: form.otp }, setErrors)) return

    const captchaPayload = await captchaRef.value?.getPayload()
    if (!captchaRef.value?.disabled && !captchaPayload?.['g-recaptcha-response']) return

    saving.value = true
    const url = current.value.type === 'mobile' ? `${baseUrl}/otp/verify` : `${baseUrl}/email/verify`
    try {
        const res = await http.post(url, { eid: eid.value, otp: form.otp, ...captchaPayload })
        successHandler(res, COMPONENT)
        form.otp = ''

        if (stepIndex.value < steps.value.length - 1) {
            stepIndex.value++
            stopCooldown()
            sendInitial()
        } else {
            // All steps verified — show the success state, then redirect to login.
            done.value = true
            stopCooldown()
            setTimeout(() => { globalThis.location.href = `${baseUrl}/login` }, 1500)
        }
    } catch (e) {
        if (e?.response?.data?.data?.show_v2_recaptcha) {
            captchaRef.value?.triggerFallback()
            return
        }
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        saving.value = false
    }
}
</script>

<style scoped>
/* Only the fixed dimensions Bootstrap can't express as utilities — everything
   else (colours, layout, icons) uses Porto/Bootstrap classes. */
.step-circle    { width: 50px; height: 50px; }
.step-item      { min-width: 96px; }
.step-connector { max-width: 90px; }
</style>
