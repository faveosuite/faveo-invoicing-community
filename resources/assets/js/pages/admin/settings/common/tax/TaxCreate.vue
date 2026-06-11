<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.create') }} {{ __('message.tax') }}</h4>
            </div>

            <inline-loader v-if="loading" />

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <TextField
                                name="name"
                                :label="__('message.tax_name')"
                                :hint="__('message.tt_tax_name')"
                                :required="true"
                                :value="form.name"
                                :onChange="(val) => { form.name = val; setFieldError('name', undefined) }"
                                placehold="VAT, GST, Sales Tax…"
                                :error="errors.name"
                            />
                        </div>
                        <div class="col-md-4">
                            <TextField
                                name="rate"
                                :label="`${__('message.rate')} (%)`"
                                :hint="__('message.tt_rate')"
                                :required="true"
                                type="number"
                                :value="form.rate"
                                :onChange="(val) => { form.rate = val; setFieldError('rate', undefined) }"
                                placehold="0.00"
                                :error="errors.rate"
                            />
                        </div>
                        <div class="col-md-4">
                            <SelectField
                                name="tax_class"
                                :label="__('message.tax_class')"
                                :tooltip="__('message.tt_tax_class')"
                                :elements="classOptions"
                                :value="classOptions.find(c => c.id === form.tax_class) ?? classOptions[0]"
                                :onChange="(val) => form.tax_class = val?.id ?? ''"
                                :clearable="false"
                                :searchable="false"
                            />
                        </div>
                        <div class="col-md-4">
                            <SelectField
                                name="country"
                                :label="__('message.country')"
                                :tooltip="__('message.tt_country')"
                                :elements="[{ id: '', name: __('message.all_countries') }, ...countries]"
                                :value="countries.find(c => c.id === form.country) ?? { id: '', name: __('message.all_countries') }"
                                :onChange="onCountrySelect"
                                :clearable="false"
                                :searchable="true"
                            />
                        </div>
                        <div class="col-md-4">
                            <SelectField
                                name="state"
                                :label="__('message.state')"
                                :tooltip="__('message.tt_state')"
                                :elements="[{ id: '', name: __('message.all_states') }, ...states]"
                                :value="states.find(s => s.id === form.state) ?? { id: '', name: __('message.all_states') }"
                                :onChange="(val) => form.state = val?.id ?? ''"
                                :clearable="false"
                                :searchable="true"
                            />
                        </div>
                        <div class="col-md-4">
                            <TextField
                                name="priority"
                                :label="__('message.priority')"
                                :hint="__('message.tt_priority')"
                                type="number"
                                :value="form.priority"
                                :onChange="(val) => form.priority = val"
                                placehold="1"
                            />
                        </div>
                        <div class="col-md-4">
                            <SelectField
                                name="compound"
                                :label="__('message.compound')"
                                :tooltip="__('message.tt_compound')"
                                :elements="yesNo"
                                :value="yesNo.find(o => o.id === form.compound) ?? yesNo[1]"
                                :onChange="(val) => form.compound = val?.id ?? 0"
                                :clearable="false"
                                :searchable="false"
                            />
                        </div>
                        <div class="col-md-4">
                            <SelectField
                                name="active"
                                :label="__('message.status')"
                                :tooltip="__('message.tt_status')"
                                :elements="activeOptions"
                                :value="activeOptions.find(o => o.id === form.active) ?? activeOptions[0]"
                                :onChange="(val) => form.active = val?.id ?? 1"
                                :clearable="false"
                                :searchable="false"
                            />
                        </div>
                        <div class="col-md-6">
                            <TextField
                                name="postcode"
                                :label="__('message.postcode')"
                                :hint="__('message.tt_postcode')"
                                :value="form.postcode"
                                :onChange="(val) => form.postcode = val"
                                placehold="e.g. 600001, 12*, 12000...12999"
                            />
                        </div>
                        <div class="col-md-6">
                            <TextField
                                name="city"
                                :label="__('message.city')"
                                :hint="__('message.tt_city')"
                                :value="form.city"
                                :onChange="(val) => form.city = val"
                                placehold="comma-separated"
                            />
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
import { reactive, ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { buildTaxCreateSchema } from '@/validations/admin/taxValidations'
import TextField from '@/components/Reusable/FormField/TextField.vue'

const COMPONENT = 'tax-create'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const router  = useRouter()
const route   = useRoute()

const { errors, setErrors, setFieldError } = useForm()

const loading    = ref(true)
const saving     = ref(false)
const countries  = ref([])
const states     = ref([])
const classOptions = ref([{ id: '', name: 'Standard' }])

const yesNo         = [{ id: 1, name: __('message.yes') }, { id: 0, name: __('message.no') }]
const activeOptions = [{ id: 1, name: __('message.active') }, { id: 0, name: __('message.inactive') }]

const form = reactive({
    name: '', rate: '', tax_class: '', country: '', state: '',
    priority: 1, compound: 0, active: 1, postcode: '', city: '',
})

async function onCountrySelect(val) {
    form.country = val?.id ?? ''
    form.state   = ''
    states.value = []
    if (!form.country) return
    try {
        const res = await http.get(`${baseUrl}/get-state/${form.country}`)
        states.value = (res.data?.data?.states ?? []).map(s => ({ id: s.iso2, name: s.state_subdivision_name }))
    } catch (e) { /* ignore */ }
}

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/tax-options`)
        const d   = res.data?.data ?? {}
        countries.value = Object.entries(d.countries ?? {}).map(([id, name]) => ({ id, name }))
        classOptions.value = (d.classes ?? []).map(c => ({ id: c.slug, name: c.name }))
        if (!classOptions.value.length) classOptions.value = [{ id: '', name: 'Standard' }]

        // Pre-select the tax class from the active tab (?class=slug).
        const preset = route.query.class
        if (typeof preset === 'string' && classOptions.value.some(c => c.id === preset)) {
            form.tax_class = preset
        }
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function submit() {
    try {
        buildTaxCreateSchema().validateSync(form, { abortEarly: false })
    } catch (err) {
        const errMap = {}
        err.inner?.forEach(e => { if (e.path && !errMap[e.path]) errMap[e.path] = e.message })
        setErrors(errMap)
        return
    }

    saving.value = true
    try {
        const res = await http.post(`${baseUrl}/create/tax-class`, form)
        successHandler(res, COMPONENT)
        setTimeout(() => router.push('/settings/common/tax'), 2000)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
