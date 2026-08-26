<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.edit_coupon') }}</h4>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">
                    <!-- Row 1: Code / Type / Value -->
                    <div class="row">
                        <div class="col-md-4">
                            <TextField
                                name="code"
                                :label="__('message.coupon-code')"
                                :required="true"
                                :value="form.code"
                                :onChange="onChange"
                                :placehold="__('message.coupon-code')"
                                :inputGroupBtn="{ text: 'generate', action: generateCode }"
                                :error="errors.code"
                            />
                        </div>
                        <div class="col-md-4">
                            <DynamicSelect
                                name="type"
                                :label="__('message.type')"
                                :required="true"
                                :elements="promotionTypes"
                                :value="promotionTypes.find(o => o.id === form.type) ?? null"
                                :onChange="(val) => { form.type = val?.id ?? ''; setFieldError('type', undefined) }"
                                :clearable="false"
                                :searchable="false"
                                :error="errors.type"
                            />
                        </div>
                        <div class="col-md-4">
                            <TextField name="value" :label="__('message.value')" :required="true" type="number" :value="form.value" :onChange="onChange" :error="errors.value" />
                        </div>
                    </div>

                    <!-- Row 2: Applied / Uses / Start -->
                    <div class="row">
                        <div class="col-md-4">
                            <TreeSelect
                                name="applied"
                                :label="__('message.applied')"
                                :required="true"
                                :apiEndpoint="`${baseUrl}/dependency/products`"
                                dataKey="products"
                                :value="form.productObj"
                                :onChange="onChange"
                                :placeholder="__('message.choose')"
                                :error="errors.applied"
                            />
                        </div>
                        <div class="col-md-4">
                            <TextField name="uses" :label="__('message.uses')" :required="true" type="number" :value="form.uses" :onChange="onChange" :error="errors.uses" />
                        </div>
                        <div class="col-md-4">
                            <DatePicker name="start" :label="__('message.start')" :required="true" :value="form.start" :onChange="onChange" format="YYYY-MM-DD" :error="errors.start" />
                        </div>
                    </div>

                    <!-- Row 3: Expiry -->
                    <div class="row">
                        <div class="col-md-4">
                            <DatePicker name="expiry" :label="__('message.expiry')" :required="true" :value="form.expiry" :onChange="onChange" format="YYYY-MM-DD" :error="errors.expiry" />
                        </div>
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
import { validateForm } from '@/helpers/formUtils.js'
import { couponSchema } from '@/validations/admin/couponValidations'
import TextField from '@/components/Reusable/FormField/TextField.vue'
import TreeSelect from '@/components/Reusable/FormField/TreeSelect.vue'
import { useBaseUrl } from '@/core/composables/useBaseUrl'

const COMPONENT = 'coupons-edit'
const baseUrl = useBaseUrl()
const route = useRoute()
const router = useRouter()

const { errors, setErrors, setFieldError } = useForm()

const loading = ref(true)
const saving = ref(false)
const generating = ref(false)
const promotionTypes = ref([])

const form = reactive({
    code: '',
    type: '',
    value: '',
    applied: null,
    productObj: null,
    uses: '',
    start: null,
    expiry: null,
})

function onChange(val, name) {
    setFieldError(name, undefined)
    if (name === 'applied') {
        form.productObj = val
        form.applied = val ?? null
    } else {
        form[name] = val
    }
}

async function generateCode() {
    generating.value = true
    try {
        const res = await http.get(`/getPromotionCode`)
        form.code = res.data?.data ?? res.data ?? ''
        setFieldError('code', undefined)
    } catch (e) {
        // ignore
    } finally {
        generating.value = false
    }
}

onMounted(async () => {
    try {
        const [typesRes, promoRes] = await Promise.all([
            http.get(`/dependency/promotion-types`),
            http.get(`/promotion/${route.params.id}`),
        ])
        promotionTypes.value = typesRes.data?.data?.promotion_types ?? []

        const p = promoRes.data.data
        form.code  = p.code ?? ''
        form.type  = p.type ?? ''
        form.value = p.value ? String(p.value).replace('%', '') : ''
        form.uses  = p.uses ?? ''
        form.start  = p.start  ? p.start.substring(0, 10)  : null
        form.expiry = p.expiry ? p.expiry.substring(0, 10) : null

        // `products` is a hasOneThrough relation, so it serializes as a single
        // object (not an array). Guard for both shapes just in case.
        const product = Array.isArray(p.products) ? p.products[0] : p.products
        if (product) {
            form.applied = product.id
            form.productObj = { id: product.id, name: product.name }
        }
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function submit() {
    if (!await validateForm(couponSchema, form, setErrors)) return

    saving.value = true
    try {
        const res = await http.patch(`/updatePromotion/${route.params.id}`, {
            code:   form.code,
            type:   form.type,
            value:  form.value,
            applied: form.applied,
            uses:   form.uses,
            start:  form.start,
            expiry: form.expiry,
        })
        successHandler(res, COMPONENT)
        setTimeout(() => router.push('/products/coupons'), 2000)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
