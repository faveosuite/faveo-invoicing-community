<template>
    <div class="form-check">
        <input :id="fieldId" type="checkbox"
               class="form-check-input"
               :class="{ 'is-invalid': error }"
               :checked="modelValue"
               :disabled="disabled"
               @change="$emit('update:modelValue', $event.target.checked)">
        <label class="form-check-label" :for="fieldId">
            <slot>{{ label }}</slot>
        </label>
        <div v-if="error" class="invalid-feedback d-block">{{ error }}</div>
    </div>
</template>

<script setup>
import { computed, useId } from 'vue'

defineProps({
    modelValue: { type: Boolean, default: false },
    label:      { type: String,  default: '' },
    error:      { type: String,  default: undefined },
    disabled:   { type: Boolean, default: false },
})

defineEmits(['update:modelValue'])

const uid = useId()
const fieldId = computed(() => `check-${uid}`)
</script>

<style scoped>
/* Bootstrap top-aligns the box (float + margin-top) to the first line of the
   label. There's no utility class to vertically center it, so center it here. */
.form-check {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
    padding-left: 0;
    min-height: auto;
    margin-bottom: 0;
}
.form-check .form-check-input {
    float: none;
    margin: 0;
    flex: 0 0 auto;
}
.form-check .form-check-label {
    margin: 0;
    cursor: pointer;
}
.form-check .invalid-feedback {
    flex: 0 0 100%;   /* keep the error message on its own line below */
}

/* Porto primary for the checked / focused state. */
.form-check-input:checked {
    background-color: var(--primary, #0088CC);
    border-color: var(--primary, #0088CC);
}
.form-check-input:focus {
    border-color: var(--primary, #0088CC);
    box-shadow: 0 0 0 0.2rem rgba(0, 136, 204, 0.25);
}
</style>
