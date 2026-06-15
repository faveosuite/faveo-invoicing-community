<template>
    <div>
        <AppCard :title="__('message.two_factor_authentication')">
            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <div v-else>
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-shield-alt" style="font-size:20px;"></i>
                        <span class="text-2">
                            {{
                                is2faEnabled
                                    ? __('message.2_step_verification') + (dateSinceEnabled ? ' ' + dateSinceEnabled : '')
                                    : __('message.authenticator_app')
                            }}
                        </span>
                    </div>
                    <div>
                        <button v-if="!is2faEnabled"
                                type="button"
                                class="btn btn-primary btn-sm btn-modern"
                                @click="openEnableModal">
                            <i class="fas fa-toggle-on me-1"></i>{{ __('message.enable') }}
                        </button>
                        <button v-else
                                type="button"
                                class="btn btn-outline-secondary btn-sm btn-modern"
                                @click="openDisableModal">
                            <i class="fas fa-toggle-off me-1"></i>{{ __('message.disable') }}
                        </button>
                    </div>
                </div>
            </div>
        </AppCard>

        <!-- Enable 2FA Modal -->
        <AppModal :showModal="showEnableModal" :onClose="closeEnableModal" :showCloseBtn="false">
            <template #title>
                <h5 class="modal-title">{{ __('message.setup_authenticator') }}</h5>
            </template>
            <template #fields>
                <div v-if="twoFaLoading" class="row justify-content-center py-3"><loader /></div>
                <template v-else>

                    <div v-if="modalError" class="alert alert-danger py-2 mb-3">{{ modalError }}</div>

                    <!-- Step: Password -->
                    <template v-if="twoFaStep === 'password'">
                        <p class="text-muted mb-3">{{ __('message.to_continue_first_verify') }}</p>
                        <ClientField
                            type="password"
                            name="user_password"
                            :label="__('message.password')"
                            v-model="userPassword"
                        />
                    </template>

                    <!-- Step: Recovery Codes -->
                    <template v-if="twoFaStep === 'recovery'">
                        <p class="mb-3">{{ __('message.recovery_codes_are_used') }}</p>
                        <div class="card border mb-2">
                            <div class="card-header d-flex align-items-center justify-content-between py-2">
                                <span class="fw-bold text-2">{{ __('message.recovery_codes') }}</span>
                                <button type="button" class="btn btn-sm btn-light" @click="copyRecovery">
                                    <i :class="recoveryCopied ? 'fas fa-check' : 'fas fa-clipboard'"></i>
                                </button>
                            </div>
                            <div class="card-body py-2">
                                <div class="row">
                                    <div v-for="code in recoveryCodes" :key="code" class="col-6 text-center">
                                        <span class="font-monospace">{{ code }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted small mt-2">{{ __('message.treat_recovery_codes') }}</p>
                    </template>

                    <!-- Step: QR Code -->
                    <template v-if="twoFaStep === 'qr'">
                        <template v-if="!showSecretKey">
                            <ul class="text-center list-unstyled mb-3">
                                <li>{{ __('message.get_authenticator_app') }}</li>
                                <li>{{ __('message.choose') }} <b>{{ __('message.scan_a_barcode') }}</b></li>
                            </ul>
                            <div class="text-center mb-3" v-html="qrImage"></div>
                            <div class="text-center">
                                <a href="#" class="text-decoration-underline text-3"
                                   @click.prevent="showSecretKey = true">{{ __('message.cant_scan') }}</a>
                            </div>
                        </template>
                        <template v-else>
                            <div class="form-group row mb-3">
                                <label class="col-lg-4 col-form-label form-control-label line-height-9 pt-2 text-2">
                                    {{ __('message.secret_key_label') }}
                                </label>
                                <div class="col-lg-8">
                                    <input type="text" class="form-control text-3 h-auto py-2"
                                           :value="qrSecret" readonly style="background-color:#f8f9fa;">
                                </div>
                            </div>
                            <div class="text-center">
                                <a href="#" class="text-decoration-underline text-3"
                                   @click.prevent="showSecretKey = false">{{ __('message.scan_barcode') }}</a>
                            </div>
                        </template>
                    </template>

                    <!-- Step: TOTP -->
                    <template v-if="twoFaStep === 'totp'">
                        <p class="mb-3">{{ __('message.enter_the_code_you_see_in_the_app') }}</p>
                        <div class="form-group">
                            <input type="text"
                                   class="form-control text-3 h-auto py-2"
                                   v-model="totp"
                                   placeholder="Enter Passcode..."
                                   maxlength="6"
                                   @keydown.enter="verify2fa" />
                        </div>
                    </template>

                    <!-- Step: Done -->
                    <template v-if="twoFaStep === 'done'">
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle text-success" style="font-size:3rem;"></i>
                            <p class="mt-3 mb-0 fw-bold text-2">{{ __('message.you_are_all_set') }}</p>
                        </div>
                    </template>

                </template>
            </template>
            <template #controls>
                <template v-if="twoFaStep === 'password'">
                    <button type="button" class="btn btn-primary btn-modern"
                            :disabled="verifyingPassword || !userPassword"
                            @click="validatePassword">
                        <i v-if="verifyingPassword" class="fas fa-circle-notch fa-spin me-1"></i>
                        <i v-else class="fas fa-check me-1"></i>
                        {{ __('message.validate') }}
                    </button>
                </template>
                <template v-if="twoFaStep === 'recovery'">
                    <button type="button" class="btn btn-primary btn-modern"
                            :disabled="!recoveryCopied || enabling2fa"
                            @click="goToQr">
                        <i v-if="enabling2fa" class="fas fa-circle-notch fa-spin me-1"></i>
                        <i v-else class="fas fa-arrow-right me-1"></i>
                        {{ __('message.next') }}
                    </button>
                </template>
                <template v-if="twoFaStep === 'qr'">
                    <div class="d-flex w-100 justify-content-between">
                        <button type="button" class="btn btn-light"
                                @click="twoFaStep = 'recovery'; modalError = ''">
                            <i class="fas fa-arrow-left me-1"></i>{{ __('message.previous') }}
                        </button>
                        <button type="button" class="btn btn-primary btn-modern"
                                @click="twoFaStep = 'totp'; modalError = ''">
                            {{ __('message.next') }}<i class="fas fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                </template>
                <template v-if="twoFaStep === 'totp'">
                    <div class="d-flex w-100 justify-content-between">
                        <button type="button" class="btn btn-light"
                                @click="twoFaStep = 'qr'; modalError = ''">
                            <i class="fas fa-arrow-left me-1"></i>{{ __('message.previous') }}
                        </button>
                        <button type="button" class="btn btn-primary btn-modern"
                                :disabled="verifying2fa || !totp"
                                @click="verify2fa">
                            <i v-if="verifying2fa" class="fas fa-circle-notch fa-spin me-1"></i>
                            <i v-else class="fas fa-check me-1"></i>
                            {{ __('message.verify') }}
                        </button>
                    </div>
                </template>
                <template v-if="twoFaStep === 'done'">
                    <button type="button" class="btn btn-primary btn-modern" @click="onEnableDone">
                        {{ __('message.done') }}
                    </button>
                </template>
            </template>
        </AppModal>

        <!-- Disable 2FA Modal -->
        <AppModal :showModal="showDisableModal" :onClose="closeDisableModal" :showCloseBtn="false">
            <template #title>
                <h5 class="modal-title">{{ __('message.turn_off_authenticator') }}</h5>
            </template>
            <template #fields>
                <p class="mb-0">{{ __('message.turn_off_authenticator_setup') }}</p>
            </template>
            <template #controls>
                <button type="button" class="btn btn-outline-secondary btn-modern"
                        :disabled="disabling2fa"
                        @click="disable2fa">
                    <i v-if="disabling2fa" class="fas fa-circle-notch fa-spin me-1"></i>
                    <i v-else class="fas fa-power-off me-1"></i>
                    {{ __('message.disable') }}
                </button>
            </template>
        </AppModal>
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

const COMPONENT = 'client-page'

const loading        = ref(true)
const is2faEnabled   = ref(false)
const dateSinceEnabled = ref(null)

// Enable modal state
const showEnableModal   = ref(false)
const twoFaStep         = ref('password')
const twoFaLoading      = ref(false)
const modalError        = ref('')
const userPassword      = ref('')
const verifyingPassword = ref(false)
const recoveryCodes     = ref([])
const recoveryCopied    = ref(false)
const enabling2fa       = ref(false)
const qrImage           = ref('')
const qrSecret          = ref('')
const showSecretKey     = ref(false)
const totp              = ref('')
const verifying2fa      = ref(false)

// Disable modal state
const showDisableModal = ref(false)
const disabling2fa     = ref(false)

onMounted(async () => {
    try {
        const res  = await http.get(`${baseUrl}/get-my-profile`)
        const user = res.data?.data?.user ?? {}
        is2faEnabled.value   = Boolean(user.is_2fa_enabled)
        dateSinceEnabled.value = user.google2fa_activation_date ?? null
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

function setModalError(e) {
    modalError.value = e?.response?.data?.message ?? e?.message ?? __('message.something_went_wrong')
}

// ── Enable modal ──────────────────────────────────────────────────────────────

async function openEnableModal() {
    twoFaStep.value        = 'password'
    userPassword.value     = ''
    recoveryCopied.value   = false
    recoveryCodes.value    = []
    totp.value             = ''
    qrImage.value          = ''
    qrSecret.value         = ''
    showSecretKey.value    = false
    modalError.value       = ''
    twoFaLoading.value     = true
    showEnableModal.value  = true

    try {
        await http.get(`${baseUrl}/show/verify-password`)
        // Password already confirmed this session — skip to recovery
        twoFaStep.value    = 'recovery'
        const res = await http.post(`${baseUrl}/2fa-recovery-code`)
        recoveryCodes.value = res.data?.data?.code ?? []
    } catch {
        // Not confirmed — show password step
    } finally {
        twoFaLoading.value = false
    }
}

function closeEnableModal() {
    showEnableModal.value = false
    twoFaStep.value       = 'password'
    userPassword.value    = ''
    recoveryCopied.value  = false
    recoveryCodes.value   = []
    totp.value            = ''
    qrImage.value         = ''
    qrSecret.value        = ''
    showSecretKey.value   = false
}

async function validatePassword() {
    if (!userPassword.value) return
    modalError.value        = ''
    verifyingPassword.value = true
    try {
        await http.post(`${baseUrl}/verify-password`, { user_password: userPassword.value })
        twoFaLoading.value = true
        twoFaStep.value    = 'recovery'
        const res = await http.post(`${baseUrl}/2fa-recovery-code`)
        recoveryCodes.value = res.data?.data?.code ?? []
    } catch (e) {
        setModalError(e)
    } finally {
        verifyingPassword.value = false
        twoFaLoading.value      = false
    }
}

function copyRecovery() {
    navigator.clipboard?.writeText(recoveryCodes.value.join('\n'))
    recoveryCopied.value = true
    setTimeout(() => { recoveryCopied.value = false }, 5000)
}

async function goToQr() {
    enabling2fa.value = true
    try {
        const res      = await http.post(`${baseUrl}/2fa/enable`)
        qrImage.value  = res.data?.data?.image  ?? ''
        qrSecret.value = res.data?.data?.secret ?? ''
        twoFaStep.value = 'qr'
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        enabling2fa.value = false
    }
}

async function verify2fa() {
    if (!totp.value) return
    modalError.value   = ''
    verifying2fa.value = true
    try {
        const res = await http.post(`${baseUrl}/2fa/setupValidate`, { totp: totp.value })
        successHandler(res, COMPONENT)
        twoFaStep.value = 'done'
    } catch (e) {
        setModalError(e)
    } finally {
        verifying2fa.value = false
    }
}

function onEnableDone() {
    is2faEnabled.value = true
    closeEnableModal()
}

// ── Disable modal ─────────────────────────────────────────────────────────────

function openDisableModal()  { showDisableModal.value = true }
function closeDisableModal() { showDisableModal.value = false }

async function disable2fa() {
    disabling2fa.value = true
    try {
        const res = await http.post(`${baseUrl}/2fa/disable/${userId}`)
        successHandler(res, COMPONENT)
        is2faEnabled.value     = false
        dateSinceEnabled.value = null
        closeDisableModal()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        disabling2fa.value = false
    }
}
</script>
