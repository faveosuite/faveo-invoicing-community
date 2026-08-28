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
                        <div class="col-md-4 mb-3">
                            <TextField name="name" :label="__('message.name')" :required="true" :value="form.name" :onChange="onChange" :error="errors.name" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <TextField name="link" :label="__('message.link')" :required="true" :value="form.link" :onChange="onChange" :error="errors.link" />
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

const COMPONENT = 'social-media-edit'
const route = useRoute()
const router = useRouter()
const mediaId = route.params.id

const { errors, setErrors, setFieldError } = useForm()

const loading = ref(true)
const saving = ref(false)
const form = reactive({ name: '', link: '' })

function onChange(val, name) {
    setFieldError(name, undefined)
    form[name] = val
}

onMounted(async () => {
    try {
        const res = await http.get(`/social-media/show/${mediaId}`)
        const d = res.data?.data ?? {}
        form.name = d.name ?? ''
        form.link = d.link ?? ''
    } catch (e) { errorHandler(e, COMPONENT, { setErrors }) }
    finally { loading.value = false }
})

async function submit() {
    if (!await validateForm(socialMediaSchema, form, setErrors)) return

    saving.value = true
    try {
        const res = await http.patch(`/social-media/update/${mediaId}`, form)
        successHandler(res, COMPONENT)
        setTimeout(() => router.push('/settings/widgets/social-media'), 2000)
    } catch (e) { errorHandler(e, COMPONENT, { setErrors }) }
    finally { saving.value = false }
}
</script>
