<template>
    <div class="mb-3">
        <label v-if="label" class="form-label text-dark">
            {{ label }}<span v-if="required" class="text-danger ms-1">*</span>
        </label>
        <v-select
            :inputId="name"
            :options="elements"
            v-model="selectedValue"
            :label="optionLabel"
            :multiple="multiple"
            :placeholder="placeholder"
            :disabled="disabled"
            :clearable="clearable"
            :searchable="searchable"
            :closeOnSelect="closeOnSelect"
            :class="['faveo-dynamic-select', { 'is-invalid': fieldError }]"
            @update:modelValue="onValueChange"
        >
            <template #option="option">
                <slot name="option" v-bind="option">{{ option[optionLabel] }}</slot>
            </template>
            <template #no-options="{ search }">
                <span v-if="search">No results for <em>{{ search }}</em></span>
                <span v-else>No options found</span>
            </template>
        </v-select>
        <div v-if="fieldError" class="invalid-feedback d-block">{{ fieldError }}</div>
    </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css'

const props = defineProps({
    name:          { type: String, required: true },
    label:         { type: String, default: '' },
    elements:      { type: Array, default: () => [] },
    multiple:      { type: Boolean, default: false },
    value:         { type: [Object, Array, String, Number], default: null },
    onChange:      { type: Function, required: true },
    placeholder:   { type: String, default: 'Select' },
    clearable:     { type: Boolean, default: true },
    searchable:    { type: Boolean, default: true },
    disabled:      { type: Boolean, default: false },
    closeOnSelect: { type: Boolean, default: true },
    optionLabel:   { type: String, default: 'name' },
    required:      { type: Boolean, default: false },
    error:         { type: String, default: undefined },
})

const fieldError = computed(() => props.error ?? '')
const selectedValue = ref(props.value)

function onValueChange(val) {
    props.onChange(val, props.name)
}

watch(() => props.value, (val) => { selectedValue.value = val })
</script>

<style>
.faveo-dynamic-select .vs__dropdown-toggle {
    width: 100%;
    line-height: 1.4;
    display: flex;
    padding: 0;
    border: 1px solid rgba(60, 60, 60, 0.26);
    overflow-y: auto;
    min-height: 44px;
    max-height: 85px;
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.faveo-dynamic-select .vs__dropdown-toggle::-webkit-scrollbar {
    display: none;
}

.faveo-dynamic-select.is-invalid .vs__dropdown-toggle {
    border-color: #dc3545 !important;
}

.faveo-dynamic-select .vs__selected {
    margin: 3px;
}

.faveo-dynamic-select .vs__dropdown-menu {
    max-height: 200px;
}

.faveo-dynamic-select .vs__selected-options {
    padding: 0 5px;
}

.faveo-dynamic-select .vs__actions {
    padding: 0 5px 0 3px;
}

.faveo-dynamic-select .vs__search,
.faveo-dynamic-select .vs__search:focus {
    margin: 5px;
}
</style>
