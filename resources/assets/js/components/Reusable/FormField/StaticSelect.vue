<template>
    <FormFieldTemplate :label="label" :name="name" :classname="classname" :hint="hint"
                       :required="required" :labelStyle="labelStyle" :error="error">
        <select :class="['form-control', { 'is-invalid': error }]" v-model="selectedValue" :name="name"
                @change="onChange(selectedValue, name)" :id="id"
                :style="inputStyle" :disabled="disabled">
            <option value="" v-if="!hideEmptySelect">Select</option>
            <option v-for="element in elements" :value="element.id" :key="element.id">
                {{ subString(element.name) }}
            </option>
        </select>
    </FormFieldTemplate>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { getSubStringValue } from '@/helpers/extraLogics'
import FormFieldTemplate from './FormFieldTemplate.vue'

const props = defineProps({
    label:           { type: String,           required: true },
    elements:        { type: Array,            required: true },
    name:            { type: [String, Number], required: true },
    value:           { type: [String, Number], required: true },
    classname:       { type: String,           default: '' },
    onChange:        { type: Function,         required: true },
    hint:            { type: String,           default: '' },
    required:        { type: Boolean,          default: false },
    hideEmptySelect: { type: Boolean,          default: false },
    id:              { type: [String, Number], default: 'static-select' },
    inputStyle:      { type: Object,           default: () => ({}) },
    labelStyle:      { type: Object,           default: () => ({}) },
    strlength:       { type: [String, Number], default: 100 },
    disabled:        { type: Boolean,          default: false },
    error:           { type: String,           default: undefined },
})

const selectedValue = ref('')

onMounted(() => {
    selectedValue.value = props.value
})

watch(() => props.value, (newValue) => {
    selectedValue.value = newValue
})

function subString(value) {
    return getSubStringValue(value, parseInt(props.strlength))
}
</script>
