<template>
    <div>
        <AppAlert componentName="invoices-create" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.create-invoice') }}</h4>
            </div>

            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="row">
                        <!-- User -->
                        <div class="col-md-4">
                            <DynamicSelect
                                name="user"
                                :label="__('message.user')"
                                :required="true"
                                :apiEndpoint="`${baseUrl}/dependency/users`"
                                dataKey="managers"
                                :value="form.user"
                                :onChange="onChange"
                                :placeholder="__('message.select_user')"
                                :error="errors.user"
                            >
                                <template #option="option">
                                    {{ option.name }} &lt;{{ option.email }}&gt;
                                </template>
                            </DynamicSelect>
                        </div>

                        <!-- Date -->
                        <div class="col-md-4">
                            <DatePicker
                                name="date"
                                :label="__('message.invoice_date')"
                                :required="true"
                                :value="form.date"
                                :onChange="onChange"
                                placeholder="MM/DD/YYYY"
                                :error="errors.date"
                            />
                        </div>

                        <!-- Product -->
                        <div class="col-md-4">
                            <DynamicSelect
                                name="product"
                                :label="__('message.product')"
                                :required="true"
                                :apiEndpoint="`${baseUrl}/dependency/products`"
                                dataKey="products"
                                :value="form.product"
                                :onChange="onProductChange"
                                :placeholder="__('message.select_product')"
                                :error="errors.product"
                            />
                        </div>

                        <!-- Plan (shows when product selected) -->
                        <div v-if="form.product" class="col-md-4">
                            <DynamicSelect
                                name="plan"
                                :label="__('message.plan')"
                                :apiEndpoint="`${baseUrl}/dependency/product-plans?product_id=${productId}`"
                                dataKey="plans"
                                :value="form.plan"
                                :onChange="onPlanChange"
                                :placeholder="__('message.select_plan')"
                            />
                        </div>

                        <!-- Price -->
                        <div class="col-md-4">
                            <TextField
                                name="price"
                                :label="__('message.price')"
                                :required="true"
                                :value="form.price"
                                :onChange="onChange"
                                placehold="0.00"
                                :error="errors.price"
                            />
                        </div>

                        <!-- Coupon Code -->
                        <div class="col-md-4">
                            <TextField
                                name="code"
                                :label="__('message.coupon-code')"
                                :value="form.code"
                                :onChange="onChange"
                            />
                        </div>

                        <!-- Domain (conditional: product requires it) -->
                        <div v-if="dynamic.required_domain" class="col-md-4">
                            <TextField
                                name="domain"
                                :label="__('message.domain')"
                                :required="true"
                                :value="form.domain"
                                :onChange="onChange"
                                :error="errors.domain"
                            />
                        </div>

                        <!-- Cloud Domain (conditional: cloud product) -->
                        <div v-if="dynamic.is_cloud_product" class="col-md-4">
                            <TextField
                                name="cloud_domain"
                                :label="__('message.cloud_domain_label')"
                                :required="true"
                                :value="form.cloud_domain"
                                :onChange="onChange"
                                :error="errors.cloud_domain"
                            />
                        </div>

                        <!-- Quantity (conditional: product allows qty modification) -->
                        <div v-if="dynamic.show_quantity" class="col-md-4">
                            <NumberField
                                name="quantity"
                                :label="__('message.quantity')"
                                :value="form.quantity"
                                :onChange="onChange"
                            />
                        </div>

                        <!-- Agents (conditional: product allows agent modification) -->
                        <div v-if="dynamic.show_agents" class="col-md-4">
                            <NumberField
                                name="agents"
                                :label="__('message.agents')"
                                :value="form.agents"
                                :onChange="onChange"
                            />
                        </div>

                    </div>
                </form>
            </div>

            <div class="card-footer">
                <action-button action="create" type="button" :loading="saving" :label="__('message.generate-invoice')" @click="submit" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { errorHandler, successHandler } from '@/helpers/responseHandler.js'
import { validateForm } from '@/helpers/formUtils.js'
import { buildInvoiceCreateSchema } from '@/validations/admin/invoiceValidations'
import TextField from '@/components/Reusable/FormField/TextField.vue'
import NumberField from '@/components/Reusable/FormField/NumberField.vue'

const COMPONENT = 'invoices-create'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const router = useRouter()
const route  = useRoute()
const { errors, setErrors, setFieldError } = useForm()

const saving = ref(false)

const form = reactive({
    user:         null,
    date:         null,
    product:      null,
    plan:         null,
    price:        '',
    code:         '',
    domain:       '',
    cloud_domain: '',
    quantity:     1,
    agents:       null,
})

const dynamic = reactive({
    required_domain: false,
    is_cloud_product: false,
    show_quantity: false,
    show_agents: false,
})

const productId = computed(() => {
    if (!form.product) return ''
    return typeof form.product === 'object' ? form.product.id : form.product
})

onMounted(async () => {
    const clientid = route.query.clientid
    if (clientid) {
        try {
            const res = await http.get(`${baseUrl}/user/${clientid}`)
            const u = res.data?.data
            if (u) {
                form.user = {
                    id:    u.id,
                    name:  `${u.first_name ?? ''} ${u.last_name ?? ''}`.trim(),
                    email: u.email,
                }
            }
        } catch { /* leave user field empty if fetch fails */ }
    }
})

function onChange(val, name) {
    setFieldError(name, undefined)
    form[name] = val
}

function onProductChange(val) {
    setFieldError('product', undefined)
    form.product = val
    form.plan = null
    form.price = ''
    resetDynamicFields()
}

function onPlanChange(val, name) {
    setFieldError('plan', undefined)
    form.plan = val
    if (val) {
        fetchPrice()
    } else {
        form.price = ''
        resetDynamicFields()
    }
}

function resetDynamicFields() {
    dynamic.required_domain = false
    dynamic.is_cloud_product = false
    dynamic.show_quantity = false
    dynamic.show_agents = false
    form.domain = ''
    form.cloud_domain = ''
    form.quantity = 1
    form.agents = null
}

async function fetchPrice() {
    const pid = productId.value
    const planId = typeof form.plan === 'object' ? form.plan?.id : form.plan
    const userId = typeof form.user === 'object' ? form.user?.id : form.user
    if (!pid || !planId) return

    try {
        const res = await http.post(`${baseUrl}/get-price`, {
            product: pid,
            plan:    planId,
            user:    userId || null,
        })
        const data = res.data?.data ?? res.data
        form.price = data.price ?? ''

        const fields = data.fields ?? {}
        dynamic.required_domain  = !!fields.required_domain
        dynamic.is_cloud_product = !!fields.is_cloud_product

        const qty = data.product_quantity ?? {}
        dynamic.show_quantity = !!qty.can_modify
        if (qty.can_modify) form.quantity = qty.quantity ?? 1

        const agents = data.agents ?? {}
        dynamic.show_agents = !!agents.can_modify
        if (agents.can_modify) form.agents = agents.quantity ?? 0
    } catch (e) {
        // price fetch is best-effort; leave price field editable
    }
}

async function submit() {
    if (!await validateForm(buildInvoiceCreateSchema(dynamic), form, setErrors)) return

    const planId = typeof form.plan === 'object' ? form.plan?.id : form.plan

    const payload = {
        user:         typeof form.user    === 'object' ? form.user?.id    : form.user,
        date:         form.date,
        product:      productId.value,
        plan:         planId || null,
        subscription: planId ? 'true' : 'false',
        price:        form.price,
        code:         form.code,
        domain:       dynamic.required_domain  ? form.domain       : null,
        cloud_domain: dynamic.is_cloud_product ? form.cloud_domain : null,
        quantity:     dynamic.show_quantity    ? form.quantity      : null,
        agents:       dynamic.show_agents      ? form.agents        : null,
    }

    saving.value = true
    try {
        const res = await http.post(`${baseUrl}/generate/invoice`, payload)
        successHandler(res, COMPONENT)
        setTimeout(() => router.push('/invoices'), 2000)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
