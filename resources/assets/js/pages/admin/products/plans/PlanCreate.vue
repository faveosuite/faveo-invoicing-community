<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.create_product_plan') }}</h4>
                <!-- On sale or not, visible at the top of the page -->
                <div class="card-tools d-flex align-items-center gap-2">
                    <span id="plan-status-label" class="fw-bold">{{ form.status ? __('message.active') : __('message.inactive') }}</span>
                    <ToolTip :message="__('message.plan_status_help')" size="small" />
                    <Switch name="status" :value="!!form.status" labelledby="plan-status-label" :onChange="(on) => (form.status = on ? 1 : 0)" />
                </div>
            </div>

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

                <!-- Row 3: Price Description -->
                <div class="row">
                    <div class="col-md-12">
                        <TextField name="price_description" :label="__('message.price_description')" type="textarea" :rows="3" :value="form.price_description" :onChange="onChange" />
                    </div>
                </div>

                <!-- Pricing Table -->
                <div class="mt-3">
                    <span class="d-inline-block form-label fw-bold">{{ __('message.pricing') }}</span>
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
                                    <input type="number" class="form-control" v-model="row.offer_price" min="0" max="100" step="0.01" @keydown="(e) => blockInvalidKey(e, true)" @input="pricingError = ''" />
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
                <action-button action="save" :loading="saving" @click="submit" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { planSchema } from '@/validations/admin/planValidations'
import { validateForm, blockInvalidKey } from '@/helpers/formUtils.js'
import DynamicSelect from '@/components/Reusable/FormField/DynamicSelect.vue'
import TreeSelect from '@/components/Reusable/FormField/TreeSelect.vue'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import Switch from '@/components/Reusable/FormField/Switch.vue'
import ToolTip from '@/components/Reusable/Tooltip.vue'

const COMPONENT = 'plans-create'
const baseUrl = useBaseUrl()
const router = useRouter()

const { errors, setErrors, setFieldError } = useForm()

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
    price_description: '',
    prices: [{ currency: '', add_price: '', offer_price: '', renew_price: '' }],
})

function onChange(val, name) {
    setFieldError(name, undefined)
    if (name === 'product') {
        form.productObj = val
        form.product = val ?? null
    } else {
        form[name] = val
    }
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
        const [pRes, cRes] = await Promise.all([
            http.get(`/dependency/periods`),
            http.get(`/dependency/currencies`),
        ])
        periods.value = pRes.data?.data?.periods ?? []
        currencies.value = cRes.data?.data?.currencies ?? []
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    }
})

async function submit() {
    const schemaValid = await validateForm(planSchema, form, setErrors)

    const invalidRow = form.prices.find(p => !p.currency || p.add_price === '' || p.renew_price === '')
    const currencyValues = form.prices.map(p => p.currency).filter(Boolean)
    const hasDuplicateCurrency = new Set(currencyValues).size !== currencyValues.length
    const hasNegative = form.prices.some(p =>
        Number(p.add_price) < 0 || Number(p.renew_price) < 0 || (p.offer_price !== '' && Number(p.offer_price) < 0))
    const offerOver100 = form.prices.some(p => p.offer_price !== '' && p.offer_price !== null && Number(p.offer_price) > 100)

    if (invalidRow) {
        pricingError.value = __('message.pricing_row_required') || 'Each pricing row requires a currency, price, and renewal price.'
    } else if (hasDuplicateCurrency) {
        pricingError.value = __('validation.plan_request.currency_duplicate')
    } else if (hasNegative) {
        pricingError.value = __('validation.plan_request.non_negative')
    } else if (offerOver100) {
        pricingError.value = __('validation.plan_request.offer_price')
    } else {
        pricingError.value = ''
    }

    if (!schemaValid || invalidRow || hasDuplicateCurrency || hasNegative || offerOver100) return

    saving.value = true
    try {
        const payload = {
            name:              form.name,
            product:           form.product,
            days:              form.days || null,
            status:            form.status,
            price_description: form.price_description || null,
            currency:          form.prices.map(p => p.currency),
            add_price:         form.prices.map(p => p.add_price),
            renew_price:       form.prices.map(p => p.renew_price),
            offer_price:       form.prices.map(p => p.offer_price === '' ? null : p.offer_price),
        }
        const res = await http.post(`/plans`, payload)
        successHandler(res, COMPONENT)
        const id = res.data?.data?.id
        setTimeout(() => router.push(id ? `/products/plans/${id}/edit` : '/products/plans'), 1500)
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
