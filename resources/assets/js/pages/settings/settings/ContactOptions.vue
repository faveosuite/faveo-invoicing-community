<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">Contact Options</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold d-block">Email Verification</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" v-model="form.email_enabled" id="emailVerification" />
                                <label class="form-check-label" for="emailVerification">
                                    {{ form.email_enabled ? 'Enabled' : 'Disabled' }}
                                </label>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold d-block">Mobile Verification (MSG91)</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" v-model="form.mobile_enabled" id="mobileVerification" />
                                <label class="form-check-label" for="mobileVerification">
                                    {{ form.mobile_enabled ? 'Enabled' : 'Disabled' }}
                                </label>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Preferred Verification</label>
                            <select class="form-select" v-model="form.preferred_verification">
                                <option value="">None</option>
                                <option value="email">Email</option>
                                <option value="mobile">Mobile</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button class="btn btn-primary" @click="submit" :disabled="saving">
                        <span v-if="saving" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        Save
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

const COMPONENT = 'contact-options'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const saving = ref(false)

const form = reactive({
    email_enabled:          false,
    mobile_enabled:         false,
    preferred_verification: '',
})

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/contact-option`)
        const d = res.data?.data ?? {}
        form.email_enabled          = Boolean(d.emailverification_status)
        form.mobile_enabled         = Boolean(d.msg91_status)
        form.preferred_verification = d.verification_preference ?? ''
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
            email_enabled:          form.email_enabled ? 1 : 0,
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
