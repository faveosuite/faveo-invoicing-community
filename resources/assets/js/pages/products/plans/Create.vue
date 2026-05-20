<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.create_product_plan') }}</h4>
            </div>

            <div class="card-body">
                <!-- Row 1: Name / Product / Period / Status -->
                <div class="row">
                    <div class="col-md-3">
                        <TextField name="name" :label="__('message.name')" :required="true" :value="form.name" :onChange="onChange" />
                    </div>
                    <div class="col-md-3">
                        <DynamicSelect
                            name="product"
                            :label="__('message.product')"
                            :required="true"
                            :apiEndpoint="`${baseUrl}/dependency/products`"
                            dataKey="products"
                            :value="form.productObj"
                            :onChange="onChange"
                            :placeholder="__('message.choose')"
                        />
                    </div>
                    <div class="col-md-3">
                        <StaticSelect
                            name="days"
                            :label="__('message.period')"
                            :elements="periods"
                            :value="form.days"
                            :onChange="(val) => form.days = val"
                        />
                    </div>
                    <div class="col-md-3">
                        <StaticSelect
                            name="status"
                            :label="__('message.status')"
                            :required="true"
                            :elements="[{ id: 1, name: __('message.active') }, { id: 0, name: __('message.inactive') }]"
                            :value="form.status"
                            :onChange="(val) => form.status = val"
                            :hideEmptySelect="true"
                        />
                    </div>
                </div>

                <!-- Agents / Quantity -->
                <div class="row">
                    <div class="col-md-3">
                        <TextField name="no_of_agents" :label="__('message.agent')" type="number" :value="form.no_of_agents" :onChange="onChange" />
                    </div>
                    <div class="col-md-3">
                        <TextField name="product_quantity" :label="__('message.product_quantity')" type="number" :value="form.product_quantity" :onChange="onChange" />
                    </div>
                </div>

                <!-- Pricing Table -->
                <div class="mt-3">
                    <label class="form-label fw-bold">{{ __('message.pricing') }}</label>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ __('message.currency') }} <span class="text-danger">*</span></th>
                                <th>{{ __('message.price') }} <span class="text-danger">*</span></th>
                                <th>{{ __('message.offer_price') }} (%)</th>
                                <th>{{ __('message.renew-price') }} <span class="text-danger">*</span></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, idx) in form.prices" :key="idx">
                                <td>
                                    <select class="form-select form-select-sm" v-model="row.currency" @change="pricingError = ''">
                                        <option value="">{{ __('message.choose') }}</option>
                                        <option v-for="c in currencies" :key="c.id" :value="c.id">{{ c.name }}</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm" v-model="row.add_price" min="0" @input="pricingError = ''" />
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm" v-model="row.offer_price" min="0" max="100" step="0.01" />
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm" v-model="row.renew_price" min="0" @input="pricingError = ''" />
                                </td>
                                <td>
                                    <action-button action="delete" size="sm" icon-only type="button" :disabled="form.prices.length === 1" @click="removeRow(idx)" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="pricingError" class="text-danger small mb-2">{{ pricingError }}</div>
                    <action-button variant="secondary" size="sm" icon="fas fa-plus" :label="__('message.add_currency')" type="button" @click="addRow" />
                </div>
            </div>

            <div class="card-footer">
                <action-button action="save" :loading="saving" @click="submit" />
                <action-button action="cancel" to="/products/plans" class="ms-2" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { useFormValidation } from '@/composables/useFormValidation'
import StaticSelect from '@/components/Reusable/FormField/StaticSelect.vue'

const COMPONENT = 'plans-create'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const router = useRouter()

const { validate, clearFieldError, clearAllErrors } = useFormValidation()

const saving = ref(false)
const periods = ref([])
const currencies = ref([])
const pricingError = ref('')

const form = reactive({
    name: '',
    product: null,
    productObj: null,
    days: '',
    status: 1,
    no_of_agents: '',
    product_quantity: '',
    prices: [{ currency: '', add_price: '', offer_price: '', renew_price: '' }],
})

function onChange(val, name) {
    clearFieldError(name)
    if (name === 'product') {
        form.productObj = val
        form.product = val?.id ?? null
    } else {
        form[name] = val
    }
}

function addRow() {
    form.prices.push({ currency: '', add_price: '', offer_price: '', renew_price: '' })
}

function removeRow(idx) {
    form.prices.splice(idx, 1)
}

onMounted(async () => {
    clearAllErrors()
    try {
        const [pRes, cRes] = await Promise.all([
            http.get(`${baseUrl}/dependency/periods`),
            http.get(`${baseUrl}/dependency/currencies`),
        ])
        periods.value = pRes.data?.data?.periods ?? []
        currencies.value = cRes.data?.data?.currencies ?? []
    } catch (e) {
        errorHandler(e, COMPONENT)
    }
})

async function submit() {
    const isValid = validate({
        name:    [form.name,    { isRequired: __('validation.plan_request.name_required') }],
        product: [form.product, { isRequired: __('validation.plan_request.pro_req') }],
    })

    const invalidRow = form.prices.find(p => !p.currency || p.add_price === '' || p.renew_price === '')
    if (invalidRow) {
        pricingError.value = __('message.pricing_row_required') || 'Each pricing row requires a currency, price, and renewal price.'
    } else {
        pricingError.value = ''
    }

    if (!isValid || invalidRow) return

    saving.value = true
    try {
        const payload = {
            name:             form.name,
            product:          form.product,
            days:             form.days || null,
            status:           form.status,
            no_of_agents:     form.no_of_agents !== '' ? form.no_of_agents : null,
            product_quantity: form.product_quantity !== '' ? form.product_quantity : null,
            currency:         form.prices.map(p => p.currency),
            add_price:        form.prices.map(p => p.add_price),
            renew_price:      form.prices.map(p => p.renew_price),
            offer_price:      form.prices.map(p => p.offer_price !== '' ? p.offer_price : null),
        }
        const res = await http.put(`${baseUrl}/plans`, payload)
        successHandler(res, COMPONENT)
        setTimeout(() => router.push('/products/plans'), 2000)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
