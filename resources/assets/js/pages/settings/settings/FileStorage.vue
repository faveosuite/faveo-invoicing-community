<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">File Storage</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <SelectField
                                name="disk"
                                label="Storage Disk"
                                :elements="diskOptions"
                                :value="diskOptions.find(o => o.id === form.disk) ?? null"
                                :onChange="onDiskSelect"
                                :clearable="false"
                                :searchable="false"
                            />
                        </div>

                        <div v-if="form.disk === 'system'" class="col-md-4">
                            <TextField name="path" label="Storage Path *" :value="form.path" :onChange="onChange" />
                        </div>

                        <template v-if="form.disk === 's3'">
                            <div class="col-md-4">
                                <SelectField
                                    name="s3_path_style_endpoint"
                                    label="S3 Path Style Endpoint"
                                    :elements="yesNoOptions"
                                    :value="yesNoOptions.find(o => o.id === form.s3_path_style_endpoint) ?? null"
                                    :onChange="(val) => form.s3_path_style_endpoint = val?.id ?? 'false'"
                                    :clearable="false"
                                    :searchable="false"
                                />
                            </div>
                            <div class="col-md-4">
                                <TextField name="s3_bucket" label="S3 Bucket *" :value="form.s3_bucket" :onChange="onChange" />
                            </div>
                            <div class="col-md-4">
                                <TextField name="s3_region" label="S3 Region *" :value="form.s3_region" :onChange="onChange" />
                            </div>
                            <div class="col-md-4">
                                <TextField name="s3_access_key" label="S3 Access Key *" type="password"
                                    :value="form.s3_access_key" :onChange="onChange" />
                            </div>
                            <div class="col-md-4">
                                <TextField name="s3_secret_key" label="S3 Secret Key *" type="password"
                                    :value="form.s3_secret_key" :onChange="onChange" />
                            </div>
                            <div class="col-md-4">
                                <TextField name="s3_endpoint_url" label="S3 Endpoint URL *" :value="form.s3_endpoint_url" :onChange="onChange" />
                            </div>
                            <div class="col-md-4">
                                <TextField name="s3_url" label="S3 URL" :value="form.s3_url" :onChange="onChange" />
                            </div>
                        </template>
                    </div>
                </div>

                <div class="card-footer">
                    <button class="btn btn-primary" @click="submit" :disabled="saving">
                        <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
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
import { validateFileStorage } from '@/helpers/validator/fileStorageValidation.js'
import { useAlertStore } from '@/core/stores/alert'

const COMPONENT = 'file-storage'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const alertStore = useAlertStore()
const loading = ref(true)
const saving  = ref(false)

const diskOptions = [
    { id: 'system', name: 'System (Local)' },
    { id: 's3',     name: 'Amazon S3'      },
]

const yesNoOptions = [
    { id: 'true',  name: 'Yes' },
    { id: 'false', name: 'No'  },
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

function onChange(val, name) { form[name] = val }

function onDiskSelect(val) {
    form.disk = val?.id ?? 'system'
    alertStore.unsetValidationError()
}

onMounted(async () => {
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
    const { isValid } = validateFileStorage(form)
    if (!isValid) return

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
        alertStore.unsetValidationError()
        successHandler(res, COMPONENT)
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { saving.value = false }
}
</script>
