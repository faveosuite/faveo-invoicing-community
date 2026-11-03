<template>
    <!-- 6 single-digit boxes (Bootstrap form-controls) behaving like one field: typing moves on,
         Backspace moves back, arrows navigate, and a pasted/autofilled code fills every box. -->
    <div class="mb-3">
        <span :id="labelId" class="form-label d-block text-dark">{{ label }}</span>
        <div class="d-flex gap-2" role="group" :aria-labelledby="labelId" dir="ltr">
            <input v-for="i in LENGTH" :key="i" :ref="el => { boxes[i - 1] = el }"
                   type="text" inputmode="numeric"
                   :autocomplete="i === 1 ? 'one-time-code' : 'off'"
                   class="form-control text-center text-5 font-weight-semibold px-0"
                   :class="{ 'is-invalid': error }" style="height: 3.25rem"
                   :value="digits[i - 1]" :disabled="disabled"
                   :aria-label="`${label} ${i}/${LENGTH}`"
                   :aria-invalid="error ? 'true' : undefined" :aria-describedby="error ? errorId : undefined"
                   @input="onInput(i - 1, $event)" @keydown="onKeydown(i - 1, $event)" @paste.prevent="onPaste">
        </div>
        <div v-if="error" :id="errorId" class="invalid-feedback d-block" role="alert">{{ error }}</div>
    </div>
</template>

<script setup>
import { computed, useId } from 'vue'

const LENGTH = 6

const props = defineProps({
    modelValue: { type: String,  default: '' },
    label:      { type: String,  required: true },
    error:      { type: String,  default: undefined },
    disabled:   { type: Boolean, default: false },
})
const emit = defineEmits(['update:modelValue'])

const uid = useId()
const labelId = `otp-label-${uid}`
const errorId = `otp-error-${uid}`
const boxes = []
const digits = computed(() => props.modelValue.padEnd(LENGTH, ' ').slice(0, LENGTH).split('').map(d => d.trim()))

const focusBox = (i) => boxes[Math.max(0, Math.min(LENGTH - 1, i))]?.focus()

// Writes `text`'s digits from box `start` onward; returns how many were placed.
function fill(start, text) {
    const add = String(text).replace(/\D/g, '').slice(0, LENGTH - start)
    const next = digits.value.map(d => d || ' ')
    add.split('').forEach((d, k) => { next[start + k] = d })
    emit('update:modelValue', next.join('').replace(/\s+$/, '').replace(/\s/g, ''))
    return add.length
}

function onInput(i, e) {
    if (e.target.value === '') return clearAt(i) // Backspace/Delete on a filled box
    // No gaps: typing past the end lands right after the last digit.
    const start = Math.min(i, props.modelValue.length)
    // A filled box gets the newly typed digit; longer input (autofill/paste) fills onward.
    const old = digits.value[i]
    const text = old && e.target.value.length === 2 ? e.target.value.replace(old, '') || old : e.target.value
    const placed = fill(start, text)
    // Keep the box at one digit even when the value didn't change (Vue won't re-render then).
    e.target.value = placed ? text.replace(/\D/g, '').charAt(0) : (digits.value[i] ?? '')
    if (placed) focusBox(start + placed)
}

function onKeydown(i, e) {
    if (e.key === 'Backspace' && !digits.value[i]) { e.preventDefault(); clearAt(i - 1); focusBox(i - 1) }
    else if (e.key === 'ArrowLeft') { e.preventDefault(); focusBox(i - 1) }
    else if (e.key === 'ArrowRight') { e.preventDefault(); focusBox(i + 1) }
}

function clearAt(i) {
    if (i < 0) return
    emit('update:modelValue', props.modelValue.slice(0, i) + props.modelValue.slice(i + 1))
}

function onPaste(e) {
    const placed = fill(0, e.clipboardData?.getData('text') ?? '')
    focusBox(placed)
}
</script>
