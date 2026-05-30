<template>
    <div>
        <AppCard :title="__('message.profile_information')">
        <inline-loader v-if="!hasDataPopulated" />

        <div v-if="hasDataPopulated">

            <!-- Avatar -->
            <div class="d-flex justify-content-center mb-5">
                <ProfileImageUpload
                    :src="avatarPreview"
                    :initials="initials"
                    :alt="form.first_name"
                    @change="onImageChange"
                />
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

                <!-- Email — read-only -->
                <div class="form-group row">
                    <label class="col-lg-3 col-form-label form-control-label line-height-9 pt-2 text-2">
                        {{ __('message.email') }}
                    </label>
                    <div class="col-lg-9">
                        <input class="form-control text-3 h-auto py-2"
                               type="email" :value="form.email" disabled
                               style="background-color: #f8f9fa;">
                    </div>
                </div>

                <ClientField type="text" name="company"
                             :label="__('message.company')"
                             v-model="form.company"
                             :error="errors.company"
                             :required="true" />

                <!-- Mobile -->
                <div class="form-group row">
                    <label class="col-lg-3 col-form-label form-control-label line-height-9 pt-2 text-2 required">
                        {{ __('message.mobile') }}
                    </label>
                    <div class="col-lg-9">
                        <input class="form-control text-3 h-auto py-2"
                               :class="{ 'is-invalid': errors.mobile }"
                               type="text" v-model="form.mobile">
                        <div v-if="errors.mobile" class="invalid-feedback">{{ errors.mobile }}</div>
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
                    <option v-for="c in countries" :key="c.id" :value="c.code">{{ c.name }}</option>
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
        </AppCard>
    </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { profileSchema } from '@/validations/client/profile.js'
import ProfileImageUpload from '@/themes/porto/components/common/ProfileImageUpload.vue'

const el           = document.getElementById('app-client')
const baseUrl      = el?.dataset?.baseUrl ?? ''
const avatarPreview = ref(el?.dataset?.userAvatar ?? '')
const selectedImage = ref(null)

const COMPONENT = 'client-page'

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

const countries = ref([])
const states    = ref([])

onMounted(async () => {
    try {
        const [profileRes, countriesRes] = await Promise.all([
            http.get(`${baseUrl}/my-profile`),
            http.get(`${baseUrl}/dependency/countries`, { params: { limit: 'all' } }),
        ])
        countries.value = countriesRes.data?.data?.countries ?? []
        const d    = profileRes.data?.data ?? {}
        const user = d.user ?? {}
        Object.assign(form, {
            first_name: user.first_name ?? '', last_name: user.last_name ?? '',
            user_name: user.user_name ?? '', email: user.email ?? '',
            company: user.company ?? '', mobile: user.mobile ?? '',
            mobile_code: user.mobile_code ?? '', mobile_country_iso: user.mobile_country_iso ?? '',
            address: user.address ?? '', town: user.town ?? '',
            country: user.country ?? '', state: user.state ?? '', zipcode: user.zipcode ?? user.zip ?? '',
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
        const res = await http.get(`${baseUrl}/dependency/states`, { params: { country: code, limit: 'all' } })
        states.value = (res.data?.data?.states ?? []).map(st => ({
            id:   st.iso2,
            name: st.name,
        }))
    } catch { states.value = [] }
}

async function onCountryChange() {
    errors.value = { ...errors.value, country: undefined }
    form.state = ''
    states.value = []
    if (form.country) await loadStates(form.country)
}

function onImageChange({ file, previewUrl }) {
    selectedImage.value  = file
    avatarPreview.value  = previewUrl
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
        if (selectedImage.value) {
            data.append('profile_pic', selectedImage.value, 'profile_pic.jpg')
        }
        data.append('_method', 'PATCH')
        const res = await http.post(`${baseUrl}/my-profile`, data, { headers: { 'Content-Type': 'multipart/form-data' } })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        savingProfile.value = false
    }
}
</script>
