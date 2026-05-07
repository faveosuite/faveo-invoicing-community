<template>
    <div v-if="show" class="card card-light mb-3">
        <div class="card-header">
            <h4 class="card-title">Filter</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Order No</label>
                    <input
                        type="text"
                        class="form-control mb-3"
                        v-model="form.order_no"
                        placeholder="Exact order number"
                    />
                </div>
                <div class="col-md-4">
                    <DynamicSelect
                        name="product_id"
                        label="Product"
                        :apiEndpoint="`${baseUrl}/dependency/products`"
                        dataKey="products"
                        :value="form.product_id"
                        :onChange="(val) => form.product_id = val"
                    />
                </div>
                <div class="col-md-4">
                    <DatePicker
                        name="from"
                        label="From"
                        :value="form.from"
                        :onChange="(val) => form.from = val"
                        placeholder="Select date"
                    />
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <DatePicker
                        name="till"
                        label="To"
                        :value="form.till"
                        :onChange="(val) => form.till = val"
                        placeholder="Select date"
                    />
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Domain</label>
                    <input
                        type="text"
                        class="form-control mb-3"
                        v-model="form.domain"
                        placeholder="e.g. example.com"
                    />
                </div>
                <div class="col-md-4">
                    <DynamicSelect
                        name="act_ins"
                        label="Installations"
                        :elements="installationOptions"
                        :value="form.act_ins"
                        :onChange="(val) => form.act_ins = val"
                        placeholder="Choose"
                    />
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <DynamicSelect
                        name="renewal"
                        label="Subscriptions"
                        :elements="subscriptionOptions"
                        :value="form.renewal"
                        :onChange="(val) => form.renewal = val"
                        placeholder="Choose"
                    />
                </div>
                <div class="col-md-4">
                    <DynamicSelect
                        name="version"
                        label="Version"
                        :apiEndpoint="`${baseUrl}/dependency/order-versions`"
                        dataKey="versions"
                        :value="form.version"
                        :onChange="(val) => form.version = val"
                        placeholder="Choose"
                    />
                </div>
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <button class="btn btn-primary" type="button" @click="apply">
                <i class="fas fa-check"></i>&nbsp; Apply
            </button>
            <button class="btn btn-primary" type="button" @click="reset">
                <i class="fas fa-rotate-left"></i>&nbsp; Reset
            </button>
            <button class="btn btn-secondary" type="button" @click="$emit('close')">
                <i class="fas fa-xmark"></i>&nbsp; Cancel
            </button>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue'

const props = defineProps({
    show:    { type: Boolean, default: false },
    baseUrl: { type: String, default: '' },
})

const emit = defineEmits(['apply', 'reset', 'close'])

const installationOptions = [
    { id: 'installed',         name: 'Installed (at least once)' },
    { id: 'not_installed',     name: 'Not Installed' },
    { id: 'paid_ins',          name: 'Active Installation' },
    { id: 'paid_inactive_ins', name: 'Inactive Installation' },
]

const subscriptionOptions = [
    { id: 'active_subscription',   name: 'Active' },
    { id: 'expiring_subscription', name: 'Expiring (within 30 days)' },
    { id: 'expired_subscription',  name: 'Expired' },
]

const empty = () => ({
    order_no: '', product_id: null, from: null, till: null,
    domain: '', act_ins: null, renewal: null, version: null,
})

const form = reactive(empty())

function apply() {
    const params = {}
    Object.entries(form).forEach(([k, v]) => {
        if (v !== '' && v !== null) {
            params[k] = typeof v === 'object' ? v.id : v
        }
    })
    emit('apply', params)
}

function reset() {
    Object.assign(form, empty())
    emit('reset')
}
</script>
