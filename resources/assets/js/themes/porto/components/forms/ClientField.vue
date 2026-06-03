<template>
    <div class="mb-3">
        <label v-if="label" :for="fieldId" class="form-label text-dark">
            {{ label }}
            <span v-if="required" class="text-danger">*</span>
        </label>

        <!-- password (with show / hide toggle) -->
        <div v-if="type === 'password'" class="input-group">
            <input :id="fieldId"
                   class="form-control form-control-lg text-4"
                   :class="{ 'is-invalid': error }"
                   :type="revealed ? 'text' : 'password'"
                   :value="modelValue"
                   :autocomplete="autocomplete"
                   :placeholder="placeholder"
                   :disabled="disabled"
                   @input="onInput"
                   @focus="$emit('focus', $event)"
                   @blur="$emit('blur', $event)">
            <button type="button" class="input-group-text" tabindex="-1"
                    :aria-label="revealed ? 'Hide password' : 'Show password'"
                    @mousedown.prevent
                    @click="revealed = !revealed">
                <i class="fa" :class="revealed ? 'fa-eye' : 'fa-eye-slash'"></i>
            </button>
        </div>

        <!-- select -->
        <select v-else-if="type === 'select'" :id="fieldId"
                class="form-control form-control-lg text-4"
                :class="{ 'is-invalid': error }"
                :value="modelValue"
                :disabled="disabled"
                @change="onChange">
            <slot />
        </select>

        <!-- textarea -->
        <textarea v-else-if="type === 'textarea'" :id="fieldId"
                  class="form-control form-control-lg text-4"
                  :class="{ 'is-invalid': error }"
                  :rows="rows"
                  :value="modelValue"
                  :placeholder="placeholder"
                  :disabled="disabled"
                  @input="onInput"></textarea>

        <!-- text / email / number / tel / … -->
        <input v-else :id="fieldId"
               class="form-control form-control-lg text-4"
               :class="{ 'is-invalid': error }"
               :type="type"
               :value="modelValue"
               :autocomplete="autocomplete"
               :placeholder="placeholder"
               :disabled="disabled"
               @input="onInput"
               @focus="$emit('focus', $event)"
               @blur="$emit('blur', $event)">

        <div v-if="error" class="invalid-feedback d-block">{{ error }}</div>
    </div>
</template>

<script setup>
import { ref, computed, useId } from 'vue'

const props = defineProps({
    label:        { type: String,  default: '' },
    name:         { type: String,  required: true },
    type:         { type: String,  default: 'text' },
    modelValue:   { type: [String, Number], default: '' },
    required:     { type: Boolean, default: false },
    error:        { type: String,  default: undefined },
    disabled:     { type: Boolean, default: false },
    autocomplete: { type: String,  default: '' },
    placeholder:  { type: String,  default: '' },
    rows:         { type: [String, Number], default: 3 },
})

const emit = defineEmits(['update:modelValue', 'change', 'focus', 'blur'])

const revealed = ref(false)
const uid = useId()
const fieldId = computed(() => `field-${props.name}-${uid}`)

const onInput  = (e) => emit('update:modelValue', e.target.value)
const onChange = (e) => { emit('update:modelValue', e.target.value); emit('change', e.target.value) }
</script>

<style scoped>
input[type="password"]::-ms-reveal { display: none; }
.input-group-text { cursor: pointer; }
</style>
