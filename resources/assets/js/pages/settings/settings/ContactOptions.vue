<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h3 class="card-title">Settings</h3>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else>
                <div class="card-body">

                    <!-- Enable Verification row -->
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-4 col-form-label fw-semibold">
                            {{ __('message.enable_verification') }}
                        </label>
                        <div class="col-sm-4 d-flex align-items-center">
                            <div class="form-check form-switch">
                                <input
                                    id="email_enabled"
                                    v-model="form.email_enabled"
                                    class="form-check-input"
                                    type="checkbox"
                                    @change="syncPreference"
                                />
                                <label class="form-check-label" for="email_enabled">
                                    {{ __('message.email') }}
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-4 d-flex align-items-center">
                            <div class="form-check form-switch">
                                <input
                                    id="mobile_enabled"
                                    v-model="form.mobile_enabled"
                                    class="form-check-input"
                                    type="checkbox"
                                    @change="syncPreference"
                                />
                                <label class="form-check-label" for="mobile_enabled">
                                    {{ __('message.mobile') }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Preferred Verification row -->
                    <div class="row align-items-center">
                        <label for="preferred_verification" class="col-sm-4 col-form-label fw-semibold">
                            {{ __('message.preferred_verification') }}
                        </label>
                        <div class="col-sm-8">
                            <SelectField
                                name="preferred_verification"
                                :elements="preferenceOptions"
                                :value="selectedPreference"
                                :onChange="onPreferenceChange"
                                :disabled="!preferenceEnabled"
                                :clearable="true"
                                :searchable="false"
                            />
                        </div>
                    </div>

                </div>

                <div class="card-footer">
                    <button class="btn btn-primary" :disabled="saving" @click="submit">
                        <span v-if="saving" class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
                        <i v-else class="fa fa-save me-1"></i>
                        {{ __('message.save') }}
                    </button>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'contact-options'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const saving  = ref(false)

const form = reactive({
    email_enabled:          false,
    mobile_enabled:         false,
    preferred_verification: '',
})

const preferenceOptions = [
    { id: 'email',  name: 'Email First'  },
    { id: 'mobile', name: 'Mobile First' },
]

const preferenceEnabled = computed(() => form.email_enabled && form.mobile_enabled)

const selectedPreference = computed(() =>
    preferenceOptions.find(o => o.id === form.preferred_verification) ?? null
)

function onPreferenceChange(val) {
    form.preferred_verification = val?.id ?? ''
}

function syncPreference() {
    if (form.email_enabled && form.mobile_enabled) return
    if (form.email_enabled)  { form.preferred_verification = 'email';  return }
    if (form.mobile_enabled) { form.preferred_verification = 'mobile'; return }
    form.preferred_verification = ''
}

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/contact-option`)
        const d   = res.data?.data ?? {}
        form.email_enabled          = Boolean(d.emailverification_status)
        form.mobile_enabled         = Boolean(d.msg91_status)
        form.preferred_verification = d.verification_preference ?? ''
        syncPreference()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function submit() {
    saving.value = true
    try {
        const res = await http.post(`${baseUrl}/verificationSettings`, {
            email_enabled:          form.email_enabled  ? 1 : 0,
            mobile_enabled:         form.mobile_enabled ? 1 : 0,
            preferred_verification: form.preferred_verification,
        })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
