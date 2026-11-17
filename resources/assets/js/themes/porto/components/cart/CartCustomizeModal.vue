<template>
  <!-- Same look as the cart: rows split by lines, label | control | amount in fixed columns -->
  <Modal :showModal="!!item" :onClose="() => emit('close')" :showCloseBtn="false" closeOnBackdrop
         classname="modal-lg modal-dialog-scrollable" footerClass="d-block">
    <template #title>
      <h4 class="font-weight-bold text-uppercase text-4 mb-0">{{ item?.name }}</h4>
      <div v-if="item" class="text-2 text-color-grey">
        {{ __('message.cart_customize_title') }}<template v-if="item.period"> · {{ item.period }}</template>
      </div>
    </template>

    <template #fields>
      <!-- .shop: Porto styles its quantity stepper only inside it (the modal sits outside the page) -->
      <fieldset v-if="item" class="shop" :disabled="busy">
        <!-- Plan -->
        <strong class="d-block text-color-dark text-uppercase">{{ __('message.plan') }}</strong>
        <div class="opt-row">
          <span>
            <strong class="d-block text-color-dark">{{ __('message.cart_base_plan') }}</strong>
            <span v-if="item.period" class="d-block text-2 text-color-grey">{{ item.period }}</span>
          </span>
          <span class="opt-ctrl">
            <CartQtyStepper v-if="item.can_modify_quantity" :value="item.quantity" :label="__('message.quantity')" @change="n => n >= 1 && update({ quantity: n })" />
          </span>
          <span class="opt-amount amount">{{ item.currency_symbol }}{{ item.plan_total }}</span>
        </div>
        <div v-if="canChooseAgents" class="opt-row">
          <span>
            <strong class="d-block text-color-dark">{{ __('message.agents') }}</strong>
            <span class="d-block text-2 text-color-grey">
              {{ agentsNote }}<template v-if="item.agent_price"> · {{ item.currency_symbol }}{{ item.agent_price }} {{ __('message.extra_each') }}</template>
            </span>
          </span>
          <span class="opt-ctrl">
            <CartQtyStepper :value="item.agents" :label="__('message.agents')" @change="n => n >= 1 && update({ agents: n })" />
          </span>
          <span class="opt-amount amount" :class="{ 'text-success': !item.agents_total }">{{ agentsAmount }}</span>
        </div>

        <!-- Add-ons: tick to add; unit ones then get a stepper -->
        <template v-if="extras.length">
          <strong class="d-block text-color-dark text-uppercase mt-4">{{ __('message.addons') }}</strong>
          <div v-for="extra in extras" :key="extra.entitlement_id" class="opt-row">
            <div class="form-check mb-0">
              <input :id="tickId(extra)" class="form-check-input" type="checkbox"
                     :checked="extra.quantity > 0" @change="setExtra(extra, $event.target.checked ? 1 : 0)">
              <label class="form-check-label d-block" :for="tickId(extra)">
                <strong class="d-block text-color-dark">{{ extra.label }}</strong>
                <span class="d-block text-2 text-color-grey">
                  {{ extra.type === 'number'
                    ? __('message.extra_price_unit', { price: item.currency_symbol + extra.unit_price })
                    : __('message.extra_price_feature', { price: item.currency_symbol + extra.unit_price }) }}
                </span>
              </label>
            </div>
            <span class="opt-ctrl">
              <CartQtyStepper v-if="extra.type === 'number' && extra.quantity > 0" :value="extra.quantity" :label="extra.label" @change="n => n >= 1 && setExtra(extra, n)" />
            </span>
            <span class="opt-amount amount">
              {{ extra.quantity ? `${item.currency_symbol}${extra.line_total}` : '' }}
            </span>
          </div>
        </template>
      </fieldset>
    </template>

    <!-- Total + done, same as the cart's order summary -->
    <template #controls>
      <div v-if="item" class="m-0">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <strong class="text-color-dark text-4">{{ __('message.cart_plan_total') }}</strong>
          <span>
            <i v-if="busy" class="fas fa-spinner fa-spin text-color-grey me-2" aria-hidden="true"></i>
            <strong class="text-color-dark text-4">{{ item.currency_symbol }}{{ item.line_total }}</strong>
          </span>
        </div>
        <button type="button" class="btn btn-dark btn-modern w-100 text-uppercase border-radius-0 text-3 py-3" @click="emit('close')">{{ __('message.done') }}</button>
      </div>
    </template>
  </Modal>
</template>

<script setup>
import { computed } from 'vue'
import { __ } from '@/plugins/i18n'
import Modal from '../common/Modal.vue'
import CartQtyStepper from './CartQtyStepper.vue'
import { useCartLine } from './useCartLine'

// item: the live cart line (null = closed). Every change is saved and re-priced by the server at once.
const props = defineProps({
  item: { type: Object, default: null },
  busy: { type: Boolean, default: false },
})
const emit = defineEmits(['close', 'update'])

const { canChooseAgents, agentsAmount, agentsNote } = useCartLine(() => props.item)

// Unit add-ons first (they carry a stepper), then on/off ones.
const extras = computed(() => [...(props.item?.extras ?? [])].sort((a, b) => (b.type === 'number') - (a.type === 'number')))

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
/* One grid for every row so steppers and amounts line up down the list. */
.opt-row { display: grid; grid-template-columns: minmax(0, 1fr) auto 7.5rem; align-items: center; gap: .5rem 1rem; padding: 1rem 0; border-bottom: 1px solid rgba(0, 0, 0, .06); }
.opt-ctrl { display: flex; justify-content: flex-end; }
.opt-amount { text-align: right; white-space: nowrap; color: var(--dark); font-weight: 600; }
.form-check-input { cursor: pointer; }
.form-check-input:checked { background-color: var(--primary); border-color: var(--primary); }
.form-check-label { cursor: pointer; }
@media (max-width: 575px) {
  /* Phones: label and amount on top, the stepper (if any) under the label */
  .opt-row { grid-template-columns: minmax(0, 1fr) auto; grid-template-areas: "label amount" "ctrl ctrl"; }
  .opt-row > :first-child { grid-area: label; }
  .opt-amount { grid-area: amount; align-self: start; }
  .opt-ctrl { grid-area: ctrl; justify-content: flex-start; }
  .opt-ctrl:empty { display: none; }
}
</style>
