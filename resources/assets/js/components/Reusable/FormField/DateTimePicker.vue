<template>
    <FormFieldTemplate :label="label" :name="name" :labelStyle="labelStyle"
                       :classname="classname" :hint="hint" :required="required">
        <DatePicker
            v-model:value="changedValue"
            :type="type"
            :time-picker-options="timePickerOptions"
            :format="format"
            :placeholder="place"
            :disabled="disabled"
            :range="range"
            :input-class="['form-control']"
            @change="onDateTimeChange(changedValue, name)"
            :clearable="clearable"
            :confirm="confirm"
            :editable="editable"
            :shortcuts="pickers"
            :disabled-date="notBefore"
            :disabled-time="notAfter"
        />
    </FormFieldTemplate>
</template>

<script setup>
import { ref, watch } from 'vue'
import DatePicker from 'vue-datepicker-next'
import FormFieldTemplate from './FormFieldTemplate.vue'

const props = defineProps({
    label:             { type: String,           default: '' },
    hint:              { type: String,           default: '' },
    value:             { type: [String, Date],   required: true },
    name:              { type: [String, Number], required: true },
    type:              { type: String,           default: 'text' },
    onChange:          { type: Function,         required: true },
    classname:         { type: String,           default: '' },
    labelStyle:        { type: Object },
    required:          { type: Boolean,          default: true },
    timePickerOptions: { type: Object,           default: () => ({}) },
    format:            { type: String,           default: '' },
    disabled:          { type: Boolean,          default: false },
    clearable:         { type: Boolean,          default: false },
    range:             { type: Boolean,          default: false },
    place:             { type: String,           default: 'Select date' },
    notBefore:         { type: [String, Date] },
    notAfter:          { type: [String, Date] },
    currentYearDate:   { type: Boolean,          default: false },
    confirm:           { type: Boolean,          default: false },
    editable:          { type: Boolean,          default: true },
    pickers:           { type: [Boolean, Array], default: false },
})

const changedValue = ref(props.value)

watch(() => props.value, (newValue) => {
    changedValue.value = newValue === '' ? null : newValue
})

function onDateTimeChange(val, name) {
    props.onChange(val, name)
}
</script>

<style>
.mx-input { border-radius: 0 !important; }
.mx-shortcuts-wrapper .mx-shortcuts { text-transform: capitalize; }
.mx-datepicker { width: 100% !important; }
.mx-datepicker-range { width: 100% !important; }
.mx-input-wrapper input { background-color: transparent !important; }
.mx-calendar-icon { height: auto !important; }
.mx-input-append { background-color: transparent !important; }
</style>
