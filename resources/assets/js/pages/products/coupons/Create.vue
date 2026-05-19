<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.create_coupon_title') }}</h4>
            </div>

            <div class="card-body">
                <!-- Row 1: Code / Type / Value -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('message.coupon-code') }} *</label>
                            <div class="input-group">
                                <input type="text" class="form-control" v-model="form.code" :placeholder="__('message.coupon-code')" />
                                <action-button action="refresh" variant="secondary" :loading="generating" :label="__('message.generate')" type="button" @click="generateCode" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('message.type') }} *</label>
                            <select class="form-select" v-model="form.type">
                                <option value="">{{ __('message.choose') }}</option>
                                <option v-for="t in promotionTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <TextField name="value" :label="__('message.value') + ' *'" type="number" :value="form.value" :onChange="onChange" />
                    </div>
                </div>

                <!-- Row 2: Applied / Uses / Start / Expiry -->
                <div class="row">
                    <div class="col-md-3">
                        <DynamicSelect
                            name="applied"
                            :label="__('message.applied') + ' *'"
                            :apiEndpoint="`${baseUrl}/dependency/all-products`"
                            dataKey="products"
                            :value="form.productObj"
                            :onChange="onChange"
                            :placeholder="__('message.choose')"
                        />
                    </div>
                    <div class="col-md-3">
                        <TextField name="uses" :label="__('message.uses') + ' *'" type="number" :value="form.uses" :onChange="onChange" />
                    </div>
                    <div class="col-md-3">
                        <DatePicker name="start" :label="__('message.start') + ' *'" :value="form.start" :onChange="onChange" format="YYYY-MM-DD" />
                    </div>
                    <div class="col-md-3">
                        <DatePicker name="expiry" :label="__('message.expiry') + ' *'" :value="form.expiry" :onChange="onChange" format="YYYY-MM-DD" />
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
import { reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'coupons-create'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const router = useRouter()

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
    } catch (e) {
        // ignore
    } finally {
        generating.value = false
    }
}

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/dependency/promotion-types`)
        promotionTypes.value = res.data?.data?.promotion_types ?? []
    } catch (e) {
        errorHandler(e, COMPONENT)
    }
})

async function submit() {
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
        router.push('/products/coupons')
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
