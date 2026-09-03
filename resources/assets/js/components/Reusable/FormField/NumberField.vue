<template>
    <FormFieldTemplate :label="label" :labelStyle="labelStyle" :name="name"
                       :classname="classname" :hint="hint" :required="required" :error="error">
        <span class="inline">
            <input :class="['form-control', { 'is-invalid': error }]" :style="formStyle"
                   :type="type"
                   id="number"
                   v-model="changedValue"
                   @input="onChange(changedValue, name)"
                   @keypress="checkValue"
                   @paste="onPaste"
                   min="0"
                   :placeholder="placeholder"
                   :required="required"
            />
        </span>
    </FormFieldTemplate>
</template>

<script setup>
import { ref, watch } from 'vue'
import FormFieldTemplate from './FormFieldTemplate.vue'

const props = defineProps({
    label:       { type: String,           default: '' },
    hint:        { type: String,           default: '' },
    value:       { required: true },
    name:        { type: String,           required: true },
    type:        { type: String,           default: 'text' },
    onChange:    { type: Function,         required: true },
    classname:   { type: String,           default: '' },
    required:    { type: Boolean,          default: false },
    labelStyle:  { type: Object },
    formStyle:   { type: Object },
    max:         { type: [String, Number], default: '' },
    placeholder: { type: String,           default: 'Enter a value' },
    pattern:     { type: String,           default: null },
    error:       { type: String,           default: undefined },
})

const changedValue = ref(props.value)

watch(() => props.value, (newVal) => {
    changedValue.value = newVal
})

function checkValue(evt) {
    const charCode = evt.which ?? evt.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        evt.preventDefault()
    }
}

function onPaste(evt) {
    if (evt.clipboardData.getData('Text').match(/[^\d]/)) {
        evt.preventDefault()
    }
}
</script>

<style scoped>
.inline { display: inline; }
.form-control { display: inline !important; }
</style>
