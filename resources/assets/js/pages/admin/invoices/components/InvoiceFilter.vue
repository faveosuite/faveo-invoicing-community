<template>
    <div v-if="show" class="card card-light mb-3">
        <div class="card-header">
            <h4 class="card-title">{{ __('message.filter') }}</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <TextField
                        name="name"
                        :label="`${__('message.first_name')} / ${__('message.last_name')}`"
                        :value="form.name"
                        :onChange="(val) => form.name = val"
                        :placeholder="__('message.search_by_name')"
                    />
                </div>
                <div class="col-md-4">
                    <TextField
                        name="invoice_no"
                        :label="__('message.invoice_no')"
                        :value="form.invoice_no"
                        :onChange="(val) => form.invoice_no = val"
                        :placeholder="__('message.exact_invoice_number')"
                    />
                </div>
                <div class="col-md-4">
                    <DynamicSelect
                        name="status"
                        :label="__('message.status')"
                        :elements="statusOptions"
                        :value="form.status"
                        :onChange="(val) => form.status = val"
                    />
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <DynamicSelect
                        name="currency"
                        :label="__('message.currency')"
                        :apiEndpoint="`${baseUrl}/dependency/invoice-currencies`"
                        dataKey="currencies"
                        :value="form.currency"
                        :onChange="(val) => form.currency = val"
                    />
                </div>
                <div class="col-md-4">
                    <DatePicker
                        name="from_date"
                        :label="__('message.invoice_form')"
                        :value="form.from_date"
                        :onChange="(val) => form.from_date = val"
                        :placeholder="__('message.select_date')"
                    />
                </div>
                <div class="col-md-4">
                    <DatePicker
                        name="to_date"
                        :label="__('message.invoice_till')"
                        :value="form.to_date"
                        :onChange="(val) => form.to_date = val"
                        :placeholder="__('message.select_date')"
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

const props = defineProps({
    show:          { type: Boolean, default: false },
    baseUrl:       { type: String, default: '' },
    initialValues: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['apply', 'reset', 'close'])

const statusOptions = [
    { id: 'pending',        name: __('message.unpaid') },
    { id: 'Partially paid', name: __('message.partially_paid') },
    { id: 'success',        name: __('message.paid') },
]

const empty = () => ({
    name: '', invoice_no: '', status: null, currency: null, from_date: null, to_date: null,
})

const form = reactive({
    ...empty(),
    name:       props.initialValues.name       ?? '',
    invoice_no: props.initialValues.invoice_no ?? '',
    from_date:  props.initialValues.from_date  ?? null,
    to_date:    props.initialValues.to_date    ?? null,
    status:     statusOptions.find(o => o.id === props.initialValues.status) ?? null,
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
