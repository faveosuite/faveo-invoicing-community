<template>
  <!-- Customize one cart plan. Every change saves at once and is re-priced by the server. -->
  <Modal :showModal="!!item" :onClose="() => emit('close')" :showCloseBtn="false" closeOnBackdrop
         classname="modal-dialog-scrollable co-modal" footerClass="justify-content-between">
    <template #title>
      <h2 class="modal-title h5 mb-0">{{ __('message.cart_customize_title') }}</h2>
      <p v-if="item" class="co-note mb-0">{{ item.name }}<template v-if="item.period"> · {{ item.period }}</template></p>
    </template>

    <template #fields>
      <fieldset v-if="item" :disabled="busy">
        <!-- Quantity -->
        <div v-if="item.can_modify_quantity" class="opt-row">
          <span class="fw-semibold text-color-dark">{{ __('message.quantity') }}</span>
          <CartQtyStepper :value="item.quantity" :label="__('message.quantity')" @change="n => n >= 1 && update({ quantity: n })" />
        </div>

        <!-- Agents -->
        <div v-if="canChooseAgents" class="opt-row">
          <span>
            <span class="d-block fw-semibold text-color-dark">{{ __('message.agents') }}</span>
            <span class="co-note">
              {{ agentsNote }}<template v-if="item.agent_price"> · {{ __('message.cart_per_extra_agent', { price: item.currency_symbol + item.agent_price }) }}</template>
            </span>
          </span>
          <CartQtyStepper :value="item.agents" :label="__('message.agents')" @change="n => n >= 1 && update({ agents: n })" />
        </div>

        <!-- Add-ons -->
        <div v-if="!authStore.isAuthenticated" class="alert alert-light border d-flex gap-2 align-items-start mt-3 mb-0">
          <i class="fas fa-info-circle mt-1" aria-hidden="true"></i>
          <span><router-link to="/login" class="fw-semibold" @click="emit('close')">{{ __('message.login') }}</router-link> {{ __('message.cart_login_for_addons') }}</span>
        </div>
        <template v-else-if="extras.length">
          <p class="fw-semibold text-color-dark mt-3 mb-0">{{ __('message.addons') }}</p>
          <p class="co-note mb-1">{{ __('message.cart_addons_billed') }}</p>
          <div v-for="extra in extras" :key="extra.entitlement_id" class="opt-row">
            <div class="form-check mb-0">
              <input :id="tickId(extra)" class="form-check-input" type="checkbox"
                     :checked="extra.quantity > 0" @change="setExtra(extra, $event.target.checked ? 1 : 0)">
              <label class="form-check-label fw-semibold text-color-dark" :for="tickId(extra)">{{ extra.label }}</label>
              <div class="co-note">
                {{ extra.type === 'number'
                  ? __('message.extra_price_unit', { price: item.currency_symbol + extra.unit_price })
                  : __('message.extra_price_feature', { price: item.currency_symbol + extra.unit_price }) }}
              </div>
            </div>
            <CartQtyStepper v-if="extra.type === 'number' && extra.quantity > 0" :value="extra.quantity" :label="extra.label" @change="n => n >= 1 && setExtra(extra, n)" />
          </div>
        </template>
      </fieldset>
    </template>

    <!-- Live total + done -->
    <template #controls>
      <div v-if="item" class="m-0" aria-live="polite" :aria-busy="busy">
        <div class="co-note">{{ __('message.cart_plan_total') }}</div>
        <div class="d-flex align-items-center gap-2">
          <strong class="text-color-dark text-5" :class="{ 'co-busy': busy }">{{ item.currency_symbol }}{{ item.line_total }}</strong>
          <span v-if="busy" class="co-note d-inline-flex align-items-center gap-1">
            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>{{ __('message.saving') }}
          </span>
        </div>
      </div>
      <button type="button" class="btn btn-primary btn-modern text-uppercase px-4 m-0" @click="emit('close')">{{ __('message.done') }}</button>
    </template>
  </Modal>
</template>

<script setup>
import { computed } from 'vue'
import { __ } from '@/plugins/i18n'
import { useAuthStore } from '@/core/stores/auth'
import Modal from '../common/Modal.vue'
import CartQtyStepper from './CartQtyStepper.vue'
import { useCartLine } from './useCartLine'

// item: the live cart line (null = closed). Every change is saved and re-priced by the server at once.
const props = defineProps({
  item: { type: Object, default: null },
  busy: { type: Boolean, default: false },
})
const emit = defineEmits(['close', 'update'])

const { canChooseAgents, agentsNote } = useCartLine(() => props.item)

// Unit add-ons first (they carry a stepper), then on/off ones.
const extras = computed(() => [...(props.item?.extras ?? [])].sort((a, b) => (b.type === 'number') - (a.type === 'number')))

// Guests can change quantity/agents only; add-ons need an account.
const authStore = useAuthStore()

const tickId = extra => `extra-${props.item.id}-${extra.entitlement_id}`

function update(payload) {
  emit('update', { id: props.item.id, ...payload })
}

// 0 removes the add-on.
function setExtra(extra, quantity) {
  update({ extras: { [extra.entitlement_id]: quantity } })
}
</script>

<style scoped>
.opt-row { display: flex; justify-content: space-between; align-items: center; gap: 1rem; min-height: 64px; padding: .75rem 0; border-bottom: 1px solid rgba(0, 0, 0, .06); }
.form-check-input { cursor: pointer; }
.form-check-input:checked { background-color: var(--primary); border-color: var(--primary); }
.form-check-label { cursor: pointer; }
</style>

<style>
.modal-dialog.co-modal { max-width: 560px; }
</style>
