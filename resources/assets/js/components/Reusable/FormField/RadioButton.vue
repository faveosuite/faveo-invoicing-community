<template>
    <div :class="classname">
        <div class="d-flex align-items-center gap-1 mb-1">
            <label class="form-label fw-bold mb-0" :style="labelStyle">{{ label }}</label>
            <ToolTip v-if="hint" :message="lang(hint)" size="small" />
        </div>
        <div>
            <div v-for="(option, index) in options" :key="index" class="form-check form-check-inline">
                <input
                    class="form-check-input"
                    type="radio"
                    :name="name"
                    :id="`${name}-${index}`"
                    :value="option.value"
                    :disabled="disabled"
                    v-model="checked"
                />
                <label class="form-check-label" :for="`${name}-${index}`">
                    {{ lang(option.name) }}
                </label>
                <ToolTip v-if="option.hint" :message="lang(option.hint)" size="small" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { lang } from '@/helpers/extraLogics'
import ToolTip from '../Tooltip.vue'

const props = defineProps({
    options:     { type: Array,            default: () => [] },
    value:       { type: [Number, String], default: 0 },
    name:        { type: String,           default: 'radio' },
    label:       { type: String,           default: 'label' },
    classname:   { type: String,           default: 'mb-3' },
    optionClass: { type: String,           default: 'col-sm-4' },
    onChange:    { type: Function,         required: true },
    labelStyle:  { type: Object,           default: () => ({}) },
    hint:        { type: String,           default: '' },
    disabled:    { type: Boolean,          default: false },
})

const checked = ref(props.value)

watch(() => props.value, (newValue) => {
    checked.value = newValue
})

watch(checked, (newVal) => {
    props.onChange(newVal, props.name)
})
</script>

