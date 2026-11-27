import { computed, toValue } from 'vue'

// What a cart line offers: shared by the cart card and the Customize pop-up.
export function useCartLine(source) {
  const item = computed(() => toValue(source) ?? {})

  // A number the plan already gives some of (Storage: 10 included) and sells more of.
  const isTopUp = extra => extra.type === 'number' && extra.included != null

  const chosen = computed(() => (item.value.extras ?? []).filter(e => e.quantity > 0))
  const offers = computed(() => (item.value.extras ?? []).filter(e => e.quantity === 0))

  const customizable = computed(() => !!(item.value.can_modify_quantity || item.value.extras?.length))

  return { chosen, offers, customizable, isTopUp }
}
