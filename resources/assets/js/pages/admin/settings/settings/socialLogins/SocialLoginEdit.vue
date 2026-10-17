<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ form.type }} {{ __('message.settings') }}</h4>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <TextField
                                name="client_id"
                                :label="__('message.client_id')"
                                :required="true"
                                :placeholder="__('message.enter_client_id_placeholder')"
                                :value="form.client_id"
                                :onChange="(val) => { setFieldError('client_id', undefined); form.client_id = val }"
                                :error="errors.client_id"
                            />
                        </div>
                        <div class="col-md-6">
                            <TextField
                                name="client_secret"
                                :label="__('message.client_secret')"
                                :required="true"
                                :placeholder="__('message.enter_client_secret_placeholder')"
                                :value="form.client_secret"
                                :onChange="(val) => { setFieldError('client_secret', undefined); form.client_secret = val }"
                                :error="errors.client_secret"
                            />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('message.redirect_url') }}</label>
                            <div class="input-group">
                                <input class="form-control" readonly :value="callbackUrl" />
                                <span class="input-group-text cursor-pointer" @click="copyCallbackUrl">
                                    <i :class="copied ? 'fas fa-check text-success' : 'fas fa-copy'"></i>
                                    {{ copied ? __('message.copied') : __('message.copy') }}
                                </span>
                            </div>
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
                    <action-button action="save" :loading="saving" @click="submit" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import TextField from '@/components/Reusable/FormField/TextField.vue'
import Switch from '@/components/Reusable/FormField/Switch.vue'
import { useRoute, useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import { validateForm } from '@/helpers/formUtils.js'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import { socialLoginSchema } from '@/validations/admin/socialLoginValidations'

const COMPONENT = 'social-logins-edit'
const route = useRoute()
const router = useRouter()

const { errors, setErrors, setFieldError } = useForm()

const baseUrl = useBaseUrl()
// Fixed by the backend route; this is what must be registered with the provider.
const callbackUrl = computed(() => `${baseUrl}/auth/callback/${form.type.toLowerCase()}`)
const copied = ref(false)

function copyCallbackUrl() {
    navigator.clipboard.writeText(callbackUrl.value).then(() => {
        copied.value = true
        setTimeout(() => { copied.value = false }, 2000)
    })
}

const loading = ref(true)
const saving = ref(false)

const form = reactive({
    type:         '',
    client_id:    '',
    client_secret: '',
    status:       false,
})

onMounted(async () => {
    try {
        const res = await http.get(`/edit/SocialLogins/${route.params.id}`)
        const d = res.data?.data ?? res.data
        form.type         = d.type ?? ''
        form.client_id    = d.client_id ?? ''
        form.client_secret = d.client_secret ?? ''
        form.status       = Boolean(d.status)
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        loading.value = false
    }
})

async function submit() {
    if (!await validateForm(socialLoginSchema, form, setErrors)) return
    saving.value = true
    try {
        const payload = {
            type:         form.type,
            optradio:     form.status ? 1 : 0,
        }
        if (form.type === 'Twitter') {
            payload.api_key    = form.client_id
            payload.api_secret = form.client_secret
        } else {
            payload.client_id     = form.client_id
            payload.client_secret = form.client_secret
        }
        const res = await http.post(`/update-social-login`, payload)
        successHandler(res, COMPONENT)
        setTimeout(() => router.push('/settings/social-logins'), 2000)
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        saving.value = false
    }
}
</script>
