<template>
    <div v-if="show" class="card card-light mb-3">
        <div class="card-header">
            <h4 class="card-title">{{ __('message.advance_search') }}</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label fw-bold">{{ __('message.first_name') }} / {{ __('message.last_name') }}</label>
                    <input
                        type="text"
                        class="form-control mb-3"
                        v-model="form.name"
                    />
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">{{ __('message.invoice_no') }}</label>
                    <input
                        type="text"
                        class="form-control mb-3"
                        v-model="form.invoice_no"
                    />
                </div>
                <div class="col-md-3">
                    <DynamicSelect
                        name="status"
                        :label="__('message.status')"
                        :elements="statusOptions"
                        :value="form.status"
                        :onChange="(val) => form.status = val"
                        :placeholder="__('message.choose')"
                    />
                </div>
                <div class="col-md-3">
                    <DynamicSelect
                        name="currency"
                        :label="__('message.currency')"
                        :apiEndpoint="`${baseUrl}/dependency/currencies`"
                        dataKey="currencies"
                        :value="form.currency"
                        :onChange="(val) => form.currency = val"
                        :placeholder="__('message.choose')"
                    />
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <DatePicker
                        name="from_date"
                        :label="__('message.invoice_form')"
                        :value="form.from_date"
                        :onChange="(val) => form.from_date = val"
                        :placeholder="__('message.select_date')"
                    />
                </div>
                <div class="col-md-3">
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
            <button class="btn btn-secondary" type="button" @click="apply">
                <i class="fas fa-magnifying-glass"></i>&nbsp; {{ __('message.search') }}
            </button>
            <button class="btn btn-secondary" type="button" @click="reset">
                <i class="fas fa-rotate-left"></i>&nbsp; {{ __('message.reset') }}
            </button>
            <button class="btn btn-secondary" type="button" @click="$emit('close')">
                <i class="fas fa-xmark"></i>&nbsp; {{ __('message.cancel') }}
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

const statusOptions = [
    { id: 'pending',        name: __('message.unpaid') },
    { id: 'Partially paid', name: __('message.partially_paid') },
    { id: 'success',        name: __('message.paid') },
]

const empty = () => ({
    name: '', invoice_no: '', status: null, currency: null, from_date: null, to_date: null,
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
