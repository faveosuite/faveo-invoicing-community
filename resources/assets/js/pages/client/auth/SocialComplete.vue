<template>
    <div>
        <!-- Page title comes from AuthLayout (route titleKey); this explains why we're asking. -->
        <p class="text-3 mb-4">{{ __('message.social_complete_hint', { provider }) }}</p>

        <form @submit.prevent="submit" novalidate>
            <div class="row">
                <div class="col-md-6">
                    <ClientField name="first_name" type="text" :label="__('message.first_name')" required
                                 autocomplete="given-name"
                                 v-model="form.first_name" :error="errors.first_name"
                                 @update:modelValue="setFieldError('first_name', undefined)"/>
                </div>
                <div class="col-md-6">
                    <ClientField name="last_name" type="text" :label="__('message.last_name')" required
                                 autocomplete="family-name"
                                 v-model="form.last_name" :error="errors.last_name"
                                 @update:modelValue="setFieldError('last_name', undefined)"/>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <ClientField name="email" type="email" :label="__('message.email_address')" required
                                 :disabled="emailLocked" autocomplete="email" :spellcheck="false"
                                 v-model="form.email" :error="errors.email"
                                 @update:modelValue="setFieldError('email', undefined)"/>
                </div>
                <div class="col-md-6">
                    <PhoneField name="mobile" :label="__('message.mobile')" required
                                :value="form.mobile" :error="errors.mobile"
                                :onChange="onMobileInput"
                                @countryChange="onMobileCountryChange"/>
                </div>
            </div>

            <ClientCheckbox v-if="termsEnabled" v-model="form.terms" :error="errors.terms" class="mb-3"
                            @update:modelValue="setFieldError('terms', undefined)">
                {{ __('message.i_agree_to') }}
                <a :href="termsUrl" target="_blank" class="text-decoration-none">{{ __('message.terms_and_conditions') }}</a>
            </ClientCheckbox>

            <button type="submit"
                    class="btn btn-primary btn-modern w-100"
                    :disabled="saving">
                <i v-if="saving" class="fas fa-circle-notch fa-spin me-1" aria-hidden="true"></i>{{ __('message.register') }}
            </button>
        </form>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { errorHandler } from '@/helpers/responseHandler.js'
import { socialCompleteSchema } from '@/validations/client/authSchemas.js'
import { scrollToFirstError } from '@/helpers/formUtils.js'

const COMPONENT = 'client-page'
const { errors, setErrors, setFieldError } = useForm()

const form = reactive({
    first_name: '', last_name: '', email: '',
    mobile: '', mobile_code: '', mobile_country_iso: '', terms: false,
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
    } catch (e) {
        errorHandler(e, COMPONENT)
    }
})

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
