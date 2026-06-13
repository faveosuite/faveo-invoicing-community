<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.whatsapp_config') }}</h4>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <TextField
                                name="app_id"
                                :label="__('message.app_id')"
                                :value="form.app_id"
                                :placeholder="__('message.enter_whatsapp_app_id')"
                                :required="true"
                                :error="errors.app_id"
                                :onChange="(val, key) => { setFieldError(key, undefined); form[key] = val }"
                            />
                        </div>
                        <div class="col-md-6 mb-3">
                            <TextField
                                name="app_secret"
                                :label="__('message.app_secret')"
                                type="password"
                                :value="form.app_secret"
                                :placeholder="__('message.enter_whatsapp_app_secret')"
                                :required="true"
                                :error="errors.app_secret"
                                :onChange="(val, key) => { setFieldError(key, undefined); form[key] = val }"
                            />
                        </div>
                        <div class="col-md-6 mb-3">
                            <TextField
                                name="config_id"
                                :label="__('message.config_id')"
                                :value="form.config_id"
                                :placeholder="__('message.enter_whatsapp_config_id')"
                                :required="true"
                                :error="errors.config_id"
                                :onChange="(val, key) => { setFieldError(key, undefined); form[key] = val }"
                            />
                        </div>
                        <div class="col-md-6 mb-3">
                            <TextField
                                name="verify_token"
                                :label="__('message.verify_token')"
                                :value="form.verify_token"
                                :placeholder="__('message.enter_whatsapp_verify_token')"
                                :required="true"
                                :error="errors.verify_token"
                                :onChange="(val, key) => { setFieldError(key, undefined); form[key] = val }"
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
import { useForm } from 'vee-validate'
import { validateForm } from '@/helpers/formUtils.js'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import TextField from '@/components/Reusable/FormField/TextField.vue'
import { whatsappSchema } from '@/validations/admin/whatsappValidations'

const COMPONENT = 'whatsapp-settings'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const { errors, setErrors, setFieldError } = useForm()

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
    if (!await validateForm(whatsappSchema, form, setErrors)) return

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
