<template>
    <div>
        <AppAlert componentName="product-version-create" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.add_version') || 'Add Version' }}</h4>
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <TextField name="title" :label="__('message.title')" :required="true" :value="form.title"
                            :onChange="(v) => { form.title = v; setFieldError('title', undefined) }" :error="errors.title" />
                    </div>
                    <div class="col-md-4">
                        <TextField name="version" :label="__('message.version')" :required="true" :value="form.version"
                            :onChange="(v) => { form.version = v; setFieldError('version', undefined) }" :error="errors.version" />
                    </div>
                    <div class="col-md-4">
                        <DynamicSelect name="release_type" :label="__('message.release_type')" :required="true"
                            :elements="releaseTypes" :value="selectedReleaseType"
                            :onChange="(v) => { form.release_type = v?.value ?? ''; setFieldError('release_type', undefined) }"
                            :clearable="false" :searchable="false" :error="errors.release_type" />
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold d-block">{{ __('message.file') }}<span class="text-danger ms-1">*</span></label>
                        <input type="file" class="form-control" :class="{ 'is-invalid': fileError }" accept=".zip" :disabled="uploading" @change="onFile" />
                        <UploadStatus :uploading="uploading" :progress="uploadProgress" :error="fileError" :uploadedName="uploadedName" />
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold d-block">{{ __('message.private_release') }}</label>
                        <Switch name="is_private" :value="form.is_private" :onChange="(v) => form.is_private = v" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold d-block">{{ __('message.restrict_update') }}</label>
                        <Switch name="is_restricted" :value="form.is_restricted" :onChange="(v) => form.is_restricted = v" />
                    </div>

                    <div class="col-md-12">
                        <TinyMCE name="description" id="editor-version-description-create" :label="__('message.description')" :required="true"
                            :value="form.description" :onChange="(v) => { form.description = v; setFieldError('description', undefined) }" :error="errors.description" />
                    </div>

                    <div class="col-md-12">
                        <TextArea name="dependencies" type="textarea" :rows="8" :length="100000" :label="__('message.dependencies')"
                            :hint="__('message.enter_json_format')" placeholder="{}" :value="form.dependencies"
                            :onChange="(v) => { form.dependencies = v; setFieldError('dependencies', undefined) }" :error="errors.dependencies" />
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <AppButton>
                    <button type="button" class="btn btn-primary" :disabled="saving || uploading" @click="submit">
                        <i class="fas fa-circle-notch fa-spin me-1" v-if="saving"></i>
                        <i class="fas fa-save me-1" v-else></i>
                        {{ saving ? __('message.please_wait') : __('message.save') }}
                    </button>
                </AppButton>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { useAlertStore } from '@/core/stores/alert'
import TextArea from '@/components/Reusable/FormField/TextField.vue'
import Switch from '@/components/Reusable/FormField/Switch.vue'
import UploadStatus from '@/components/Reusable/UploadStatus.vue'
import { useChunkedFileUpload } from '@/core/composables/useChunkedFileUpload'
const route = useRoute()
const router = useRouter()
const productId = route.params.id
const alertStore = useAlertStore()
const { errors, setErrors, setFieldError } = useForm()

const saving = ref(false)
const { file, uploading, uploadProgress, fileError, uploadedName, uploadedForFile, onFile } = useChunkedFileUpload()

const form = ref({
    title: '', version: '', description: '', release_type: 'official',
    is_private: false, is_restricted: false, dependencies: '',
})

const releaseTypes = [
    { name: __('message.official') || 'Official', value: 'official' },
    { name: __('message.pre_release') || 'Pre Release', value: 'pre_release' },
    { name: __('message.beta') || 'Beta', value: 'beta' },
]
const selectedReleaseType = computed(() => releaseTypes.find(r => r.value === form.value.release_type) ?? releaseTypes[0])

function parseDependencies() {
    const raw = (form.value.dependencies || '').trim() || '{}'
    try {
        const data = JSON.parse(raw)

        // Backend just needs an `array` (json_decode(..., true) turns both a
        // JSON array and a JSON object into a PHP array) - reject only what
        // that would actually reject: a bare string/number/bool/null.
        return typeof data === 'object' && data !== null ? { data } : { error: 'invalid_json' }
    } catch {
        return { error: 'invalid_json' }
    }
}

// The file has its own independent upload step (useChunkedFileUpload) — this
// just checks it actually succeeded.
function validateSlot() {
    if (!file.value) {
        fileError.value = __('validation.product_validate.filename_required')
    } else if (uploading.value) {
        fileError.value = __('message.please_wait')
    } else if (uploadedForFile.value !== file.value && !fileError.value) {
        fileError.value = __('message.something_wrong')
    }
}

async function submit() {
    validateSlot()

    const errs = {}
    if (!form.value.title)       errs.title       = __('validation.product_validate.producttitle_required')
    if (!form.value.version)     errs.version     = __('validation.product_validate.version_required')
    if (!form.value.description) errs.description = __('validation.product_validate.description_required')
    const deps = parseDependencies()
    if (deps.error) errs.dependencies = __('message.enter_json_format') || 'Enter valid JSON format.'
    setErrors(errs)
    if (Object.keys(errs).length || fileError.value) return

    saving.value = true
    alertStore.unsetAlert()
    try {
        await http.post(`/product/upload/${productId}`, {
            producttitle: form.value.title,
            version: form.value.version,
            filename: uploadedName.value,
            description: form.value.description,
            release_type: form.value.release_type,
            is_private: form.value.is_private,
            is_restricted: form.value.is_restricted,
            dependencies: deps.data,
        })
        alertStore.setAlert({ message: __('message.product_uploaded_successfully'), type: 'success', component_name: 'product-version-create' })
        setTimeout(() => router.push(`/products/${productId}/edit?tab=versions`), 2000)
    } catch (err) {
        const res = err.response?.data
        if (res?.errors) setErrors(Object.fromEntries(Object.entries(res.errors).map(([k, v]) => [k, v[0]])))
        else alertStore.setAlert({ message: res?.message || err.message || __('message.something_wrong'), type: 'danger', component_name: 'product-version-create' })
    } finally {
        saving.value = false
    }
}
</script>
