<template>
    <div>
        <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

        <form v-else @submit.prevent="submit" novalidate>
            <ClientField name="company" type="text" :label="__('message.company')" required
                         autocomplete="organization"
                         v-model="form.company" :error="errors.company"
                         @update:modelValue="setFieldError('company', undefined)"/>

            <ClientField name="address" type="textarea" :label="__('message.address')" required
                         autocomplete="street-address"
                         v-model="form.address" :error="errors.address"
                         @update:modelValue="setFieldError('address', undefined)"/>

            <div class="row">
                <div class="col-sm-6">
                    <ClientField name="town" type="text" :label="`${__('message.town')} (${__('message.optional')})`"
                                 autocomplete="address-level2"
                                 v-model="form.town" :error="errors.town"
                                 @update:modelValue="setFieldError('town', undefined)"/>
                </div>
                <div class="col-sm-6">
                    <ClientField name="zip" type="text" :label="`${__('message.zip')} (${__('message.optional')})`"
                                 autocomplete="postal-code"
                                 v-model="form.zip" :error="errors.zip"
                                 @update:modelValue="setFieldError('zip', undefined)"/>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <DynamicSelect name="country" :label="__('message.country')" required
                                   :elements="countries" optionLabel="name"
                                   :value="countries.find(c => c.code === form.country) ?? null"
                                   :onChange="onCountryChange" :error="errors.country"/>
                </div>
                <div class="col-sm-6">
                    <DynamicSelect name="state" :label="__('message.state')" :required="states.length > 0"
                                   :elements="states" optionLabel="name"
                                   :value="states.find(s => s.id === form.state) ?? null"
                                   :onChange="(v) => { form.state = v?.id ?? ''; setFieldError('state', undefined) }"
                                   :error="errors.state"/>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-medium" :disabled="saving">
                <i v-if="saving" class="fas fa-circle-notch fa-spin me-1" aria-hidden="true"></i>{{ __('message.save_and_continue') }}
            </button>
        </form>

        <p class="text-center small mt-4 mb-0">
            <a :href="`${baseUrl}/auth/logout`" class="text-color-primary fw-medium text-decoration-none">{{ __('message.logout') }}</a>
        </p>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { errorHandler } from '@/helpers/responseHandler.js'
import { scrollToFirstError } from '@/helpers/formUtils.js'
import { useBaseUrl } from '@/core/composables/useBaseUrl'

const COMPONENT = 'client-page'
const baseUrl = useBaseUrl()
const { errors, setErrors, setFieldError } = useForm()

const form = reactive({ company: '', address: '', town: '', zip: '', country: '', state: '' })
const countries = ref([])
const states = ref([])
const loading = ref(true)
const saving = ref(false)

onMounted(async () => {
    try {
        const [detailsRes, countriesRes] = await Promise.all([
            http.get('/account-setup/details'),
            http.get('/dependency/countries', { params: { limit: 'all' } }),
        ])
        countries.value = countriesRes.data?.data?.countries ?? []
        Object.assign(form, detailsRes.data?.data ?? {})
        await loadStates(form.country)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function loadStates(code) {
    states.value = []
    if (!code) return
    try {
        const res = await http.get('/dependency/states', { params: { country: code, limit: 'all' } })
        states.value = (res.data?.data?.states ?? []).map(st => ({ id: st.iso2, name: st.name }))
    } catch { /* state list is best-effort; the server still validates */ }
}

async function onCountryChange(v) {
    setFieldError('country', undefined)
    form.country = v?.code ?? ''
    form.state = ''
    await loadStates(form.country)
}

async function submit() {
    // Mirrors the server rules; the server has the final say.
    const errs = {}
    if (!form.company.trim()) errs.company = __('message.login_validation.company_required')
    if (!form.address.trim()) errs.address = __('message.login_validation.address_required')
    if (!form.country) errs.country = __('message.login_validation.country_required')
    if (states.value.length && !form.state) errs.state = __('validation.profile_form.state.required_if')
    if (Object.keys(errs).length) {
        setErrors(errs)
        await scrollToFirstError()
        return
    }

    saving.value = true
    try {
        const res = await http.post('/account-setup', form)
        // Full reload: the page shell's "needs setup" flag is server-rendered.
        globalThis.location.href = res.data?.data?.redirect ?? `${baseUrl}/`
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        saving.value = false
    }
}
</script>
