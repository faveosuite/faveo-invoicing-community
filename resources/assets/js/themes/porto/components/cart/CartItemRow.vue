<template>
  <!-- One plan in the cart, styled like the order summary next to it. Changes happen in the Customize panel. -->
  <div class="card border-width-3 border-radius-0 mb-4">
    <div class="card-body">
      <!-- Header: product + price -->
      <div class="d-flex align-items-center gap-3 pb-3 border-bottom">
        <img v-if="item.image" :src="item.image" width="48" height="48" alt="" class="img-fluid thumb flex-shrink-0" />
        <span v-else class="d-inline-flex align-items-center justify-content-center bg-light thumb flex-shrink-0" aria-hidden="true">
          <i class="fas fa-box text-color-grey"></i>
        </span>
        <div class="flex-grow-1 min-w-0">
          <h4 class="font-weight-bold text-uppercase text-4 mb-0">{{ item.name }}</h4>
          <span class="d-block text-2 text-color-grey">
            {{ meta }}
          </span>
        </div>
        <strong class="text-color-dark text-4 text-nowrap">{{ item.currency_symbol }}{{ item.line_total }}</strong>
      </div>

      <!-- Breakdown: only when the price is made of more than the plan -->
      <template v-if="hasBreakdown">
        <div class="d-flex justify-content-between py-3 border-bottom">
          <strong class="text-color-dark">{{ __('message.cart_base_plan') }}<span v-if="item.quantity > 1" class="text-color-grey fw-normal"> × {{ item.quantity }}</span></strong>
          <span class="amount font-weight-medium">{{ item.currency_symbol }}{{ item.plan_total ?? item.line_total }}</span>
        </div>
        <div v-if="item.show_agent && item.agents_total" class="d-flex justify-content-between py-3 border-bottom">
          <strong class="text-color-dark">{{ __('message.agents') }}<span class="text-color-grey fw-normal"> ({{ agentsText }})</span></strong>
          <span class="amount font-weight-medium">{{ agentsAmount }}</span>
        </div>
        <template v-if="chosen.length > 3">
          <div class="d-flex justify-content-between py-3 border-bottom">
            <strong class="text-color-dark">
              {{ __('message.cart_addons_count', { count: chosen.length }) }}
              <button type="button" class="btn btn-link p-0 ms-2 text-2 align-baseline" :aria-expanded="showAddons" @click="showAddons = !showAddons">
                {{ showAddons ? __('message.show_less') : __('message.show_more') }}
                <i class="fas ms-1" :class="showAddons ? 'fa-chevron-up' : 'fa-chevron-down'" aria-hidden="true"></i>
              </button>
            </strong>
            <span class="amount font-weight-medium">{{ item.currency_symbol }}{{ item.extras_total }}</span>
          </div>
          <template v-if="showAddons">
            <div v-for="extra in chosen" :key="extra.entitlement_id" class="d-flex justify-content-between py-2 ps-3 border-bottom text-2 text-color-grey">
              <span>{{ extra.label }}<template v-if="extra.type === 'number'"> × {{ extra.quantity }}</template></span>
              <span class="amount">{{ item.currency_symbol }}{{ extra.line_total }}</span>
            </div>
          </template>
        </template>
        <template v-else>
          <div v-for="extra in chosen" :key="extra.entitlement_id" class="d-flex justify-content-between py-3 border-bottom">
            <strong class="text-color-dark">
              {{ extra.label }}
              <span class="text-color-grey fw-normal">({{ extra.type === 'number' ? `${extra.quantity} × ${item.currency_symbol}${extra.unit_price}` : __('message.cart_addon') }})</span>
            </strong>
            <span class="amount font-weight-medium">{{ item.currency_symbol }}{{ extra.line_total }}</span>
          </div>
        </template>
      </template>

      <!-- Actions -->
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3">
        <span v-if="customizable" class="d-flex flex-wrap align-items-center gap-3">
          <button type="button" class="btn btn-light btn-modern text-2 text-uppercase border-radius-0" @click="emit('customize', item.id)">
            <i class="fas fa-sliders-h me-1" aria-hidden="true"></i>{{ __('message.cart_customize') }}
          </button>
          <span class="text-2 text-color-grey">{{ chosen.length ? __('message.cart_n_addons', { count: chosen.length }) : __('message.cart_customize_hint') }}</span>
        </span>
        <span v-else></span>
        <button type="button" class="btn btn-link btn-remove text-decoration-none text-2 p-0" :aria-label="`${__('message.remove')} ${item.name}`" @click="emit('remove', item.id)">
          <i class="far fa-trash-alt me-1" aria-hidden="true"></i>{{ __('message.remove') }}
        </button>
      </div>
    </div>
  </div>
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

// "One Time · 1 agent · × 2" under the product name.
const meta = computed(() => [
  props.item.period,
  props.item.show_agent ? agentsText.value : null,
  props.item.quantity > 1 ? `× ${props.item.quantity}` : null,
].filter(Boolean).join(' · '))

// With nothing but the plan, the header price already says it all.
const hasBreakdown = computed(() => props.item.quantity > 1 || !!props.item.agents_total || chosen.value.length > 0)
</script>

<style scoped>
.thumb { width: 48px; height: 48px; object-fit: contain; }
.btn-remove { color: #777; }
.btn-remove:hover, .btn-remove:focus-visible { color: var(--bs-danger, #dc3545); }
.min-w-0 { min-width: 0; }
</style>
