<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.system-settings') }}</h4>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <template v-else>
                <div class="card-body">

                    <!-- Row 1: Company Name · Company Email · Website -->
                    <div class="row">
                        <div class="col-md-4">
                            <TextField
                                name="company"
                                :label="__('message.company') + ' *'"
                                :value="form.company"
                                :onChange="(val, name) => form[name] = val"
                            />
                        </div>
                        <div class="col-md-4">
                            <TextField
                                name="company_email"
                                type="email"
                                :label="__('message.company-email') + ' *'"
                                :value="form.company_email"
                                :onChange="(val, name) => form[name] = val"
                            />
                        </div>
                        <div class="col-md-4">
                            <TextField
                                name="website"
                                :label="__('message.website') + ' *'"
                                :value="form.website"
                                :onChange="(val, name) => form[name] = val"
                                placeholder="https://example.com"
                            />
                        </div>
                    </div>

                    <!-- Row 2: Title · Meta Title (Admin) · Meta Title (Client) -->
                    <div class="row">
                        <div class="col-md-4">
                            <TextField
                                name="title"
                                :label="__('message.title')"
                                :value="form.title"
                                :onChange="(val, name) => form[name] = val"
                            />
                        </div>
                        <div class="col-md-4">
                            <TextField
                                name="favicon_title"
                                :label="__('message.meta_title_admin')"
                                :value="form.favicon_title"
                                :onChange="(val, name) => form[name] = val"
                            />
                        </div>
                        <div class="col-md-4">
                            <TextField
                                name="favicon_title_client"
                                :label="__('message.meta_title_client')"
                                :value="form.favicon_title_client"
                                :onChange="(val, name) => form[name] = val"
                            />
                        </div>
                    </div>

                    <!-- Row 3: Phone · Language · Knowledge Base URL -->
                    <div class="row">
                        <div class="col-md-4">
                            <PhoneField
                                name="phone"
                                :label="__('message.phone') + ' *'"
                                :value="form.phone"
                                :onChange="(val) => form.phone = val"
                                :initialCountry="form.phone_country_iso ? form.phone_country_iso.toLowerCase() : 'auto'"
                                @countryChange="onPhoneCountryChange"
                            />
                        </div>
                        <div class="col-md-4">
                            <SelectField
                                name="language"
                                :label="__('message.language')"
                                :elements="languageOptions"
                                :value="form.language"
                                :onChange="(val) => form.language = val"
                                :searchable="true"
                                :placeholder="__('message.choose')"
                            />
                        </div>
                        <div class="col-md-4">
                            <TextField
                                name="knowledge_base_url"
                                :label="__('message.knowledge_base_url')"
                                :value="form.knowledge_base_url"
                                :onChange="(val, name) => form[name] = val"
                            />
                        </div>
                    </div>

                    <!-- Row 4: Default Currency · CIN · GSTIN -->
                    <div class="row">
                        <div class="col-md-4">
                            <SelectField
                                name="default_currency"
                                :label="__('message.default-currency') + ' *'"
                                :elements="currencyOptions"
                                :value="form.default_currency"
                                :onChange="(val) => form.default_currency = val"
                                :searchable="true"
                                :placeholder="__('message.choose')"
                            />
                        </div>
                        <div class="col-md-4">
                            <TextField
                                name="cin_no"
                                :label="__('message.cin')"
                                :value="form.cin_no"
                                :onChange="(val, name) => form[name] = val"
                            />
                        </div>
                        <div class="col-md-4">
                            <TextField
                                name="gstin"
                                :label="__('message.gstin')"
                                :value="form.gstin"
                                :onChange="(val, name) => form[name] = val"
                            />
                        </div>
                    </div>

                    <!-- Row 5: Address (full-width textarea) -->
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">{{ __('message.address') }} *</label>
                            <textarea
                                :class="['form-control', { 'is-invalid': alertStore.validation_errors.address }]"
                                v-model="form.address"
                                rows="3"
                            ></textarea>
                            <div v-if="alertStore.validation_errors.address" class="invalid-feedback">
                                {{ alertStore.validation_errors.address }}
                            </div>
                        </div>
                    </div>

                    <!-- Row 6: City · Country · State -->
                    <div class="row">
                        <div class="col-md-4">
                            <TextField
                                name="city"
                                :label="__('message.city')"
                                :value="form.city"
                                :onChange="(val, name) => form[name] = val"
                            />
                        </div>
                        <div class="col-md-4">
                            <SelectField
                                name="country"
                                :label="__('message.country') + ' *'"
                                :elements="countryOptions"
                                :value="form.country"
                                :onChange="onCountryChange"
                                :searchable="true"
                                :placeholder="__('message.choose')"
                            />
                        </div>
                        <div class="col-md-4">
                            <SelectField
                                name="state"
                                :label="__('message.state') + ' *'"
                                :elements="stateOptions"
                                :value="form.state"
                                :onChange="(val) => form.state = val"
                                :searchable="true"
                                :placeholder="__('message.choose')"
                            />
                        </div>
                    </div>

                    <!-- Row 7: Zip · Autorenewal -->
                    <div class="row">
                        <div class="col-md-6">
                            <TextField
                                name="zip"
                                :label="__('message.zip')"
                                :value="form.zip"
                                :onChange="(val, name) => form[name] = val"
                            />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold d-block">{{ __('message.auto_renewal') }}</label>
                            <div class="form-check form-switch mt-2">
                                <input id="autorenewalStatus" class="form-check-input" type="checkbox" v-model="form.autorenewal_status" />
                                <label class="form-check-label" for="autorenewalStatus">
                                    {{ form.autorenewal_status ? __('message.enable') : __('message.disable') }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Logo and Favicon Card -->
                    <div for="Logo and Favicon" class="col-sm-12 px-0">
                        <div class="card card-light">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    {{ __('message.logo_and_favicon') }}
                                </h5>
                            </div>

                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-md-4 text-center">
                                        <div class="form-check d-inline-flex align-items-center gap-1">
                                            <input class="form-check-input" type="checkbox" id="defaulticon" v-model="defaulticon" />
                                            <label class="form-check-label fw-normal" for="defaulticon">{{ __('message.use_default') }}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <div class="form-check d-inline-flex align-items-center gap-1">
                                            <input class="form-check-input" type="checkbox" id="defaultlogo" v-model="defaultlogo" />
                                            <label class="form-check-label fw-normal" for="defaultlogo">{{ __('message.use_default') }}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <div class="form-check d-inline-flex align-items-center gap-1">
                                            <input class="form-check-input" type="checkbox" id="uselogo" v-model="uselogo" />
                                            <label class="form-check-label fw-normal" for="uselogo">{{ __('message.use_logo') }}</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <ImageUpload
                                        :label="__('message.fav-icon')"
                                        :labelStyle="{ visibility: 'hidden' }"
                                        :value="icon"
                                        name="icon"
                                        :onChange="onImageChange"
                                        btnName="change_icon"
                                        classname="col-sm-4 text-center"
                                        :is_default="defaulticon"
                                        :componentName="COMPONENT"
                                    />

                                    <ImageUpload
                                        :label="__('message.admin-logo')"
                                        :labelStyle="{ visibility: 'hidden' }"
                                        :value="logo_admin_agent"
                                        name="logo_admin_agent"
                                        :onChange="onImageChange"
                                        classname="col-sm-4 text-center"
                                        :is_default="defaultlogo"
                                        :componentName="COMPONENT"
                                    />

                                    <ImageUpload
                                        :label="__('message.client-logo')"
                                        :labelStyle="{ visibility: 'hidden' }"
                                        :value="logo"
                                        name="logo"
                                        :onChange="onImageChange"
                                        classname="col-sm-4 text-center"
                                        :componentName="COMPONENT"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <action-button action="save" :loading="saving" @click="save" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import ImageUpload from '@/components/Reusable/FormField/ImageUpload.vue'
import { validateSystemSettings } from '@/helpers/validator/systemSettingsValidation.js'
import { useAlertStore } from '@/core/stores/alert'

const COMPONENT = 'system-settings'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const alertStore = useAlertStore()

const loading = ref(true)
const saving  = ref(false)

const countryOptions  = ref([])
const stateOptions    = ref([])
const currencyOptions = ref([])
const languageOptions = ref([])

// Image display values (URL on load, base64 after crop)
const icon            = ref('')
const logo_admin_agent = ref('')
const logo            = ref('')

// Cropped blobs ready for upload
const selectedIcon            = ref(null)
const selectedLogoAdminAgent  = ref(null)
const selectedLogo            = ref(null)

// Original filenames (needed to preserve extension when uploading Blob)
const selectedIconName            = ref('')
const selectedLogoAdminAgentName  = ref('')
const selectedLogoName            = ref('')

// Default / use-logo toggles
const defaulticon = ref(false)
const defaultlogo = ref(false)
const uselogo     = ref(false)

const form = reactive({
    company: '',
    company_email: '',
    title: '',
    favicon_title: '',
    favicon_title_client: '',
    website: '',
    phone: '',
    phone_code: '',
    phone_country_iso: '',
    address: '',
    city: '',
    state: null,
    country: null,
    zip: '',
    cin_no: '',
    gstin: '',
    default_currency: null,
    language: null,
    knowledge_base_url: '',
    autorenewal_status: false,
})

onMounted(async () => {
    try {
        const res  = await http.get(`${baseUrl}/settings/system-data`)
        const data = res.data?.data ?? {}
        const s    = data.settings ?? {}

        form.company              = s.company              ?? ''
        form.company_email        = s.company_email        ?? ''
        form.title                = s.title                ?? ''
        form.favicon_title        = s.favicon_title        ?? ''
        form.favicon_title_client = s.favicon_title_client ?? ''
        form.website              = s.website              ?? ''
        form.phone             = s.phone             ?? ''
        form.phone_code        = s.phone_code        ?? ''
        form.phone_country_iso = s.phone_country_iso ?? ''
        form.address           = s.address           ?? ''
        form.city              = s.city              ?? ''
        form.zip               = s.zip               ?? ''
        form.cin_no            = s.cin_no            ?? ''
        form.gstin             = s.gstin             ?? ''
        form.knowledge_base_url = s.knowledge_base_url ?? ''
        form.autorenewal_status = !!s.autorenewal_status

        // Build option arrays — store the actual object references so SelectField's
        // Array#includes check passes by reference on initial selection.
        countryOptions.value = (data.countries ?? []).map(c => ({
            id:   c.country_code_char2,
            name: c.country_name,
        }))
        stateOptions.value = (data.states ?? []).map(st => ({
            id:   stateValue(st),
            name: stateLabel(st),
        }))
        currencyOptions.value = (data.currencies ?? []).map(c => ({
            id:   c.code,
            name: `${c.name} (${c.code})`,
        }))
        languageOptions.value = (data.languages ?? []).map(l => ({
            id:   l.locale,
            name: l.name || l.locale,
        }))

        form.country          = countryOptions.value.find(c  => c.id  === s.country)          ?? null
        form.state            = stateOptions.value.find(st   => st.id === s.state)            ?? null
        form.default_currency = currencyOptions.value.find(c => c.id  === s.default_currency) ?? null
        form.language         = languageOptions.value.find(l => l.id  === s.language)         ?? null

        icon.value            = s.fav_icon    ?? ''
        logo_admin_agent.value = s.admin_logo ?? ''
        logo.value            = s.logo        ?? ''
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

function stateValue(s) {
    return s.state_subdivision_code ?? s.iso2 ?? String(s.id)
}

function stateLabel(s) {
    return s.state_subdivision_name ?? s.primary_level_name ?? s.name
}

function onCountryChange(val) {
    form.country = val
    loadStates()
}

function onPhoneCountryChange({ iso, dialCode }) {
    form.phone_country_iso = iso
    form.phone_code        = dialCode
}

async function loadStates() {
    form.state = null
    const code = form.country?.id
    if (!code) { stateOptions.value = []; return }
    try {
        const res = await http.get(`${baseUrl}/get-state/${code}`)
        stateOptions.value = (res.data?.data?.states ?? []).map(st => ({
            id:   stateValue(st),
            name: stateLabel(st),
        }))
    } catch (e) {
        errorHandler(e, COMPONENT)
    }
}

function onImageChange(value, name) {
    switch (name) {
        case 'icon':
            icon.value               = value.image
            selectedIcon.value       = value.file
            selectedIconName.value   = value.name
            break
        case 'logo_admin_agent':
            logo_admin_agent.value           = value.image
            selectedLogoAdminAgent.value     = value.file
            selectedLogoAdminAgentName.value = value.name
            break
        case 'logo':
            logo.value           = value.image
            selectedLogo.value   = value.file
            selectedLogoName.value = value.name
            break
    }
}

async function save() {
    const { isValid } = validateSystemSettings(form)
    if (!isValid) return

    saving.value = true
    try {
        const fd = new FormData()

        fd.append('company',               form.company              ?? '')
        fd.append('company_email',         form.company_email        ?? '')
        fd.append('title',                 form.title                ?? '')
        fd.append('favicon_title',         form.favicon_title        ?? '')
        fd.append('favicon_title_client',  form.favicon_title_client ?? '')
        fd.append('website',               form.website              ?? '')
        fd.append('phone',              form.phone              ?? '')
        fd.append('phone_code',         form.phone_code         ?? '')
        fd.append('phone_country_iso',  form.phone_country_iso  ?? '')
        fd.append('address',            form.address            ?? '')
        fd.append('city',               form.city               ?? '')
        fd.append('zip',                form.zip                ?? '')
        fd.append('cin_no',             form.cin_no             ?? '')
        fd.append('gstin',              form.gstin              ?? '')
        fd.append('knowledge_base_url', form.knowledge_base_url ?? '')
        fd.append('autorenewal_status', form.autorenewal_status ? 1 : 0)
        fd.append('country',            form.country?.id          ?? '')
        fd.append('state',              form.state?.id            ?? '')
        fd.append('default_currency',   form.default_currency?.id ?? '')
        fd.append('language',           form.language?.id         ?? '')

        fd.append('uselogo',     uselogo.value     ? 1 : 0)
        fd.append('defaulticon', defaulticon.value ? 1 : 0)
        fd.append('defaultlogo', defaultlogo.value ? 1 : 0)

        if (!defaulticon.value && selectedIcon.value) {
            fd.append('fav-icon', selectedIcon.value, selectedIconName.value)
        }
        if (!defaultlogo.value && selectedLogoAdminAgent.value) {
            fd.append('admin-logo', selectedLogoAdminAgent.value, selectedLogoAdminAgentName.value)
        }
        if (selectedLogo.value) {
            fd.append('logo', selectedLogo.value, selectedLogoName.value)
        }

        fd.append('_method', 'PATCH')

        const res = await http.post(`${baseUrl}/settings/system-data`, fd, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })
        alertStore.unsetValidationError()
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
