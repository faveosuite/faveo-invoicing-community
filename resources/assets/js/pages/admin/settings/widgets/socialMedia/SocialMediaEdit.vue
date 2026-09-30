<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.edit_social_media') }}</h4>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <!-- Platform Presets (or Custom) -->
                        <div :class="form.platform === 'custom' ? 'col-md-6 mb-3' : 'col-md-12 mb-3'">
                            <PlatformPicker
                                name="platform"
                                :label="__('message.platform')"
                                :required="true"
                                :options="platformOptions"
                                :value="form.platform"
                                :onChange="onPlatformChange"
                            />
                        </div>

                        <!-- Custom Icon field (Only visible when Custom is selected) -->
                        <div v-if="form.platform === 'custom'" class="col-md-6 mb-3 d-flex align-items-end gap-2">
                            <div class="flex-grow-1">
                                <TextField
                                    name="fa_class"
                                    :label="__('message.fa-class')"
                                    :required="true"
                                    :value="form.fa_class"
                                    :onChange="onChange"
                                    placeholder="e.g. fab fa-bluesky, fas fa-heart"
                                    :error="errors.fa_class"
                                />
                            </div>
                            <div
                                v-if="form.fa_class"
                                class="mb-3 d-flex align-items-center justify-content-center border rounded bg-light flex-shrink-0"
                                style="width: 38px; height: 38px;"
                                :title="form.fa_class"
                            >
                                <i :class="form.fa_class" class="fs-5 text-secondary"></i>
                            </div>
                        </div>

                        <!-- Name -->
                        <div class="col-md-6 mb-3">
                            <TextField
                                name="name"
                                :label="__('message.name')"
                                :required="true"
                                :value="form.name"
                                :onChange="onChange"
                                :error="errors.name"
                            />
                        </div>

                        <!-- Link -->
                        <div class="col-md-6 mb-3">
                            <TextField
                                name="link"
                                :label="__('message.link')"
                                :required="true"
                                :value="form.link"
                                :onChange="onChange"
                                :error="errors.link"
                            />
                        </div>
                    </div>
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
import { useRoute, useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import { validateForm } from '@/helpers/formUtils.js'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { socialMediaSchema } from '@/validations/admin/widgetValidations'
import {
    SOCIAL_PLATFORMS,
    SOCIAL_PLATFORM_OPTIONS,
    findPlatformByClass,
} from './platforms'
import PlatformPicker from './PlatformPicker.vue'

const COMPONENT = 'social-media-edit'
const route = useRoute()
const router = useRouter()
const mediaId = route.params.id

const { errors, setErrors, setFieldError } = useForm()

const loading = ref(true)
const saving = ref(false)
const platformOptions = SOCIAL_PLATFORM_OPTIONS
const form = reactive({ platform: '', name: '', link: '', class: '', fa_class: '' })

function onChange(val, name) {
    setFieldError(name, undefined)
    form[name] = val

    if (name === 'name' && form.platform === 'custom') {
        const slug = val.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')
        form.class = slug ? `social-icons-${slug}` : 'social-icons-custom'
    }
}

function onPlatformChange(val, preset) {
    const oldPreset = SOCIAL_PLATFORMS[form.platform]
    form.platform = val

    if (val === 'custom') {
        if (!form.fa_class || form.fa_class === oldPreset?.fa_class) {
            form.fa_class = ''
        }
        form.class = 'social-icons-custom'
    } else {
        const p = preset || SOCIAL_PLATFORMS[val]
        if (p) {
            if (!form.name || (oldPreset && form.name === oldPreset.label)) {
                form.name = p.label
            }
            form.class = p.class
            form.fa_class = p.fa_class
            if (!form.link && p.urlTemplate) {
                form.link = p.urlTemplate
            }
        }
    }

    setFieldError('class', undefined)
    setFieldError('fa_class', undefined)
}

onMounted(async () => {
    try {
        const res = await http.get(`/social-media/show/${mediaId}`)
        const d = res.data?.data ?? {}
        form.name = d.name ?? ''
        form.link = d.link ?? ''
        form.class = d.class ?? ''
        form.fa_class = d.fa_class ?? ''
        form.platform = findPlatformByClass(form.class, form.fa_class)
        if (form.platform !== 'custom' && SOCIAL_PLATFORMS[form.platform]) {
            form.class = SOCIAL_PLATFORMS[form.platform].class
            form.fa_class = SOCIAL_PLATFORMS[form.platform].fa_class
        }
    } catch (e) { errorHandler(e, COMPONENT, { setErrors }) }
    finally { loading.value = false }
})

async function submit() {
    if (form.platform !== 'custom' && SOCIAL_PLATFORMS[form.platform]) {
        form.class = SOCIAL_PLATFORMS[form.platform].class
        form.fa_class = SOCIAL_PLATFORMS[form.platform].fa_class
    } else {
        if (!form.class) form.class = 'social-icons-custom'
        if (!form.fa_class) form.fa_class = 'fas fa-icons'
    }

    if (!await validateForm(socialMediaSchema, form, setErrors)) return

    saving.value = true
    try {
        const { name, link, class: cssClass, fa_class } = form
        const res = await http.patch(`/social-media/update/${mediaId}`, { name, link, class: cssClass, fa_class })
        successHandler(res, COMPONENT)
        setTimeout(() => router.push('/settings/widgets/social-media'), 2000)
    } catch (e) { errorHandler(e, COMPONENT, { setErrors }) }
    finally { saving.value = false }
}
</script>
