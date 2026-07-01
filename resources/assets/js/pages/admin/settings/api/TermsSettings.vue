<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.terms_heading') }}</h4>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

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
import { validateForm } from '@/helpers/formUtils.js'
import TextField from '@/components/Reusable/FormField/TextField.vue'
import { termsSchema } from '@/validations/admin/termsValidations'

const COMPONENT = 'terms-settings'

const loading = ref(true)
const saving  = ref(false)

const { errors, setErrors, setFieldError } = useForm()

const form = reactive({ terms_url: '' })

onMounted(async () => {
    try {
        const res = await http.get(`/settings/terms`)
        form.terms_url = res.data?.data?.terms_url ?? ''
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function save() {
    if (!await validateForm(termsSchema, form, setErrors)) return

    saving.value = true
    try {
        const res = await http.post(`/updateTermsDetails`, {
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
