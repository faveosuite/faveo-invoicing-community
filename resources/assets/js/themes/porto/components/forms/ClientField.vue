<template>
    <div class="form-group row">
        <label class="col-lg-3 col-form-label form-control-label line-height-9 pt-2 text-2"
               :class="{ required }">
            {{ label }}
        </label>
        <div class="col-lg-9">

            <!-- password with show/hide toggle -->
            <div v-if="type === 'password'" class="input-group">
                <input class="form-control text-3 h-auto py-2"
                       :class="{ 'is-invalid': error }"
                       :type="showPassword ? 'text' : 'password'"
                       :value="modelValue"
                       :autocomplete="autocomplete"
                       @input="$emit('update:modelValue', $event.target.value)">
                <button type="button" class="btn btn-outline-secondary"
                        @click="showPassword = !showPassword" tabindex="-1">
                    <i class="fa" :class="showPassword ? 'fa-eye' : 'fa-eye-slash'"></i>
                </button>
                <div v-if="error" class="invalid-feedback">{{ error }}</div>
            </div>

            <!-- select -->
            <div v-else-if="type === 'select'" class="custom-select-1">
                <select class="form-control text-3 h-auto py-2"
                        :class="{ 'is-invalid': error }"
                        :value="modelValue"
                        @change="$emit('update:modelValue', $event.target.value); $emit('change', $event.target.value)">
                    <slot />
                </select>
                <div v-if="error" class="invalid-feedback d-block">{{ error }}</div>
            </div>

            <!-- text / email / number / etc -->
            <template v-else>
                <input class="form-control text-3 h-auto py-2"
                       :class="{ 'is-invalid': error }"
                       :type="type"
                       :value="modelValue"
                       :disabled="disabled"
                       :placeholder="placeholder"
                       @input="$emit('update:modelValue', $event.target.value)">
                <div v-if="error" class="invalid-feedback">{{ error }}</div>
            </template>

        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
    label:        { type: String,  required: true },
    name:         { type: String,  required: true },
    type:         { type: String,  default: 'text' },
    modelValue:   { type: [String, Number], default: '' },
    required:     { type: Boolean, default: false },
    error:        { type: String,  default: undefined },
    disabled:     { type: Boolean, default: false },
    autocomplete: { type: String,  default: '' },
    placeholder:  { type: String,  default: '' },
})

defineEmits(['update:modelValue', 'change'])

const showPassword = ref(false)
</script>

<style>
input[type="password"]::-ms-reveal { display: none; }
</style>
