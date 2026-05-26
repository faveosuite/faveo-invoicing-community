<template>
    <div>
        <AppAlert componentName="client-profile" />
        <inline-loader v-if="!hasDataPopulated" />

        <div v-if="hasDataPopulated">

            <!-- Avatar -->
            <div class="d-flex justify-content-center mb-5">
                <div class="profile-image-outer-container">
                    <div class="profile-image-inner-container bg-color-primary">
                        <img v-if="avatarUrl" :src="avatarUrl" :alt="form.first_name">
                        <span v-else class="d-flex align-items-center justify-content-center h-100 text-white fw-bold"
                              style="font-size:2rem">
                            {{ initials }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Profile form -->
            <form @submit.prevent="submitProfile" class="needs-validation">

                <ClientField type="text" name="first_name"
                             :label="__('message.first_name')"
                             v-model="form.first_name"
                             :error="errors.first_name"
                             :required="true" />

                <ClientField type="text" name="last_name"
                             :label="__('message.last_name')"
                             v-model="form.last_name"
                             :error="errors.last_name"
                             :required="true" />

                <ClientField type="text" name="user_name"
                             :label="__('message.user_name')"
                             v-model="form.user_name"
                             :error="errors.user_name"
                             :required="true" />

                <!-- Email — disabled display with verified badge + OTP flow -->
                <div class="form-group row">
                    <label class="col-lg-3 col-form-label form-control-label line-height-9 pt-2 text-2">
                        {{ __('message.email') }}
                    </label>
                    <div class="col-lg-9 d-flex align-items-center gap-2">
                        <input class="form-control text-3 h-auto py-2"
                               type="email" :value="form.email" disabled>
                        <span class="badge flex-shrink-0"
                              :class="profileData.email_verified ? 'bg-success' : 'bg-warning text-dark'">
                            {{ profileData.email_verified ? __('message.verified') : __('message.unverified') }}
                        </span>
                    </div>
                </div>

                <div class="form-group row" v-if="!profileData.email_verified">
                    <div class="col-lg-9 offset-lg-3">
                        <div v-if="!emailOtpSent" class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary btn-modern"
                                    :disabled="sendingEmailOtp" @click="sendEmailOtp">
                                {{ __('message.send_otp') }}
                            </button>
                        </div>
                        <div v-else class="d-flex gap-2 align-items-center">
                            <input class="form-control text-3 h-auto py-1" style="max-width:160px"
                                   type="text" v-model="emailOtp" :placeholder="__('message.enter_otp')">
                            <button type="button" class="btn btn-sm btn-primary btn-modern"
                                    :disabled="verifyingEmailOtp" @click="verifyEmailOtp">
                                {{ __('message.verify_otp') }}
                            </button>
                            <button type="button" class="btn btn-sm btn-light"
                                    @click="emailOtpSent = false; emailOtp = ''">
                                {{ __('message.cancel') }}
                            </button>
                        </div>
                    </div>
                </div>

                <ClientField type="text" name="company"
                             :label="__('message.company')"
                             v-model="form.company"
                             :error="errors.company"
                             :required="true" />

                <!-- Mobile — with verified badge + OTP flow -->
                <div class="form-group row">
                    <label class="col-lg-3 col-form-label form-control-label line-height-9 pt-2 text-2 required">
                        {{ __('message.mobile') }}
                    </label>
                    <div class="col-lg-9 d-flex align-items-center gap-2">
                        <input class="form-control text-3 h-auto py-2"
                               :class="{ 'is-invalid': errors.mobile }"
                               type="text" v-model="form.mobile">
                        <span class="badge flex-shrink-0"
                              :class="profileData.mobile_verified ? 'bg-success' : 'bg-warning text-dark'">
                            {{ profileData.mobile_verified ? __('message.verified') : __('message.unverified') }}
                        </span>
                        <div v-if="errors.mobile" class="invalid-feedback">{{ errors.mobile }}</div>
                    </div>
                </div>

                <div class="form-group row" v-if="!profileData.mobile_verified">
                    <div class="col-lg-9 offset-lg-3">
                        <div v-if="!mobileOtpSent" class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary btn-modern"
                                    :disabled="sendingMobileOtp" @click="sendMobileOtp">
                                {{ __('message.send_otp') }}
                            </button>
                        </div>
                        <div v-else class="d-flex gap-2 align-items-center">
                            <input class="form-control text-3 h-auto py-1" style="max-width:160px"
                                   type="text" v-model="mobileOtp" :placeholder="__('message.enter_otp')">
                            <button type="button" class="btn btn-sm btn-primary btn-modern"
                                    :disabled="verifyingMobileOtp" @click="verifyMobileOtp">
                                {{ __('message.verify_otp') }}
                            </button>
                            <button type="button" class="btn btn-sm btn-light"
                                    @click="mobileOtpSent = false; mobileOtp = ''">
                                {{ __('message.cancel') }}
                            </button>
                        </div>
                    </div>
                </div>

                <ClientField type="text" name="address"
                             :label="__('message.address')"
                             v-model="form.address"
                             :error="errors.address"
                             :required="true" />

                <ClientField type="text" name="town"
                             :label="__('message.town')"
                             v-model="form.town" />

                <ClientField type="select" name="country"
                             :label="__('message.country')"
                             v-model="form.country"
                             :error="errors.country"
                             @change="onCountryChange">
                    <option value="">-- {{ __('message.select') }} --</option>
                    <option v-for="c in countries" :key="c.id" :value="c.id">{{ c.name }}</option>
                </ClientField>

                <ClientField type="select" name="state"
                             :label="__('message.state')"
                             v-model="form.state">
                    <option value="">-- {{ __('message.select') }} --</option>
                    <option v-for="s in states" :key="s.id" :value="s.id">{{ s.name }}</option>
                </ClientField>

                <ClientField type="text" name="zipcode"
                             :label="__('message.zip')"
                             v-model="form.zipcode" />

                <div class="form-group row">
                    <div class="col-lg-9 offset-lg-3">
                        <button type="submit" class="btn btn-primary btn-modern" :disabled="savingProfile">
                            <i v-if="savingProfile" class="fas fa-circle-notch fa-spin me-1"></i>
                            {{ __('message.save') }}
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { profileSchema } from '@/validations/client/profile.js'

const el        = document.getElementById('app-client')
const baseUrl   = el?.dataset?.baseUrl ?? ''
const avatarUrl = el?.dataset?.userAvatar ?? ''

const COMPONENT = 'client-profile'

const hasDataPopulated = ref(false)
const savingProfile    = ref(false)

const form = reactive({
    first_name: '', last_name: '', user_name: '', email: '',
    company: '', mobile: '', mobile_code: '', mobile_country_iso: '',
    address: '', town: '', country: '', state: '', zipcode: '',
})
const errors = ref({})

const initials = computed(() => {
    const f = form.first_name?.[0] ?? ''
    const l = form.last_name?.[0] ?? ''
    return (f + l).toUpperCase() || '?'
})

const profileData = reactive({ email_verified: false, mobile_verified: false })

const countries = ref([])
const states    = ref([])

const emailOtpSent      = ref(false)
const emailOtp          = ref('')
const sendingEmailOtp   = ref(false)
const verifyingEmailOtp = ref(false)

const mobileOtpSent      = ref(false)
const mobileOtp          = ref('')
const sendingMobileOtp   = ref(false)
const verifyingMobileOtp = ref(false)

onMounted(async () => {
    try {
        const [profileRes, countriesRes] = await Promise.all([
            http.get(`${baseUrl}/my-profile`),
            http.get(`${baseUrl}/profile/countries`),
        ])
        countries.value = countriesRes.data?.data?.countries ?? []
        const d    = profileRes.data?.data ?? {}
        const user = d.user ?? {}
        profileData.email_verified  = Boolean(user.email_verified_at ?? d.email_verified)
        profileData.mobile_verified = Boolean(user.mobile_verified_at ?? d.mobile_verified)
        Object.assign(form, {
            first_name: user.first_name ?? '', last_name: user.last_name ?? '',
            user_name: user.user_name ?? '', email: user.email ?? '',
            company: user.company ?? '', mobile: user.mobile ?? '',
            mobile_code: user.mobile_code ?? '', mobile_country_iso: user.mobile_country_iso ?? '',
            address: user.address ?? '', town: user.town ?? '',
            country: user.country ?? '', state: user.state ?? '', zipcode: user.zipcode ?? '',
        })
        if (form.country) await loadStates(form.country)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        hasDataPopulated.value = true
    }
})

async function loadStates(code) {
    if (!code) { states.value = []; return }
    try {
        const res = await http.get(`${baseUrl}/profile/states/${code}`)
        states.value = res.data?.data?.states ?? []
    } catch { states.value = [] }
}

async function onCountryChange() {
    errors.value = { ...errors.value, country: undefined }
    form.state = ''
    states.value = []
    if (form.country) await loadStates(form.country)
}

async function submitProfile() {
    errors.value = {}
    try {
        profileSchema.validateSync(form, { abortEarly: false })
    } catch (err) {
        const map = {}
        err.inner?.forEach(e => { if (e.path && !map[e.path]) map[e.path] = e.message })
        errors.value = map
        return
    }
    savingProfile.value = true
    try {
        const data = new FormData()
        Object.entries(form).forEach(([k, v]) => { if (v != null) data.append(k, v) })
        data.append('_method', 'PATCH')
        const res = await http.post(`${baseUrl}/my-profile`, data, { headers: { 'Content-Type': 'multipart/form-data' } })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        savingProfile.value = false
    }
}

async function sendEmailOtp() {
    sendingEmailOtp.value = true
    try {
        await http.post(`${baseUrl}/profile/email/send-otp`)
        emailOtpSent.value = true
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { sendingEmailOtp.value = false }
}

async function verifyEmailOtp() {
    verifyingEmailOtp.value = true
    try {
        const res = await http.post(`${baseUrl}/profile/email/verify-otp`, { otp: emailOtp.value })
        successHandler(res, COMPONENT)
        profileData.email_verified = true
        emailOtpSent.value = false; emailOtp.value = ''
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { verifyingEmailOtp.value = false }
}

async function sendMobileOtp() {
    sendingMobileOtp.value = true
    try {
        await http.post(`${baseUrl}/profile/mobile/send-otp`)
        mobileOtpSent.value = true
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { sendingMobileOtp.value = false }
}

async function verifyMobileOtp() {
    verifyingMobileOtp.value = true
    try {
        const res = await http.post(`${baseUrl}/profile/mobile/verify-otp`, { otp: mobileOtp.value })
        successHandler(res, COMPONENT)
        profileData.mobile_verified = true
        mobileOtpSent.value = false; mobileOtp.value = ''
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { verifyingMobileOtp.value = false }
}
</script>
