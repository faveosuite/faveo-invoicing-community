<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.msg91_heading') }}</h4>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <TextField
                                name="msg91_auth_key"
                                :label="__('message.msg91_key')"
                                :value="form.msg91_auth_key"
                                placeholder="Enter your MSG91 Auth Key"
                                :required="true"
                                :onChange="(val, key) => { setFieldError('msg91_auth_key', undefined); form[key] = val }"
                                :error="errors.msg91_auth_key"
                            />
                        </div>
                        <div class="col-md-6 mb-3">
                            <TextField
                                name="msg91_sender"
                                :label="__('message.msg91_sender')"
                                :value="form.msg91_sender"
                                placeholder="Enter Sender ID"
                                :required="true"
                                :onChange="(val, key) => { setFieldError('msg91_sender', undefined); form[key] = val }"
                                :error="errors.msg91_sender"
                            />
                        </div>
                        <div class="col-md-6 mb-3">
                            <TextField
                                name="msg91_template_id"
                                :label="__('message.msg91_template_id')"
                                :value="form.msg91_template_id"
                                placeholder="Enter Template ID"
                                :required="true"
                                :onChange="(val, key) => { setFieldError('msg91_template_id', undefined); form[key] = val }"
                                :error="errors.msg91_template_id"
                            />
                        </div>
                        <div class="col-md-6 mb-3">
                            <SelectField
                                name="third_party_id"
                                :label="__('message.msg91_third_party_app_key')"
                                :elements="thirdPartyOptions"
                                :value="selectedApp"
                                :onChange="(val) => form.third_party_id = val?.id ?? null"
                                :searchable="true"
                                :clearable="true"
                                :placeholder="__('message.select_third_party_app')"
                            />
                        </div>

                        <div v-if="webhookUrl" class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">{{ __('message.msg91_webhook_url') }}</label>
                            <div class="input-group">
                                <input
                                    type="text"
                                    class="form-control"
                                    :value="webhookUrl"
                                    readonly
                                />
                                <button
                                    class="btn btn-outline-secondary"
                                    type="button"
                                    :title="__('message.copy')"
                                    @click="copyWebhookUrl"
                                >
                                    <i :class="copied ? 'fas fa-check text-success' : 'fas fa-copy'"></i>
                                </button>
                            </div>
                            <div class="form-text text-muted">
                                {{ __('message.msg91_webhook_url_hint') }}
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
import { reactive, ref, computed, onMounted } from 'vue'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import TextField from '@/themes/adminlte/components/forms/TextField.vue'
import SelectField from '@/themes/adminlte/components/forms/SelectField.vue'
import { msg91Schema } from '@/validations/admin/msg91Validations'

const COMPONENT = 'msg91-settings'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const saving  = ref(false)
const copied  = ref(false)

const thirdPartyApps = ref([])

const form = reactive({
    msg91_auth_key:    '',
    msg91_sender:      '',
    msg91_template_id: '',
    third_party_id:    null,
})

const { errors, setErrors, setFieldError } = useForm()

const thirdPartyOptions = computed(() =>
    thirdPartyApps.value.map(a => ({ id: a.id, name: a.app_name }))
)

const selectedApp = computed(() =>
    thirdPartyOptions.value.find(o => o.id === form.third_party_id) ?? null
)

const webhookUrl = computed(() => {
    if (!form.third_party_id) return null
    const app = thirdPartyApps.value.find(a => a.id === form.third_party_id)
    if (!app?.app_key || !app?.app_secret) return null
    return `${baseUrl}/msg91/reports/${app.app_key}/${app.app_secret}`
})

function copyWebhookUrl() {
    if (!webhookUrl.value) return
    navigator.clipboard.writeText(webhookUrl.value).then(() => {
        copied.value = true
        setTimeout(() => { copied.value = false }, 2000)
    })
}

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/settings/msg91`)
        const d   = res.data?.data ?? {}
        Object.assign(form, {
            msg91_auth_key:    d.msg91_auth_key    ?? '',
            msg91_sender:      d.msg91_sender       ?? '',
            msg91_template_id: d.msg91_template_id  ?? '',
            third_party_id:    d.third_party_id     ?? null,
        })
        thirdPartyApps.value = d.third_party_apps ?? []
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function save() {
    try {
        await msg91Schema.validate(form, { abortEarly: false })
    } catch (err) {
        const errMap = {}
        err.inner?.forEach(e => { if (e.path && !errMap[e.path]) errMap[e.path] = e.message })
        setErrors(errMap)
        return
    }

    saving.value = true
    try {
        const res = await http.post(`${baseUrl}/updatemobileDetails`, {
            msg91_auth_key:    form.msg91_auth_key,
            msg91_sender:      form.msg91_sender,
            msg91_template_id: form.msg91_template_id,
            thirdPartyId:      form.third_party_id,
            status:            1,
        })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
