<template>
    <div>
        <AppAlert componentName="client-2fa" />
        <inline-loader v-if="loading" />

        <div v-else>

            <!-- 2FA Enabled state -->
            <div v-if="is2faEnabled">
                <div class="form-group row">
                    <label class="col-lg-3 col-form-label form-control-label line-height-9 pt-2 text-2">
                        {{ __('message.status') }}
                    </label>
                    <div class="col-lg-9 d-flex align-items-center">
                        <span class="badge bg-success">{{ __('message.enabled') }}</span>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-lg-9 offset-lg-3">
                        <button type="button" class="btn btn-danger btn-modern" :disabled="disabling" @click="disable2fa">
                            <i v-if="disabling" class="fas fa-circle-notch fa-spin me-1"></i>
                            {{ __('message.disable_2fa') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- 2FA Disabled — setup flow -->
            <div v-else>

                <!-- Step 1: not yet started -->
                <div v-if="!qrCode">
                    <div class="form-group row">
                        <label class="col-lg-3 col-form-label form-control-label line-height-9 pt-2 text-2">
                            {{ __('message.status') }}
                        </label>
                        <div class="col-lg-9 d-flex align-items-center">
                            <span class="badge bg-warning text-dark">{{ __('message.disabled') }}</span>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-lg-9 offset-lg-3">
                            <button type="button" class="btn btn-primary btn-modern" :disabled="enabling" @click="enable2fa">
                                <i v-if="enabling" class="fas fa-circle-notch fa-spin me-1"></i>
                                {{ __('message.enable_2fa') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 2: QR shown, enter TOTP -->
                <div v-else>
                    <div class="form-group row">
                        <label class="col-lg-3 col-form-label form-control-label line-height-9 pt-2 text-2">
                            {{ __('message.scan_qr_code') }}
                        </label>
                        <div class="col-lg-9">
                            <img :src="qrCode" alt="QR Code" class="img-fluid" style="max-width:200px">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-3 col-form-label form-control-label line-height-9 pt-2 text-2">
                            {{ __('message.secret_key_label') }}
                        </label>
                        <div class="col-lg-9 d-flex align-items-center">
                            <code class="text-3">{{ secret }}</code>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-3 col-form-label form-control-label line-height-9 pt-2 text-2 required">
                            {{ __('message.verification_code') }}
                        </label>
                        <div class="col-lg-9">
                            <input class="form-control text-3 h-auto py-2"
                                   :class="{ 'is-invalid': totpError }"
                                   type="text" v-model="totp"
                                   :placeholder="__('message.enter_6_digit_code')"
                                   maxlength="6" style="max-width:200px">
                            <div v-if="totpError" class="invalid-feedback d-block">{{ totpError }}</div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-lg-9 offset-lg-3 d-flex gap-2">
                            <button type="button" class="btn btn-primary btn-modern" :disabled="verifying" @click="verify2fa">
                                <i v-if="verifying" class="fas fa-circle-notch fa-spin me-1"></i>
                                {{ __('message.verify_enable') }}
                            </button>
                            <button type="button" class="btn btn-light" @click="cancelSetup">
                                {{ __('message.cancel') }}
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const el      = document.getElementById('app-client')
const baseUrl = el?.dataset?.baseUrl ?? ''
const userId  = el?.dataset?.userId ?? ''

const COMPONENT = 'client-2fa'

const loading    = ref(true)
const is2faEnabled = ref(false)

const enabling  = ref(false)
const disabling = ref(false)
const verifying = ref(false)

const qrCode = ref('')
const secret = ref('')
const totp   = ref('')
const totpError = ref('')

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/my-profile`)
        const d   = res.data?.data ?? {}
        const user = d.user ?? d
        is2faEnabled.value = Boolean(user.is_2fa_enabled)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function enable2fa() {
    enabling.value = true
    try {
        const res = await http.post(`${baseUrl}/2fa/enable`)
        const d   = res.data?.data ?? res.data ?? {}
        qrCode.value = d.image ?? d.qr_image ?? ''
        secret.value = d.secret ?? ''
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        enabling.value = false
    }
}

async function verify2fa() {
    totpError.value = ''
    if (!totp.value || totp.value.length !== 6) {
        totpError.value = __('message.enter_6_digit_code')
        return
    }
    verifying.value = true
    try {
        const res = await http.post(`${baseUrl}/2fa/setupValidate`, { totp: totp.value })
        successHandler(res, COMPONENT)
        is2faEnabled.value = true
        qrCode.value = ''
        secret.value = ''
        totp.value   = ''
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        verifying.value = false
    }
}

async function disable2fa() {
    disabling.value = true
    try {
        const res = await http.post(`${baseUrl}/2fa/disable/${userId}`)
        successHandler(res, COMPONENT)
        is2faEnabled.value = false
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        disabling.value = false
    }
}

function cancelSetup() {
    qrCode.value = ''
    secret.value = ''
    totp.value   = ''
    totpError.value = ''
}
</script>
