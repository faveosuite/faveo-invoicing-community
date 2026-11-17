<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.create_coupon_title') }}</h4>
            </div>

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
                        <TextField name="value" :label="__('message.value')" :required="true" type="number" :value="form.value" :onChange="onChange" :error="errors.value" @keydown="(e) => blockInvalidKey(e, String(form.type) === '1')" />
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
                        <TextField name="uses" :label="__('message.uses')" :required="true" type="number" :value="form.uses" :onChange="onChange" :error="errors.uses" @keydown="blockInvalidKey" />
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
                    <!-- A Fixed Amount is money in one currency; percentage coupons work in any -->
                    <div v-if="String(form.type) === '2'" class="col-md-4">
                        <DynamicSelect
                            name="currency"
                            :label="__('message.currency')"
                            :required="true"
                            :elements="currencies"
                            :value="currencies.find(c => c.id === form.currency) ?? null"
                            :onChange="(val) => { form.currency = val?.id ?? ''; setFieldError('currency', undefined) }"
                            :clearable="false"
                            :error="errors.currency"
                        />
                    </div>
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
import { validateForm, blockInvalidKey } from '@/helpers/formUtils.js'
import { couponSchema } from '@/validations/admin/couponValidations'
import TextField from '@/components/Reusable/FormField/TextField.vue'
import TreeSelect from '@/components/Reusable/FormField/TreeSelect.vue'
import { useBaseUrl } from '@/core/composables/useBaseUrl'

const COMPONENT = 'coupons-create'
const baseUrl = useBaseUrl()
const router = useRouter()

const { errors, setErrors, setFieldError } = useForm()

const saving = ref(false)
const generating = ref(false)
const promotionTypes = ref([])
const currencies = ref([])

const form = reactive({
    code: '',
    type: '',
    value: '',
    currency: '',
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
        const [res, cRes] = await Promise.all([
            http.get(`/dependency/promotion-types`),
            http.get(`/dependency/currencies`),
        ])
        promotionTypes.value = res.data?.data?.promotion_types ?? []
        currencies.value = cRes.data?.data?.currencies ?? []
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    }
})

async function submit() {
    if (!await validateForm(couponSchema, form, setErrors)) return

    saving.value = true
    try {
        const res = await http.post(`/promotionCreate`, {
            code:   form.code,
            type:   form.type,
            value:  form.value,
            currency: String(form.type) === '2' ? form.currency : null,
            applied: form.applied,
            uses:   form.uses,
            start:  form.start,
            expiry: form.expiry,
        })
        successHandler(res, COMPONENT)
        setTimeout(() => router.push('/products/coupons'), 2000)
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        saving.value = false
    }
}
</script>
