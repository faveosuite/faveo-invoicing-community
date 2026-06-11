<template>
    <Modal :showModal="show" :onClose="close" :showCloseBtn="false">
        <template #title>
            <h5 class="modal-title fw-bold">{{ __('message.change_mobile_number') }}</h5>
        </template>

        <template #fields>
            <AppAlert componentName="mobile-change" />

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
            <template v-else>
                <p class="text-muted mb-3">
                    {{ step === 'verify_mobile' ? __('message.otp_description') : __('message.confirm_with_email') }}
                </p>
                <ClientField type="text" name="otp" required
                             :label="__('message.enter_code')"
                             v-model="otp"
                             :placeholder="__('message.otp_placeholder')"
                             :error="otpError" />
                <div class="d-flex align-items-center gap-2 mt-2">
                    <button type="button" class="btn btn-link text-primary p-0 fw-semibold text-decoration-none"
                            :disabled="cooldown > 0" @click="resend">
                        <i class="fas fa-sync-alt me-1"></i>{{ step === 'verify_mobile' ? __('message.resend_otp') : __('message.resend_email') }}
                    </button>
                    <span v-if="cooldown > 0" class="text-muted fw-semibold">{{ String(cooldown).padStart(2, '0') }}s</span>
                </div>
            </template>
        </template>

        <template #controls>
            <action-button v-if="step === 'enter'"
                           action="confirm" :label="__('message.save')"
                           :loading="busy" :disabled="!newMobile" @click="submitMobile" />
            <action-button v-else
                           action="confirm" :label="__('message.verify')"
                           :loading="busy" :disabled="otp.length !== 6" @click="submitOtp" />
        </template>
    </Modal>
</template>

<script setup>
import { ref, watch, onBeforeUnmount } from 'vue'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import Modal from '@/themes/porto/components/common/Modal.vue'
import AppAlert from '@/themes/porto/components/common/Alert.vue'
import PhoneField from '@/themes/porto/components/forms/PhoneField.vue'

const props = defineProps({
    show:         { type: Boolean, default: false },
    currentEmail: { type: String,  default: '' },
    currentCode:  { type: String,  default: '' },
    currentIso:   { type: String,  default: '' },
})
const emit = defineEmits(['update:show', 'updated'])

const el      = document.getElementById('app-client')
const baseUrl = el?.dataset?.baseUrl ?? ''
const COMPONENT = 'mobile-change'
const COOLDOWN  = 120

const step        = ref('enter')   // 'enter' | 'verify_mobile' | 'verify_mobile_email'
const newMobile   = ref('')
const dialCode    = ref('')
const countryIso  = ref('')
const otp         = ref('')
const mobileError = ref('')
const otpError    = ref('')
const busy        = ref(false)
const cooldown    = ref(0)
let timer = null

watch(() => props.show, (open) => {
    if (open) reset()
    else clearInterval(timer)
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
    cooldown.value = 0
    clearInterval(timer)
}

function close() {
    clearInterval(timer)
    emit('update:show', false)
}

function startCooldown() {
    cooldown.value = COOLDOWN
    clearInterval(timer)
    timer = setInterval(() => {
        if (--cooldown.value <= 0) clearInterval(timer)
    }, 1000)
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
        const res = await http.post(`${baseUrl}/profile/mobile/send-otp`, {
            mobile_to_verify: cleanMobile(),
            dial_code: dialCode.value,
            country_iso: countryIso.value,
        })
        const data = res.data?.data ?? {}
        if (data.mobile_updated) {
            finish(data)
        } else {
            // Mobile verification enabled — OTP sent via SMS.
            step.value = 'verify_mobile'
            otp.value = ''
            startCooldown()
        }
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        busy.value = false
    }
}

async function submitOtp() {
    busy.value = true
    try {
        if (step.value === 'verify_mobile') {
            const res = await http.post(`${baseUrl}/profile/mobile/verify-otp`, {
                mobile_to_verify: fullMobile(),
                otp: otp.value,
                new_mobile: cleanMobile(),
                dial_code: dialCode.value,
                country_iso: countryIso.value,
            })
            const data = res.data?.data ?? {}
            if (data.mobile_updated) {
                finish(data)
            } else {
                // Mobile verified, but an email confirmation is also required.
                await http.post(`${baseUrl}/profile/email/send-otp`, {
                    email_to_verify: props.currentEmail,
                    is_mobile: 1,
                })
                step.value = 'verify_mobile_email'
                otp.value = ''
                startCooldown()
            }
        } else {
            // Confirm via the registered email; the server applies the mobile from session.
            const res = await http.post(`${baseUrl}/profile/email/verify-otp`, {
                email_to_verify: props.currentEmail,
                otp: otp.value,
                verify_type: 'mobile_email',
            })
            finish(res.data?.data ?? {})
        }
    } catch (e) {
        errorHandler(e, COMPONENT)
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
        const res = await http.post(`${baseUrl}/profile/resend-otp`, payload)
        successHandler(res, COMPONENT)
        startCooldown()
    } catch (e) {
        errorHandler(e, COMPONENT)
    }
}

function finish(data) {
    emit('updated', {
        mobile: data.mobile ?? cleanMobile(),
        mobile_code: data.mobile_code ?? dialCode.value,
        mobile_country_iso: countryIso.value,
    })
    close()
}

onBeforeUnmount(() => clearInterval(timer))
</script>
