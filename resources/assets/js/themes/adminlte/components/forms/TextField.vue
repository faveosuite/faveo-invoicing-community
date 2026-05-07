<template>
    <div class="mb-3">
        <label v-if="label" class="form-label fw-bold">{{ label }}</label>
        <input
            :type="type"
            :name="name"
            :value="value"
            :placeholder="placeholder"
            :disabled="disabled"
            :readonly="readonly"
            class="form-control"
            @input="onChange($event.target.value, name)"
        />
        <div v-if="error" class="text-danger small mt-1">{{ error }}</div>
    </div>
</template>

<script setup>
const props = defineProps({
    name:        { type: String, required: true },
    label:       { type: String, default: '' },
    value:       { type: [String, Number], default: '' },
    type:        { type: String, default: 'text' },
    placeholder: { type: String, default: '' },
    disabled:    { type: Boolean, default: false },
    readonly:    { type: Boolean, default: false },
    error:       { type: String, default: '' },
    onChange:    { type: Function, default: () => {} },
})
</script>
