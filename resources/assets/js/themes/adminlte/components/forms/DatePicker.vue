<template>
    <div class="mb-3 position-relative">
        <label v-if="label" class="form-label fw-bold">
            {{ label }}<span v-if="required" class="text-danger ms-1">*</span>
        </label>
        <VueDatePicker
            v-model:value="selectedValue"
            :type="type"
            :format="format"
            :value-type="format"
            :placeholder="placeholder"
            :disabled="disabled"
            :clearable="clearable"
            :range="range"
            :editable="editable"
            :confirm="confirm"
            :append-to-body="false"
            :popup-style="{ top: '100%', left: 0 }"
            v-bind="disabledDate ? { 'disabled-date': disabledDate } : {}"
            :input-class="['form-control mx-input', { 'is-invalid': fieldError }]"
            @change="onDateChange"
            @confirm="onDateChange"
        />
        <div v-if="fieldError" class="invalid-feedback d-block">{{ fieldError }}</div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import VueDatePicker from 'vue-datepicker-next'
import 'vue-datepicker-next/index.css'
import { useAlertStore } from '@/core/stores/alert'

const props = defineProps({
    name:         { type: String, required: true },
    label:        { type: String, default: '' },
    value:        { type: [String, Date, Array], default: null },
    onChange:     { type: Function, required: true },
    type:         { type: String, default: 'date' },
    format:       { type: String, default: 'YYYY-MM-DD' },
    placeholder:  { type: String, default: 'Select date' },
    disabled:     { type: Boolean, default: false },
    clearable:    { type: Boolean, default: true },
    range:        { type: Boolean, default: false },
    editable:     { type: Boolean, default: true },
    confirm:      { type: Boolean, default: false },
    disabledDate: { type: Function, default: null },
    required:     { type: Boolean, default: false },
})

const fieldError = computed(() => useAlertStore().validation_errors[props.name] ?? '')

const selectedValue = ref(props.value ?? null)

function onDateChange(val) {
    props.onChange(val, props.name)
}

watch(() => props.value, (val) => { selectedValue.value = val ?? null })
</script>

<style>
.mx-datepicker       { width: 100%; }
.mx-datepicker-range { width: 100%; }

/* Fix Bootstrap 5 reboot overriding table styles inside the calendar */
.mx-table { border-collapse: separate !important; }
.mx-table td,
.mx-table th { padding: 0 !important; box-shadow: none !important; }

.mx-datepicker-popup { z-index: 9999 !important; }
</style>
