<template>
    <div :class="classname">
        <label for="radio" :style="labelStyle">{{ label }}</label>
        <ToolTip v-if="hint" :message="lang(hint)" size="small" />
        <div>
            <span style="display:inline">
                <span v-for="(option, index) in options" :key="index">
                    <input class="radio_align" :name="name" v-model="checked"
                           type="radio" :value="option.value"
                           :disabled="disabled" />
                    {{ lang(option.name) }}&nbsp;
                    <ToolTip v-if="option.hint" :message="lang(option.hint)" size="small" />&nbsp;
                </span>
            </span>
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
    classname:   { type: String,           default: 'col-sm-4' },
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

<style type="text/css">
.radio_align {
    width: 13px;
    height: 13px;
    padding: 0;
    margin: 0;
    vertical-align: bottom;
    position: relative;
    top: -5px;
    overflow: hidden;
}
</style>
