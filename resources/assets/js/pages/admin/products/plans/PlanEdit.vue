<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.edit_plan') }}</h4>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">
                    <!-- Row 1: Name / Product / Period -->
                    <div class="row">
                        <div class="col-md-4">
                            <TextField name="name" :label="__('message.name')" :required="true" :value="form.name" :onChange="onChange" :error="errors.name" />
                        </div>
                        <div class="col-md-4">
                            <TreeSelect
                                name="product"
                                :label="__('message.product')"
                                :required="true"
                                :apiEndpoint="`${baseUrl}/dependency/products`"
                                dataKey="products"
                                :value="form.productObj"
                                :onChange="onChange"
                                :placeholder="__('message.choose')"
                                :error="errors.product"
                            />
                        </div>
                        <div class="col-md-4">
                            <DynamicSelect
                                name="days"
                                :label="__('message.period')"
                                :elements="periods"
                                :value="periods.find(p => p.id === form.days) ?? null"
                                :onChange="(val) => form.days = val?.id ?? ''"
                                :clearable="false"
                                :searchable="false"
                            />
                        </div>
                    </div>

                    <!-- Row 2: Agents / Quantity / Status -->
                    <div class="row">
                        <div class="col-md-4" v-if="productShowAgent !== false">
                            <TextField name="no_of_agents" :label="__('message.agent')" type="number" :required="productShowAgent === true" :value="form.no_of_agents" :onChange="onChange" :error="errors.no_of_agents" @keydown="blockInvalidKey" />
                        </div>
                        <div class="col-md-4" v-if="productShowAgent !== true">
                            <TextField name="product_quantity" :label="__('message.product_quantity')" type="number" :required="productShowAgent === false" :value="form.product_quantity" :onChange="onChange" :error="errors.product_quantity" @keydown="blockInvalidKey" />
                        </div>
                        <div class="col-md-4">
                            <DynamicSelect
                                name="status"
                                :label="__('message.status')"
                                :required="true"
                                :elements="statusOptions"
                                :value="statusOptions.find(o => o.id === form.status) ?? null"
                                :onChange="(val) => form.status = val?.id ?? 1"
                                :clearable="false"
                                :searchable="false"
                            />
                        </div>
                    </div>

                    <!-- Row 3: Price Description -->
                    <div class="row">
                        <div class="col-md-12">
                            <TextField name="price_description" :label="__('message.price_description')" type="textarea" :rows="3" :value="form.price_description" :onChange="onChange" />
                        </div>
                    </div>

                    <!-- Pricing Table -->
                    <div class="mt-3">
                        <label class="form-label fw-bold">{{ __('message.pricing') }}</label>
                        <table class="table table-bordered pricing-table">
                            <colgroup>
                                <col style="width: 22%">
                                <col style="width: 22%">
                                <col style="width: 22%">
                                <col style="width: 22%">
                                <col style="width: 12%">
                            </colgroup>
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
                                        <DynamicSelect
                                            :name="'currency_' + idx"
                                            :elements="availableCurrencies(idx)"
                                            :value="currencies.find(c => c.id === row.currency) ?? null"
                                            :placeholder="__('message.choose')"
                                            :onChange="(val) => { row.currency = val?.id ?? ''; pricingError = '' }"
                                            :clearable="false"
                                        />
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" v-model="row.add_price" min="0" @keydown="blockInvalidKey" @input="pricingError = ''" />
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" v-model="row.offer_price" min="0" max="100" step="0.01" @keydown="(e) => blockInvalidKey(e, true)" />
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" v-model="row.renew_price" min="0" @keydown="blockInvalidKey" @input="pricingError = ''" />
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-light table_btn" v-tooltip="__('message.delete')" :disabled="form.prices.length === 1" @click="removeRow(idx)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-if="pricingError" class="text-danger small mb-2">{{ pricingError }}</div>
                        <action-button variant="secondary" size="sm" icon="fas fa-plus" :label="__('message.add_currency')" type="button" @click="addRow" />
                    </div>
                </div>

                <div class="card-footer">
                    <action-button action="update" :loading="saving" @click="submit" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { planSchema } from '@/validations/admin/planValidations'
import { validateForm } from '@/helpers/formUtils.js'
import DynamicSelect from '@/components/Reusable/FormField/DynamicSelect.vue'
import TreeSelect from '@/components/Reusable/FormField/TreeSelect.vue'
import { useBaseUrl } from '@/core/composables/useBaseUrl'

const COMPONENT = 'plans-edit'
const baseUrl = useBaseUrl()
const route = useRoute()
const router = useRouter()

const { errors, setErrors, setFieldError } = useForm()

const loading = ref(true)
const saving = ref(false)
const periods = ref([])
const currencies = ref([])
const pricingError = ref('')
// true = product is agent-based (show no_of_agents), false = quantity-based
// (show product_quantity), null = not known yet (no product picked / still loading).
const productShowAgent = ref(null)

const statusOptions = [
    { id: 1, name: __('message.active') },
    { id: 0, name: __('message.inactive') },
]

const form = reactive({
    name: '',
    product: null,
    productObj: null,
    days: '',
    status: 1,
    no_of_agents: '',
    product_quantity: '',
    price_description: '',
    prices: [{ currency: '', add_price: '', offer_price: '', renew_price: '' }],
})

function onChange(val, name) {
    setFieldError(name, undefined)
    if (name === 'product') {
        form.productObj = val
        form.product = val ?? null
        loadProductMode(form.product)
    } else {
        form[name] = val
    }
}

// Whether a product takes agents or a plain quantity is set on the product
// itself (Cart & Display > "Show cart page as") - look it up so the plan
// form only asks for whichever one actually applies.
async function loadProductMode(productId) {
    if (!productId) { productShowAgent.value = null; return }
    try {
        const res = await http.get(`/product/${productId}`)
        const p = res.data?.data?.product ?? res.data?.data ?? res.data
        productShowAgent.value = Boolean(p.show_agent)
    } catch {
        productShowAgent.value = null
    }
}

// type="number" still lets you type +, -, e/E (scientific notation) and,
// for the integer-only fields, a decimal point - none of which are valid
// here (PlanRequest requires plain non-negative integers; offer_price is
// the one exception that allows a decimal).
function blockInvalidKey(e, allowDecimal = false) {
    const blocked = allowDecimal ? ['e', 'E', '+', '-'] : ['e', 'E', '+', '-', '.']
    if (blocked.includes(e.key)) e.preventDefault()
}

function addRow() {
    form.prices.push({ currency: '', add_price: '', offer_price: '', renew_price: '' })
}

// A currency already picked in another row is dropped from this row's list,
// so the same currency can't be selected twice.
function availableCurrencies(idx) {
    const usedElsewhere = new Set(form.prices.filter((_, i) => i !== idx).map(p => p.currency).filter(Boolean))
    return currencies.value.filter(c => !usedElsewhere.has(c.id))
}

function removeRow(idx) {
    form.prices.splice(idx, 1)
}

onMounted(async () => {
    try {
        const [pRes, cRes, planRes] = await Promise.all([
            http.get(`/dependency/periods`),
            http.get(`/dependency/currencies`),
            http.get(`/plan/${route.params.id}`),
        ])
        periods.value = pRes.data?.data?.periods ?? []
        currencies.value = cRes.data?.data?.currencies ?? []

        const plan = planRes.data?.data ?? planRes.data
        form.name = plan.name ?? ''
        form.product = plan.product ?? null
        form.days = plan.days ?? ''
        form.status = plan.status ?? 1
        form.no_of_agents = plan.no_of_agents ?? ''
        form.product_quantity = plan.product_quantity ?? ''
        loadProductMode(form.product)

        const pr = plan.product_relation ?? plan.productRelation
        if (pr) {
            form.productObj = { id: plan.product, name: pr.name }
        }

        const planPrices = plan.plan_price ?? plan.planPrice
        if (planPrices?.length) {
            form.price_description = planPrices[0]?.price_description ?? ''
            form.prices = planPrices.map(p => ({
                currency:    p.currency ?? '',
                add_price:   p.add_price ?? '',
                offer_price: p.offer_price ?? '',
                renew_price: p.renew_price ?? '',
            }))
        }
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        loading.value = false
    }
})

async function submit() {
    const schemaValid = await validateForm(planSchema, form, setErrors)

    const invalidRow = form.prices.find(p => !p.currency || p.add_price === '' || p.renew_price === '')
    const currencyValues = form.prices.map(p => p.currency).filter(Boolean)
    const hasDuplicateCurrency = new Set(currencyValues).size !== currencyValues.length
    const hasNegative = form.prices.some(p =>
        Number(p.add_price) < 0 || Number(p.renew_price) < 0 || (p.offer_price !== '' && Number(p.offer_price) < 0))

    if (invalidRow) {
        pricingError.value = 'Each pricing row requires a currency, price, and renewal price.'
    } else if (hasDuplicateCurrency) {
        pricingError.value = __('validation.plan_request.currency_duplicate')
    } else if (hasNegative) {
        pricingError.value = __('validation.plan_request.non_negative')
    } else {
        pricingError.value = ''
    }

    if (!schemaValid || invalidRow || hasDuplicateCurrency || hasNegative) return

    saving.value = true
    try {
        const payload = {
            name:              form.name,
            product:           form.product,
            days:              form.days || null,
            status:            form.status,
            no_of_agents:      form.no_of_agents === '' ? null : form.no_of_agents,
            product_quantity:  form.product_quantity === '' ? null : form.product_quantity,
            price_description: form.price_description || null,
            currency:          form.prices.map(p => p.currency),
            add_price:         form.prices.map(p => p.add_price),
            renew_price:       form.prices.map(p => p.renew_price),
            offer_price:       form.prices.map(p => p.offer_price === '' ? null : p.offer_price),
        }
        const res = await http.patch(`/plan/${route.params.id}`, payload)
        successHandler(res, COMPONENT)
        setTimeout(() => router.push('/products/plans'), 2000)
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        saving.value = false
    }
}
</script>

<style scoped>
/* colgroup widths are ignored unless the table commits to a fixed layout,
   otherwise columns keep auto-sizing to content. */
.pricing-table { table-layout: fixed; }
</style>
