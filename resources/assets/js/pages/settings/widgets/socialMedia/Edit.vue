<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.edit_social_media') }}</h4>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <TextField name="name" :label="__('message.name')" :required="true" :value="form.name" :onChange="onChange" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <TextField name="link" :label="__('message.link')" :required="true" :value="form.link" :onChange="onChange" />
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <action-button action="update" :loading="saving" @click="submit" />
                    <action-button action="cancel" to="/settings/widgets/social-media" class="ms-2" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { useFormValidation } from '@/composables/useFormValidation'

const COMPONENT = 'social-media-edit'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const route = useRoute()
const router = useRouter()
const mediaId = route.params.id

const { validate, clearFieldError, clearAllErrors } = useFormValidation()

const loading = ref(true)
const saving = ref(false)
const form = reactive({ name: '', link: '' })

function onChange(val, name) {
    clearFieldError(name)
    form[name] = val
}

onMounted(async () => {
    clearAllErrors()
    try {
        const res = await http.get(`${baseUrl}/social-media/show/${mediaId}`)
        const d = res.data?.data ?? {}
        form.name = d.name ?? ''
        form.link = d.link ?? ''
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { loading.value = false }
})

async function submit() {
    const isValid = validate({
        name: [form.name, { isRequired: __('validation.social_media_form.name.required') }],
        link: [form.link, { isRequired: __('validation.social_media_form.link.required') }, { isUrl: __('validation.social_media_form.link.url') }],
    })
    if (!isValid) return

    saving.value = true
    try {
        const res = await http.patch(`${baseUrl}/social-media/update/${mediaId}`, form)
        successHandler(res, COMPONENT)
        setTimeout(() => router.push('/settings/widgets/social-media'), 2000)
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { saving.value = false }
}
</script>
