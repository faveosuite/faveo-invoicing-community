<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h4 class="card-title">Create Coupon</h4>
            </div>

            <div class="card-body">
                <!-- Row 1: Code / Type / Value -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Code *</label>
                            <div class="input-group">
                                <input type="text" class="form-control" v-model="form.code" placeholder="Enter code" />
                                <button class="btn btn-secondary" type="button" @click="generateCode" :disabled="generating">
                                    <span v-if="generating" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    <span v-else><i class="fas fa-arrows-rotate"></i> Generate</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Type *</label>
                            <select class="form-select" v-model="form.type">
                                <option value="">Choose</option>
                                <option v-for="t in promotionTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <TextField name="value" label="Value *" type="number" :value="form.value" :onChange="onChange" />
                    </div>
                </div>

                <!-- Row 2: Applied / Uses / Start / Expiry -->
                <div class="row">
                    <div class="col-md-3">
                        <DynamicSelect
                            name="applied"
                            label="Applied To (Product) *"
                            :apiEndpoint="`${baseUrl}/dependency/all-products`"
                            dataKey="products"
                            :value="form.productObj"
                            :onChange="onChange"
                            placeholder="Select product"
                        />
                    </div>
                    <div class="col-md-3">
                        <TextField name="uses" label="Uses *" type="number" :value="form.uses" :onChange="onChange" />
                    </div>
                    <div class="col-md-3">
                        <DatePicker name="start" label="Start Date *" :value="form.start" :onChange="onChange" format="YYYY-MM-DD" />
                    </div>
                    <div class="col-md-3">
                        <DatePicker name="expiry" label="Expiry Date *" :value="form.expiry" :onChange="onChange" format="YYYY-MM-DD" />
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button class="btn btn-primary" @click="submit" :disabled="saving">
                    <span v-if="saving" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Save
                </button>
                <router-link to="/products/coupons" class="btn btn-secondary ms-2">Cancel</router-link>
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
