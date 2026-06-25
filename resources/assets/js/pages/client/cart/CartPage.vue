<template>
  <div role="main" class="main shop pb-4">
    <div class="container pt-4">

      <!-- Loading -->
      <div v-if="cartStore.loading && !cartStore.items.length" class="row justify-content-center py-3"><loader /></div>

      <!-- Error -->
      <div v-else-if="cartStore.error"
           class="d-flex flex-column align-items-center justify-content-center text-center py-6">
        <i class="fas fa-exclamation-circle fa-3x text-danger mb-3 d-block"></i>
        <p class="text-color-grey mb-4">{{ cartStore.error }}</p>
        <router-link to="/store" class="btn btn-dark btn-modern text-uppercase border-radius-0 btn-px-4 py-3">
          {{ __('message.browse_products') }}
        </router-link>
      </div>

      <!-- Empty -->
      <div v-else-if="cartStore.items.length === 0"
           class="d-flex flex-column align-items-center justify-content-center text-center py-6">
        <i class="fas fa-shopping-cart fa-3x text-color-grey-lighten mb-3 d-block"></i>
        <p class="text-color-grey mb-4">{{ __('message.cart_empty') }}</p>
        <router-link to="/store" class="btn btn-dark btn-modern text-uppercase border-radius-0 btn-px-4 py-3">
          {{ __('message.browse_products') }}
        </router-link>
      </div>

      <!-- Cart -->
      <div v-else class="row pb-4 mb-5">

        <!-- Items table -->
        <div class="col-lg-8 mb-4 mb-lg-0">
          <div class="table-responsive">
            <table class="shop_table cart">
              <thead>
                <tr class="text-color-dark">
                  <th class="product-thumbnail" width="12%">&nbsp;</th>
                  <th class="product-name text-uppercase" width="27%">{{ __('message.product') }}</th>
                  <th class="product-price text-uppercase" width="13%">{{ __('message.price') }}</th>
                  <th class="product-quantity text-uppercase" width="17%">{{ __('message.quantity') }}</th>
                  <th class="product-quantity text-uppercase" width="17%">{{ __('message.agents') }}</th>
                  <th class="product-subtotal text-uppercase text-end" width="14%">{{ __('message.subtotal') }}</th>
                </tr>
              </thead>
              <tbody>
                <CartItemRow
                  v-for="item in cartStore.items"
                  :key="item.id"
                  :item="item"
                  @update="onUpdate"
                  @remove="cartStore.removeItem($event)"
                />
              </tbody>
            </table>
          </div>
        </div>

        <!-- Cart totals -->
        <div class="col-lg-4 position-relative">
          <div class="card border-width-3 border-radius-0">
            <div class="card-body">
              <h4 class="font-weight-bold text-uppercase text-4 mb-3">{{ __('message.cart_totals') }}</h4>
              <div class="table-responsive">
                <table class="shop_table cart-totals mb-4">
                <tbody>
                  <tr class="cart-subtotal">
                    <td class="border-top-0"><strong class="text-color-dark">{{ __('message.subtotal') }}</strong></td>
                    <td class="border-top-0 text-end">
                      <strong><span class="amount font-weight-medium">{{ symbol }}{{ cartStore.subtotal }}</span></strong>
                    </td>
                  </tr>
                  <tr v-if="cartStore.couponDiscount > 0" class="cart-discount">
                    <td><strong class="text-color-dark">{{ __('message.discount') }}</strong></td>
                    <td class="text-end">
                      <strong><span class="amount text-success">−{{ symbol }}{{ cartStore.couponDiscount }}</span></strong>
                    </td>
                  </tr>
                  <tr class="total">
                    <td><strong class="text-color-dark text-3-5">{{ __('message.total') }}</strong></td>
                    <td class="text-end">
                      <strong class="text-color-dark"><span class="amount text-color-dark text-5">{{ symbol }}{{ cartStore.total }}</span></strong>
                    </td>
                  </tr>
                </tbody>
                </table>
              </div>
              <div class="row justify-content-between align-items-center mx-0 flex-wrap">
                <div class="col-12 col-md-auto px-0 mb-2 mb-md-0">
                  <button type="button"
                          class="btn btn-light btn-modern w-100 text-2 text-uppercase"
                          style="background: #F4F4F4;"
                          :disabled="cartStore.loading"
                          @click="cartStore.clearCart()">
                    {{ __('message.clear_cart') }}
                  </button>
                </div>
                <div class="col-12 col-md-auto px-0">
                  <button type="button"
                          class="btn btn-dark btn-modern w-100 text-2 text-uppercase checkout"
                          @click="checkout">
                    {{ __('message.checkout') }} <i class="fas fa-arrow-right ms-2"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useCartStore } from '@/core/stores/cart'
import { useAlertStore } from '@/core/stores/alert'
import { __ } from '@/plugins/i18n'
import CartItemRow from '@/themes/porto/components/cart/CartItemRow.vue'
import { useAuthStore } from '@/core/stores/auth'

const router     = useRouter()
const route      = useRoute()
const cartStore  = useCartStore()
const alertStore = useAlertStore()
const authStore  = useAuthStore()

const symbol = computed(() => cartStore.currencySymbol)

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

function checkout() {
  if (!authStore.isAuthenticated) {
    router.push('/login').then(() => {
      alertStore.setAlert({
        message: __('message.please_login_to_checkout'),
        type: 'warning',
        component_name: 'client-page',
      })
    })
    return
  }
  router.push('/checkout')
}

// CartItemRow emits { id, quantity } or { id, agents }.
function onUpdate({ id, quantity, agents }) {
  const payload = {}
  if (quantity != null) payload.quantity = quantity
  if (agents != null) payload.agents = agents
  cartStore.updateItem(id, payload)
}
</script>
