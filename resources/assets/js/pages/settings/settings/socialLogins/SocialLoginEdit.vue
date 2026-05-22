<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.edit_social_login') }} — {{ form.type }}</h4>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <TextField
                                name="client_id"
                                :label="form.type === 'Twitter' ? __('message.api_key') : __('message.client_id')"
                                :value="form.client_id"
                                :onChange="(val) => { setFieldError('client_id', undefined); form.client_id = val }"
                                :error="errors.client_id"
                            />
                        </div>
                        <div class="col-md-6">
                            <TextField
                                name="client_secret"
                                :label="form.type === 'Twitter' ? __('message.lic_api_secret') : __('message.client_secret')"
                                :value="form.client_secret"
                                :onChange="(val) => { setFieldError('client_secret', undefined); form.client_secret = val }"
                                :error="errors.client_secret"
                            />
                        </div>
                        <div class="col-md-6">
                            <TextField
                                name="redirect_url"
                                :label="__('message.redirect_url')"
                                :value="form.redirect_url"
                                :onChange="(val) => { setFieldError('redirect_url', undefined); form.redirect_url = val }"
                                :error="errors.redirect_url"
                            />
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold d-block">{{ __('message.status') }}</label>
                                <Switch name="status" :value="form.status" :onChange="(val) => form.status = val" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <action-button action="update" :loading="saving" @click="submit" />
                    <action-button action="cancel" to="/settings/social-logins" class="ms-2" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import TextField from '@/components/Reusable/FormField/TextField.vue'
import Switch from '@/components/Reusable/FormField/Switch.vue'
import { useRoute, useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { socialLoginSchema } from '@/validations/socialLoginValidations'

const COMPONENT = 'social-logins-edit'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const route = useRoute()
const router = useRouter()

const { errors, setErrors, setFieldError } = useForm()

const loading = ref(true)
const saving = ref(false)

const form = reactive({
    type:         '',
    client_id:    '',
    client_secret: '',
    redirect_url: '',
    status:       false,
})

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/edit/SocialLogins/${route.params.id}`)
        const d = res.data?.data ?? res.data
        form.type         = d.type ?? ''
        form.client_id    = d.client_id ?? ''
        form.client_secret = d.client_secret ?? ''
        form.redirect_url = d.redirect_url ?? ''
        form.status       = Boolean(d.status)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function submit() {
    try {
        socialLoginSchema.validateSync(form, { abortEarly: false })
    } catch (err) {
        const errMap = {}
        err.inner?.forEach(e => { if (e.path && !errMap[e.path]) errMap[e.path] = e.message })
        setErrors(errMap)
        return
    }
    saving.value = true
    try {
        const payload = {
            type:         form.type,
            redirect_url: form.redirect_url,
            optradio:     form.status ? 1 : 0,
        }
        if (form.type === 'Twitter') {
            payload.api_key    = form.client_id
            payload.api_secret = form.client_secret
        } else {
            payload.client_id     = form.client_id
            payload.client_secret = form.client_secret
        }
        const res = await http.post(`${baseUrl}/update-social-login`, payload)
        successHandler(res, COMPONENT)
        setTimeout(() => router.push('/settings/social-logins'), 2000)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
