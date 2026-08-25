<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div v-if="!hasDataPopulated" class="row justify-content-center py-3"><loader /></div>

        <div class="row">
            <!-- Left: Profile Card -->
            <div class="col-md-6" v-if="hasDataPopulated">
                <div class="card card-light">
                    <div class="card-header">
                        <h4 class="card-title">{{ __('message.profile') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <ImageUpload
                                name="profile_pic"
                                :label="__('message.profile-picture')"
                                :labelStyle="{ display: 'none' }"
                                :labelCss="{ visibility: 'hidden', margin: 'auto' }"
                                :componentName="COMPONENT"
                                :value="profilePicUrl"
                                :onChange="onImageChange"
                                placeholderType="avatar"
                            />
                        </div>

                        <TextField
                            name="first_name"
                            :label="__('message.first_name')"
                            :required="true"
                            :value="form.first_name"
                            :onChange="onChange"
                            :error="errors.first_name"
                        />
                        <TextField
                            name="last_name"
                            :label="__('message.last_name')"
                            :required="true"
                            :value="form.last_name"
                            :onChange="onChange"
                            :error="errors.last_name"
                        />
                        <TextField
                            name="user_name"
                            :label="__('message.user_name')"
                            :required="true"
                            :value="form.user_name"
                            :onChange="onChange"
                            :error="errors.user_name"
                        />
                        <TextField
                            name="email"
                            :label="__('message.email')"
                            :value="form.email"
                            :disabled="true"
                            :onChange="() => {}"
                        />
                        <TextField
                            name="company"
                            :label="__('message.company')"
                            :required="true"
                            :value="form.company"
                            :onChange="onChange"
                            :error="errors.company"
                        />

                        <PhoneField
                            name="mobile"
                            :label="__('message.mobile')"
                            :required="true"
                            :value="form.mobile"
                            :initialCountry="form.mobile_country_iso"
                            :onChange="onChange"
                            :error="errors.mobile"
                            @countryChange="onMobileCountryChange"
                        />

                        <TextField
                            name="address"
                            :label="__('message.address')"
                            :required="true"
                            :value="form.address"
                            :onChange="onChange"
                            :error="errors.address"
                        />
                        <TextField
                            name="town"
                            :label="__('message.town')"
                            :value="form.town"
                            :onChange="onChange"
                        />

                        <DynamicSelect
                            name="timezone_id"
                            :label="__('message.timezone')"
                            :required="true"
                            :elements="timezones"
                            :value="timezones.find(t => t.id === form.timezone_id) ?? null"
                            :onChange="onTimezoneChange"
                            :clearable="false"
                            :error="errors.timezone_id"
                        />
                        <DynamicSelect
                            name="country"
                            :label="__('message.country')"
                            :required="true"
                            :elements="countries"
                            :value="countries.find(c => c.id === form.country) ?? null"
                            :onChange="onCountryChange"
                            :clearable="false"
                            :error="errors.country"
                        />
                        <DynamicSelect
                            name="state"
                            :label="__('message.state')"
                            :required="form.has_states"
                            :elements="states"
                            :value="states.find(s => s.id === form.state) ?? null"
                            :onChange="(val) => { form.state = val?.id ?? ''; setFieldError('state', undefined) }"
                            :error="errors.state"
                        />
                        <TextField
                            name="zip"
                            :label="__('message.zip')"
                            :value="form.zip"
                            :onChange="onChange"
                        />
                        <TextField
                            v-if="form.country === 'IN'"
                            name="gstin"
                            :label="__('message.gstin')"
                            :value="form.gstin"
                            :onChange="onChange"
                            :error="errors.gstin"
                        />
                    </div>
                    <div class="card-footer">
                        <action-button action="update" :loading="savingProfile" @click="submitProfile" />
                    </div>
                </div>
            </div>

            <!-- Right: Password + 2FA -->
            <div class="col-md-6" v-if="hasDataPopulated">
                <!-- Password Card -->
                <div class="card card-light mb-3">
                    <div class="card-header">
                        <h4 class="card-title">{{ __('message.change_password') }}</h4>
                    </div>
                    <div class="card-body">
                        <TextField
                            name="old_password"
                            :label="__('message.old_password')"
                            :required="true"
                            :value="pwForm.old_password"
                            type="password"
                            :onChange="onPwChange"
                            :error="errors.old_password"
                        />
                        <TextField
                            name="new_password"
                            :label="__('message.new_password')"
                            :required="true"
                            :value="pwForm.new_password"
                            type="password"
                            :onChange="onPwChange"
                            :error="errors.new_password"
                        />

                        <div v-if="pwForm.new_password.length > 0" class="mb-3">
                            <strong>{{ __('message.password_requirements') }}</strong>
                            <ul class="mt-1 password-rules-list">
                                <li
                                    v-for="rule in passwordRules"
                                    :key="rule.key"
                                    :class="rule.valid ? 'rule-valid' : 'rule-invalid'"
                                >
                                    {{ rule.label }}
                                </li>
                            </ul>
                        </div>

                        <TextField
                            name="confirm_password"
                            :label="__('message.confirm_password')"
                            :required="true"
                            :value="pwForm.confirm_password"
                            type="password"
                            :onChange="onPwChange"
                            :error="errors.confirm_password"
                        />
                    </div>
                    <div class="card-footer">
                        <action-button action="update" :loading="savingPassword" @click="submitPassword" />
                    </div>
                </div>

                <!-- 2FA Card -->
                <div class="card card-light">
                    <div class="card-header">
                        <h4 class="card-title">{{ __('message.two_factor_authentication') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-9">
                                <span>
                                    <i class="fas fa-shield-alt me-1 text-secondary shield-icon"></i>
                                    {{
                                        is2faEnabled
                                            ? __('message.2_step_verification') + ' ' + dateSinceEnabled
                                            : __('message.authenticator_app')
                                    }}
                                </span>
                            </div>
                            <div class="col-md-3 text-end">
                                <action-button
                                    v-if="!is2faEnabled"
                                    variant="primary"
                                    size="sm"
                                    icon="fas fa-toggle-on"
                                    :label="__('message.enable')"
                                    @click="openEnableModal"
                                />
                                <action-button
                                    v-else
                                    variant="secondary"
                                    size="sm"
                                    icon="fas fa-toggle-off"
                                    :label="__('message.disable')"
                                    @click="openDisableModal"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enable 2FA Modal -->
    <AppModal :showModal="showEnableModal" :onClose="closeEnableModal" :showCloseBtn="false">
        <template #title>
            <h4>{{ __('message.setup_authenticator') }}</h4>
        </template>
        <template #fields>
            <div v-if="twoFaLoading" class="row justify-content-center py-3"><loader /></div>
            <template v-else>

                <AppAlert :componentName="MODAL_COMPONENT" />

                <!-- Step: Password Verify -->
                <template v-if="twoFaStep === 'password'">
                    <TextField
                        name="user_password"
                        :label="__('message.to_continue_first_verify')"
                        type="password"
                        :required="true"
                        :value="userPassword"
                        :onChange="(val) => userPassword = val"
                        :placehold="__('message.enter_password')"
                        :keyupListener="(e) => { if (e.key === 'Enter' && userPassword) validatePassword() }"
                    />
                </template>

                <!-- Step: Recovery Codes -->
                <template v-if="twoFaStep === 'recovery'">
                    <p>{{ __('message.recovery_codes_are_used') }}</p>
                    <div class="card card-light px-0">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('message.recovery_codes') }}</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" @click="copyRecovery" v-tooltip="__('message.copy')">
                                    <i :class="recoveryCopied ? 'fas fa-check' : 'fas fa-clipboard'"></i>
                                </button>
                                <button type="button" class="btn btn-tool" @click="downloadRecovery" v-tooltip="__('message.download')">
                                    <i class="fas fa-download"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div v-for="code in recoveryCodes" :key="code" class="col-sm-6 text-center">
                                    <p>{{ code }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="mt-2 text-muted small">{{ __('message.treat_recovery_codes') }}</p>
                </template>

                <!-- Step: QR Code -->
                <template v-if="twoFaStep === 'qr'">
                    <template v-if="!showSecretKey">
                        <div class="text-center my-3">
                            <ul class="d-inline-block text-start">
                                <li>{{ __('message.get_authenticator_app') }}</li>
                                <li>{{ __('message.app_select') }} <b>{{ __('message.set_up_account') }}</b></li>
                                <li>{{ __('message.choose') }} <b>{{ __('message.scan_a_barcode') }}</b></li>
                            </ul>
                        </div>
                        <div class="text-center mb-3" v-html="qrImage"></div> <!-- nosemgrep: javascript.vue.security.audit.xss.templates.avoid-v-html.avoid-v-html -->
                        <div class="text-center">
                            <a href="#" class="text-decoration-underline" @click.prevent="showSecretKey = true">{{ __('message.cant_scan') }}</a>
                        </div>
                    </template>
                    <template v-else>
                        <div class="text-center">
                            <div class="d-inline-block text-start my-3">
                                <ul class="ps-3 mb-0">
                                    <li>{{ __('message.tap') }} <b>{{ __('message.menu') }}</b>, {{ __('message.then') }} <b>{{ __('message.set_up_account') }}</b></li>
                                    <li>{{ __('message.tap') }} <b>{{ __('message.enter_provided_key') }}</b></li>
                                    <li>{{ __('message.enter_email_address') }}</li>
                                </ul>
                                <input type="text" class="form-control w-100 my-3" :value="qrSecret" readonly disabled>
                                <ul class="ps-3 mb-0">
                                    <li>{{ __('message.make_sure') }} <b>{{ __('message.time_based') }}</b> {{ __('message.is_turned_on') }} <b>{{ __('message.add') }}</b> {{ __('message.to_finish') }}</li>
                                </ul>
                            </div>
                        </div>
                        <div class="text-center">
                            <a href="#" class="text-decoration-underline" @click.prevent="showSecretKey = false">{{ __('message.caps_scan_barcode') }}</a>
                        </div>
                    </template>
                </template>

                <!-- Step: TOTP Verify -->
                <template v-if="twoFaStep === 'totp'">
                    <p class="mb-3">{{ __('message.enter_the_code_you_see_in_the_app') }}</p>
                    <TextField
                        name="totp"
                        label=""
                        :labelStyle="{ display: 'none' }"
                        :value="totp"
                        :onChange="(val) => totp = val"
                        placehold="Enter Passcode..."
                        :max="6"
                    />
                    <Honeypot name="2fa_code" v-model="totp2faCode" @ready="totpHpReady = $event" />
                </template>

                <!-- Step: Done -->
                <template v-if="twoFaStep === 'done'">
                    <div class="text-center py-3">
                        <i class="fas fa-check-circle text-success icon-3rem"></i>
                        <p class="mt-3 mb-0 fw-bold">{{ __('message.you_are_all_set') }}</p>
                    </div>
                </template>

            </template>
        </template>
        <template #controls>
            <template v-if="twoFaStep === 'password'">
                <action-button
                    variant="primary"
                    icon="fas fa-check"
                    :label="__('message.validate')"
                    :loading="verifyingPassword"
                    :disabled="!userPassword"
                    @click="validatePassword"
                />
            </template>
            <template v-if="twoFaStep === 'recovery'">
                <action-button
                    variant="primary"
                    :label="__('message.next')"
                    icon="fas fa-arrow-right"
                    :disabled="!recoveryCopied"
                    :loading="enabling2fa"
                    @click="goToQr"
                />
            </template>
            <template v-if="twoFaStep === 'qr'">
                <div class="d-flex w-100 justify-content-between">
                    <button type="button" class="btn btn-light" @click="twoFaStep = 'recovery'; clearModalAlert()">
                        <i class="fas fa-arrow-left me-1"></i>{{ __('message.previous') }}
                    </button>
                    <action-button variant="primary" :label="__('message.next')" icon="fas fa-arrow-right" @click="twoFaStep = 'totp'; clearModalAlert()" />
                </div>
            </template>
            <template v-if="twoFaStep === 'totp'">
                <div class="d-flex w-100 justify-content-between">
                    <button type="button" class="btn btn-light" @click="twoFaStep = 'qr'; clearModalAlert()">
                        <i class="fas fa-arrow-left me-1"></i>{{ __('message.previous') }}
                    </button>
                    <action-button
                        variant="primary"
                        :label="__('message.verify')"
                        :loading="verifying2fa"
                        :disabled="!totp || !totpHpReady"
                        @click="verify2fa"
                    />
                </div>
            </template>
            <template v-if="twoFaStep === 'done'">
                <action-button variant="primary" :label="__('message.done')" @click="onEnableDone" />
            </template>
        </template>
    </AppModal>

    <!-- Disable 2FA Modal -->
    <AppModal :showModal="showDisableModal" :onClose="closeDisableModal" :showCloseBtn="false">
        <template #title>
            <h4>{{ __('message.turn_off_authenticator') }}</h4>
        </template>
        <template #fields>
            <span>{{ __('message.turn_off_authenticator_setup') }}</span>
        </template>
        <template #controls>
            <action-button
                variant="secondary"
                icon="fas fa-power-off"
                :label="__('message.disable')"
                :loading="disabling2fa"
                @click="disable2fa"
            />
        </template>
    </AppModal>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { useAlertStore } from '@/core/stores/alert.js'
import { validateForm } from '@/helpers/formUtils.js'
import ImageUpload from '@/components/Reusable/FormField/ImageUpload.vue'
import TextField from '@/components/Reusable/FormField/TextField.vue'
import Honeypot from '@/components/Reusable/Honeypot.vue'
import { profileSchema, passwordChangeSchema } from '@/validations/admin/profileValidations'
import { passwordChecks } from '@/validations/client/authSchemas'

const COMPONENT = 'profile-index'
const MODAL_COMPONENT = 'profile-2fa-modal'
const clearModalAlert = () => useAlertStore().unsetAlert()

const { errors, setErrors, setFieldError } = useForm()

const hasDataPopulated = ref(false)
const savingProfile    = ref(false)
const savingPassword   = ref(false)
const enabling2fa      = ref(false)
const disabling2fa     = ref(false)
const verifying2fa     = ref(false)
const is2faEnabled     = ref(false)
const dateSinceEnabled = ref(null)
const qrImage          = ref('')
const qrSecret         = ref('')
const totp             = ref('')
const totp2faCode      = ref({})
const totpHpReady      = ref(false)

// Enable 2FA modal state
const showEnableModal    = ref(false)
const twoFaStep          = ref('password')
const twoFaLoading       = ref(false)
const recoveryCodes      = ref([])
const recoveryCopied     = ref(false)
const showSecretKey      = ref(false)
const userPassword       = ref('')
const verifyingPassword  = ref(false)

// Disable 2FA modal state
const showDisableModal = ref(false)
const profilePicUrl    = ref('')
const selectedImage    = ref(null)
const userId           = ref(null)

const timezones = ref([])
const countries = ref([])
const states    = ref([])

const form = reactive({
    first_name:         '',
    last_name:          '',
    user_name:          '',
    email:              '',
    company:            '',
    mobile:             '',
    mobile_country_iso: 'IN',
    mobile_code:        '',
    address:            '',
    town:               '',
    timezone_id:        null,
    country:            '',
    state:              '',
    has_states:         false,
    zip:                '',
    gstin:              '',
})

const pwForm = reactive({
    old_password:     '',
    new_password:     '',
    confirm_password: '',
})

const PASSWORD_RULE_LABELS = {
    length:  'Between 8-16 characters',
    lower:   'Lowercase characters (a-z)',
    upper:   'Uppercase characters (A-Z)',
    number:  'Numbers (0-9)',
    special: 'Special characters (~*!@$#%_+.?:)',
}

const passwordRules = computed(() => {
    const checks = passwordChecks(pwForm.new_password)
    return Object.entries(PASSWORD_RULE_LABELS).map(([key, label]) => ({ key, label, valid: checks[key] }))
})

onMounted(async () => {
    try {
        const [profileRes, countriesRes] = await Promise.all([
            http.get(`/profile`),
            http.get(`/profile/countries`),
        ])

        const d    = profileRes.data?.data ?? {}
        const user = d.user ?? {}

        userId.value           = user.id
        is2faEnabled.value     = Boolean(d.is2faEnabled)
        dateSinceEnabled.value = d.dateSinceEnabled ?? null

        const tzRaw = d.timezones ?? {}
        timezones.value = Object.entries(tzRaw).map(([id, name]) => ({ id: Number(id), name }))

        countries.value = countriesRes.data?.data?.countries ?? []

        Object.assign(form, {
            first_name:         user.first_name         ?? '',
            last_name:          user.last_name           ?? '',
            user_name:          user.user_name           ?? '',
            email:              user.email               ?? '',
            company:            user.company             ?? '',
            mobile:             user.mobile              ?? '',
            mobile_country_iso: user.mobile_country_iso  ?? 'IN',
            mobile_code:        user.mobile_code         ?? '',
            address:            user.address             ?? '',
            town:               user.town                ?? '',
            timezone_id:        user.timezone_id ? Number(user.timezone_id) : null,
            country:            user.country             ?? '',
            state:              user.state               ?? '',
            zip:                user.zip                 ?? '',
            gstin:              user.gstin               ?? '',
        })

        if (user.profile_pic) {
            profilePicUrl.value = user.profile_pic
        }

        if (form.country) {
            await loadStates(form.country)
        }
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        hasDataPopulated.value = true
    }
})

async function loadStates(countryCode) {
    try {
        const res = await http.get(`/profile/states/${countryCode}`)
        states.value = res.data?.data?.states ?? []
    } catch {
        states.value = []
    }
    form.has_states = states.value.length > 0
}

function onChange(value, name) {
    if (name === 'profile_pic') return
    setFieldError(name, undefined)
    form[name] = value ?? ''
}

function onPwChange(value, name) {
    setFieldError(name, undefined)
    pwForm[name] = value ?? ''
}

function onTimezoneChange(val) {
    setFieldError('timezone_id', undefined)
    form.timezone_id = val?.id ?? null
}

function onImageChange(value, _name) {
    selectedImage.value = value ?? null
    if (value?.image) profilePicUrl.value = value.image
}

function onMobileCountryChange({ iso, dialCode }) {
    form.mobile_country_iso = iso
    form.mobile_code        = dialCode
}

async function onCountryChange(val) {
    setFieldError('country', undefined)
    form.country     = val?.id ?? ''
    form.state       = ''
    form.has_states  = false
    states.value     = []
    if (form.country) {
        await loadStates(form.country)
    }
}

async function submitProfile() {
    if (!await validateForm(profileSchema, form, setErrors)) return

    savingProfile.value = true
    try {
        const data = new FormData()
        Object.entries(form).forEach(([k, v]) => {
            if (k !== 'has_states' && v !== null && v !== undefined) data.append(k, v)
        })
        if (selectedImage.value?.file) {
            data.append('profile_pic', selectedImage.value.file, selectedImage.value.name || 'profile_pic.jpg')
        }
        data.append('_method', 'PATCH')

        const res = await http.post(`/profile`, data, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        savingProfile.value = false
    }
}

async function submitPassword() {
    if (!await validateForm(passwordChangeSchema, pwForm, setErrors)) return

    savingPassword.value = true
    try {
        const res = await http.patch(`/password`, {
            old_password:     pwForm.old_password,
            new_password:     pwForm.new_password,
            confirm_password: pwForm.confirm_password,
        })
        successHandler(res, COMPONENT)
        pwForm.old_password = pwForm.new_password = pwForm.confirm_password = ''
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        savingPassword.value = false
    }
}

// ── Enable 2FA modal ──────────────────────────────────────────────────────────

async function openEnableModal() {
    twoFaStep.value       = 'password'
    userPassword.value    = ''
    recoveryCopied.value  = false
    recoveryCodes.value   = []
    totp.value            = ''
    qrImage.value         = ''
    qrSecret.value        = ''
    showSecretKey.value   = false
    clearModalAlert()
    twoFaLoading.value    = true
    showEnableModal.value = true

    try {
        await http.get(`/show/verify-password`)
        // Password already confirmed — skip straight to recovery codes
        twoFaStep.value    = 'recovery'
        const res = await http.post(`/2fa-recovery-code`)
        recoveryCodes.value = res.data?.data?.code ?? []
    } catch {
        // Not yet confirmed — stay on password step
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
    clearModalAlert()
    verifyingPassword.value = true
    try {
        await http.post(`/verify-password`, { user_password: userPassword.value })
        // Password verified — load recovery codes
        twoFaLoading.value = true
        twoFaStep.value    = 'recovery'
        const res = await http.post(`/2fa-recovery-code`)
        recoveryCodes.value = res.data?.data?.code ?? []
    } catch (e) {
        errorHandler(e, MODAL_COMPONENT)
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

function downloadRecovery() {
    const content = [
        __('message.recovery_codes'),
        form.email,
        '',
        ...recoveryCodes.value,
        '',
        __('message.treat_recovery_codes'),
    ].join('\n')
    const url = URL.createObjectURL(new Blob([content], { type: 'text/plain' }))
    const link = Object.assign(document.createElement('a'), { href: url, download: `Backup-recovery-codes-${form.email}.txt` })
    link.click()
    URL.revokeObjectURL(url)
}

async function goToQr() {
    enabling2fa.value = true
    try {
        const res      = await http.post(`/2fa/enable`)
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
    clearModalAlert()
    verifying2fa.value = true
    try {
        const res = await http.post(`/2fa/setupValidate`, { totp: totp.value, '2fa_code': totp2faCode.value })
        successHandler(res, COMPONENT)
        twoFaStep.value = 'done'
    } catch (e) {
        errorHandler(e, MODAL_COMPONENT)
    } finally {
        verifying2fa.value = false
    }
}

function onEnableDone() {
    is2faEnabled.value = true
    closeEnableModal()
}

// ── Disable 2FA modal ─────────────────────────────────────────────────────────

function openDisableModal()  { showDisableModal.value = true }
function closeDisableModal() { showDisableModal.value = false }

async function disable2fa() {
    disabling2fa.value = true
    try {
        const res = await http.post(`/2fa/disable/${userId.value}`)
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


<style scoped>
.password-rules-list { padding-left: 1.25rem; }
.rule-valid   { color: green; font-size: 14px; margin-bottom: 4px; }
.rule-invalid { color: red;   font-size: 14px; margin-bottom: 4px; }
.shield-icon  { font-size: 20px; vertical-align: middle; }
.icon-3rem    { font-size: 3rem; }
</style>
