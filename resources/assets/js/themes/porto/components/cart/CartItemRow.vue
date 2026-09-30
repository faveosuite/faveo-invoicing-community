<template>
  <tr class="cart_table_item">
    <td class="product-thumbnail">
      <div class="product-thumbnail-wrapper">
        <a href="#" class="product-thumbnail-remove" :title="__('message.remove')" @click.prevent="$emit('remove', item.id)">
          <i class="fas fa-times"></i>
        </a>
        <span class="product-thumbnail-image d-inline-block">
          <img v-if="item.image" :src="item.image" width="90" height="90" alt="" class="img-fluid" />
          <span v-else class="d-inline-flex align-items-center justify-content-center bg-light product-placeholder">
            <i class="fas fa-box text-color-grey fa-2x"></i>
          </span>
        </span>
      </div>
    </td>
    <td class="product-name">
      <span class="font-weight-semi-bold text-color-dark">{{ item.name }}</span>
    </td>
    <td class="product-price">
      <span class="amount font-weight-medium text-color-grey">{{ item.currency_symbol }}{{ item.unit_price }}</span>
    </td>

    <!-- Quantity: editable only when the product allows it -->
    <td class="product-quantity">
      <div v-if="item.can_modify_quantity" class="quantity float-none m-0">
        <input type="button" class="minus text-color-hover-light bg-color-hover-primary border-color-hover-primary"
               value="-" :disabled="item.quantity <= 1" @click="step('quantity', -1)">
        <input type="text" class="input-text qty text" :value="item.quantity" readonly>
        <input type="button" class="plus text-color-hover-light bg-color-hover-primary border-color-hover-primary"
               value="+" @click="step('quantity', 1)">
      </div>
      <span v-else class="text-color-dark">{{ item.quantity }}</span>
    </td>

    <!-- Agents: "Unlimited" when 0, editable only when the product allows it -->
    <td class="product-quantity">
      <template v-if="!item.show_agent">
        <span class="text-color-grey">—</span>
      </template>
      <template v-else-if="!item.agents">
        <span class="text-color-grey">{{ __('message.unlimited_agents') }}</span>
      </template>
      <div v-else-if="item.can_modify_agent" class="quantity float-none m-0">
        <input type="button" class="minus text-color-hover-light bg-color-hover-primary border-color-hover-primary"
               value="-" :disabled="item.agents <= 1" @click="step('agents', -1)">
        <input type="text" class="input-text qty text" :value="item.agents" readonly>
        <input type="button" class="plus text-color-hover-light bg-color-hover-primary border-color-hover-primary"
               value="+" @click="step('agents', 1)">
      </div>
      <span v-else class="text-color-dark">{{ item.agents }}</span>
    </td>

    <td class="product-subtotal text-end">
      <span class="amount text-color-dark font-weight-bold text-4">{{ item.currency_symbol }}{{ item.line_total }}</span>
    </td>
  </tr>
</template>

<script setup>
import { __ } from '@/plugins/i18n'

const props = defineProps({ item: { type: Object, required: true } })
const emit = defineEmits(['update', 'remove'])

// Increment/decrement either `quantity` or `agents`; never below 1.
function step(field, delta) {
  const next = props.item[field] + delta
  if (next < 1) return
  emit('update', { id: props.item.id, [field]: next })
}
</script>

<style scoped>
.product-placeholder { width: 90px; height: 90px; }
</style>
