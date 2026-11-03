<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.login_page') }}</h4>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">
                    <p class="text-muted">{{ __('message.auth_brand_help') }}</p>

                    <div class="d-flex align-items-center gap-2 mb-4">
                        <Switch name="show" :value="form.show" :onChange="(val) => form.show = val" labelledby="auth-brand-show-label" />
                        <span id="auth-brand-show-label" class="fw-semibold">{{ __('message.auth_brand_show') }}</span>
                    </div>

                    <fieldset :disabled="!form.show">
                        <!-- Placeholder = the translated default shown when the field is left empty. -->
                        <div class="row">
                            <TextField v-for="f in TEXT_FIELDS" :key="f.name" :name="f.name" :label="f.label"
                                       :value="form[f.name]" :onChange="onChange" :error="errors[f.name]"
                                       :placeholder="f.placeholder" :hint="__('message.auth_brand_default_hint')"
                                       length="255" classname="col-md-12 mb-3" />
                        </div>

                        <label class="form-label fw-semibold d-block">{{ __('message.image') }}</label>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <Switch name="use_default_image" :value="useDefaultImage" :onChange="(val) => useDefaultImage = val" labelledby="auth-brand-default-image-label" />
                            <span id="auth-brand-default-image-label">{{ __('message.use_default') }}</span>
                        </div>
                        <div class="row">
                            <ImageUpload :label="__('message.image')" :labelStyle="{ visibility: 'hidden' }" :value="imagePreview" name="image"
                                         :onChange="onImageChange" :is_default="useDefaultImage" :componentName="COMPONENT"
                                         :allowedTypes="['image/png', 'image/jpg', 'image/jpeg', 'image/webp']"
                                         classname="col-sm-4" />
                        </div>
                    </fieldset>
                </div>

                <div class="card-footer">
                    <action-button action="update" :loading="saving" @click="submit" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import Switch from '@/components/Reusable/FormField/Switch.vue'
import ImageUpload from '@/components/Reusable/FormField/ImageUpload.vue'
import { useBaseUrl } from '@/core/composables/useBaseUrl'

const COMPONENT = 'auth-brand-settings'
const { errors, setErrors, setFieldError } = useForm()

const TEXT_FIELDS = [
    { name: 'title',       label: __('message.title'),       placeholder: __('message.auth_brand_title') },
    { name: 'description', label: __('message.description'), placeholder: __('message.auth_brand_description') },
    { name: 'tagline',     label: __('message.tagline'),     placeholder: __('message.auth_brand_tagline') },
]

const form = reactive({ show: true, title: '', description: '', tagline: '' })
const loading = ref(true)
const saving = ref(false)

// The built-in illustration is used when no image is saved (or "Use default" is on).
const defaultImage = `${useBaseUrl()}/images/auth-brand.svg`
const useDefaultImage = ref(true)
const imagePreview = ref('')
const selectedImage = ref(null)
const selectedImageName = ref('')

function onChange(val, name) {
    setFieldError(name, undefined)
    form[name] = val
}

function onImageChange(value) {
    imagePreview.value = value.image
    selectedImage.value = value.file
    selectedImageName.value = value.name
}

onMounted(async () => {
    try {
        const data = (await http.get('/auth-brand')).data?.data ?? {}
        form.show = data.show !== false
        form.title = data.title ?? ''
        form.description = data.description ?? ''
        form.tagline = data.tagline ?? ''
        useDefaultImage.value = !data.image
        imagePreview.value = data.image || defaultImage
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function submit() {
    saving.value = true
    try {
        const fd = new FormData()
        fd.append('show', form.show ? 1 : 0)
        fd.append('title', form.title ?? '')
        fd.append('description', form.description ?? '')
        fd.append('tagline', form.tagline ?? '')
        fd.append('use_default_image', useDefaultImage.value ? 1 : 0)
        if (!useDefaultImage.value && selectedImage.value) {
            fd.append('image', selectedImage.value, selectedImageName.value)
        }

        const res = await http.post('/auth-brand', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors, excludeFields: ['image'] })
    } finally {
        saving.value = false
    }
}
</script>
