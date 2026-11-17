<template>
  <div class="main shop pb-4">
    <div class="container pt-4">

      <!-- Loading -->
      <div v-if="cartStore.loading && !cartStore.items.length" class="row justify-content-center py-3"><loader /></div>

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
      <div v-else class="row pb-4 mb-5">

        <!-- Items: one card per plan -->
        <div class="col-lg-8 mb-4 mb-lg-0">
          <CartItemRow
            v-for="item in cartStore.items"
            :key="item.id"
            :item="item"
            @customize="customizeId = $event"
            @remove="cartStore.removeItem($event)"
          />
        </div>
        <!-- Cart totals (same look as the checkout summary) -->
        <div class="col-lg-4 position-relative">
          <div class="card border-width-3 border-radius-0">
            <div class="card-body">
              <h4 class="font-weight-bold text-uppercase text-4 mb-0 pb-3 border-bottom">{{ __('message.cart_totals') }}</h4>

              <!-- Each plan and what it holds -->
              <div v-for="item in cartStore.items" :key="item.id" class="d-flex justify-content-between gap-3 py-3 border-bottom">
                <span class="min-w-0">
                  <strong class="d-block text-color-dark">{{ item.name }}</strong>
                  <span class="d-block text-2 text-color-grey">{{ addonCount(item) ? __('message.cart_plan_with_addons', { count: addonCount(item) }) : __('message.cart_plan_only') }}</span>
                </span>
                <span class="amount font-weight-medium text-nowrap">{{ symbol }}{{ item.line_total }}</span>
              </div>

              <!-- Subtotal only differs from the total when a coupon takes something off -->
              <div v-if="hasDiscount" class="d-flex justify-content-between py-3 border-bottom">
                <strong class="text-color-dark">{{ __('message.subtotal') }}</strong>
                <span class="amount font-weight-medium">{{ symbol }}{{ cartStore.subtotal }}</span>
              </div>
              <div v-if="hasDiscount" class="d-flex justify-content-between py-3 border-bottom">
                <strong class="text-color-dark">{{ __('message.discount') }}</strong>
                <span class="amount text-success">−{{ symbol }}{{ cartStore.couponDiscount }}</span>
              </div>
              <div class="d-flex justify-content-between py-3 border-bottom mb-4">
                <strong class="text-color-dark text-4">{{ __('message.total') }}</strong>
                <strong class="text-color-dark text-4">{{ symbol }}{{ cartStore.total }}</strong>
              </div>

              <button type="button" class="btn btn-dark btn-modern w-100 text-uppercase border-radius-0 text-3 py-3 checkout" @click="checkout">
                {{ __('message.checkout') }} <i class="fas fa-arrow-right ms-2" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>

    <CartCustomizeModal :item="customizing" :busy="cartStore.loading"
                        @close="customizeId = null" @update="onUpdate" />
    <CartDomainModal :show="showDomainModal" :items="domainItems"
                     @close="showDomainModal = false" @save="saveDomains" />
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

// How many add-ons a plan holds (order summary).
const addonCount = item => (item.extras ?? []).filter(e => e.quantity > 0).length

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

// { [itemId]: domain } from the modal: save the changed ones, then continue.
async function saveDomains(domains) {
  for (const item of domainItems.value) {
    if (domains[item.id] !== item.domain) await cartStore.updateItem(item.id, { domain: domains[item.id] })
  }
  showDomainModal.value = false
  if (!cartStore.error) router.push('/checkout')
}

// The Customize pop-up emits { id, quantity | agents | extras }.
function onUpdate({ id, ...payload }) {
  cartStore.updateItem(id, payload)
}
</script>

<style scoped>
.min-w-0 { min-width: 0; }
</style>
