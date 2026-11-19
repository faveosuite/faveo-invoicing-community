<template>
  <!-- One plan in the "Your cart" card. Changes happen in the Customize modal. -->
  <li class="co-item">
    <span class="co-thumb" aria-hidden="true">
      <img v-if="item.image" :src="item.image" alt="" width="56" height="56" />
      <i v-else class="fas fa-box"></i>
    </span>

    <div class="flex-grow-1 min-w-0">
      <div class="d-flex justify-content-between gap-3">
        <div class="min-w-0">
          <h3 class="co-name mb-1">{{ item.name }}</h3>
          <div class="co-meta">
            <span v-if="item.period" class="co-chip">{{ item.period }}</span>
            <span v-if="item.show_agent" class="co-chip">{{ agentsText }}</span>
            <span v-if="item.quantity > 1" class="co-chip">{{ __('message.cart_qty', { count: item.quantity }) }}</span>
          </div>
        </div>
        <div class="text-end">
          <div class="co-price">{{ item.currency_symbol }}{{ item.line_total }}</div>
          <div v-if="item.quantity > 1 && !hasBreakdown" class="co-note">{{ __('message.cart_each', { price: item.currency_symbol + item.unit_price }) }}</div>
        </div>
      </div>

      <!-- Breakdown: only when the price is made of more than the plan -->
      <div v-if="hasBreakdown" class="co-breakdown">
        <div class="co-row">
          <span>{{ __('message.cart_base_plan') }}<template v-if="item.quantity > 1"> × {{ item.quantity }}</template></span>
          <span class="co-amt">{{ item.currency_symbol }}{{ item.plan_total }}</span>
        </div>
        <div v-if="item.show_agent && item.agents_total" class="co-row" :class="{ 'is-discount': item.agents_fewer }">
          <span>{{ item.agents_fewer ? __('message.cart_fewer_agents') : __('message.cart_agents_extra') }}</span>
          <span class="co-amt">{{ agentsAmount }}</span>
        </div>
        <template v-if="chosen.length > 3 && !showAddons">
          <div class="co-row">
            <span>{{ __('message.cart_addons_count', { count: chosen.length }) }}</span>
            <span class="co-amt">+{{ item.currency_symbol }}{{ item.extras_total }}</span>
          </div>
        </template>
        <template v-else>
          <div v-for="extra in chosen" :key="extra.entitlement_id" class="co-row">
            <span>{{ extra.label }}<template v-if="extra.type === 'number'"> ({{ __('message.cart_units', { count: extra.quantity }) }})</template></span>
            <span class="co-amt">+{{ item.currency_symbol }}{{ extra.line_total }}</span>
          </div>
        </template>
        <button v-if="chosen.length > 3" type="button" class="co-link my-1" :aria-expanded="showAddons" @click="showAddons = !showAddons">
          {{ showAddons ? __('message.show_less') : __('message.show_more') }}
        </button>
      </div>

      <!-- Actions -->
      <div class="d-flex gap-4 mt-2">
        <button v-if="customizable" type="button" class="co-link" @click="emit('customize', item.id)">
          <i class="fas fa-sliders-h" aria-hidden="true"></i>{{ __('message.cart_customize') }}
        </button>
        <button type="button" class="co-link co-link-danger" @click="emit('remove', item.id)">
          <i class="far fa-trash-alt" aria-hidden="true"></i>{{ __('message.remove') }}<span class="visually-hidden"> {{ item.name }}</span>
        </button>
      </div>
    </div>
  </li>
</template>

<script setup>
import { computed, ref } from 'vue'
import { __ } from '@/plugins/i18n'
import { useCartLine } from './useCartLine'

const props = defineProps({
  item: { type: Object, required: true },
})
const emit = defineEmits(['remove', 'customize'])

const { agentsAmount, chosen, customizable } = useCartLine(() => props.item)

const showAddons = ref(false)

// agents = 0 means the plan has no agent limit.
const agentsText = computed(() => {
  const n = props.item.agents
  if (!n) return __('message.plan_agents_unlimited')
  return n === 1 ? __('message.cart_one_agent') : __('message.cart_n_agents', { count: n })
})

// With nothing but the plan, the price already says it all.
const hasBreakdown = computed(() => !!props.item.agents_total || chosen.value.length > 0)
</script>

<style scoped>
.min-w-0 { min-width: 0; }
</style>
