<template>
    <div>
        <AppCard :title="__('message.change_password')">
        <form @submit.prevent="submitPassword" class="needs-validation">

            <ClientField type="password" name="current_password"
                         :label="__('message.current_password')"
                         v-model="form.current_password"
                         :error="errors.current_password"
                         @update:modelValue="setFieldError('current_password', undefined)"
                         autocomplete="current-password"
                         :required="true" />

            <ClientField type="password" name="password"
                         :label="__('message.new_password')"
                         v-model="form.password"
                         :error="errors.password"
                         @update:modelValue="setFieldError('password', undefined)"
                         autocomplete="new-password"
                         :required="true" />

            <ClientField type="password" name="password_confirmation"
                         :label="__('message.confirm_password')"
                         v-model="form.password_confirmation"
                         :error="errors.password_confirmation"
                         @update:modelValue="setFieldError('password_confirmation', undefined)"
                         autocomplete="new-password"
                         :required="true" />

            <div class="form-group row">
                <div class="form-group col-lg-9"></div>
                <div class="form-group col-lg-3">
                    <button type="submit" class="btn btn-primary btn-modern float-end" :disabled="saving">
                        <i v-if="saving" class="fas fa-circle-notch fa-spin me-1"></i>
                        {{ __('message.save') }}
                    </button>
                </div>
            </div>

        </form>
        </AppCard>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useForm } from 'vee-validate'
import { validateForm } from '@/helpers/formUtils.js'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { passwordChangeSchema } from '@/validations/client/profile.js'

const COMPONENT = 'client-page'

const saving = ref(false)
const { errors, setErrors, setFieldError } = useForm()

const form = reactive({
    current_password: '',
    password: '',
    password_confirmation: '',
})

async function submitPassword() {
    if (!await validateForm(passwordChangeSchema, form, setErrors)) return
    saving.value = true
    try {
        const data = new FormData()
        Object.entries(form).forEach(([k, v]) => { if (v != null) data.append(k, v) })
        data.append('_method', 'PATCH')
        const res = await http.post(`/my-password`, data, { headers: { 'Content-Type': 'multipart/form-data' } })
        successHandler(res, COMPONENT)
        form.current_password = ''
        form.password = ''
        form.password_confirmation = ''
    } catch (e) {
        if (e?.response?.status === 422) {
            const serverErrors = e.response.data?.errors ?? {}
            const map = {}
            Object.entries(serverErrors).forEach(([k, v]) => { map[k] = Array.isArray(v) ? v[0] : v })
            if (Object.keys(map).length) {
                setErrors(map)
                return
            }
        }
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
