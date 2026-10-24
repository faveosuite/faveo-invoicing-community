<template>
    <Modal :showModal="show" :onClose="close" :showCloseBtn="false">
        <template #title>
            <h5 class="modal-title fw-bold">{{ __('message.change_mobile_number') }}</h5>
        </template>

        <template #fields>
            <AppAlert componentName="mobile-change" />

            <form @submit.prevent="onSubmit">
            <!-- Step 1: enter the new mobile -->
            <template v-if="step === 'enter'">
                <PhoneField name="newMobile" required
                            :label="__('message.enter_new_mobile_no')"
                            :value="newMobile"
                            :error="mobileError"
                            :initialCountry="(currentIso || 'auto').toLowerCase()"
                            :onChange="onMobileInput"
                            @countryChange="onCountryChange" />
            </template>

            <!-- Step 2: verify the mobile OTP, Step 3: verify the auth-email OTP -->
            <template v-else-if="step === 'verify_mobile' || step === 'verify_mobile_email'">
                <p class="text-muted mb-3">
                    {{ step === 'verify_mobile' ? __('message.otp_description') : __('message.confirm_with_email') }}
                </p>
                <ClientField type="text" name="otp" required
                             :label="__('message.enter_code')"
                             v-model="otp"
                             :autocomplete="'one-time-code'" inputmode="numeric" :spellcheck="false"
                             :placeholder="__('message.otp_placeholder')"
                             :error="otpError" />
                <div class="d-flex align-items-center gap-2 mt-2">
                    <button type="button" class="btn btn-link text-primary p-0 fw-semibold text-decoration-none"
                            :disabled="cooldown > 0 || locked" @click="resend">
                        <i class="fas fa-sync-alt me-1" aria-hidden="true"></i>{{ __('message.resend_otp') }}
                    </button>
                    <span v-if="cooldown > 0" class="text-muted fw-semibold">{{ String(cooldown).padStart(2, '0') }}s</span>
                </div>
            </template>

            <!-- Step 4: done -->
            <template v-else-if="step === 'done'">
                <div class="text-center py-4">
                    <i class="fas fa-check-circle text-success fa-3x" aria-hidden="true"></i>
                    <p class="mt-3 mb-0 fw-bold text-2">{{ doneMessage }}</p>
                </div>
            </template>
            </form>
        </template>

        <template #controls>
            <action-button v-if="step !== 'done'" action="cancel" @click="close" />
            <action-button v-if="step === 'enter'"
                           action="confirm" :label="__('message.send_otp')"
                           :loading="busy" :disabled="!newMobile || locked" @click="submitMobile" />
            <action-button v-else-if="step === 'done'"
                           action="confirm" :label="__('message.done')" @click="close" />
            <action-button v-else
                           action="confirm" :label="__('message.verify')"
                           :loading="busy" :disabled="otp.length !== 6 || locked" @click="submitOtp" />
        </template>
    </Modal>
</template>

<script setup>
import { ref, watch } from 'vue'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import Modal from '@/themes/porto/components/common/Modal.vue'
import AppAlert from '@/components/Reusable/Alert.vue'
import PhoneField from '@/components/Reusable/FormField/PhoneField.vue'
import { useCooldown } from '@/core/composables/useCooldown'

const props = defineProps({
    show:         { type: Boolean, default: false },
    currentEmail: { type: String,  default: '' },
    currentCode:  { type: String,  default: '' },
    currentIso:   { type: String,  default: '' },
})
const emit = defineEmits(['update:show', 'updated'])
const COMPONENT = 'mobile-change'
const COOLDOWN  = 120

const step        = ref('enter')   // 'enter' | 'verify_mobile' | 'verify_mobile_email' | 'done'
const newMobile   = ref('')
const dialCode    = ref('')
const countryIso  = ref('')
const otp         = ref('')
const mobileError = ref('')
const otpError    = ref('')
const busy        = ref(false)
const locked      = ref(false)
const doneMessage = ref('')
const { cooldown, start: startCooldown, stop: stopCooldown } = useCooldown(COOLDOWN)

watch(() => props.show, (open) => {
    if (open) reset()
    else stopCooldown()
})

watch(otp, (v) => {
    const clean = String(v).replace(/\D/g, '').slice(0, 6)
    if (clean !== v) otp.value = clean
    otpError.value = ''
})

function reset() {
    step.value = 'enter'
    newMobile.value = ''
    dialCode.value = props.currentCode || ''
    countryIso.value = (props.currentIso || '').toUpperCase()
    otp.value = ''
    mobileError.value = ''
    otpError.value = ''
    doneMessage.value = ''
    locked.value = false
    stopCooldown()
}

// 429 = max attempts reached: show the server's message, lock the popup, then close it.
function onError(e) {
    errorHandler(e, COMPONENT)
    if (e?.response?.status === 429 && !locked.value) {
        locked.value = true
        stopCooldown()
        setTimeout(close, 5000)
    }
}

function close() {
    stopCooldown()
    emit('update:show', false)
}

function onMobileInput(value) {
    newMobile.value = String(value).replace(/\D/g, '')
    mobileError.value = ''
}

function onCountryChange({ iso, dialCode: dc }) {
    countryIso.value = iso
    dialCode.value = dc
}

const cleanMobile = () => newMobile.value.replace(/\D/g, '')
const fullMobile  = () => (dialCode.value + newMobile.value).replace(/\D/g, '')

async function submitMobile() {
    mobileError.value = ''
    if (!cleanMobile()) {
        mobileError.value = __('message.mobile_required')
        return
    }
    busy.value = true
    try {
        const res = await http.post(`/profile/mobile/send-otp`, {
            mobile_to_verify: cleanMobile(),
            dial_code: dialCode.value,
            country_iso: countryIso.value,
        })
        const data = res.data?.data ?? {}
        if (data.mobile_updated) {
            finish(res, data)
        } else {
            // Mobile verification enabled — OTP sent via SMS.
            step.value = 'verify_mobile'
            otp.value = ''
            startCooldown()
        }
    } catch (e) {
        onError(e)
    } finally {
        busy.value = false
    }
}

// Enter key submits the current step (same guards as the footer buttons).
function onSubmit() {
    if (busy.value || locked.value) return
    if (step.value === 'enter') { if (newMobile.value) submitMobile() }
    else if (step.value !== 'done' && otp.value.length === 6) submitOtp()
}

async function submitOtp() {
    if (busy.value) return // a repeat click would re-send the used code and show "OTP not valid"
    busy.value = true
    try {
        if (step.value === 'verify_mobile') {
            const res = await http.post(`/profile/mobile/verify-otp`, {
                mobile_to_verify: fullMobile(),
                otp: otp.value,
                new_mobile: cleanMobile(),
                dial_code: dialCode.value,
                country_iso: countryIso.value,
            })
            const data = res.data?.data ?? {}
            if (data.mobile_updated) {
                finish(res, data)
            } else {
                // Mobile verified, but an email confirmation is also required.
                await http.post(`/profile/email/send-otp`, {
                    email_to_verify: props.currentEmail,
                    is_mobile: 1,
                })
                step.value = 'verify_mobile_email'
                otp.value = ''
                startCooldown()
            }
        } else {
            // Confirm via the registered email; the server applies the mobile from session.
            const res = await http.post(`/profile/email/verify-otp`, {
                email_to_verify: props.currentEmail,
                otp: otp.value,
                verify_type: 'mobile_email',
            })
            finish(res, res.data?.data ?? {})
        }
    } catch (e) {
        // A rejected code belongs under the OTP field, not in the page alert.
        const msg = e?.response?.data?.message
        if (msg && e.response.status < 500 && e.response.status !== 429 && e.config?.url?.includes('verify-otp')) otpError.value = msg
        else onError(e)
    } finally {
        busy.value = false
    }
}

async function resend() {
    if (cooldown.value > 0) return
    try {
        const payload = step.value === 'verify_mobile'
            ? { type: 'mobile', mobile_to_verify: cleanMobile(), dial_code: dialCode.value, country_iso: countryIso.value, retry_type: 'text' }
            : { type: 'email', email_to_verify: props.currentEmail, is_mobile: 1 }
        const res = await http.post(`/profile/resend-otp`, payload)
        successHandler(res, COMPONENT)
        startCooldown()
    } catch (e) {
        onError(e)
    }
}

function finish(res, data) {
    emit('updated', {
        mobile: data.mobile ?? cleanMobile(),
        mobile_code: data.mobile_code ?? dialCode.value,
        mobile_country_iso: countryIso.value,
    })
    // Stay open on a "done" step so the success message is actually seen —
    // the modal (and its own <Alert>) would otherwise unmount immediately.
    doneMessage.value = res?.data?.message ?? ''
    step.value = 'done'
}
</script>
