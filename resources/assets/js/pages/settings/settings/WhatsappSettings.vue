<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.whatsapp_config') }}</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('message.whatsapp_thirdParty_explanation') }}
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <TextField
                                name="app_id"
                                label="App ID"
                                :value="form.app_id"
                                placeholder="Enter your WhatsApp App ID"
                                :onChange="(val, key) => form[key] = val"
                            />
                        </div>
                        <div class="col-md-6 mb-3">
                            <TextField
                                name="app_secret"
                                label="App Secret"
                                type="password"
                                :value="form.app_secret"
                                placeholder="Enter your WhatsApp App Secret"
                                :onChange="(val, key) => form[key] = val"
                            />
                        </div>
                        <div class="col-md-6 mb-3">
                            <TextField
                                name="config_id"
                                label="Config ID"
                                :value="form.config_id"
                                placeholder="Enter your WhatsApp Config ID"
                                :onChange="(val, key) => form[key] = val"
                            />
                        </div>
                        <div class="col-md-6 mb-3">
                            <TextField
                                name="verify_token"
                                label="Verify Token"
                                :value="form.verify_token"
                                placeholder="Enter your WhatsApp Verify Token"
                                :onChange="(val, key) => form[key] = val"
                            />
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button class="btn btn-primary" @click="save" :disabled="saving">
                        <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                        <i v-else class="fas fa-save me-1"></i>
                        {{ __('message.save') }}
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
import TextField from '@/themes/adminlte/components/forms/TextField.vue'

const COMPONENT = 'whatsapp-settings'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const saving  = ref(false)

const form = reactive({
    app_id:       '',
    app_secret:   '',
    config_id:    '',
    verify_token: '',
})

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/whatsapp-integration-info`)
        const d   = res.data?.data ?? {}
        Object.assign(form, {
            app_id:       d.app_id       ?? '',
            app_secret:   d.app_secret   ?? '',
            config_id:    d.config_id    ?? '',
            verify_token: d.verify_token ?? '',
        })
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function save() {
    saving.value = true
    try {
        const res = await http.post(`${baseUrl}/whatsapp-integration-save`, {
            app_id:       form.app_id,
            app_secret:   form.app_secret,
            config_id:    form.config_id,
            verify_token: form.verify_token,
        })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
