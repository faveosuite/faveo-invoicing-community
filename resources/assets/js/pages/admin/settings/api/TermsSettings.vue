<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.terms_heading') }}</h4>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <TextField
                                name="terms_url"
                                :label="__('message.terms_url')"
                                :value="form.terms_url"
                                placeholder="https://example.com/terms"
                                :onChange="(val, key) => { setFieldError(key, undefined); form[key] = val }"
                                :error="errors.terms_url"
                                :required="true"
                            />
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <action-button action="save" :loading="saving" @click="save" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import TextField from '@/themes/adminlte/components/forms/TextField.vue'
import { termsSchema } from '@/validations/admin/termsValidations'

const COMPONENT = 'terms-settings'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const saving  = ref(false)

const { errors, setErrors, setFieldError } = useForm()

const form = reactive({ terms_url: '' })

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/settings/terms`)
        form.terms_url = res.data?.data?.terms_url ?? ''
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function save() {
    try {
        termsSchema.validateSync(form, { abortEarly: false })
    } catch (err) {
        const errMap = {}
        err.inner?.forEach(e => { if (e.path && !errMap[e.path]) errMap[e.path] = e.message })
        setErrors(errMap)
        return
    }

    saving.value = true
    try {
        const res = await http.post(`${baseUrl}/updateTermsDetails`, {
            terms_url: form.terms_url,
            status:    1,
        })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
