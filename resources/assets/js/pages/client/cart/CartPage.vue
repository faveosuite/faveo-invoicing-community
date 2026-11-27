<template>
  <div class="main shop pb-4">
    <div class="container pt-4">

      <!-- Loading -->
      <div v-if="cartStore.loading && !cartStore.items.length" class="row justify-content-center py-3" role="status" aria-busy="true">
        <loader /><span class="visually-hidden">{{ __('message.cart_loading') }}</span>
      </div>

      <!-- Error -->
      <div v-else-if="cartStore.error"
           class="d-flex flex-column align-items-center justify-content-center text-center py-5">
        <i class="fas fa-exclamation-circle fa-3x text-danger mb-3 d-block" aria-hidden="true"></i>
        <p class="text-color-grey mb-4" role="alert">{{ cartStore.error }}</p>
        <router-link to="/store" class="btn btn-primary btn-modern btn-px-4 py-3">
          {{ __('message.browse_products') }}
        </router-link>
      </div>

      <!-- Empty -->
      <div v-else-if="cartStore.items.length === 0"
           class="d-flex flex-column align-items-center justify-content-center text-center py-5">
        <i class="fas fa-shopping-cart fa-3x text-color-grey mb-3 d-block" aria-hidden="true"></i>
        <p class="text-color-grey mb-4">{{ __('message.cart_empty') }}</p>
        <router-link to="/store" class="btn btn-primary btn-modern btn-px-4 py-3">
          {{ __('message.browse_products') }}
        </router-link>
      </div>

      <!-- Cart -->
      <div v-else class="pb-5">
        <div class="row g-4">
          <!-- Items -->
          <div class="col-lg-8">
            <section class="co-card" aria-labelledby="cart-h">
              <div class="co-card-header">
                <h2 id="cart-h" class="co-title">{{ __('message.cart_your_cart') }}</h2>
                <span class="co-note">{{ cartStore.items.length === 1 ? __('message.cart_one_plan') : __('message.cart_n_plans', { count: cartStore.items.length }) }}</span>
              </div>
              <ul class="list-unstyled mb-0">
                <CartItemRow
                  v-for="item in cartStore.items"
                  :key="item.id"
                  :item="item"
                  @customize="customizeId = $event"
                  @remove="removingId = $event"
                />
              </ul>
            </section>
            <router-link to="/store" class="co-link mt-3">
              <i class="fas fa-arrow-left" aria-hidden="true"></i>{{ __('message.cart_continue_shopping') }}
            </router-link>
          </div>

          <!-- Order summary -->
          <div class="col-lg-4">
            <aside class="co-card co-summary" aria-labelledby="sum-h">
              <div class="co-card-header"><h2 id="sum-h" class="co-title">{{ __('message.cart_totals') }}</h2></div>
              <div class="co-card-body" :class="{ 'co-busy': cartStore.loading }" :aria-busy="cartStore.loading">
                <div v-for="item in cartStore.items" :key="item.id" class="co-row">
                  <span class="min-w-0">
                    <span class="d-block fw-semibold text-color-dark">{{ item.name }}<template v-if="item.quantity > 1"> × {{ item.quantity }}</template></span>
                    <span class="co-note">{{ addonText(item) }}</span>
                  </span>
                  <span class="co-amt">{{ symbol }}{{ item.line_total }}</span>
                </div>
                <!-- Subtotal only differs from the total when a coupon takes something off -->
                <div v-if="hasDiscount" class="co-row">
                  <span>{{ __('message.subtotal') }}</span>
                  <span class="co-amt">{{ symbol }}{{ cartStore.subtotal }}</span>
                </div>
                <div v-if="hasDiscount" class="co-row is-discount">
                  <span>{{ __('message.coupon') }}: <strong>{{ cartStore.couponCode }}</strong></span>
                  <span class="co-amt">−{{ symbol }}{{ cartStore.couponDiscount }}</span>
                </div>
                <div class="co-row is-total">
                  <span>{{ __('message.total') }}</span>
                  <span class="co-amt">{{ symbol }}{{ cartStore.total }}</span>
                </div>
                <p class="co-note mb-3">{{ __('message.cart_tax_note') }}</p>
                <button type="button" class="btn btn-primary btn-modern w-100 text-uppercase py-3 checkout" @click="checkout">
                  <i class="fas fa-lock me-2" aria-hidden="true"></i>{{ __('message.proceed_checkout') }}
                </button>
              </div>
            </aside>
          </div>
        </div>

      </div>
    </div>

    <CartCustomizeModal :item="customizing" :busy="cartStore.loading"
                        @close="customizeId = null" @update="onUpdate" />
    <CartDomainModal :show="showDomainModal" :items="domainItems" :error="domainError"
                     @close="showDomainModal = false" @save="saveDomains" />

    <!-- Removing a plan takes its add-ons and settings with it: ask first -->
    <Modal :showModal="!!removing" :onClose="() => (removingId = null)" :showCloseBtn="false" classname="modal-md">
      <template #title><h2 class="modal-title h5 mb-0">{{ __('message.cart_remove_title', { name: removing?.name ?? '' }) }}</h2></template>
      <template #fields><p class="mb-0">{{ __('message.cart_remove_body') }}</p></template>
      <template #controls>
        <action-button action="cancel" @click="removingId = null" />
        <action-button action="remove" :label="__('message.cart_remove_confirm')" :loading="cartStore.loading" @click="confirmRemove" />
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useCartStore } from '@/core/stores/cart'
import { useAlertStore } from '@/core/stores/alert'
import { __ } from '@/plugins/i18n'
import CartItemRow from '@/themes/porto/components/cart/CartItemRow.vue'
import CartDomainModal from '@/themes/porto/components/cart/CartDomainModal.vue'
import CartCustomizeModal from '@/themes/porto/components/cart/CartCustomizeModal.vue'
import Modal from '@/themes/porto/components/common/Modal.vue'
import { useAuthStore } from '@/core/stores/auth'

const router     = useRouter()
const route      = useRoute()
const cartStore  = useCartStore()

// Removing the last item swaps the table for the short "empty" message — bring it into view
// instead of leaving the user scrolled down past it.
watch(() => cartStore.items.length === 0, (empty) => { if (empty) window.scrollTo({ top: 0 }) })
const alertStore = useAlertStore()
const authStore  = useAuthStore()

const symbol = computed(() => cartStore.currencySymbol)

// Money comes formatted ("1,000.00"): strip separators before comparing.
const hasDiscount = computed(() => (parseFloat(String(cartStore.couponDiscount).replace(/,/g, '')) || 0) > 0)

onMounted(async () => {
    const productId = route.query.id
    if (productId) {
        try {
            await cartStore.addItem({ product_id: Number(productId) })
        } catch {
            await cartStore.fetchCart()
        }
        router.replace({ path: '/cart' })
    } else {
        await cartStore.fetchCart()
    }
})

// How many add-ons a plan holds (order summary), in words: "Plan only" / "Plan + 1 add-on" / "Plan + 3 add-ons".
const addonCount = item => (item.extras ?? []).filter(e => e.quantity > 0).length
function addonText(item) {
  const n = addonCount(item)
  return n === 0 ? __('message.cart_plan_only') : n === 1 ? __('message.cart_plan_with_addon') : __('message.cart_plan_with_addons', { count: n })
}

// The plan waiting for the admin's "Remove" confirmation.
const removingId = ref(null)
const removing = computed(() => cartStore.items.find(i => i.id === removingId.value) ?? null)
async function confirmRemove() {
  await cartStore.removeItem(removingId.value)
  removingId.value = null
}

// The line open in the Customize panel (read live from the store so prices update in place).
const customizeId = ref(null)
const customizing = computed(() => cartStore.items.find(i => i.id === customizeId.value) ?? null)

// Products whose license is locked to a domain: asked in a modal before checkout.
const domainItems = computed(() => cartStore.items.filter(item => item.require_domain))
const showDomainModal = ref(false)

function checkout() {
  if (!authStore.isAuthenticated) {
    // Back to the cart after login (it handles the domain prompt before checkout).
    router.push({ path: '/login', query: { redirect: '/cart' } }).then(() => {
      alertStore.setAlert({
        message: __('message.please_login_to_checkout'),
        type: 'warning',
        component_name: 'client-page',
      })
    })
    return
  }
  if (domainItems.value.length) {
    showDomainModal.value = true
    return
  }
  router.push('/checkout')
}

// { [itemId]: domain } from the modal: save the changed ones, then continue. A refused domain
// stays in the modal (next to the fields), instead of swapping the whole cart for the error screen.
const domainError = ref('')
watch(showDomainModal, open => { if (open) domainError.value = '' })
async function saveDomains(domains) {
  domainError.value = ''
  for (const item of domainItems.value) {
    if (domains[item.id] === item.domain) continue
    await cartStore.updateItem(item.id, { domain: domains[item.id] })
    if (cartStore.error) {
      domainError.value = cartStore.error
      cartStore.error = null // the cart itself is unchanged: keep showing it
      return
    }
  }
  showDomainModal.value = false
  router.push('/checkout')
}

// The Customize pop-up emits { id, quantity | agents | extras }.
function onUpdate({ id, ...payload }) {
  cartStore.updateItem(id, payload)
}
</script>

<style scoped>
.min-w-0 { min-width: 0; }
</style>
