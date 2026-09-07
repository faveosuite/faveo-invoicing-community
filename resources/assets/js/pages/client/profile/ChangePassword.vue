<template>
    <div>
        <AppCard :title="__('message.change_password')">
        <form @submit.prevent="submitPassword" class="needs-validation">

            <ClientField type="password" name="old_password"
                         :label="__('message.current_password')"
                         v-model="form.old_password"
                         :error="errors.old_password"
                         @update:modelValue="setFieldError('old_password', undefined)"
                         autocomplete="current-password"
                         :required="true" />

            <ClientField type="password" name="new_password"
                         :label="__('message.new_password')"
                         v-model="form.new_password"
                         :error="errors.new_password"
                         @update:modelValue="setFieldError('new_password', undefined)"
                         @focus="passwordFocused = true" @blur="passwordFocused = false"
                         autocomplete="new-password"
                         :required="true" />

            <div v-if="passwordFocused" class="mb-3 text-1">
                <strong class="d-block mb-1 text-dark">{{ __('message.password_requirements') }}</strong>
                <ul class="list-unstyled mb-0">
                    <li v-for="c in checklist" :key="c.key" :class="c.ok ? 'text-success' : 'text-danger'">
                        <i class="fas" :class="c.ok ? 'fa-check' : 'fa-times'"></i> {{ c.label }}
                    </li>
                </ul>
            </div>

            <ClientField type="password" name="confirm_password"
                         :label="__('message.confirm_password')"
                         v-model="form.confirm_password"
                         :error="errors.confirm_password"
                         @update:modelValue="setFieldError('confirm_password', undefined)"
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
import { reactive, ref, computed } from 'vue'
import { useForm } from 'vee-validate'
import { validateForm } from '@/helpers/formUtils.js'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { passwordChangeSchema } from '@/validations/client/profile.js'
import { passwordChecks } from '@/validations/client/authSchemas.js'

const COMPONENT = 'client-page'

const saving = ref(false)
const { errors, setErrors, setFieldError } = useForm()

const form = reactive({
    old_password: '',
    new_password: '',
    confirm_password: '',
})

// Show the requirements list only while the new-password field is focused.
const passwordFocused = ref(false)
const checklist = computed(() => {
    const c = passwordChecks(form.new_password)
    return [
        { key: 'length',  ok: c.length,  label: __('message.pwd_req_length') },
        { key: 'upper',   ok: c.upper,   label: __('message.pwd_req_upper') },
        { key: 'lower',   ok: c.lower,   label: __('message.pwd_req_lower') },
        { key: 'number',  ok: c.number,  label: __('message.pwd_req_number') },
        { key: 'special', ok: c.special, label: __('message.pwd_req_special') },
    ]
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
        form.old_password = ''
        form.new_password = ''
        form.confirm_password = ''
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        saving.value = false
    }
}
</script>
