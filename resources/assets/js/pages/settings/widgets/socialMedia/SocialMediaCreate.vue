<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.add_social_media') }}</h4>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <TextField name="name" :label="__('message.name')" :required="true" :value="form.name" :onChange="onChange" placeholder="e.g. Twitter" />
                    </div>
                    <div class="col-md-4 mb-3">
                        <TextField name="link" :label="__('message.link')" :required="true" :value="form.link" :onChange="onChange" placeholder="https://twitter.com/..." />
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <action-button action="save" :loading="saving" @click="submit" />
                <action-button action="cancel" to="/settings/widgets/social-media" class="ms-2" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { useFormValidation } from '@/composables/useFormValidation'

const COMPONENT = 'social-media-create'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const router = useRouter()

const { validate, clearFieldError, clearAllErrors } = useFormValidation()

const saving = ref(false)
const form = reactive({ name: '', link: '' })

onMounted(() => { clearAllErrors() })

function onChange(val, name) {
    clearFieldError(name)
    form[name] = val
}

async function submit() {
    const isValid = validate({
        name: [form.name, { isRequired: __('validation.social_media_form.name.required') }],
        link: [form.link, { isRequired: __('validation.social_media_form.link.required') }, { isUrl: __('validation.social_media_form.link.url') }],
    })
    if (!isValid) return

    saving.value = true
    try {
        const res = await http.post(`${baseUrl}/social-media/create`, form)
        successHandler(res, COMPONENT)
        setTimeout(() => router.push('/settings/widgets/social-media'), 2000)
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { saving.value = false }
}
</script>
