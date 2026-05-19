<template>
    <div>
        <AppAlert componentName="invoices-create" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.create-invoice') }}</h4>
                <div class="card-tools">
                    <router-link to="/invoices" class="btn btn-tool" :title="__('message.back_to_invoices')" v-tooltip>
                        <i class="fas fa-arrow-left"></i> {{ __('message.back') }}
                    </router-link>
                </div>
            </div>

            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="row">
                        <!-- User -->
                        <div class="col-md-4 mb-3">
                            <DynamicSelect
                                name="user"
                                :label="__('message.user')"
                                :apiEndpoint="`${baseUrl}/dependency/managers?role=client`"
                                dataKey="managers"
                                :value="form.user"
                                :onChange="(val) => { form.user = val; errors.user = null; }"
                                :placeholder="__('message.select_user')"
                            />
                            <span v-if="errors.user" class="text-danger small">{{ errors.user[0] }}</span>
                        </div>

                        <!-- Date -->
                        <div class="col-md-4 mb-3">
                            <DatePicker
                                name="date"
                                :label="__('message.invoice_date')"
                                :value="form.date"
                                :onChange="(val) => { form.date = val; errors.date = null; }"
                                placeholder="MM/DD/YYYY"
                            />
                            <span v-if="errors.date" class="text-danger small">{{ errors.date[0] }}</span>
                        </div>

                        <!-- Product -->
                        <div class="col-md-4 mb-3">
                            <DynamicSelect
                                name="product"
                                :label="__('message.product')"
                                :apiEndpoint="`${baseUrl}/dependency/products`"
                                dataKey="products"
                                :value="form.product"
                                :onChange="onProductChange"
                                :placeholder="__('message.select_product')"
                            />
                            <span v-if="errors.product" class="text-danger small">{{ errors.product[0] }}</span>
                        </div>

                        <!-- Plan (conditional) -->
                        <div v-if="form.product" class="col-md-4 mb-3">
                            <DynamicSelect
                                name="plan"
                                :label="__('message.plan')"
                                :apiEndpoint="`${baseUrl}/dependency/product-plans?product_id=${form.product?.id || form.product}`"
                                dataKey="plans"
                                :value="form.plan"
                                :onChange="(val) => { form.plan = val; errors.plan = null; }"
                                :placeholder="__('message.select_plan')"
                            />
                            <span v-if="errors.plan" class="text-danger small">{{ errors.plan[0] }}</span>
                        </div>

                        <!-- Price -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('message.price') }}</label>
                            <input
                                type="text"
                                class="form-control"
                                v-model="form.price"
                                @input="errors.price = null"
                                placeholder="0.00"
                            />
                            <span v-if="errors.price" class="text-danger small">{{ errors.price[0] }}</span>
                        </div>

                        <!-- Quantity -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('message.quantity') }}</label>
                            <input
                                type="number"
                                class="form-control"
                                v-model="form.quantity"
                                min="1"
                            />
                        </div>

                        <!-- Agents -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('message.agents') }}</label>
                            <input
                                type="number"
                                class="form-control"
                                v-model="form.agents"
                                min="0"
                            />
                        </div>

                        <!-- Coupon Code -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('message.coupon-code') }}</label>
                            <input
                                type="text"
                                class="form-control"
                                v-model="form.code"
                            />
                        </div>

                        <!-- Domain -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('message.domain') }}</label>
                            <input
                                type="text"
                                class="form-control"
                                v-model="form.domain"
                            />
                            <span v-if="errors.domain" class="text-danger small">{{ errors.domain[0] }}</span>
                        </div>

                        <!-- Cloud Domain -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('message.cloud_domain_label') }}</label>
                            <input
                                type="text"
                                class="form-control"
                                v-model="form.cloud_domain"
                            />
                        </div>

                        <!-- Description -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">{{ __('message.description') }}</label>
                            <textarea
                                class="form-control"
                                rows="3"
                                v-model="form.description"
                            ></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <action-button action="create" type="submit" :loading="saving" :label="__('message.generate-invoice')" />
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/plugins/axios'
import { errorHandler, successHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'invoices-create'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const router = useRouter()

const saving = ref(false)
const errors = reactive({})

const form = reactive({
    user: null,
    date: null,
    product: null,
    plan: null,
    price: '',
    quantity: 1,
    agents: null,
    code: '',
    domain: '',
    cloud_domain: '',
    description: '',
})

function onProductChange(val) {
    form.product = val
    form.plan = null
    errors.product = null
}

async function submit() {
    Object.keys(errors).forEach(k => delete errors[k])

    // Prepare payload
    const payload = {
        user: typeof form.user === 'object' ? form.user?.id : form.user,
        date: form.date,
        product: typeof form.product === 'object' ? form.product?.id : form.product,
        plan: typeof form.plan === 'object' ? form.plan?.id : form.plan,
        price: form.price,
        quantity: form.quantity,
        agents: form.agents,
        code: form.code,
        domain: form.domain,
        cloud_domain: form.cloud_domain,
        description: form.description,
    }

    saving.value = true
    try {
        const res = await http.post(`${baseUrl}/generate/invoice`, payload)
        successHandler(res, COMPONENT)
        router.push('/invoices')
    } catch (e) {
        if (e.response?.status === 422) {
            Object.assign(errors, e.response.data.errors || {})
        }
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
