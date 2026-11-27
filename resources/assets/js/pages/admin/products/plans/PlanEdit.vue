<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.edit_plan') }}</h4>
                <!-- On sale or not, visible at the top of the page -->
                <div class="card-tools d-flex align-items-center gap-2">
                    <span id="plan-status-label" class="fw-bold">{{ form.status ? __('message.active') : __('message.inactive') }}</span>
                    <ToolTip :message="__('message.plan_status_help')" size="small" />
                    <Switch name="status" :value="!!form.status" labelledby="plan-status-label" :onChange="(on) => (form.status = on ? 1 : 0)" />
                </div>
            </div>
            <div class="card-body pb-0">
        <div v-if="loading" class="text-center py-4"><loader /></div>

        <div v-else class="row">
            <!-- Left: what the plan is -->
            <div class="col-lg-8">
                <div class="card card-light">
                    <div class="card-header"><h4 class="card-title">{{ __('message.plan_details') }}</h4></div>
                    <div class="card-body pb-0">
                        <div class="row">
                            <div class="col-md-6">
                                <TextField name="name" :label="__('message.name')" :required="true" :value="form.name" :onChange="onChange" :error="errors.name" />
                            </div>
                            <div class="col-md-6">
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
                            <div class="col-md-6">
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
                            <div class="col-md-6">
                                <TextField name="price_description" :label="__('message.price_description')" :value="form.price_description" :onChange="onChange" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-light">
                    <div class="card-header"><h4 class="card-title">{{ __('message.price') }}</h4></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <div class="price-table">
                                <v-client-table :data="form.prices" :columns="priceColumns" :options="priceTableOptions">
                                    <template #currency="{ row }">
                                        <DynamicSelect
                                            :name="'currency_' + row._key"
                                            :elements="availableCurrencies(row._key)"
                                            :value="currencies.find(c => c.id === row.currency) ?? null"
                                            :placeholder="__('message.choose')"
                                            :ariaLabel="__('message.currency')"
                                            :onChange="(val) => setPrice(row, 'currency', val?.id ?? '')"
                                            :clearable="false"
                                            :appendToBody="true"
                                        />
                                    </template>
                                    <template #add_price="{ row }">
                                        <TextField :name="'add_price_' + row._key" type="number" inputmode="decimal" autocomplete="off" :ariaLabel="`${__('message.price')} ${row.currency}`" classname="mb-0" :value="row.add_price" :onChange="(v) => setPrice(row, 'add_price', v)" @keydown="blockInvalidKey" />
                                    </template>
                                    <template #renew_price="{ row }">
                                        <TextField :name="'renew_price_' + row._key" type="number" inputmode="decimal" autocomplete="off" :ariaLabel="`${__('message.renew-price')} ${row.currency}`" classname="mb-0" :value="row.renew_price" :onChange="(v) => setPrice(row, 'renew_price', v)" @keydown="blockInvalidKey" />
                                    </template>
                                    <template #offer_price="{ row }">
                                        <TextField :name="'offer_price_' + row._key" type="number" inputmode="decimal" autocomplete="off" :ariaLabel="`${__('message.plan_offer_percent')} ${row.currency}`" classname="mb-0" :value="row.offer_price" :placeholder="__('message.plan_no_offer')" :onChange="(v) => setPrice(row, 'offer_price', v)" @keydown="(e) => blockInvalidKey(e, true)" />
                                    </template>
                                    <template #action="{ row }">
                                        <action-button action="delete" variant="light" :iconOnly="true" class="table_btn" v-tooltip="__('message.delete')"
                                                       :disabled="form.prices.length === 1" @click="removeRow(row._key)" />
                                    </template>
                                </v-client-table>
                            </div>
                        </div>
                        <div v-if="pricingError" class="text-danger small mb-2">{{ pricingError }}</div>
                        <action-button variant="secondary" size="sm" icon="fas fa-plus" :label="__('message.add_currency')" type="button" @click="addRow" />
                    </div>
                </div>

                <!-- Features card -->
                <PlanEntitlements ref="featuresRef" :planId="route.params.id" :currencies="planCurrencies" :period="periodName" />
            </div>

            <!-- Right: status, save and what the customer gets -->
            <div class="col-lg-4">
                <div class="card card-light">
                    <div class="card-header"><h4 class="card-title">{{ __('message.plan_summary') }}</h4></div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between gap-2">
                            <span>{{ __('message.status') }}</span>
                            <span class="badge" :class="form.status ? 'bg-success' : 'bg-secondary'">{{ form.status ? __('message.active') : __('message.inactive') }}</span>
                        </li>
                        <li v-for="p in summaryPrices" :key="p.currency" class="list-group-item">
                            <div class="fw-bold tabular-nums">{{ p.currency }} {{ p.add_price }} <span class="fw-normal text-muted">/ {{ periodName || '—' }}</span></div>
                            <div class="small text-muted">
                                {{ __('message.plan_summary_renews', { price: `${p.currency} ${p.renew_price}` }) }}<template v-if="Number(p.offer_price) > 0"> · {{ __('message.plan_summary_offer', { percent: p.offer_price }) }}</template>
                            </div>
                        </li>
                        <li v-for="line in featuresRef?.summary ?? []" :key="line.label" class="list-group-item d-flex justify-content-between gap-2">
                            <span>{{ line.label }}</span>
                            <span class="text-end">{{ line.value }}<div v-if="line.extra" class="small text-muted">{{ line.extra }}</div></span>
                        </li>
                        <li class="list-group-item small text-muted d-flex justify-content-between gap-2">
                            {{ __('message.plan_summary_licenses', { count: featuresRef?.liveLicenses ?? 0 }) }}
                            <router-link v-if="form.product" :to="`/orders?product_id=${form.product}&plan_id=${route.params.id}`">{{ __('message.plan_view_orders') }}</router-link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

            </div>
            <div v-if="!loading" class="card-footer d-flex align-items-center gap-2">
                <action-button action="save" :label="__('message.plan_save')" :loading="saving" @click="submit" />
                <action-button variant="secondary" icon="fas fa-users" :label="`${__('message.entitlement_apply_existing')} (${featuresRef?.liveLicenses ?? 0})`"
                               @click="showApply = true" />
            </div>
        </div>

        <DeleteModal
            :showModal="showApply"
            :onClose="() => (showApply = false)"
            :deleteUrl="`/plan/${route.params.id}/entitlements/apply`"
            method="post"
            :componentName="COMPONENT"
            :title="__('message.entitlement_apply_existing')"
            :message="__('message.plan_apply_confirm', { count: featuresRef?.liveLicenses ?? 0 })"
            :btnLabel="__('message.entitlement_apply_button')"
            btnVariant="primary"
            btnIcon="fa-check"
        />
    </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRoute, onBeforeRouteLeave } from 'vue-router'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { planSchema } from '@/validations/admin/planValidations'
import { validateForm, blockInvalidKey } from '@/helpers/formUtils.js'
import DynamicSelect from '@/components/Reusable/FormField/DynamicSelect.vue'
import TreeSelect from '@/components/Reusable/FormField/TreeSelect.vue'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import PlanEntitlements from './PlanEntitlements.vue'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'
import Switch from '@/components/Reusable/FormField/Switch.vue'
import ToolTip from '@/components/Reusable/Tooltip.vue'

const COMPONENT = 'plans-edit'
const baseUrl = useBaseUrl()
const route = useRoute()

const { errors, setErrors, setFieldError } = useForm()

const loading = ref(true)
const saving = ref(false)
const periods = ref([])
const currencies = ref([])
const pricingError = ref('')
const featuresRef = ref(null)
const showApply = ref(false)

// Warn before leaving with unsaved edits: the plan's own fields, or its features (saved with it).
const savedForm = ref('')
const formSnapshot = () => JSON.stringify(form)
const isDirty = () => !loading.value && ((savedForm.value !== '' && formSnapshot() !== savedForm.value) || !!featuresRef.value?.dirty)
const warnOnUnload = e => { if (isDirty()) e.preventDefault() }
onBeforeRouteLeave(() => !isDirty() || confirm(__('message.unsaved_changes_confirm')))
window.addEventListener('beforeunload', warnOnUnload)
onBeforeUnmount(() => window.removeEventListener('beforeunload', warnOnUnload))

const form = reactive({
    name: '',
    product: null,
    productObj: null,
    days: '',
    status: 1,
    price_description: '',
    prices: [{ _key: 1, currency: '', add_price: '', offer_price: '', renew_price: '' }],
})

const planCurrencies = computed(() => [...new Set(form.prices.map(p => p.currency).filter(Boolean))])
const periodName = computed(() => periods.value.find(p => p.id === form.days)?.name ?? '')
const summaryPrices = computed(() => form.prices.filter(p => p.currency && p.add_price !== ''))

function onChange(val, name) {
    setFieldError(name, undefined)
    if (name === 'product') {
        form.productObj = val
        form.product = val ?? null
    } else {
        form[name] = val
    }
}

// Price rows are found by _key: the table hands its cells a copy of the row.
let nextKey = 1
const priceRow = row => form.prices.find(p => p._key === row._key)

function setPrice(row, field, value) {
    priceRow(row)[field] = value
    pricingError.value = ''
}

function addRow() {
    form.prices.push({ _key: ++nextKey, currency: '', add_price: '', offer_price: '', renew_price: '' })
}

// A currency already picked in another row is dropped from this row's list,
// so the same currency can't be selected twice.
function availableCurrencies(key) {
    const usedElsewhere = new Set(form.prices.filter(p => p._key !== key).map(p => p.currency).filter(Boolean))
    return currencies.value.filter(c => !usedElsewhere.has(c.id))
}

function removeRow(key) {
    form.prices.splice(form.prices.findIndex(p => p._key === key), 1)
}

const priceColumns = ['currency', 'add_price', 'renew_price', 'offer_price', 'action']
const priceTableOptions = {
    skin: 'table align-middle mb-2',
    columnsClasses: { currency: 'w-25', action: 'text-end' },
    headings: {
        currency:    __('message.currency') + ' *',
        add_price:   __('message.price') + ' *',
        renew_price: __('message.renew-price') + ' *',
        offer_price: __('message.plan_offer_percent'),
        action:      '',
    },
    sortable:      [],
    filterable:    false,
    pagination:    { show: false },
    perPage:       1000,
    perPageValues: [],
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
        // Older plans store days no period option has (e.g. One Time at 365) — list it so the box isn't empty.
        if (form.days && !periods.value.some(p => p.id === form.days)) {
            periods.value.push({ id: form.days, name: plan.periods?.[0]?.name ?? `${form.days} days` })
        }
        form.status = plan.status ?? 1

        const pr = plan.product_relation ?? plan.productRelation
        if (pr) {
            form.productObj = { id: plan.product, name: pr.name }
        }

        const planPrices = plan.plan_price ?? plan.planPrice
        if (planPrices?.length) {
            form.price_description = planPrices[0]?.price_description ?? ''
            form.prices = planPrices.map(p => ({
                _key:        ++nextKey,
                currency:    p.currency ?? '',
                add_price:   p.add_price ?? '',
                offer_price: p.offer_price ?? '',
                renew_price: p.renew_price ?? '',
            }))
        }
        savedForm.value = formSnapshot()
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
    const offerOver100 = form.prices.some(p => p.offer_price !== '' && p.offer_price !== null && Number(p.offer_price) > 100)

    if (invalidRow) {
        pricingError.value = 'Each pricing row requires a currency, price, and renewal price.'
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
        const res = await http.patch(`/plan/${route.params.id}`, payload)
        // Then the plan's features, now that its currencies are saved.
        successHandler(featuresRef.value ? await featuresRef.value.save() : res, COMPONENT)
        savedForm.value = formSnapshot()
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        saving.value = false
    }
}
</script>

<style scoped>
/* The price table keeps its columns usable; it scrolls sideways on small screens */
.price-table { min-width: 640px; }
/* Digits line up in the summary */
.tabular-nums { font-variant-numeric: tabular-nums; }
</style>
