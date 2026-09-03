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
                        <TextField name="name" :label="__('message.name')" :required="true" :value="form.name" :onChange="onChange" placeholder="e.g. Twitter" :error="errors.name" />
                    </div>
                    <div class="col-md-4 mb-3">
                        <TextField name="link" :label="__('message.link')" :required="true" :value="form.link" :onChange="onChange" placeholder="https://twitter.com/..." :error="errors.link" />
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <action-button action="save" :loading="saving" @click="submit" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import { validateForm } from '@/helpers/formUtils.js'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { socialMediaSchema } from '@/validations/admin/widgetValidations'

const COMPONENT = 'social-media-create'
const router = useRouter()

const { errors, setErrors, setFieldError } = useForm()

const saving = ref(false)
const form = reactive({ name: '', link: '' })

function onChange(val, name) {
    setFieldError(name, undefined)
    form[name] = val
}

async function submit() {
    if (!await validateForm(socialMediaSchema, form, setErrors)) return

    saving.value = true
    try {
        const res = await http.post(`/social-media/create`, form)
        successHandler(res, COMPONENT)
        setTimeout(() => router.push('/settings/widgets/social-media'), 2000)
    } catch (e) { errorHandler(e, COMPONENT, { setErrors }) }
    finally { saving.value = false }
}
</script>
