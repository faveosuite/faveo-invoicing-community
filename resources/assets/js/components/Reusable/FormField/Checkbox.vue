<template>
    <div :class="['form-check', classname]">
        <input
            class="form-check-input"
            type="checkbox"
            :id="name"
            :name="name"
            :checked="checked"
            :disabled="!!disabled"
            @change="checked = $event.target.checked"
        />
        <label v-if="label" class="form-check-label" :for="name">{{ label }}</label>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
    name:      { type: [String, Number], required: true },
    value:     { type: [Boolean, Number], default: false },
    label:     { type: String,           default: '' },
    classname: { type: String,           default: '' },
    onChange:  { type: Function,         required: true },
    disabled:  { type: [Boolean, Number], default: false },
})

const checked = ref(Boolean(props.value))

watch(() => props.value, (newVal) => {
    checked.value = Boolean(newVal)
})

watch(checked, (newVal) => {
    props.onChange(newVal, props.name)
})
</script>
