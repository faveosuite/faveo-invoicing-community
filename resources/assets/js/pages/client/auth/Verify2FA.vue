<template>
    <AuthLayout>
        <div v-if="loading" class="text-center py-5">
            <inline-loader />
        </div>

        <template v-else>
            <!-- TOTP code -->
            <form v-if="!useRecovery" @submit.prevent="submitTotp" novalidate>
                <ClientField name="totp" type="text" :label="__('message.authentication_code')"
                             :model-value="form.totp" :error="errors.totp"
                             @update:modelValue="onTotpInput" />

                <Honeypot name="2fa_code" v-model="form.code2fa" @ready="totpHpReady = $event" />

                <p class="text-color-default text-2 mb-2">{{ __('message.open_two_factor') }}</p>
                <p class="text-2 mb-3">
                    {{ __('message.having_problem') }}
                    <a href="javascript:;" class="text-color-primary fw-semibold text-decoration-none"
                       @click="toggleMode(true)">
                        {{ __('message.login_recovery_code') }}
                    </a>
                </p>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-dark btn-modern text-uppercase fw-bold py-3" :disabled="saving || !totpHpReady">
                        <i v-if="saving" class="fas fa-circle-notch fa-spin me-1"></i>
                        {{ __('message.verify') }}
                    </button>
                </div>
            </form>

            <!-- Recovery code -->
            <form v-else @submit.prevent="submitRecovery" novalidate>
                <ClientField name="rec_code" type="text" :label="__('message.recovery_code')"
                             v-model="form.rec_code" :error="errors.rec_code"
                             @update:modelValue="setFieldError('rec_code', undefined)" />

                <Honeypot name="recovery_code" v-model="form.recovery" @ready="recHpReady = $event" />

                <p class="text-color-default text-2 mb-2">{{ __('message.enter_recovery_code_hint') }}</p>
                <p class="text-2 mb-3">
                    {{ __('message.having_problem') }}
                    <a href="javascript:;" class="text-color-primary fw-semibold text-decoration-none"
                       @click="toggleMode(false)">
                        {{ __('message.use_authentication_code') }}
                    </a>
                </p>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-dark btn-modern text-uppercase fw-bold py-3" :disabled="saving || !recHpReady">
                        <i v-if="saving" class="fas fa-circle-notch fa-spin me-1"></i>
                        {{ __('message.verify') }}
                    </button>
                </div>
            </form>
        </template>
    </AuthLayout>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { twoFaSchema, recoverySchema } from '@/validations/client/authSchemas.js'
import AuthLayout from './partials/AuthLayout.vue'
import Honeypot from '@/components/Reusable/Honeypot.vue'

const COMPONENT = 'client-page'
const el      = document.getElementById('app-client')
const baseUrl = el?.dataset?.baseUrl ?? ''

const { errors, setErrors, setFieldError } = useForm()
const form        = reactive({ totp: '', rec_code: '', code2fa: {}, recovery: {} })
const totpHpReady = ref(false)
const recHpReady  = ref(false)
const loading     = ref(true)
const saving      = ref(false)
const useRecovery = ref(false)

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/auth/2fa-check`)
        // No active 2FA session → backend hands back a login redirect.
        const redirect = res.data?.data?.redirect
        if (redirect) {
            window.location.href = redirect
            return
        }
    } catch (e) {
        window.location.href = `${baseUrl}/login`
        return
    } finally {
        loading.value = false
    }
})

function onTotpInput(value) {
    form.totp = String(value).replace(/\D/g, '')
    setFieldError('totp', undefined)
}

function toggleMode(recovery) {
    useRecovery.value = recovery
    setErrors({})
}

async function submitTotp() {
    try {
        twoFaSchema.validateSync({ totp: form.totp }, { abortEarly: false })
    } catch (err) {
        const map = {}
        err.inner?.forEach(e => { if (e.path && !map[e.path]) map[e.path] = e.message })
        setErrors(map)
        return
    }
    await postVerify(`${baseUrl}/2fa/loginValidate`, { totp: form.totp, '2fa_code': form.code2fa })
}

async function submitRecovery() {
    try {
        recoverySchema.validateSync({ rec_code: form.rec_code }, { abortEarly: false })
    } catch (err) {
        const map = {}
        err.inner?.forEach(e => { if (e.path && !map[e.path]) map[e.path] = e.message })
        setErrors(map)
        return
    }
    // `recovery_code` is the honeypot field validated by the backend (App\Rules\Honeypot).
    await postVerify(`${baseUrl}/verify-recovery-code`, { rec_code: form.rec_code, recovery_code: form.recovery })
}

async function postVerify(url, payload) {
    saving.value = true
    try {
        const res = await http.post(url, payload)
        const redirect = res.data?.data?.redirect
        if (redirect) {
            window.location.href = redirect
        } else {
            successHandler(res, COMPONENT)
        }
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>

<style scoped>
.letter-spacing-2 { letter-spacing: 0.4rem; }
</style>
