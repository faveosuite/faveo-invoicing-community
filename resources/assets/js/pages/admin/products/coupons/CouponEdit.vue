<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.edit_coupon') }}</h4>
            </div>

            <inline-loader v-if="loading" context="card-body" />

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
                            <StaticSelect
                                name="type"
                                :label="__('message.type')"
                                :required="true"
                                :elements="promotionTypes"
                                :value="form.type"
                                :onChange="onChange"
                                :error="errors.type"
                            />
                        </div>
                        <div class="col-md-4">
                            <TextField name="value" :label="__('message.value')" :required="true" type="number" :value="form.value" :onChange="onChange" :error="errors.value" />
                        </div>
                    </div>

                    <!-- Row 2: Applied / Uses / Start / Expiry -->
                    <div class="row">
                        <div class="col-md-3">
                            <DynamicSelect
                                name="applied"
                                :label="__('message.applied')"
                                :required="true"
                                :apiEndpoint="`${baseUrl}/dependency/all-products`"
                                dataKey="products"
                                :value="form.productObj"
                                :onChange="onChange"
                                :placeholder="__('message.choose')"
                                :error="errors.applied"
                            />
                        </div>
                        <div class="col-md-3">
                            <TextField name="uses" :label="__('message.uses')" :required="true" type="number" :value="form.uses" :onChange="onChange" :error="errors.uses" />
                        </div>
                        <div class="col-md-3">
                            <DatePicker name="start" :label="__('message.start')" :required="true" :value="form.start" :onChange="onChange" format="YYYY-MM-DD" :error="errors.start" />
                        </div>
                        <div class="col-md-3">
                            <DatePicker name="expiry" :label="__('message.expiry')" :required="true" :value="form.expiry" :onChange="onChange" format="YYYY-MM-DD" :error="errors.expiry" />
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <action-button action="update" :loading="saving" @click="submit" />
                    <action-button action="cancel" to="/products/coupons" class="ms-2" />
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
import { couponSchema } from '@/validations/admin/couponValidations'
import TextField from '@/components/Reusable/FormField/TextField.vue'
import StaticSelect from '@/components/Reusable/FormField/StaticSelect.vue'

const COMPONENT = 'coupons-edit'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
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
        form.applied = val?.id ?? null
    } else {
        form[name] = val
    }
}

async function generateCode() {
    generating.value = true
    try {
        const res = await http.get(`${baseUrl}/getPromotionCode`)
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
            http.get(`${baseUrl}/dependency/promotion-types`),
            http.get(`${baseUrl}/promotion/${route.params.id}`),
        ])
        promotionTypes.value = typesRes.data?.data?.promotion_types ?? []

        const p = promoRes.data
        form.code  = p.code ?? ''
        form.type  = p.type ?? ''
        form.value = p.value ? String(p.value).replace('%', '') : ''
        form.uses  = p.uses ?? ''
        form.start  = p.start  ? p.start.substring(0, 10)  : null
        form.expiry = p.expiry ? p.expiry.substring(0, 10) : null

        const firstProduct = p.products?.[0]
        if (firstProduct) {
            form.applied = firstProduct.id
            form.productObj = { id: firstProduct.id, name: firstProduct.name }
        }
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function submit() {
    try {
        couponSchema.validateSync(form, { abortEarly: false })
    } catch (err) {
        const errMap = {}
        err.inner?.forEach(e => { if (e.path && !errMap[e.path]) errMap[e.path] = e.message })
        setErrors(errMap)
        return
    }

    saving.value = true
    try {
        const res = await http.patch(`${baseUrl}/updatePromotion/${route.params.id}`, {
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
