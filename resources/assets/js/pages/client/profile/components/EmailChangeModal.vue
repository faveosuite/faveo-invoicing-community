<template>
    <Modal :showModal="show" :onClose="close" :showCloseBtn="false">
        <template #title>
            <h5 class="modal-title fw-bold">{{ __('message.change_email') }}</h5>
        </template>

        <template #fields>
            <AppAlert componentName="email-change" />

            <!-- Step 1: enter the new email -->
            <template v-if="step === 'enter'">
                <ClientField type="email" name="newEmail" required
                             :label="__('message.enter_new_email')"
                             v-model="newEmail"
                             placeholder="name@example.com"
                             :error="emailError" />
            </template>

            <!-- Step 2/3: enter the OTP -->
            <template v-else>
                <p class="text-muted mb-3">
                    {{ step === 'verify_old' ? __('message.otp_sent_old_email') : __('message.otp_sent_new_email') }}
                </p>
                <ClientField type="text" name="otp" required
                             :label="__('message.enter_code')"
                             v-model="otp"
                             :placeholder="__('message.otp_placeholder')"
                             :error="otpError" />
                <div class="d-flex align-items-center gap-2 mt-2">
                    <button type="button" class="btn btn-link text-primary p-0 fw-semibold text-decoration-none"
                            :disabled="cooldown > 0" @click="resend">
                        <i class="fas fa-sync-alt me-1"></i>{{ __('message.resend_email') }}
                    </button>
                    <span v-if="cooldown > 0" class="text-muted fw-semibold">{{ String(cooldown).padStart(2, '0') }}s</span>
                </div>
            </template>
        </template>

        <template #controls>
            <action-button v-if="step === 'enter'"
                           action="confirm" :label="__('message.save')"
                           :loading="busy" :disabled="!newEmail" @click="submitEmail" />
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
import AppAlert from '@/components/Reusable/Alert.vue'

const props = defineProps({
    show:         { type: Boolean, default: false },
    currentEmail: { type: String,  default: '' },
})
const emit = defineEmits(['update:show', 'updated'])
const COMPONENT = 'email-change'
const COOLDOWN  = 120

const step       = ref('enter')   // 'enter' | 'verify_old' | 'verify_new'
const newEmail   = ref('')
const otp        = ref('')
const emailError = ref('')
const otpError   = ref('')
const busy       = ref(false)
const cooldown   = ref(0)
let timer = null

watch(() => props.show, (open) => {
    if (open) reset()
    else clearInterval(timer)
})

// Keep OTP numeric / 6 digits.
watch(otp, (v) => {
    const clean = String(v).replace(/\D/g, '').slice(0, 6)
    if (clean !== v) otp.value = clean
    otpError.value = ''
})

function reset() {
    step.value = 'enter'
    newEmail.value = ''
    otp.value = ''
    emailError.value = ''
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

const isEmail = (v) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) // NOSONAR

async function submitEmail() {
    emailError.value = ''
    if (!isEmail(newEmail.value)) {
        emailError.value = __('message.login_validation.email_regex')
        return
    }
    if (newEmail.value.toLowerCase() === props.currentEmail.toLowerCase()) {
        emailError.value = __('message.email_already_used')
        return
    }
    busy.value = true
    try {
        const res = await http.post(`/profile/email/send-otp`, {
            email_to_verify: newEmail.value,
            new_email: newEmail.value,
        })
        const data = res.data?.data ?? {}
        if (data.email_updated) {
            finish(data.email ?? newEmail.value)
        } else {
            // Verification enabled — OTP sent to the current (old) email first.
            step.value = 'verify_old'
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
        if (step.value === 'verify_old') {
            await http.post(`/profile/email/verify-otp`, {
                email_to_verify: props.currentEmail,
                otp: otp.value,
                verify_type: 'old_email',
            })
            // Old email confirmed → now send + verify the new email.
            await http.post(`/profile/email/send-otp`, { email_to_verify: newEmail.value })
            step.value = 'verify_new'
            otp.value = ''
            startCooldown()
        } else {
            const res = await http.post(`/profile/email/verify-otp`, {
                email_to_verify: newEmail.value,
                otp: otp.value,
                verify_type: 'new_email',
            })
            finish(res.data?.data?.email ?? newEmail.value)
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
        const res = await http.post(`/profile/resend-otp`, {
            type: 'email',
            email_to_verify: step.value === 'verify_old' ? props.currentEmail : newEmail.value,
        })
        successHandler(res, COMPONENT)
        startCooldown()
    } catch (e) {
        errorHandler(e, COMPONENT)
    }
}

function finish(email) {
    emit('updated', email)
    close()
}

onBeforeUnmount(() => clearInterval(timer))
</script>
