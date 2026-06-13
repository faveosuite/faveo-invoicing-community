<template>
    <div>
        <AppAlert componentName="product-version-edit" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.edit_version') || 'Edit Version' }}</h4>
            </div>

            <div class="card-body">
                <div v-if="loading" class="row justify-content-center py-3"><loader /></div>
                <div v-else class="row g-3">
                    <div class="col-md-4">
                        <TextField name="title" :label="__('message.title')" :required="true" :value="form.title"
                            :onChange="(v) => { form.title = v; setFieldError('title', undefined) }" :error="errors.title" />
                    </div>
                    <div class="col-md-4">
                        <TextField name="version" :label="__('message.version')" :required="true" :value="form.version"
                            :onChange="(v) => { form.version = v; setFieldError('version', undefined) }" :error="errors.version" />
                    </div>
                    <div class="col-md-4">
                        <SelectField name="release_type" :label="__('message.release_type')" :required="true"
                            :elements="releaseTypes" :value="selectedReleaseType"
                            :onChange="(v) => form.release_type = v?.value ?? ''" :clearable="false" :searchable="false" />
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold d-block">{{ __('message.file') }}</label>
                        <input type="file" class="form-control" accept=".zip" @change="onFile" />
                        <small v-if="uploadedName" class="text-success d-block mt-1">{{ uploadedName }}</small>
                        <small v-else-if="form.file" class="text-muted d-block mt-1">{{ form.file }}</small>
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
                        <TinyMCE name="description" id="editor-version-description-edit" :label="__('message.description')"
                            :value="form.description" :onChange="(v) => form.description = v" />
                    </div>

                    <div class="col-md-12">
                        <TextArea name="dependencies" type="textarea" :rows="8" :length="100000" :label="__('message.dependencies')" :required="true"
                            :hint="__('message.enter_json_format')" :value="form.dependencies"
                            :onChange="(v) => { form.dependencies = v; setFieldError('dependencies', undefined) }" :error="errors.dependencies" />
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <action-button action="update" :loading="saving" @click="submit" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { useAlertStore } from '@/core/stores/alert'
import TextArea from '@/components/Reusable/FormField/TextField.vue'
import Switch from '@/components/Reusable/FormField/Switch.vue'

const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const route = useRoute()
const router = useRouter()
const productId = route.params.id
const versionId = route.params.versionId
const alertStore = useAlertStore()
const { errors, setErrors, setFieldError } = useForm()

const loading = ref(true)
const saving = ref(false)
const file = ref(null)
const uploadedName = ref('')

const form = ref({
    title: '', version: '', description: '', release_type: 'official',
    is_private: false, is_restricted: false, dependencies: '', file: '',
})

function onFile(e) {
    file.value = e.target.files?.[0] ?? null
    uploadedName.value = ''
}

const releaseTypes = [
    { name: __('message.official') || 'Official', value: 'official' },
    { name: __('message.pre_release') || 'Pre Release', value: 'pre_release' },
    { name: __('message.beta') || 'Beta', value: 'beta' },
]
const selectedReleaseType = computed(() => releaseTypes.find(r => r.value === form.value.release_type) ?? releaseTypes[0])

function parseDependencies() {
    try {
        const parsed = JSON.parse(form.value.dependencies || '[]')
        return Array.isArray(parsed) ? parsed : null
    } catch {
        return null
    }
}

async function submit() {
    const errs = {}
    if (!form.value.title)   errs.title   = __('message.title')
    if (!form.value.version) errs.version = __('message.version')
    const deps = parseDependencies()
    if (deps === null) errs.dependencies = __('message.enter_json_format') || 'Enter valid JSON array.'
    setErrors(errs)
    if (Object.keys(errs).length) return

    saving.value = true
    alertStore.unsetAlert()
    try {
        // Upload a replacement file only if the admin picked a new one.
        let filename
        if (file.value) {
            const fd = new FormData()
            fd.append('file', file.value)
            const up = await http.post(`${baseUrl}/chunkupload`, fd, { headers: { 'Content-Type': 'multipart/form-data' } })
            filename = up.data?.name
            if (!filename) throw new Error(__('message.something_wrong'))
            uploadedName.value = filename
        }

        await http.patch(`${baseUrl}/product/upload/${versionId}`, {
            title: form.value.title,
            version: form.value.version,
            description: form.value.description,
            release_type: form.value.release_type,
            is_private: form.value.is_private,
            is_restricted: form.value.is_restricted,
            dependencies: deps,
            ...(filename ? { filename } : {}),
        })
        alertStore.setAlert({ message: __('message.product_updated_successfully'), type: 'success', component_name: 'products-edit' })
        router.push(`/products/${productId}/edit?tab=versions`)
    } catch (err) {
        const res = err.response?.data
        if (res?.errors) setErrors(Object.fromEntries(Object.entries(res.errors).map(([k, v]) => [k, v[0]])))
        else alertStore.setAlert({ message: res?.message || err.message || __('message.something_wrong'), type: 'danger', component_name: 'product-version-edit' })
    } finally {
        saving.value = false
    }
}

onMounted(async () => {
    try {
        const { data } = await http.get(`${baseUrl}/product/upload/${versionId}`)
        const u = data.data
        form.value = {
            title: u.title ?? '', version: u.version ?? '', description: u.description ?? '',
            release_type: u.release_type ?? 'official',
            is_private: !!u.is_private, is_restricted: !!u.is_restricted,
            dependencies: JSON.stringify(u.dependencies ?? [], null, 2),
            file: u.file ?? '',
        }
    } catch (err) {
        alertStore.setAlert({ message: err.response?.data?.message || __('message.something_wrong'), type: 'danger', component_name: 'product-version-edit' })
    } finally {
        loading.value = false
    }
})
</script>
