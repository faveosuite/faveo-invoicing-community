<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">Edit Tax</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <TextField name="name" label="Tax Name *" :value="form.name" :onChange="onChange" />
                        </div>
                        <div class="col-md-4">
                            <SelectField
                                name="tax_classes_id"
                                label="Tax Class *"
                                :elements="taxClassOptions"
                                :value="taxClassOptions.find(o => o.id === form.tax_classes_id) ?? null"
                                :onChange="onClassSelect"
                                :clearable="false"
                                :searchable="false"
                            />
                        </div>
                        <template v-if="form.tax_classes_id === 'Others'">
                            <div class="col-md-4">
                                <TextField name="rate" label="Rate *" :value="form.rate" :onChange="onChange" />
                            </div>
                            <div class="col-md-4">
                                <SelectField
                                    name="country"
                                    label="Country"
                                    :elements="countries"
                                    :value="countries.find(c => c.id === form.country) ?? null"
                                    :onChange="onCountrySelect"
                                    :clearable="false"
                                    :searchable="true"
                                />
                            </div>
                            <div class="col-md-4">
                                <SelectField
                                    name="state"
                                    label="State"
                                    :elements="[{ id: '', name: '— Any —' }, ...states]"
                                    :value="states.find(s => s.id === form.state) ?? { id: '', name: '— Any —' }"
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
                        Update
                    </button>
                    <RouterLink to="/settings/common/tax" class="btn btn-secondary ms-2">Cancel</RouterLink>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'tax-edit'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const route = useRoute()
const id = route.params.id

const loading  = ref(true)
const saving   = ref(false)
const countries = ref([])
const states    = ref([])

const taxClassOptions = [
    { id: 'CGST',   name: 'CGST'   },
    { id: 'SGST',   name: 'SGST'   },
    { id: 'IGST',   name: 'IGST'   },
    { id: 'UTGST',  name: 'UTGST'  },
    { id: 'Others', name: 'Others' },
]

const form = reactive({
    name: '', tax_classes_id: '', rate: '', country: 'IN', state: '',
})

function onChange(val, name) { form[name] = val }

function onClassSelect(val) {
    form.tax_classes_id = val?.id ?? ''
    form.rate    = ''
    form.country = 'IN'
    form.state   = ''
    if (form.tax_classes_id === 'Others') loadStates()
}

async function onCountrySelect(val) {
    form.country = val?.id ?? ''
    form.state   = ''
    loadStates()
}

async function loadStates() {
    form.state  = ''
    states.value = []
    if (!form.country) return
    try {
        const res = await http.get(`${baseUrl}/get-state/${form.country}`)
        states.value = (res.data?.data?.states ?? []).map(s => ({ id: s.iso2, name: s.state_subdivision_name }))
    } catch (e) { /* ignore */ }
}

onMounted(async () => {
    try {
        const [editRes, optRes] = await Promise.all([
            http.get(`${baseUrl}/tax/edit/${id}`),
            http.get(`${baseUrl}/tax-options`),
        ])
        const d = editRes.data?.data ?? {}
        form.name           = d.tax?.name ?? ''
        form.tax_classes_id = d.tax_class_name ?? ''
        form.rate           = d.tax?.rate ?? ''
        form.country        = d.tax?.country ?? 'IN'
        form.state          = d.tax?.state ?? ''

        countries.value = Object.entries(optRes.data?.data?.countries ?? {}).map(([cid, name]) => ({ id: cid, name }))

        if (form.tax_classes_id === 'Others' && form.country) {
            states.value = Object.entries(d.states ?? {}).map(([iso2, name]) => ({ id: iso2, name }))
        }
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { loading.value = false }
})

async function submit() {
    saving.value = true
    try {
        const res = await http.put(`${baseUrl}/tax/${id}`, {
            name:           form.name,
            tax_classes_id: form.tax_classes_id,
            rate:           form.rate,
            country:        form.country,
            state:          form.state,
        })
        successHandler(res, COMPONENT)
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { saving.value = false }
}
</script>
