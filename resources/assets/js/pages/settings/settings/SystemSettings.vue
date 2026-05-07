<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.system-settings') }}</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('message.company') }} *</label>
                            <input class="form-control" v-model="form.company" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('message.company_email') }} *</label>
                            <input type="email" class="form-control" v-model="form.company_email" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('message.title') }}</label>
                            <input class="form-control" v-model="form.title" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('message.website') }} *</label>
                            <input class="form-control" v-model="form.website" placeholder="https://example.com" />
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-bold">{{ __('message.phone_code') }}</label>
                            <input class="form-control" v-model="form.phone_code" />
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-bold">ISO</label>
                            <input class="form-control" v-model="form.phone_country_iso" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('message.phone') }} *</label>
                            <input class="form-control" v-model="form.phone" />
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">{{ __('message.address') }} *</label>
                            <input class="form-control" v-model="form.address" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('message.city') }}</label>
                            <input class="form-control" v-model="form.city" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('message.country') }} *</label>
                            <select class="form-select" v-model="form.country" @change="loadStates">
                                <option value="">{{ __('message.choose') }}</option>
                                <option v-for="country in countries" :key="country.country_code_char2" :value="country.country_code_char2">
                                    {{ country.country_name }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('message.state') }} *</label>
                            <select class="form-select" v-model="form.state">
                                <option value="">{{ __('message.choose') }}</option>
                                <option v-for="state in states" :key="stateValue(state)" :value="stateValue(state)">
                                    {{ stateLabel(state) }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('message.zip') }}</label>
                            <input class="form-control" v-model="form.zip" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('message.default_currency') }} *</label>
                            <select class="form-select" v-model="form.default_currency">
                                <option value="">{{ __('message.choose') }}</option>
                                <option v-for="currency in currencies" :key="currency.code" :value="currency.code">
                                    {{ currency.name }} ({{ currency.code }})
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('message.language') }}</label>
                            <select class="form-select" v-model="form.language">
                                <option value="">{{ __('message.choose') }}</option>
                                <option v-for="language in languages" :key="language.locale" :value="language.locale">
                                    {{ language.name || language.locale }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">CIN</label>
                            <input class="form-control" v-model="form.cin_no" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">GSTIN</label>
                            <input class="form-control" v-model="form.gstin" maxlength="15" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('message.knowledge_base_url') }}</label>
                            <input class="form-control" v-model="form.knowledge_base_url" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold d-block">{{ __('message.autorenewal') }}</label>
                            <div class="form-check form-switch mt-2">
                                <input id="autorenewalStatus" class="form-check-input" type="checkbox" v-model="form.autorenewal_status" />
                                <label class="form-check-label" for="autorenewalStatus">
                                    {{ form.autorenewal_status ? __('message.enable') : __('message.disable') }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Logo</label>
                            <input class="form-control" type="file" accept="image/*" @change="setFile($event, 'logo')" />
                            <a v-if="images.logo" :href="images.logo" target="_blank" class="small d-inline-block mt-1">Current logo</a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Admin Logo</label>
                            <input class="form-control" type="file" accept="image/*" @change="setFile($event, 'admin-logo')" />
                            <a v-if="images.admin_logo" :href="images.admin_logo" target="_blank" class="small d-inline-block mt-1">Current admin logo</a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Fav Icon</label>
                            <input class="form-control" type="file" accept="image/*" @change="setFile($event, 'fav-icon')" />
                            <a v-if="images.fav_icon" :href="images.fav_icon" target="_blank" class="small d-inline-block mt-1">Current fav icon</a>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button class="btn btn-primary" @click="save" :disabled="saving">
                        <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                        {{ __('message.update') }}
                    </button>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'system-settings'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const saving = ref(false)
const countries = ref([])
const states = ref([])
const currencies = ref([])
const languages = ref([])
const files = reactive({})
const images = reactive({ logo: '', admin_logo: '', fav_icon: '' })

const form = reactive({
    company: '',
    company_email: '',
    title: '',
    website: '',
    phone: '',
    phone_code: '',
    phone_country_iso: '',
    address: '',
    city: '',
    state: '',
    country: '',
    zip: '',
    cin_no: '',
    gstin: '',
    default_currency: '',
    language: '',
    knowledge_base_url: '',
    autorenewal_status: false,
})

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/settings/system-data`)
        const data = res.data?.data ?? {}
        Object.assign(form, data.settings ?? {})
        images.logo = data.settings?.logo ?? ''
        images.admin_logo = data.settings?.admin_logo ?? ''
        images.fav_icon = data.settings?.fav_icon ?? ''
        countries.value = data.countries ?? []
        states.value = data.states ?? []
        currencies.value = data.currencies ?? []
        languages.value = data.languages ?? []
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

function stateValue(state) {
    return state.state_subdivision_code ?? state.iso2 ?? state.id
}

function stateLabel(state) {
    return state.state_subdivision_name ?? state.primary_level_name ?? state.name
}

function setFile(event, name) {
    files[name] = event.target.files?.[0] ?? null
}

async function loadStates() {
    form.state = ''
    if (!form.country) {
        states.value = []
        return
    }
    try {
        const res = await http.get(`${baseUrl}/get-state/${form.country}`)
        states.value = res.data?.data?.states ?? []
    } catch (e) {
        errorHandler(e, COMPONENT)
    }
}

async function save() {
    saving.value = true
    try {
        const payload = new FormData()
        Object.entries(form).forEach(([key, value]) => {
            payload.append(key, key === 'autorenewal_status' ? (value ? 1 : 0) : (value ?? ''))
        })
        Object.entries(files).forEach(([key, file]) => {
            if (file) payload.append(key, file)
        })
        payload.append('_method', 'PATCH')

        const res = await http.post(`${baseUrl}/settings/system-data`, payload, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
