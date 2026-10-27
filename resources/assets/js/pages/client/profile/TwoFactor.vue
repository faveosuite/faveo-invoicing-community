<template>
    <div>
        <AppCard :title="__('message.two_factor_authentication')">
            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <div v-else class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                <div class="d-flex align-items-center gap-3">
                    <div class="two-factor-icon"><i class="fas fa-shield-alt" aria-hidden="true"></i></div>
                    <div>
                        <span class="fw-bold">
                            {{ __('message.two_factor_authentication') }}
                            <span class="badge rounded-pill ms-1" :class="is2faEnabled ? 'badge-soft-success' : 'badge-soft-secondary'">
                                {{ is2faEnabled ? __('message.active') : __('message.inactive') }}
                            </span>
                        </span>
                        <p class="text-muted text-2 mb-0">{{ __('message.two_factor_authentication_description') }}</p>
                        <small v-if="is2faEnabled && dateSinceEnabled" class="text-muted">
                            {{ __('message.2_step_verification') }} {{ dateSinceEnabled }}
                        </small>
                    </div>
                </div>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" role="switch"
                           :checked="is2faEnabled"
                           :aria-label="__('message.two_factor_authentication')"
                           @click.prevent="onTwoFactorToggleClick"
                           v-tooltip="is2faEnabled ? __('message.disable') : __('message.enable')">
                </div>
            </div>
        </AppCard>

        <!-- Enable 2FA Modal -->
        <AppModal :showModal="showEnableModal" :onClose="closeEnableModal" :showCloseBtn="false">
            <template #title>
                <h5 class="modal-title fw-bold">{{ __('message.setup_authenticator') }}</h5>
            </template>
            <template #fields>
                <div v-if="twoFaLoading" class="row justify-content-center py-3"><loader /></div>
                <template v-else>

                    <div v-if="modalError" class="alert alert-danger py-2 mb-3">{{ modalError }}</div>

                    <!-- Step: Password -->
                    <form v-if="twoFaStep === 'password'" @submit.prevent="validatePassword">
                        <p class="text-muted mb-3">{{ __('message.to_continue_first_verify') }}</p>
                        <ClientField
                            type="password"
                            name="user_password"
                            :label="__('message.password')"
                            autocomplete="current-password"
                            v-model="userPassword"
                        />
                    </form>

                    <!-- Step: Recovery Codes -->
                    <template v-if="twoFaStep === 'recovery'">
                        <p class="text-muted mb-3">{{ __('message.recovery_codes_are_used') }}</p>
                        <div class="card border mb-2">
                            <div class="card-header d-flex align-items-center justify-content-between py-2">
                                <span class="fw-bold text-2">{{ __('message.recovery_codes') }}</span>
                                <button type="button" class="btn btn-sm btn-light" @click="copyRecovery"
                                        :aria-label="__('message.copy_to_clipboard')">
                                    <i :class="recoveryCopied ? 'fas fa-check' : 'fas fa-clipboard'" aria-hidden="true"></i>
                                </button>
                                <span class="visually-hidden" aria-live="polite">{{ recoveryCopied ? __('message.copied') : '' }}</span>
                            </div>
                            <div ref="codesEl" class="card-body py-2">
                                <div class="row">
                                    <div v-for="code in recoveryCodes" :key="code" class="col-6 text-center">
                                        <span class="font-monospace">{{ code }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted mt-2">{{ __('message.treat_recovery_codes') }}</p>
                    </template>

                    <!-- Step: QR Code -->
                    <template v-if="twoFaStep === 'qr'">
                        <template v-if="!showSecretKey">
                            <ul class="text-center list-unstyled mb-3">
                                <li>{{ __('message.get_authenticator_app') }}</li>
                                <li>{{ __('message.choose') }} <b>{{ __('message.scan_a_barcode') }}</b></li>
                            </ul>
                            <div class="text-center mb-3" role="img" :aria-label="__('message.scan_a_barcode')" v-html="qrImage"></div> <!-- nosemgrep: javascript.vue.security.audit.xss.templates.avoid-v-html.avoid-v-html -->
                            <div class="text-center">
                                <button type="button" class="border-0 bg-transparent p-0 text-color-primary text-decoration-underline text-3"
                                   @click="showSecretKey = true">{{ __('message.cant_scan') }}</button>
                            </div>
                        </template>
                        <template v-else>
                            <div class="form-group row mb-3">
                                <label for="twofa-secret-key" class="col-lg-4 col-form-label form-control-label line-height-9 pt-2 text-2">
                                    {{ __('message.secret_key_label') }}
                                </label>
                                <div class="col-lg-8">
                                    <input id="twofa-secret-key" type="text" class="form-control text-3 h-auto py-2 bg-light-grey"
                                           :value="qrSecret" readonly spellcheck="false" translate="no">
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="button" class="border-0 bg-transparent p-0 text-color-primary text-decoration-underline text-3"
                                   @click="showSecretKey = false">{{ __('message.scan_barcode') }}</button>
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
                                   :aria-label="__('message.authentication_code')"
                                   :autocomplete="'one-time-code'" inputmode="numeric" spellcheck="false"
                                   :placeholder="__('message.enter_passcode')"
                                   maxlength="6"
                                   @keydown.enter="verify2fa" />
                        </div>
                        <Honeypot name="2fa_code" v-model="totp2faCode" @ready="totpHpReady = $event" />
                    </template>

                    <!-- Step: Done -->
                    <template v-if="twoFaStep === 'done'">
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle text-success icon-3rem" aria-hidden="true"></i>
                            <p class="mt-3 mb-0 fw-bold text-2">{{ __('message.you_are_all_set') }}</p>
                        </div>
                    </template>

                </template>
            </template>
            <template #controls>
                <template v-if="twoFaStep === 'password'">
                    <action-button action="cancel" @click="closeEnableModal" />
                    <action-button action="confirm" :label="__('message.validate')"
                                   :loading="verifyingPassword" :disabled="!userPassword"
                                   @click="validatePassword" />
                </template>
                <template v-if="twoFaStep === 'recovery'">
                    <action-button action="cancel" @click="closeEnableModal" />
                    <action-button action="confirm" icon="fas fa-arrow-right" :label="__('message.next')"
                                   :loading="enabling2fa" :disabled="!codesSaved"
                                   @click="goToQr" />
                </template>
                <template v-if="twoFaStep === 'qr'">
                    <action-button action="back" :label="__('message.previous')"
                                   @click="twoFaStep = 'recovery'; modalError = ''" />
                    <action-button action="confirm" icon="fas fa-arrow-right" :label="__('message.next')"
                                   @click="twoFaStep = 'totp'; modalError = ''; totp = ''" />
                </template>
                <template v-if="twoFaStep === 'totp'">
                    <action-button action="back" :label="__('message.previous')"
                                   @click="twoFaStep = 'qr'; modalError = ''; showSecretKey = false" />
                    <action-button action="confirm" :label="__('message.verify')"
                                   :loading="verifying2fa" :disabled="!totp || !totpHpReady"
                                   @click="verify2fa" />
                </template>
                <template v-if="twoFaStep === 'done'">
                    <action-button action="confirm" :label="__('message.done')" @click="onEnableDone" />
                </template>
            </template>
        </AppModal>

        <!-- Disable 2FA Modal -->
        <AppModal :showModal="showDisableModal" :onClose="closeDisableModal" :showCloseBtn="false">
            <template #title>
                <h5 class="modal-title fw-bold">{{ __('message.turn_off_authenticator') }}</h5>
            </template>
            <template #fields>
                <div v-if="disableError" class="alert alert-danger py-2 mb-3">{{ disableError }}</div>
                <p class="mb-3">{{ __('message.turn_off_authenticator_setup') }}</p>
                <form @submit.prevent="disable2fa">
                    <ClientField
                        type="password"
                        name="disable_password"
                        :label="__('message.password')"
                        autocomplete="current-password"
                        v-model="disablePassword"
                    />
                </form>
            </template>
            <template #controls>
                <action-button action="cancel" @click="closeDisableModal" />
                <action-button action="delete" icon="fas fa-power-off" :label="__('message.disable')"
                               :loading="disabling2fa" :disabled="!disablePassword" @click="disable2fa" />
            </template>
        </AppModal>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import Honeypot from '@/components/Reusable/Honeypot.vue'
const el      = document.getElementById('app-client')
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
const recoveryCopied    = ref(false) // transient "copied" icon state
const codesSaved        = ref(false) // gates "Next" once the codes were copied (or shown for manual copy)
const codesEl           = ref(null)
const enabling2fa       = ref(false)
const qrImage           = ref('')
const qrSecret          = ref('')
const showSecretKey     = ref(false)
const totp              = ref('')
// Digits only, max 6 — same as every other code box.
watch(totp, (v) => { const clean = String(v).replace(/\D/g, '').slice(0, 6); if (clean !== v) totp.value = clean })
const totp2faCode       = ref({})
const totpHpReady       = ref(false)
const verifying2fa      = ref(false)

// Disable modal state
const showDisableModal = ref(false)
const disabling2fa     = ref(false)
const disablePassword  = ref('')
const disableError     = ref('')

// The click is prevented so the native checkbox never flips on its own —
// enabling needs the setup wizard, disabling needs confirmation, so the
// switch only visually moves once is2faEnabled itself changes (on modal
// success), not just because it was clicked.
function onTwoFactorToggleClick() {
    is2faEnabled.value ? openDisableModal() : openEnableModal()
}

onMounted(async () => {
    try {
        const res  = await http.get(`/get-my-profile`)
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
    codesSaved.value       = false
    recoveryCodes.value    = []
    totp.value             = ''
    qrImage.value          = ''
    qrSecret.value         = ''
    showSecretKey.value    = false
    modalError.value       = ''
    twoFaLoading.value     = true
    showEnableModal.value  = true

    try {
        await http.get(`/show/verify-password`)
        // Password already confirmed this session — skip to recovery
        twoFaStep.value    = 'recovery'
        const res = await http.post(`/2fa-recovery-code`)
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
    codesSaved.value      = false
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
        await http.post(`/verify-password`, { user_password: userPassword.value })
        twoFaLoading.value = true
        twoFaStep.value    = 'recovery'
        const res = await http.post(`/2fa-recovery-code`)
        recoveryCodes.value = res.data?.data?.code ?? []
    } catch (e) {
        setModalError(e)
    } finally {
        verifyingPassword.value = false
        twoFaLoading.value      = false
    }
}

async function copyRecovery() {
    try {
        await navigator.clipboard.writeText(recoveryCodes.value.join('\n'))
        modalError.value     = ''
        recoveryCopied.value = true
        setTimeout(() => { recoveryCopied.value = false }, 5000)
    } catch {
        // No clipboard access (e.g. non-HTTPS) — select the codes for a manual copy.
        if (codesEl.value) globalThis.getSelection()?.selectAllChildren(codesEl.value)
        modalError.value = __('message.recovery_codes_copy_failed')
    }
    codesSaved.value = true
}

async function goToQr() {
    enabling2fa.value = true
    try {
        const res      = await http.post(`/2fa/enable`)
        qrImage.value  = res.data?.data?.image  ?? ''
        qrSecret.value = res.data?.data?.secret ?? ''
        twoFaStep.value = 'qr'
        modalError.value = ''
    } catch (e) {
        setModalError(e)
    } finally {
        enabling2fa.value = false
    }
}

async function verify2fa() {
    if (!totp.value) return
    modalError.value   = ''
    verifying2fa.value = true
    try {
        const res = await http.post(`/2fa/setupValidate`, { totp: totp.value, '2fa_code': totp2faCode.value })
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

function openDisableModal() {
    disablePassword.value  = ''
    disableError.value     = ''
    showDisableModal.value = true
}
function closeDisableModal() { showDisableModal.value = false }

async function disable2fa() {
    if (!disablePassword.value) return
    disableError.value = ''
    disabling2fa.value = true
    try {
        const res = await http.post(`/2fa/disable/${userId}`, { user_password: disablePassword.value })
        successHandler(res, COMPONENT)
        is2faEnabled.value     = false
        dateSinceEnabled.value = null
        closeDisableModal()
    } catch (e) {
        // Shown inside the modal — a wrong password should not close it.
        disableError.value = e?.response?.data?.message ?? e?.message ?? __('message.something_went_wrong')
    } finally {
        disabling2fa.value = false
    }
}
</script>

<style scoped>
.bg-light-grey { background-color: #f8f9fa; }
.icon-3rem { font-size: 3rem; }
.two-factor-icon {
    width: 40px;
    height: 40px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: #e7f4fb;
    color: #17a2e0;
    font-size: 16px;
}
.form-switch .form-check-input {
    width: 2.5em;
    height: 1.4em;
    cursor: pointer;
}
.badge-soft-success { background-color: #e6f9ef; color: #1a9d5c; }
.badge-soft-secondary { background-color: #eef0f2; color: #5c636a; }
</style>
