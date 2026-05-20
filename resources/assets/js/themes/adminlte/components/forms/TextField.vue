<template>
    <div class="mb-3">
        <label v-if="label" class="form-label fw-bold">
            {{ label }}<span v-if="required" class="text-danger ms-1">*</span>
        </label>
        <div v-if="type === 'password'" class="input-group">
            <input
                :type="showPassword ? 'text' : 'password'"
                :name="name"
                :value="value"
                :placeholder="placeholder"
                :disabled="disabled"
                :readonly="readonly"
                :class="['form-control', { 'is-invalid': fieldError }]"
                @input="onChange($event.target.value, name)"
            />
            <button type="button" class="btn btn-light border" @click="showPassword = !showPassword" tabindex="-1">
                <i class="fa" :class="showPassword ? 'fa-eye' : 'fa-eye-slash'"></i>
            </button>
        </div>
        <input
            v-else
            :type="type"
            :name="name"
            :value="value"
            :placeholder="placeholder"
            :disabled="disabled"
            :readonly="readonly"
            :class="['form-control', { 'is-invalid': fieldError }]"
            @input="onChange($event.target.value, name)"
        />
        <div v-if="fieldError" class="invalid-feedback">{{ fieldError }}</div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAlertStore } from '@/core/stores/alert'

const props = defineProps({
    name:        { type: String, required: true },
    label:       { type: String, default: '' },
    value:       { type: [String, Number], default: '' },
    type:        { type: String, default: 'text' },
    placeholder: { type: String, default: '' },
    disabled:    { type: Boolean, default: false },
    readonly:    { type: Boolean, default: false },
    required:    { type: Boolean, default: false },
    onChange:    { type: Function, default: () => {} },
})

const fieldError = computed(() => useAlertStore().validation_errors[props.name] ?? '')
const showPassword = ref(false)
</script>

<style scoped>
input[type="password"]::-ms-reveal { display: none; }
</style>
