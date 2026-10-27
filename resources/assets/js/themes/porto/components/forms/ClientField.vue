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
                   :name="name"
                   :aria-invalid="error ? 'true' : undefined"
                   :aria-describedby="error ? errorId : undefined"
                   :aria-required="required ? 'true' : undefined"
                   :autocomplete="autocomplete || undefined"
                   :spellcheck="spellcheckAttr"
                   :placeholder="placeholder"
                   :disabled="disabled"
                   @input="onInput"
                   @focus="$emit('focus', $event)"
                   @blur="$emit('blur', $event)">
            <button type="button" class="input-group-text"
                    :aria-label="revealed ? __('message.hide_password') : __('message.show_password')"
                    :aria-pressed="revealed ? 'true' : 'false'"
                    @mousedown.prevent
                    @click="revealed = !revealed">
                <i class="fa" :class="revealed ? 'fa-eye' : 'fa-eye-slash'" aria-hidden="true"></i>
            </button>
        </div>

        <!-- select -->
        <select v-else-if="type === 'select'" :id="fieldId"
                class="form-control form-control-lg text-4"
                :class="{ 'is-invalid': error }"
                :value="modelValue"
                :name="name"
                :aria-invalid="error ? 'true' : undefined"
                :aria-describedby="error ? errorId : undefined"
                :aria-required="required ? 'true' : undefined"
                :autocomplete="autocomplete || undefined"
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
                  :name="name"
                  :aria-invalid="error ? 'true' : undefined"
                  :aria-describedby="error ? errorId : undefined"
                  :aria-required="required ? 'true' : undefined"
                  :autocomplete="autocomplete || undefined"
                  :spellcheck="spellcheck"
                  :placeholder="placeholder"
                  :disabled="disabled"
                  :maxlength="maxlength"
                  @input="onInput"></textarea>

        <!-- text / email / number / tel / … (with optional append slot) -->
        <div v-else :class="$slots.append ? 'input-group' : ''">
            <input :id="fieldId"
                   class="form-control form-control-lg text-4"
                   :class="{ 'is-invalid': error }"
                   :type="type"
                   :value="modelValue"
                   :name="name"
                   :aria-invalid="error ? 'true' : undefined"
                   :aria-describedby="error ? errorId : undefined"
                   :aria-required="required ? 'true' : undefined"
                   :autocomplete="autocomplete || undefined"
                   :spellcheck="spellcheckAttr"
                   :inputmode="inputmode"
                   :maxlength="maxlength"
                   :placeholder="placeholder"
                   :disabled="disabled"
                   @input="onInput"
                   @focus="$emit('focus', $event)"
                   @blur="$emit('blur', $event)">
            <slot name="append" />
        </div>

        <div v-if="error" :id="errorId" class="invalid-feedback d-block" role="alert">{{ error }}</div>
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
    maxlength:    { type: [String, Number], default: undefined },
    // undefined = browser default (auto-off for email/password/username fields)
    spellcheck:   { type: Boolean, default: undefined },
    inputmode:    { type: String,  default: undefined },
    // Strip anything but 0-9 in the box itself (OTP codes), not just in the bound value.
    digitsOnly:   { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'change', 'focus', 'blur'])

const revealed = ref(false)
const uid = useId()
const fieldId = computed(() => `field-${props.name}-${uid}`)
const errorId = computed(() => `${fieldId.value}-error`)
const spellcheckAttr = computed(() => props.spellcheck
    ?? ((['email', 'password'].includes(props.type) || /username|email/.test(props.autocomplete)) ? false : undefined))

const onInput  = (e) => {
    if (props.digitsOnly) e.target.value = e.target.value.replace(/\D/g, '')
    emit('update:modelValue', e.target.value)
}
const onChange = (e) => { emit('update:modelValue', e.target.value); emit('change', e.target.value) }
</script>

<style scoped>
input[type="password"]::-ms-reveal { display: none; }
.input-group-text { cursor: pointer; }
</style>
