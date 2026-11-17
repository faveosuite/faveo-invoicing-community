import { computed, toValue } from 'vue'
import { __ } from '@/plugins/i18n'

// What a cart line offers: shared by the cart card and the Customize pop-up.
export function useCartLine(source) {
  const item = computed(() => toValue(source) ?? {})

  // Agents get a stepper only when the product is priced per agent and the customer may change them.
  const canChooseAgents = computed(() => item.value.show_agent && item.value.can_modify_agent && item.value.agents > 0)

  // What the agents above (or below) the included ones add to the plan.
  const agentsAmount = computed(() => (item.value.agents_total
    ? `${item.value.agents_fewer ? '−' : '+'}${item.value.currency_symbol}${item.value.agents_total}`
    : __('message.cart_included')))

  const agentsNote = computed(() => (item.value.included_agents === 0
    ? __('message.cart_every_agent_paid')
    : __('message.cart_agents_included', { count: item.value.included_agents })))

  const chosen = computed(() => (item.value.extras ?? []).filter(e => e.quantity > 0))
  const offers = computed(() => (item.value.extras ?? []).filter(e => e.quantity === 0))

  const customizable = computed(() => !!(item.value.can_modify_quantity || canChooseAgents.value || item.value.extras?.length))

  return { canChooseAgents, agentsAmount, agentsNote, chosen, offers, customizable }
}
