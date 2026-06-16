<template>
    <div v-if="show" class="card card-light mb-3">
        <div class="card-header">
            <h4 class="card-title">{{ __('message.filter') }}</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <SelectField
                        name="status"
                        :label="__('message.status')"
                        :elements="statusOptions"
                        :value="form.status"
                        :onChange="(val) => form.status = val"
                        :placeholder="__('message.Select')"
                    />
                </div>
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <action-button action="apply" type="button" @click="apply" />
            <action-button action="reset"  type="button" @click="reset" />
            <action-button action="cancel" type="button" @click="$emit('close')" />
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue'
import SelectField from '@/components/Reusable/FormField/SelectField.vue'

defineProps({
    show: { type: Boolean, default: false },
})

const emit = defineEmits(['apply', 'reset', 'close'])

const statusOptions = [
    { id: 'bundled',        name: 'Bundled' },
    { id: 'compatible',     name: 'Compatible' },
    { id: 'not_mapped',     name: 'Not Mapped' },
]

const empty = () => ({ status: null })
const form  = reactive(empty())

function apply() {
    const params = {}
    Object.entries(form).forEach(([k, v]) => {
        if (v !== null) params[k] = typeof v === 'object' ? v.id : v
    })
    emit('apply', params)
}

function reset() {
    Object.assign(form, empty())
    emit('reset')
}
</script>
