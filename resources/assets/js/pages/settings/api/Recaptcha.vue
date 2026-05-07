<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.recaptcha') }}</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">reCAPTCHA v2</label>
                            <div class="form-check form-switch mt-2">
                                <input id="recaptchaStatus" class="form-check-input" type="checkbox" v-model="form.recaptcha_status" />
                                <label class="form-check-label" for="recaptchaStatus">
                                    {{ form.recaptcha_status ? __('message.enable') : __('message.disable') }}
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Site Key</label>
                            <input class="form-control" v-model="form.site_key" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Secret Key</label>
                            <input class="form-control" type="password" v-model="form.secret_key" autocomplete="new-password" />
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

const COMPONENT = 'recaptcha-settings'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const saving = ref(false)
const form = reactive({
    recaptcha_status: false,
    site_key: '',
    secret_key: '',
})

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/settings/recaptcha`)
        Object.assign(form, res.data?.data ?? {})
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function save() {
    saving.value = true
    try {
        const res = await http.patch(`${baseUrl}/settings/recaptcha`, {
            recaptcha_status: form.recaptcha_status ? 1 : 0,
            site_key: form.site_key,
            secret_key: form.secret_key,
        })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
