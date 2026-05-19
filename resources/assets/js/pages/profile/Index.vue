<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <inline-loader v-if="!hasDataPopulated" />

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
                            />
                        </div>

                        <TextField
                            name="first_name"
                            :label="__('message.first_name') + ' *'"
                            :value="form.first_name"
                            :onChange="onChange"
                            :required="true"
                        />
                        <TextField
                            name="last_name"
                            :label="__('message.last_name') + ' *'"
                            :value="form.last_name"
                            :onChange="onChange"
                        />
                        <TextField
                            name="user_name"
                            :label="__('message.user_name') + ' *'"
                            :value="form.user_name"
                            :onChange="onChange"
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
                            :label="__('message.company') + ' *'"
                            :value="form.company"
                            :onChange="onChange"
                        />

                        <PhoneField
                            name="mobile"
                            :label="__('message.mobile') + ' *'"
                            :value="form.mobile"
                            :initialCountry="form.mobile_country_iso"
                            :onChange="onChange"
                            @countryChange="onMobileCountryChange"
                        />

                        <TextField
                            name="address"
                            :label="__('message.address') + ' *'"
                            :value="form.address"
                            :onChange="onChange"
                        />
                        <TextField
                            name="town"
                            :label="__('message.town')"
                            :value="form.town"
                            :onChange="onChange"
                        />

                        <SelectField
                            name="timezone_id"
                            :label="__('message.timezone') + ' *'"
                            :elements="timezones"
                            :value="timezones.find(t => t.id === form.timezone_id) ?? null"
                            :onChange="(val) => form.timezone_id = val?.id ?? null"
                            :clearable="false"
                        />
                        <SelectField
                            name="country"
                            :label="__('message.country') + ' *'"
                            :elements="countries"
                            :value="countries.find(c => c.id === form.country) ?? null"
                            :onChange="onCountryChange"
                            :clearable="false"
                        />
                        <SelectField
                            name="state"
                            :label="__('message.state')"
                            :elements="states"
                            :value="states.find(s => s.id === form.state) ?? null"
                            :onChange="(val) => form.state = val?.id ?? ''"
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
                            :label="__('message.old_password') + ' *'"
                            :value="pwForm.old_password"
                            type="password"
                            :onChange="onPwChange"
                            :required="true"
                        />
                        <TextField
                            name="new_password"
                            :label="__('message.new_password') + ' *'"
                            :value="pwForm.new_password"
                            type="password"
                            :onChange="onPwChange"
                            :required="true"
                        />

                        <div v-if="pwForm.new_password.length > 0" class="mb-3">
                            <strong>{{ __('message.password_requirements') }}</strong>
                            <ul class="mt-1" style="padding-left: 1.25rem">
                                <li
                                    v-for="rule in passwordRules"
                                    :key="rule.key"
                                    :style="{ color: rule.valid ? 'green' : 'red', fontSize: '14px', marginBottom: '4px' }"
                                >
                                    {{ rule.label }}
                                </li>
                            </ul>
                        </div>

                        <TextField
                            name="confirm_password"
                            :label="__('message.confirm_password') + ' *'"
                            :value="pwForm.confirm_password"
                            type="password"
                            :onChange="onPwChange"
                            :required="true"
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
                                    <i class="fas fa-shield-alt me-1 text-secondary" style="font-size:20px; vertical-align:middle;"></i>
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
                                    @click="showSetup = true"
                                />
                                <action-button
                                    v-else
                                    variant="secondary"
                                    size="sm"
                                    icon="fas fa-toggle-off"
                                    :label="__('message.disable')"
                                    :loading="disabling2fa"
                                    @click="disable2fa"
                                />
                            </div>
                        </div>

                        <!-- 2FA Setup inline section -->
                        <div v-if="showSetup && !is2faEnabled" class="mt-3">
                            <div v-if="!qrLoaded" class="text-center">
                                <action-button variant="success" size="sm" :label="__('message.enable') + ' 2FA'" :loading="enabling2fa" @click="load2faQr" />
                            </div>
                            <div v-else>
                                <p class="mb-2">{{ __('message.scan_barcode') }}</p>
                                <div class="text-center mb-3">
                                    <img :src="qrImage" alt="QR Code" style="max-width:180px" />
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">{{ __('message.secret_key_label') }}:</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        :value="qrSecret"
                                        readonly
                                        disabled
                                    />
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">{{ __('message.enter_otp_code') }} *</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        style="max-width: 200px"
                                        v-model="totp"
                                        placeholder="000000"
                                        maxlength="6"
                                    />
                                </div>
                                <action-button action="confirm" size="sm" :label="__('message.verify')" :loading="verifying2fa" @click="verify2fa" />
                                <action-button action="cancel" size="sm" class="ms-2" @click="cancelSetup" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import ImageUpload from '@/components/Reusable/FormField/ImageUpload.vue'

const COMPONENT = 'profile-index'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const hasDataPopulated = ref(false)
const savingProfile    = ref(false)
const savingPassword   = ref(false)
const enabling2fa      = ref(false)
const disabling2fa     = ref(false)
const verifying2fa     = ref(false)
const is2faEnabled     = ref(false)
const dateSinceEnabled = ref(null)
const showSetup        = ref(false)
const qrLoaded         = ref(false)
const qrImage          = ref('')
const qrSecret         = ref('')
const totp             = ref('')
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
    zip:                '',
    gstin:              '',
})

const pwForm = reactive({
    old_password:     '',
    new_password:     '',
    confirm_password: '',
})

const passwordRules = computed(() => [
    { key: 'length',  label: 'Between 8-16 characters',           valid: pwForm.new_password.length >= 8 && pwForm.new_password.length <= 16 },
    { key: 'lower',   label: 'Lowercase characters (a-z)',         valid: /[a-z]/.test(pwForm.new_password) },
    { key: 'upper',   label: 'Uppercase characters (A-Z)',         valid: /[A-Z]/.test(pwForm.new_password) },
    { key: 'number',  label: 'Numbers (0-9)',                      valid: /\d/.test(pwForm.new_password) },
    { key: 'special', label: 'Special characters (~*!@$#%_+.?:)', valid: /[~*!@$#%_+:?.;(){}]/.test(pwForm.new_password) },
])

onMounted(async () => {
    try {
        const [profileRes, countriesRes] = await Promise.all([
            http.get(`${baseUrl}/profile`),
            http.get(`${baseUrl}/profile/countries`),
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
        const res = await http.get(`${baseUrl}/profile/states/${countryCode}`)
        states.value = res.data?.data?.states ?? []
    } catch {
        states.value = []
    }
}

function onChange(value, name) {
    if (name === 'profile_pic') return
    form[name] = value ?? ''
}

function onPwChange(value, name) {
    pwForm[name] = value ?? ''
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
    form.country = val?.id ?? ''
    form.state   = ''
    states.value = []
    if (form.country) {
        await loadStates(form.country)
    }
}

async function submitProfile() {
    savingProfile.value = true
    try {
        const data = new FormData()
        Object.entries(form).forEach(([k, v]) => {
            if (v !== null && v !== undefined) data.append(k, v)
        })
        if (selectedImage.value?.file) {
            data.append('profile_pic', selectedImage.value.file, selectedImage.value.name || 'profile_pic.jpg')
        }
        data.append('_method', 'PATCH')

        const res = await http.post(`${baseUrl}/profile`, data, {
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
    savingPassword.value = true
    try {
        const res = await http.patch(`${baseUrl}/password`, {
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

async function load2faQr() {
    enabling2fa.value = true
    try {
        const res  = await http.post(`${baseUrl}/2fa/enable`)
        qrImage.value  = res.data?.data?.image  ?? ''
        qrSecret.value = res.data?.data?.secret ?? ''
        qrLoaded.value = true
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        enabling2fa.value = false
    }
}

async function verify2fa() {
    if (!totp.value) return
    verifying2fa.value = true
    try {
        const res = await http.post(`${baseUrl}/2fa/setupValidate`, { totp: totp.value })
        successHandler(res, COMPONENT)
        is2faEnabled.value = true
        showSetup.value    = false
        qrLoaded.value     = false
        totp.value         = ''
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        verifying2fa.value = false
    }
}

function cancelSetup() {
    showSetup.value  = false
    qrLoaded.value   = false
    qrImage.value    = ''
    qrSecret.value   = ''
    totp.value       = ''
}

async function disable2fa() {
    disabling2fa.value = true
    try {
        const res = await http.post(`${baseUrl}/2fa/disable/${userId.value}`)
        successHandler(res, COMPONENT)
        is2faEnabled.value     = false
        dateSinceEnabled.value = null
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        disabling2fa.value = false
    }
}
</script>

