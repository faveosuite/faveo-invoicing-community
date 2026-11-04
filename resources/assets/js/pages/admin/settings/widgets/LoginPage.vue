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
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <span class="form-label fw-bold d-flex align-items-center gap-1">
                                <span id="auth-brand-show-label">{{ __('message.auth_brand_show') }}</span>
                                <Tooltip :message="__('message.auth_brand_help')" size="small" />
                            </span>
                            <Switch name="show" :value="form.show" :onChange="(val) => form.show = val" labelledby="auth-brand-show-label" />
                        </div>
                        <!-- Which half the login/register form takes on large screens; the panel gets the other. -->
                        <div class="col-md-4 mb-3">
                            <span id="auth-form-position-label" class="form-label fw-bold d-block">{{ __('message.auth_form_position') }}</span>
                            <!-- Selected side = btn-primary, the other btn-light: both coloured by the admin theme. -->
                            <div class="btn-group" role="radiogroup" aria-labelledby="auth-form-position-label">
                                <button v-for="side in ['left', 'right']" :key="side" type="button" role="radio"
                                        class="btn" :class="form.form_position === side ? 'btn-primary' : 'btn-light border'"
                                        :aria-checked="form.form_position === side" :disabled="!form.show"
                                        @click="form.form_position = side">
                                    <i :class="side === 'left' ? 'fas fa-align-left' : 'fas fa-align-right'" class="me-1" aria-hidden="true"></i>
                                    {{ side === 'left' ? __('message.left') : __('message.right') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Fieldset only to disable everything when the panel is off — no box of its own. -->
                    <fieldset :disabled="!form.show" class="border-0 p-0 m-0" style="min-width: 0">
                        <!-- Placeholder = the translated default shown when the field is left empty. -->
                        <div class="row">
                            <div v-for="f in TEXT_FIELDS" :key="f.name" class="col-md-4">
                                <TextField :name="f.name" :label="f.label"
                                           :value="form[f.name]" :onChange="onChange" :error="errors[f.name]"
                                           :placeholder="f.placeholder" :hint="__('message.auth_brand_default_hint')"
                                           length="255" />
                            </div>
                        </div>

                        <div class="card card-light mb-0">
                            <div class="card-header">
                                <h5 class="card-title mb-0">{{ __('message.image') }}</h5>
                            </div>
                            <!-- Same layout as the logos card in Company settings: switch centred over the image.
                                 The circle always opens the picker; choosing an image turns "Use default" off. -->
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-md-4 text-center">
                                        <div class="d-inline-flex align-items-center gap-2">
                                            <Switch name="use_default_image" :value="useDefaultImage" :onChange="(val) => useDefaultImage = val" labelledby="auth-brand-default-image-label" />
                                            <span id="auth-brand-default-image-label" class="fw-normal">{{ __('message.use_default') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <ImageUpload :label="__('message.image')" :labelStyle="{ visibility: 'hidden' }" :labelCss="{ display: 'none !important' }"
                                                 :value="previewSrc" name="image" :onChange="onImageChange"
                                                 :componentName="COMPONENT" :allowedTypes="['image/png', 'image/jpg', 'image/jpeg', 'image/webp']"
                                                 classname="col-sm-4 text-center" />
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>

                <div class="card-footer">
                    <action-button action="save" :loading="saving" @click="submit" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import Switch from '@/components/Reusable/FormField/Switch.vue'
import ImageUpload from '@/components/Reusable/FormField/ImageUpload.vue'
import Tooltip from '@/components/Reusable/Tooltip.vue'
import { useBaseUrl } from '@/core/composables/useBaseUrl'

const COMPONENT = 'auth-brand-settings'
const { errors, setErrors, setFieldError } = useForm()

const TEXT_FIELDS = [
    { name: 'title',       label: __('message.title'),       placeholder: __('message.auth_brand_title') },
    { name: 'description', label: __('message.description'), placeholder: __('message.auth_brand_description') },
    { name: 'tagline',     label: __('message.tagline'),     placeholder: __('message.auth_brand_tagline') },
]

const form = reactive({ show: true, form_position: 'left', title: '', description: '', tagline: '' })
const loading = ref(true)
const saving = ref(false)

// The built-in illustration is used when no image is saved (or "Use default" is on).
const defaultImage = `${useBaseUrl()}/images/auth-brand.svg`
const useDefaultImage = ref(true)
const savedImage = ref('')      // image saved on the server, if any
const imagePreview = ref('')    // a newly picked (cropped) image, not saved yet
const selectedImage = ref(null)
const selectedImageName = ref('')
const previewSrc = computed(() => useDefaultImage.value ? defaultImage : (imagePreview.value || savedImage.value || defaultImage))

function onChange(val, name) {
    setFieldError(name, undefined)
    form[name] = val
}

function onImageChange(value) {
    imagePreview.value = value.image
    selectedImage.value = value.file
    selectedImageName.value = value.name
    useDefaultImage.value = false // picking an image means using it
}

onMounted(async () => {
    try {
        const data = (await http.get('/auth-brand')).data?.data ?? {}
        form.show = data.show !== false
        form.form_position = data.form_position === 'right' ? 'right' : 'left'
        form.title = data.title ?? ''
        form.description = data.description ?? ''
        form.tagline = data.tagline ?? ''
        useDefaultImage.value = !data.image
        savedImage.value = data.image || ''
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
        fd.append('form_position', form.form_position)
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
