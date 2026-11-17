<template>
  <!-- Porto's quantity stepper, as used across the cart -->
  <div class="quantity float-none m-0">
    <input type="button"
           class="minus text-color-hover-light bg-color-hover-primary border-color-hover-primary"
           value="−"
           :aria-label="__('message.decrease')"
           :disabled="value <= min"
           @click="emit('change', value - 1)">
    <input type="text"
           class="input-text qty text"
           :value="value"
           :aria-label="label"
           readonly>
    <input type="button"
           class="plus text-color-hover-light bg-color-hover-primary border-color-hover-primary"
           value="+"
           :aria-label="__('message.increase')"
           @click="emit('change', value + 1)">
  </div>
</template>

<script setup>
import { __ } from '@/plugins/i18n'

defineProps({
  value: { type: Number, required: true },
  min:   { type: Number, default: 1 },
  label: { type: String, default: '' },
})
const emit = defineEmits(['change'])
</script>
