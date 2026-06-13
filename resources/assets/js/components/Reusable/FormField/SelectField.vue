<template>
    <div class="mb-3">
        <label v-if="label" class="form-label fw-bold">
            {{ label }}<span v-if="required" class="text-danger ms-1">*</span>
            <ToolTip v-if="tooltip" :message="tooltip" size="small" />
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
            :taggable="taggable"
            :create-option="createOption || undefined"
            :noDrop="noDrop"
            :class="['faveo-dynamic-select', { 'is-invalid': fieldError }]"
            @update:modelValue="onValueChange"
        >
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
import ToolTip from '@/components/Reusable/Tooltip.vue'

const props = defineProps({
    name:          { type: String, required: true },
    label:         { type: String, default: '' },
    tooltip:       { type: String, default: '' },
    createOption:  { type: Function, default: null },
    elements:      { type: Array, default: () => [] },
    multiple:      { type: Boolean, default: false },
    value:         { type: [Object, Array, String, Number], default: null },
    onChange:      { type: Function, required: true },
    placeholder:   { type: String, default: 'Select' },
    clearable:     { type: Boolean, default: true },
    searchable:    { type: Boolean, default: false },
    disabled:      { type: Boolean, default: false },
    closeOnSelect: { type: Boolean, default: true },
    taggable:      { type: Boolean, default: false },
    noDrop:        { type: Boolean, default: false },
    optionLabel:   { type: String, default: 'name' },
    required:      { type: Boolean, default: false },
    error:         { type: String, default: undefined },
})

const fieldError = computed(() => props.error ?? '')

const selectedValue = ref(props.value)

function onValueChange(val) {
    props.onChange(val, props.name)
}

watch(() => props.value,    (val) => { selectedValue.value = val })
watch(() => props.elements, () => {
    if (!props.elements.includes(selectedValue.value)) {
        selectedValue.value = null
    }
})
</script>

<style>
.faveo-dynamic-select .vs__dropdown-toggle {
    width: 100%;
    line-height: 1.4;
    display: flex;
    padding: 0;
    border: 1px solid rgba(60, 60, 60, 0.26);
    overflow-y: auto;
    min-height: 35px;
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
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 100%;
}

.faveo-dynamic-select .vs__dropdown-menu {
    max-height: 200px;
    overflow-x: hidden;
}

.faveo-dynamic-select .vs__dropdown-option {
    white-space: normal;
    word-break: break-word;
}

.faveo-dynamic-select .vs__dropdown-menu::-webkit-scrollbar-track {
    background-color: #f1f1f1;
    border-radius: 10px;
}

.faveo-dynamic-select .vs__dropdown-menu::-webkit-scrollbar {
    width: 6px;
    background-color: #f1f1f1;
}

.faveo-dynamic-select .vs__dropdown-menu::-webkit-scrollbar-thumb {
    background-color: #c1c1c1;
    border-radius: 10px;
}

.faveo-dynamic-select .vs__selected-options {
    padding: 0;
    min-width: 0;
    overflow: hidden;
}

.faveo-dynamic-select .vs__actions {
    padding: 0 5px 0 3px;
}

.faveo-dynamic-select .vs__clear {
    position: relative;
}

.faveo-dynamic-select .vs__search,
.faveo-dynamic-select .vs__search:focus {
    margin: 0;
    padding: 0;
    height: 0;
    border: 0;
    min-width: 0;
}
</style>
