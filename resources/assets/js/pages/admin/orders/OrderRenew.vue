<template>
    <div>
        <AppAlert componentName="orders-renew" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.renew_order') }}</h4>
            </div>

            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="row">
                        <!-- Plan -->
                        <div class="col-md-4">
                            <DynamicSelect
                                name="plan"
                                :label="__('message.plan')"
                                :required="true"
                                :apiEndpoint="planEndpoint"
                                dataKey="plans"
                                :value="form.plan"
                                :onChange="onPlanChange"
                                :placeholder="__('message.select_plan')"
                                :error="errors.plan"
                            />
                        </div>

                        <!-- Cost -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold">
                                {{ __('message.renew-price') }}<span class="text-danger ms-1">*</span>
                            </label>
                            <div class="input-group" :class="{ 'is-invalid': errors.cost }">
                                <span v-if="currencySymbol" class="input-group-text">{{ currencySymbol }}</span>
                                <input
                                    type="number"
                                    step="0.01"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.cost }"
                                    :value="form.cost"
                                    @input="onChange($event.target.value, 'cost')"
                                />
                            </div>
                            <div v-if="errors.cost" class="invalid-feedback d-block">{{ errors.cost }}</div>
                        </div>

                        <!-- Payment Method -->
                        <div class="col-md-4">
                            <SelectField
                                name="payment_method"
                                :label="__('message.payment_method')"
                                :required="true"
                                :elements="paymentMethods"
                                :value="selectedMethod"
                                :onChange="(val) => onChange(val?.value ?? '', 'payment_method')"
                                :error="errors.payment_method"
                            />
                        </div>

                    </div>
                </form>
            </div>

            <div class="card-footer">
                <action-button action="create" type="button" :loading="saving" :label="__('message.renew')" @click="submit" />
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
import TextField from '@/components/Reusable/FormField/TextField.vue'
import SelectField from '@/components/Reusable/FormField/SelectField.vue'

const COMPONENT = 'orders-renew'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const router = useRouter()
const route  = useRoute()
const { errors, setErrors, setFieldError } = useForm()

const orderId = route.params.id
const saving = ref(false)
const productId = ref(null)
const currencySymbol = ref('')

const form = reactive({
    plan:           null,
    cost:           '',
    payment_method: '',
})

const paymentMethods = [
    { name: 'Cash',           value: 'cash' },
    { name: 'Check',          value: 'check' },
    { name: 'Online Payment', value: 'online payment' },
    { name: 'Razorpay',       value: 'razorpay' },
    { name: 'Stripe',         value: 'stripe' },
    { name: 'Credit Balance', value: 'Credit Balance' },
]

const selectedMethod = computed(() =>
    paymentMethods.find(m => m.value === form.payment_method) ?? null
)

const planEndpoint = computed(() =>
    productId.value ? `${baseUrl}/dependency/product-plans?product_id=${productId.value}` : ''
)

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/order/${orderId}`)
        const order = res.data?.data?.order
        if (order) {
            productId.value = order.product
            const planId = order.subscription?.plan?.id
            const planName = order.subscription?.plan?.name
            if (planId) {
                form.plan = { id: planId, name: planName }
                fetchCost(planId)
            }
        }
    } catch (e) {
        errorHandler(e, COMPONENT)
    }
})

function onChange(val, name) {
    setFieldError(name, undefined)
    form[name] = val
}

function onPlanChange(val) {
    setFieldError('plan', undefined)
    form.plan = val
    form.cost = ''
    currencySymbol.value = ''
    if (val) fetchCost(typeof val === 'object' ? val.id : val)
}

async function fetchCost(planId) {
    try {
        const res = await http.get(`${baseUrl}/get-renew-cost`, {
            params: { plan: planId, order: orderId },
        })
        const data = res.data?.data
        form.cost = data?.renewalPrice ? data.renewalPrice.replace(/[^0-9.]/g, '') : ''
        const match = data?.formatted_price?.match(/^[^\d]+/)
        currencySymbol.value = match ? match[0].trim() : ''
    } catch (e) {
        errorHandler(e, COMPONENT)
    }
}

async function submit() {
    const planId = typeof form.plan === 'object' ? form.plan?.id : form.plan

    const errs = {}
    if (!planId) errs.plan = __('message.renew_plan')
    if (!form.cost) errs.cost = __('message.renew_price')
    if (!form.payment_method) errs.payment_method = __('message.payment_method')
    if (Object.keys(errs).length) { setErrors(errs); return }

    saving.value = true
    try {
        const res = await http.post(`${baseUrl}/admin/renew/${orderId}`, {
            plan:           planId,
            payment_method: form.payment_method,
            cost:           form.cost,
        })
        successHandler(res, COMPONENT)
        setTimeout(() => router.push('/orders'), 2000)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
