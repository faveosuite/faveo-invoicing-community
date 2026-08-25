<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.file_system') }}</h4>
            </div>

            <div v-if="loading && pdfLoading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">

                    <!-- Storage Settings -->
                    <h5 class="mb-3">{{ __('message.file_storage') }}</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <DynamicSelect
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
                            <TextField name="path" :label="__('message.storage_path')" :required="true" :value="form.path" :onChange="onChange" :error="errors.path" />
                        </div>

                        <template v-if="form.disk === 's3'">
                            <div class="col-md-4">
                                <DynamicSelect
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
                                <TextField name="s3_bucket" :label="__('message.s3_bucket')" :required="true" :value="form.s3_bucket" :onChange="onChange" :error="errors.s3_bucket" />
                            </div>
                            <div class="col-md-4">
                                <TextField name="s3_region" :label="__('message.s3_region')" :required="true" :value="form.s3_region" :onChange="onChange" :error="errors.s3_region" />
                            </div>
                            <div class="col-md-4">
                                <TextField name="s3_access_key" :label="__('message.s3_access_key')" :required="true" type="password"
                                    :value="form.s3_access_key" :onChange="onChange" :error="errors.s3_access_key" />
                            </div>
                            <div class="col-md-4">
                                <TextField name="s3_secret_key" :label="__('message.s3_secret_key')" :required="true" type="password"
                                    :value="form.s3_secret_key" :onChange="onChange" :error="errors.s3_secret_key" />
                            </div>
                            <div class="col-md-4">
                                <TextField name="s3_endpoint_url" :label="__('message.s3_endpoint_url')" :required="true" :value="form.s3_endpoint_url" :onChange="onChange" :error="errors.s3_endpoint_url" />
                            </div>
                            <div class="col-md-4">
                                <TextField name="s3_url" :label="__('message.s3_url')" :value="form.s3_url" :onChange="onChange" />
                            </div>
                        </template>
                    </div>

                    <hr class="my-4" />

                    <!-- PDF Settings -->
                    <h5 class="mb-3">{{ __('message.pdf_settings') }}</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <TextField name="node_path" :label="__('message.node_path')" :required="true" :hint="__('message.node_path_tooltip')" :value="pdfForm.node_path" :onChange="onPdfChange" :error="errors.node_path" />
                        </div>
                        <div class="col-md-4">
                            <TextField name="npm_path" :label="__('message.npm_path')" :required="true" :hint="__('message.npm_path_tooltip')" :value="pdfForm.npm_path" :onChange="onPdfChange" :error="errors.npm_path" />
                        </div>
                        <div class="col-md-4">
                            <TextField name="chrome_path" :label="__('message.chrome_path')" :required="true" :hint="__('message.chrome_path_tooltip')" :value="pdfForm.chrome_path" :onChange="onPdfChange" :error="errors.chrome_path" />
                        </div>
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
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { buildFileStorageSchema, pdfSettingsSchema } from '@/validations/admin/systemSettingsValidations'
import { scrollToFirstError } from '@/helpers/formUtils.js'

const COMPONENT = 'file-storage'

const { errors, setErrors, setFieldError, resetForm } = useForm()

const loading    = ref(true)
const pdfLoading = ref(true)
const saving     = ref(false)

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

const pdfForm = reactive({
    node_path:   '',
    npm_path:    '',
    chrome_path: '',
})

function onChange(val, name) {
    setFieldError(name, undefined)
    form[name] = val
}

function onDiskSelect(val) {
    form.disk = val?.id ?? 'system'
    resetForm()
}

function onPdfChange(val, name) {
    setFieldError(name, undefined)
    pdfForm[name] = val
}

onMounted(async () => {
    try {
        const res = await http.get(`/file-storage`)
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

    try {
        const res = await http.get(`/pdf-settings`)
        const d = res.data?.data ?? {}
        Object.assign(pdfForm, {
            node_path:   d.node_path ?? '',
            npm_path:    d.npm_path ?? '',
            chrome_path: d.chrome_path ?? '',
        })
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { pdfLoading.value = false }
})

async function submit() {
    const errMap = {}

    try {
        buildFileStorageSchema(form.disk).validateSync(form, { abortEarly: false })
    } catch (err) {
        err.inner?.forEach(e => { if (e.path && !errMap[e.path]) errMap[e.path] = e.message })
    }

    try {
        pdfSettingsSchema.validateSync(pdfForm, { abortEarly: false })
    } catch (err) {
        err.inner?.forEach(e => { if (e.path && !errMap[e.path]) errMap[e.path] = e.message })
    }

    if (Object.keys(errMap).length) {
        setErrors(errMap)
        await scrollToFirstError()
        return
    }

    saving.value = true
    try {
        const storagePayload = { disk: form.disk }
        if (form.disk === 'system') {
            storagePayload.path = form.path
        } else {
            Object.assign(storagePayload, {
                s3_path_style_endpoint: form.s3_path_style_endpoint,
                s3_bucket:     form.s3_bucket,
                s3_region:     form.s3_region,
                s3_access_key: form.s3_access_key,
                s3_secret_key: form.s3_secret_key,
                s3_endpoint_url: form.s3_endpoint_url,
                s3_url:        form.s3_url,
            })
        }

        const [storageRes] = await Promise.all([
            http.post(`/file-storage-path`, storagePayload),
            http.post(`/pdf-settings`, { ...pdfForm }),
        ])

        successHandler(storageRes, COMPONENT)
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { saving.value = false }
}
</script>
