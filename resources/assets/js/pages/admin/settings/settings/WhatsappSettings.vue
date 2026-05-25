<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.whatsapp_config') }}</h4>
            </div>

            <inline-loader v-if="loading" context="card-body" />

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
                                :label="__('message.app_id')"
                                :value="form.app_id"
                                :placeholder="__('message.enter_whatsapp_app_id')"
                                :onChange="(val, key) => form[key] = val"
                            />
                        </div>
                        <div class="col-md-6 mb-3">
                            <TextField
                                name="app_secret"
                                :label="__('message.app_secret')"
                                type="password"
                                :value="form.app_secret"
                                :placeholder="__('message.enter_whatsapp_app_secret')"
                                :onChange="(val, key) => form[key] = val"
                            />
                        </div>
                        <div class="col-md-6 mb-3">
                            <TextField
                                name="config_id"
                                :label="__('message.config_id')"
                                :value="form.config_id"
                                :placeholder="__('message.enter_whatsapp_config_id')"
                                :onChange="(val, key) => form[key] = val"
                            />
                        </div>
                        <div class="col-md-6 mb-3">
                            <TextField
                                name="verify_token"
                                :label="__('message.verify_token')"
                                :value="form.verify_token"
                                :placeholder="__('message.enter_whatsapp_verify_token')"
                                :onChange="(val, key) => form[key] = val"
                            />
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
