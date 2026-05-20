<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.file_storage') }}</h4>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <SelectField
                                name="disk"
                                :label="__('message.storage_disk')"
                                :elements="diskOptions"
                                :value="diskOptions.find(o => o.id === form.disk) ?? null"
                                :onChange="onDiskSelect"
                                :clearable="false"
                                :searchable="false"
                            />
                        </div>

                        <div v-if="form.disk === 'system'" class="col-md-4">
                            <TextField name="path" :label="__('message.storage_path')" :required="true" :value="form.path" :onChange="onChange" />
                        </div>

                        <template v-if="form.disk === 's3'">
                            <div class="col-md-4">
                                <SelectField
                                    name="s3_path_style_endpoint"
                                    :label="__('message.s3_path_style_endpoint')"
                                    :elements="yesNoOptions"
                                    :value="yesNoOptions.find(o => o.id === form.s3_path_style_endpoint) ?? null"
                                    :onChange="(val) => form.s3_path_style_endpoint = val?.id ?? 'false'"
                                    :clearable="false"
                                    :searchable="false"
                                />
                            </div>
                            <div class="col-md-4">
                                <TextField name="s3_bucket" :label="__('message.s3_bucket')" :required="true" :value="form.s3_bucket" :onChange="onChange" />
                            </div>
                            <div class="col-md-4">
                                <TextField name="s3_region" :label="__('message.s3_region')" :required="true" :value="form.s3_region" :onChange="onChange" />
                            </div>
                            <div class="col-md-4">
                                <TextField name="s3_access_key" :label="__('message.s3_access_key')" :required="true" type="password"
                                    :value="form.s3_access_key" :onChange="onChange" />
                            </div>
                            <div class="col-md-4">
                                <TextField name="s3_secret_key" :label="__('message.s3_secret_key')" :required="true" type="password"
                                    :value="form.s3_secret_key" :onChange="onChange" />
                            </div>
                            <div class="col-md-4">
                                <TextField name="s3_endpoint_url" :label="__('message.s3_endpoint_url')" :required="true" :value="form.s3_endpoint_url" :onChange="onChange" />
                            </div>
                            <div class="col-md-4">
                                <TextField name="s3_url" :label="__('message.s3_url')" :value="form.s3_url" :onChange="onChange" />
                            </div>
                        </template>
                    </div>
                </div>

                <div class="card-footer">
                    <action-button action="save" :loading="saving" @click="submit" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { useFormValidation } from '@/composables/useFormValidation'

const COMPONENT = 'file-storage'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const { validate, clearFieldError, clearAllErrors } = useFormValidation()
const loading = ref(true)
const saving  = ref(false)

const diskOptions = [
    { id: 'system', name: __('message.system_local') },
    { id: 's3',     name: __('message.amazon_s3')    },
]

const yesNoOptions = [
    { id: 'true',  name: __('message.yes') },
    { id: 'false', name: __('message.no')  },
]

const form = reactive({
    disk: 'system',
    path: '',
    s3_path_style_endpoint: 'false',
    s3_bucket: '',
    s3_region: '',
    s3_access_key: '',
    s3_secret_key: '',
    s3_endpoint_url: '',
    s3_url: '',
})

function onChange(val, name) {
    clearFieldError(name)
    form[name] = val
}

function onDiskSelect(val) {
    form.disk = val?.id ?? 'system'
    clearAllErrors()
}

onMounted(async () => {
    clearAllErrors()
    try {
        const res = await http.get(`${baseUrl}/file-storage`)
        const d = res.data?.data ?? {}
        Object.assign(form, {
            disk: d.disk ?? 'system',
            path: d.local_file_storage_path ?? '',
            s3_path_style_endpoint: d.s3_path_style_endpoint ? String(d.s3_path_style_endpoint) : 'false',
            s3_bucket: d.s3_bucket ?? '',
            s3_region: d.s3_region ?? '',
            s3_access_key: d.s3_access_key ?? '',
            s3_secret_key: d.s3_secret_key ?? '',
            s3_endpoint_url: d.s3_endpoint_url ?? '',
            s3_url: d.s3_url ?? '',
        })
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { loading.value = false }
})

async function submit() {
    const rules = {}
    if (form.disk === 'system') {
        rules.path = [form.path, { isRequired: __('validation.storage_path.path.required') }]
    } else {
        rules.s3_bucket       = [form.s3_bucket,       { isRequired: __('message.field_required') }]
        rules.s3_region       = [form.s3_region,       { isRequired: __('message.field_required') }]
        rules.s3_access_key   = [form.s3_access_key,   { isRequired: __('message.field_required') }]
        rules.s3_secret_key   = [form.s3_secret_key,   { isRequired: __('message.field_required') }]
        rules.s3_endpoint_url = [form.s3_endpoint_url, { isRequired: __('message.field_required') }]
    }
    if (!validate(rules)) return

    saving.value = true
    try {
        const payload = { disk: form.disk }
        if (form.disk === 'system') {
            payload.path = form.path
        } else {
            Object.assign(payload, {
                s3_path_style_endpoint: form.s3_path_style_endpoint,
                s3_bucket:     form.s3_bucket,
                s3_region:     form.s3_region,
                s3_access_key: form.s3_access_key,
                s3_secret_key: form.s3_secret_key,
                s3_endpoint_url: form.s3_endpoint_url,
                s3_url:        form.s3_url,
            })
        }
        const res = await http.post(`${baseUrl}/file-storage-path`, payload)
        clearAllErrors()
        successHandler(res, COMPONENT)
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { saving.value = false }
}
</script>
