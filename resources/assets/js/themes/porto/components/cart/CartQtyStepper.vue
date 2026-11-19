<template>
  <!-- − value + (Bootstrap input group) -->
  <div class="input-group input-group-sm co-stepper">
    <button type="button" class="btn btn-outline-secondary"
            :aria-label="`${__('message.decrease')} ${label}`"
            :disabled="value <= min"
            @click="emit('change', value - 1)">−</button>
    <input type="text" class="form-control text-center" :value="value" :aria-label="label" readonly>
    <button type="button" class="btn btn-outline-secondary"
            :aria-label="`${__('message.increase')} ${label}`"
            @click="emit('change', value + 1)">+</button>
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

<style scoped>
.co-stepper { width: 128px; flex-wrap: nowrap; }
.co-stepper .btn { width: 36px; min-height: 36px; padding: 0; border-color: #ced4da; color: var(--dark); background: #fff; }
.co-stepper .btn:hover:not(:disabled) { background: #f4f4f4; }
.co-stepper .form-control { background: #fff; border-color: #ced4da; font-weight: 600; }
</style>
