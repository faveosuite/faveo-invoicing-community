<template>
    <div v-if="show" class="card card-light mb-3">
        <div class="card-header">
            <h4 class="card-title">{{ __('message.filter') }}</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <TextField
                        name="order_no"
                        :label="__('message.order_no')"
                        :value="form.order_no"
                        :onChange="(val) => form.order_no = val"
                        :placehold="__('message.exact_order_number')"
                    />
                </div>
                <div class="col-md-4">
                    <TreeSelect
                        name="product_id"
                        :label="__('message.product')"
                        :apiEndpoint="`${baseUrl}/dependency/products`"
                        dataKey="products"
                        :value="form.product_id"
                        :onChange="(val) => form.product_id = val"
                    />
                </div>
                <div class="col-md-4">
                    <DatePicker
                        name="from"
                        :label="__('message.from')"
                        :value="form.from"
                        :onChange="(val) => form.from = val"
                        :placeholder="__('message.select_date')"
                    />
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <DatePicker
                        name="till"
                        :label="__('message.to')"
                        :value="form.till"
                        :onChange="(val) => form.till = val"
                        :placeholder="__('message.select_date')"
                    />
                </div>
                <div class="col-md-4">
                    <TextField
                        name="domain"
                        :label="__('message.domain')"
                        :value="form.domain"
                        :onChange="(val) => form.domain = val"
                        placehold="e.g. example.com"
                    />
                </div>
                <div class="col-md-4">
                    <DynamicSelect
                        name="act_ins"
                        :label="__('message.installations')"
                        :elements="installationOptions"
                        :value="form.act_ins"
                        :onChange="(val) => form.act_ins = val"
                        :placeholder="__('message.choose')"
                    />
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <DynamicSelect
                        name="renewal"
                        :label="__('message.subscriptions')"
                        :elements="subscriptionOptions"
                        :value="form.renewal"
                        :onChange="(val) => form.renewal = val"
                        :placeholder="__('message.choose')"
                    />
                </div>
                <div class="col-md-4">
                    <DynamicSelect
                        name="version"
                        :label="__('message.version')"
                        :apiEndpoint="`${baseUrl}/dependency/order-versions`"
                        dataKey="versions"
                        :value="form.version"
                        :onChange="(val) => form.version = val"
                        :placeholder="__('message.choose')"
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
import TextField from '@/components/Reusable/FormField/TextField.vue'
import TreeSelect from '@/components/Reusable/FormField/TreeSelect.vue'

const props = defineProps({
    show:          { type: Boolean, default: false },
    baseUrl:       { type: String, default: '' },
    initialValues: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['apply', 'reset', 'close'])

const installationOptions = [
    { id: 'installed',         name: __('message.installed_at_least_once') },
    { id: 'not_installed',     name: __('message.not_installed') },
    { id: 'paid_ins',          name: __('message.active_installation') },
    { id: 'paid_inactive_ins', name: __('message.inactive_installation') },
]

const subscriptionOptions = [
    { id: 'active_subscription',   name: __('message.active') },
    { id: 'expiring_subscription', name: __('message.expiring_subscription') },
    { id: 'expired_subscription',  name: __('message.expired') },
]

const empty = () => ({
    order_no: '', product_id: null, from: null, till: null,
    domain: '', act_ins: null, renewal: null, version: null,
})

const form = reactive({
    ...empty(),
    order_no: props.initialValues.order_no ?? '',
    domain:   props.initialValues.domain   ?? '',
    from:     props.initialValues.from     ?? null,
    till:     props.initialValues.till     ?? null,
    act_ins:  installationOptions.find(o => o.id === props.initialValues.act_ins)  ?? null,
    renewal:  subscriptionOptions.find(o => o.id === props.initialValues.renewal)  ?? null,
})

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
