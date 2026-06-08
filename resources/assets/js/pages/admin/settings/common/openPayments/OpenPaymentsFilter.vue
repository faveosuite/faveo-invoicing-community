<template>
    <div v-if="show" class="card card-light mb-3">
        <div class="card-header">
            <h4 class="card-title">{{ __('message.filters') }}</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <SelectField
                        name="status"
                        label="Status"
                        :elements="statusOptions"
                        :value="form.status"
                        :onChange="(val) => form.status = val"
                        placeholder="All Statuses"
                    />
                </div>
                <div class="col-md-4">
                    <SelectField
                        name="gateway"
                        label="Gateway"
                        :elements="gatewayOptions"
                        :value="form.gateway"
                        :onChange="(val) => form.gateway = val"
                        placeholder="All Gateways"
                    />
                </div>
                <div class="col-md-4">
                    <SelectField
                        name="currency"
                        label="Currency"
                        :elements="currencyOptions"
                        :value="form.currency"
                        :onChange="(val) => form.currency = val"
                        placeholder="All Currencies"
                    />
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <DatePicker
                        name="from_date"
                        label="From Date"
                        :value="form.from_date"
                        :clearable="true"
                        :onChange="(val) => form.from_date = val"
                    />
                </div>
                <div class="col-md-4">
                    <DatePicker
                        name="to_date"
                        label="To Date"
                        :value="form.to_date"
                        :clearable="true"
                        :onChange="(val) => form.to_date = val"
                    />
                </div>
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <action-button action="apply" type="button" @click="apply" />
            <action-button action="reset" type="button" @click="reset" />
            <action-button action="cancel" type="button" @click="$emit('close')" />
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue'

defineProps({
    show:    { type: Boolean, default: false },
    baseUrl: { type: String, default: '' },
})

const emit = defineEmits(['apply', 'reset', 'close'])

const statusOptions = [
    { id: 'pending',   name: 'Pending'   },
    { id: 'completed', name: 'Completed' },
    { id: 'failed',    name: 'Failed'    },
]

const gatewayOptions = [
    { id: 'Razorpay', name: 'Razorpay' },
    { id: 'Stripe',   name: 'Stripe'   },
]

const currencyOptions = [
    { id: 'USD', name: 'USD' },
    { id: 'INR', name: 'INR' },
]

const empty = () => ({
    status:    null,
    gateway:   null,
    currency:  null,
    from_date: null,
    to_date:   null,
})

const form = reactive(empty())

function apply() {
    const params = {}
    if (form.status?.id)   params.status    = form.status.id
    if (form.gateway?.id)  params.gateway   = form.gateway.id
    if (form.currency?.id) params.currency  = form.currency.id
    if (form.from_date)    params.from_date = form.from_date
    if (form.to_date)      params.to_date   = form.to_date
    emit('apply', params)
}

function reset() {
    Object.assign(form, empty())
    emit('reset')
}
</script>
