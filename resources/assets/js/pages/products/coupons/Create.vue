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
                        />
                    </div>
                    <div class="col-md-4">
                        <TextField name="value" :label="__('message.value')" :required="true" type="number" :value="form.value" :onChange="onChange" />
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
                        />
                    </div>
                    <div class="col-md-3">
                        <TextField name="uses" :label="__('message.uses')" :required="true" type="number" :value="form.uses" :onChange="onChange" />
                    </div>
                    <div class="col-md-3">
                        <DatePicker name="start" :label="__('message.start')" :required="true" :value="form.start" :onChange="onChange" format="YYYY-MM-DD" />
                    </div>
                    <div class="col-md-3">
                        <DatePicker name="expiry" :label="__('message.expiry')" :required="true" :value="form.expiry" :onChange="onChange" format="YYYY-MM-DD" />
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <action-button action="save" :loading="saving" @click="submit" />
                <action-button action="cancel" to="/products/coupons" class="ms-2" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { useFormValidation } from '@/composables/useFormValidation'
import { useAlertStore } from '@/core/stores/alert'
import TextField from '@/components/Reusable/FormField/TextField.vue'
import StaticSelect from '@/components/Reusable/FormField/StaticSelect.vue'

const COMPONENT = 'coupons-create'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const router = useRouter()

const { validate, clearFieldError, clearAllErrors } = useFormValidation()
const validationErrors = computed(() => useAlertStore().validation_errors)

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
    clearFieldError(name)
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
        clearFieldError('code')
    } catch (e) {
        // ignore
    } finally {
        generating.value = false
    }
}

onMounted(async () => {
    clearAllErrors()
    try {
        const res = await http.get(`${baseUrl}/dependency/promotion-types`)
        promotionTypes.value = res.data?.data?.promotion_types ?? []
    } catch (e) {
        errorHandler(e, COMPONENT)
    }
})

async function submit() {
    const isValid = validate({
        code:    [form.code,    { isRequired: __('validation.coupon_form.code.required') }],
        type:    [form.type,    { isRequired: __('validation.coupon_form.type.required') }],
        value:   [form.value,   { isRequired: __('validation.coupon_form.value.required') }],
        applied: [form.applied, { isRequired: __('validation.coupon_form.applied.required') }],
        uses:    [form.uses,    { isRequired: __('validation.coupon_form.uses.required') }],
        start:   [form.start,   { isRequired: __('validation.coupon_form.start.required') }],
        expiry:  [form.expiry,  { isRequired: __('validation.coupon_form.expiry.required') }],
    })
    if (!isValid) return

    saving.value = true
    try {
        const res = await http.put(`${baseUrl}/promotionCreate`, {
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
