<template>
    <div>
        <AppCard :title="__('message.change_password')">
        <form @submit.prevent="submitPassword" class="needs-validation">

            <ClientField type="password" name="current_password"
                         :label="__('message.current_password')"
                         v-model="form.current_password"
                         :error="errors.current_password"
                         autocomplete="current-password"
                         :required="true" />

            <ClientField type="password" name="password"
                         :label="__('message.new_password')"
                         v-model="form.password"
                         :error="errors.password"
                         autocomplete="new-password"
                         :required="true" />

            <ClientField type="password" name="password_confirmation"
                         :label="__('message.confirm_password')"
                         v-model="form.password_confirmation"
                         :error="errors.password_confirmation"
                         autocomplete="new-password"
                         :required="true" />

            <div class="form-group row">
                <div class="col-lg-9 offset-lg-3">
                    <button type="submit" class="btn btn-primary btn-modern" :disabled="saving">
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
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { passwordChangeSchema } from '@/validations/client/profile.js'

const el      = document.getElementById('app-client')
const baseUrl = el?.dataset?.baseUrl ?? ''

const COMPONENT = 'client-page'

const saving = ref(false)

const form = reactive({
    current_password: '',
    password: '',
    password_confirmation: '',
})
const errors = ref({})

async function submitPassword() {
    errors.value = {}
    try {
        passwordChangeSchema.validateSync(form, { abortEarly: false })
    } catch (err) {
        const map = {}
        err.inner?.forEach(e => { if (e.path && !map[e.path]) map[e.path] = e.message })
        errors.value = map
        return
    }
    saving.value = true
    try {
        const data = new FormData()
        Object.entries(form).forEach(([k, v]) => { if (v != null) data.append(k, v) })
        data.append('_method', 'PATCH')
        const res = await http.post(`${baseUrl}/my-password`, data, { headers: { 'Content-Type': 'multipart/form-data' } })
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
                errors.value = map
                return
            }
        }
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
