<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.create') }} {{ __('message.tax_classes') }}</h4>
            </div>

            <div v-if="loadingCountries" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <SelectField
                                name="name"
                                :label="__('message.tax-type') + ' *'"
                                :elements="taxTypeOptions"
                                :value="taxTypeOptions.find(o => o.id === form.name) ?? null"
                                :onChange="onTaxTypeSelect"
                                :clearable="false"
                                :searchable="false"
                            />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">{{ __('message.tax_name') }} *</label>
                            <input
                                class="form-control"
                                v-model="form['tax-name']"
                                :readonly="form.name && form.name !== 'Others'"
                                placeholder="Tax name"
                            />
                        </div>
                        <template v-if="form.name === 'Others'">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">{{ __('message.rate') }} (%) *</label>
                                <input class="form-control" v-model="form.rate" type="number" step="0.01" placeholder="0.00" />
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
                    <button class="btn btn-primary" @click="submit" :disabled="saving">
                        <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                        <i v-else class="fas fa-save me-1"></i>
                        {{ __('message.save') }}
                    </button>
                    <RouterLink to="/settings/common/tax" class="btn btn-secondary ms-2">
                        {{ __('message.cancel') }}
                    </RouterLink>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'tax-create'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const router  = useRouter()

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
    saving.value = true
    try {
        const res = await http.post(`${baseUrl}/create/tax-class`, form)
        successHandler(res, COMPONENT)
        router.push('/settings/common/tax')
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
