<template>
    <AuthLayout>
        <h2 class="font-weight-bold text-5 mb-2">{{ __('message.social_complete_title') }}</h2>
        <p class="text-2 mb-4">{{ __('message.social_complete_hint', { provider }) }}</p>

        <form @submit.prevent="submit" novalidate>
            <div class="row">
                <div class="col-md-6">
                    <ClientField name="first_name" type="text" :label="__('message.first_name')" required
                                 v-model="form.first_name" :error="errors.first_name"
                                 @update:modelValue="setFieldError('first_name', undefined)"/>
                </div>
                <div class="col-md-6">
                    <ClientField name="last_name" type="text" :label="__('message.last_name')" required
                                 v-model="form.last_name" :error="errors.last_name"
                                 @update:modelValue="setFieldError('last_name', undefined)"/>
                </div>
            </div>

            <ClientField name="email" type="email" :label="__('message.email_address')" required
                         :disabled="emailLocked"
                         v-model="form.email" :error="errors.email"
                         @update:modelValue="setFieldError('email', undefined)"/>

            <ClientField name="company" type="text" :label="__('message.company')" required
                         v-model="form.company" :error="errors.company"
                         @update:modelValue="setFieldError('company', undefined)"/>

            <ClientField name="address" type="textarea" :label="__('message.address')" required
                         v-model="form.address" :error="errors.address"
                         @update:modelValue="setFieldError('address', undefined)"/>

            <DynamicSelect name="country" :label="__('message.country')" required
                           :apiEndpoint="`${baseUrl}/dependency/countries`"
                           dataKey="countries"
                           :value="form.country"
                           :onChange="onCountryChange"
                           :error="errors.country"/>

            <PhoneField name="mobile" :label="__('message.mobile')" required
                        :value="form.mobile" :error="errors.mobile"
                        :onChange="onMobileInput"
                        @countryChange="onMobileCountryChange"/>

            <ClientCheckbox v-if="termsEnabled" v-model="form.terms" :error="errors.terms"
                            @update:modelValue="setFieldError('terms', undefined)">
                {{ __('message.i_agree_to') }}
                <a :href="termsUrl" target="_blank" class="text-decoration-none">{{ __('message.terms_and_conditions') }}</a>
            </ClientCheckbox>

            <button type="submit"
                    class="btn btn-dark btn-modern w-100 text-uppercase rounded-0 font-weight-bold text-3 py-3 mt-3"
                    :disabled="saving">
                <i v-if="saving" class="fas fa-circle-notch fa-spin me-1"></i>{{ __('message.register') }}
            </button>
        </form>
    </AuthLayout>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { errorHandler } from '@/helpers/responseHandler.js'
import { socialCompleteSchema } from '@/validations/client/authSchemas.js'
import { scrollToFirstError } from '@/helpers/formUtils.js'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import AuthLayout from './partials/AuthLayout.vue'

const COMPONENT = 'client-page'
const baseUrl = useBaseUrl()
const { errors, setErrors, setFieldError } = useForm()

const form = reactive({
    first_name: '', last_name: '', email: '', company: '', address: '',
    country: null, mobile: '', mobile_code: '', mobile_country_iso: '', terms: false,
})
const provider = ref('')
const emailLocked = ref(false)
const termsEnabled = ref(false)
const termsUrl = ref('#')
const saving = ref(false)

onMounted(async () => {
    try {
        const { data } = (await http.get('/auth/social-complete-config')).data
        if (data.redirect) return globalThis.location.assign(data.redirect)

        provider.value = data.provider.charAt(0).toUpperCase() + data.provider.slice(1)
        form.first_name = data.first_name
        form.last_name = data.last_name
        form.email = data.email ?? ''
        emailLocked.value = !!data.email
        termsEnabled.value = !!data.terms
        termsUrl.value = data.terms_url ?? '#'
        if (data.location?.country) prefillCountry(data.location.iso_code, data.location.country)
    } catch (e) {
        errorHandler(e, COMPONENT)
    }
})

// Same geo-detected default the register form uses.
async function prefillCountry(iso, name) {
    try {
        const res = await http.get('/dependency/countries', { params: { 'search-query': name, page: 1, paginate: 1 } })
        const match = (res.data?.data?.countries ?? []).find(c => c.code === String(iso).toUpperCase())
        if (match) form.country = match
    } catch { /* best-effort */ }
}

function onCountryChange(value) {
    setFieldError('country', undefined)
    form.country = value
}

function onMobileCountryChange({ iso, dialCode }) {
    form.mobile_country_iso = iso
    form.mobile_code = dialCode
}

function onMobileInput(value) {
    form.mobile = String(value).replace(/[^\d]/g, '')
    setFieldError('mobile', undefined)
}

async function submit() {
    const errs = {}
    try {
        socialCompleteSchema.validateSync(form, { abortEarly: false })
    } catch (err) {
        err.inner?.forEach(e => { if (e.path && !errs[e.path]) errs[e.path] = e.message })
    }
    if (termsEnabled.value && !form.terms) {
        errs.terms = __('message.login_validation.terms_conditions_required')
    }
    if (Object.keys(errs).length) {
        setErrors(errs)
        await scrollToFirstError()
        return
    }

    saving.value = true
    try {
        const res = await http.post('/auth/social-complete', {
            ...form,
            country: form.country?.code ?? '',
            terms: termsEnabled.value ? form.terms : undefined,
        })
        globalThis.location.href = res.data.data.redirect
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        saving.value = false
    }
}
</script>
