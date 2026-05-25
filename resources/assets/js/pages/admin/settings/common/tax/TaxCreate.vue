<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.create') }} {{ __('message.tax_classes') }}</h4>
            </div>

            <inline-loader v-if="loadingCountries" />

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <SelectField
                                name="name"
                                :label="__('message.tax-type')"
                                :required="true"
                                :elements="taxTypeOptions"
                                :value="taxTypeOptions.find(o => o.id === form.name) ?? null"
                                :onChange="onTaxTypeSelect"
                                :clearable="false"
                                :searchable="false"
                                :error="errors.name"
                            />
                        </div>
                        <div class="col-md-4">
                            <TextField
                                name="tax-name"
                                :label="__('message.tax_name')"
                                :required="true"
                                :value="form['tax-name']"
                                :onChange="(val) => { form['tax-name'] = val; setFieldError('tax-name', undefined) }"
                                :disabled="!!(form.name && form.name !== 'Others')"
                                placehold="Tax name"
                                :error="errors['tax-name']"
                            />
                        </div>
                        <template v-if="form.name === 'Others'">
                            <div class="col-md-4">
                                <TextField
                                    name="rate"
                                    :label="`${__('message.rate')} (%)`"
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
                                    name="country"
                                    :label="__('message.country')"
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
                                    :elements="[{ id: '', name: __('message.all_states') }, ...states]"
                                    :value="states.find(s => s.id === form.state) ?? { id: '', name: __('message.all_states') }"
                                    :onChange="(val) => form.state = val?.id ?? ''"
                                    :clearable="false"
                                    :searchable="true"
                                />
                            </div>
                        </template>
                    </div>
                </div>

                <div class="card-footer">
                    <action-button action="save" :loading="saving" @click="submit" />
                    <action-button action="cancel" to="/settings/common/tax" class="ms-2" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { buildTaxCreateSchema } from '@/validations/admin/taxValidations'
import TextField from '@/components/Reusable/FormField/TextField.vue'

const COMPONENT = 'tax-create'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const router  = useRouter()

const { errors, setErrors, setFieldError } = useForm()

const loadingCountries = ref(true)
const saving           = ref(false)
const countries        = ref([])
const states           = ref([])

const taxTypeOptions = [
    { id: 'Others',              name: __('message.others')       },
    { id: 'Intra State GST',     name: 'Intra State GST'          },
    { id: 'Inter State GST',     name: 'Inter State GST'          },
    { id: 'Union Territory GST', name: 'Union Territory GST'      },
]

const form = reactive({
    name: '', 'tax-name': '', rate: '', country: '', state: '',
})

function onTaxTypeSelect(val) {
    setFieldError('name', undefined)
    form.name = val?.id ?? ''
    form.rate    = ''
    form.country = ''
    form.state   = ''
    states.value = []
    const autoNames = {
        'Intra State GST':     'CGST+SGST',
        'Inter State GST':     'IGST',
        'Union Territory GST': 'CGST+UTGST',
    }
    form['tax-name'] = autoNames[form.name] ?? ''
}

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
        const res   = await http.get(`${baseUrl}/tax-options`)
        countries.value = Object.entries(res.data?.data?.countries ?? {}).map(([id, name]) => ({ id, name }))
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loadingCountries.value = false
    }
})

async function submit() {
    try {
        buildTaxCreateSchema(form).validateSync(form, { abortEarly: false })
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
