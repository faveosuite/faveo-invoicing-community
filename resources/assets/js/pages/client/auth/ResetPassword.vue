<template>
    <AuthLayout>
        <div v-if="loading" class="text-center py-5">
            <inline-loader />
        </div>

        <div v-else-if="invalid" class="text-center py-4">
            <i class="fas fa-times-circle text-danger fa-3x mb-3"></i>
            <p class="text-color-default">{{ invalidMessage || __('message.reset_link_expired') }}</p>
            <RouterLink to="/login" class="btn btn-dark btn-modern mt-2">
                {{ __('message.back_to_login') }}
            </RouterLink>
        </div>

        <form v-else @submit.prevent="submit" novalidate>
            <ClientField name="password" type="password" :label="__('message.new_password')" required
                         autocomplete="new-password"
                         v-model="form.password" :error="errors.password"
                         @focus="passwordFocused = true" @blur="passwordFocused = false"
                         @update:modelValue="setFieldError('password', undefined)" />

            <div v-if="passwordFocused" class="mb-3 text-1">
                <strong class="d-block mb-1 text-dark">{{ __('message.password_requirements') }}</strong>
                <ul class="list-unstyled mb-0">
                    <li v-for="c in checklist" :key="c.key" :class="c.ok ? 'text-success' : 'text-danger'">
                        <i class="fas" :class="c.ok ? 'fa-check' : 'fa-times'"></i> {{ c.label }}
                    </li>
                </ul>
            </div>

            <ClientField name="password_confirmation" type="password" :label="__('message.confirm_password')" required
                         autocomplete="new-password"
                         v-model="form.password_confirmation" :error="errors.password_confirmation"
                         @update:modelValue="setFieldError('password_confirmation', undefined)" />

            <Honeypot name="reset" v-model="form.reset" @ready="hpReady = $event" />

            <button type="submit"
                    class="btn btn-dark btn-modern w-100 text-uppercase rounded-0 font-weight-bold text-3 py-3"
                    :disabled="saving || !hpReady">
                <i v-if="saving" class="fas fa-circle-notch fa-spin me-1"></i>
                {{ __('message.reset_password') }}
            </button>
        </form>
    </AuthLayout>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { successHandler, applyServerValidation } from '@/helpers/responseHandler.js'
import { resetSchema, passwordChecks } from '@/validations/client/authSchemas.js'
import AuthLayout from './partials/AuthLayout.vue'
import Honeypot from '@/components/Reusable/Honeypot.vue'

const COMPONENT = 'client-page'
const el      = document.getElementById('app-client')
const baseUrl = el?.dataset?.baseUrl ?? ''
const route   = useRoute()

const { errors, setErrors, setFieldError } = useForm()
const form = reactive({ token: route.params.token, email: '', password: '', password_confirmation: '', reset: {} })

const loading        = ref(true)
const saving         = ref(false)
const hpReady        = ref(false)
const invalid        = ref(false)
const invalidMessage = ref('')

const checklist = computed(() => {
    const c = passwordChecks(form.password)
    return [
        { key: 'length',  ok: c.length,  label: __('message.pwd_req_length') },
        { key: 'upper',   ok: c.upper,   label: __('message.pwd_req_upper') },
        { key: 'lower',   ok: c.lower,   label: __('message.pwd_req_lower') },
        { key: 'number',  ok: c.number,  label: __('message.pwd_req_number') },
        { key: 'special', ok: c.special, label: __('message.pwd_req_special') },
    ]
})

// Show the requirements list only while the password field is focused.
const passwordFocused = ref(false)

onMounted(async () => {
    try {
        const res  = await http.get(`${baseUrl}/auth/reset-validate/${route.params.token}`)
        const data = res.data?.data

        // 2FA-protected reset → backend tells us to verify first.
        if (data?.redirect) {
            window.location.href = data.redirect
            return
        }

        // showResetForm() returns the payload wrapped in an array: [{ reset_token, email }]
        const payload = Array.isArray(data) ? data[0] : data
        form.email = payload?.email ?? ''
        form.token = payload?.reset_token ?? route.params.token
    } catch (e) {
        invalid.value = true
        invalidMessage.value = e?.response?.data?.message ?? ''
    } finally {
        loading.value = false
    }
})

async function submit() {
    try {
        resetSchema.validateSync(form, { abortEarly: false })
    } catch (err) {
        const map = {}
        err.inner?.forEach(e => { if (e.path && !map[e.path]) map[e.path] = e.message })
        setErrors(map)
        return
    }

    saving.value = true
    try {
        // `reset` is the honeypot field validated by the backend (App\Rules\Honeypot).
        const res = await http.post(`${baseUrl}/password/reset`, {
            token: form.token,
            email: form.email,
            password: form.password,
            password_confirmation: form.password_confirmation,
            reset: form.reset,
        })
        successHandler(res, COMPONENT)
        const redirect = res.data?.data?.redirect
        setTimeout(() => { window.location.href = redirect || `${baseUrl}/login` }, 1500)
    } catch (e) {
        applyServerValidation(e, { setErrors, fields: ['password', 'password_confirmation'], component: COMPONENT })
    } finally {
        saving.value = false
    }
}
</script>
