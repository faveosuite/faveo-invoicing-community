<template>
    <div v-if="show" class="card card-light mb-3">
        <div class="card-header">
            <h4 class="card-title">{{ __('message.filters') }}</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <SelectField
                        name="filter_status"
                        :label="__('message.status')"
                        :elements="statusOptions"
                        :value="form.status"
                        :onChange="(val) => form.status = val"
                    />
                </div>
                <div class="col-md-4">
                    <DatePicker
                        name="date_from"
                        :label="__('message.view_logs_from')"
                        :value="form.dateFrom"
                        :clearable="true"
                        :onChange="(val) => form.dateFrom = val"
                    />
                </div>
                <div class="col-md-4">
                    <DatePicker
                        name="date_till"
                        :label="__('message.view_logs_till')"
                        :value="form.dateTill"
                        :clearable="true"
                        :onChange="(val) => form.dateTill = val"
                    />
                </div>
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <button class="btn btn-primary" type="button" @click="apply">
                <i class="fas fa-check"></i>&nbsp;{{ __('message.apply') }}
            </button>
            <button class="btn btn-primary" type="button" @click="reset">
                <i class="fas fa-rotate-left"></i>&nbsp;{{ __('message.reset') }}
            </button>
            <button class="btn btn-secondary" type="button" @click="$emit('close')">
                <i class="fas fa-xmark"></i>&nbsp;{{ __('message.cancel') }}
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
    { id: 'success', name: 'Success' },
    { id: 'failed',  name: 'Failed'  },
]

const empty = () => ({
    status:   null,
    dateFrom: null,
    dateTill: null,
})

const form = reactive(empty())

function apply() {
    const params = {}
    if (form.status?.id) params.status    = form.status.id
    if (form.dateFrom)   params.date_from = form.dateFrom
    if (form.dateTill)   params.date_till = form.dateTill
    emit('apply', params)
}

function reset() {
    Object.assign(form, empty())
    emit('reset')
}
</script>
