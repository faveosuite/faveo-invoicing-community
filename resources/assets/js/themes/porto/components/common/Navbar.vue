<template>
  <div class="header-body border-0 border-bottom-light" :class="{ 'navbar-scrolled': isScrolled }">
    <div class="header-container container-fluid p-0">
      <div class="header-row">

        <!-- Logo column -->
        <div class="header-column header-column-border-right flex-grow-0">
          <div class="header-row">
            <div id="main-logo"
                 class="header-logo p-relative m-0 d-flex align-items-center justify-content-center navbar-logo-wrapper">
              <RouterLink :to="isAuthenticated ? '/dashboard' : '/'">
                <img v-if="logoUrl" :src="logoUrl" alt="Logo"
                     class="img-fluid navbar-logo-img">
                <span v-else class="brand-text fw-bold">{{ appCompany }}</span>
              </RouterLink>
            </div>
          </div>
        </div>

        <!-- Main column -->
        <div class="header-column">

          <!-- Top info bar: phone, email, social -->
          <div class="border-bottom-light w-100 navbar-info-bar">
            <div class="hstack gap-4 px-4 py-2 font-weight-semi-bold d-none d-lg-flex">
              <div v-if="phone" class="d-none d-lg-inline-block ps-1">
                <a class="text-color-default text-color-hover-primary text-2"
                   :href="`tel:+${phoneCode} ${phone}`">
                  <i class="fas fa-phone text-4 p-relative top-2"></i>&nbsp;+{{ phoneCode }} {{ phone }}
                </a>
              </div>
              <div class="vr d-lg-inline-block opacity-2 d-none d-xl-inline-block"></div>
              <div v-if="companyEmail" class="d-none d-xl-inline-block">
                <a class="text-color-default text-color-hover-primary text-2"
                   :href="`mailto:${companyEmail}`">
                  <i class="fas fa-envelope text-4 p-relative top-2"></i>&nbsp;{{ companyEmail }}
                </a>
              </div>
              <div class="ms-auto d-none d-lg-inline-block"></div>
              <div class="vr opacity-2 d-none d-lg-inline-block"></div>
              <div class="d-none d-lg-inline-block">
                <ul class="nav nav-pills me-1">
                  <li v-for="media in socialMedia" :key="media.name" class="nav-item pe-2 mx-1">
                    <a :href="media.link" target="_blank"
                       :title="media.name"
                       class="text-color-default text-color-hover-primary text-4">
                      <i :class="`fab fa-${media.name.toLowerCase()}`"></i>
                    </a>
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Nav row -->
          <div class="header-row h-100">
            <div class="hstack h-100 w-100">
              <div class="h-100 w-100 w-xl-auto">
                <div
                    class="header-nav header-nav-links h-100 justify-content-end justify-content-lg-start me-4 me-lg-0 ms-lg-3">
                  <div
                      class="header-nav-main header-nav-main-square header-nav-main-dropdown-no-borders header-nav-main-text-capitalize header-nav-main-text-size-4 header-nav-main-arrows header-nav-main-full-width-mega-menu header-nav-main-mega-menu-bg-hover header-nav-main-effect-5">
                    <nav class="collapse">
                      <ul class="nav nav-pills" id="mainNav">

                        <!-- Store -->
                        <li class="dropdown">
                          <a class="nav-link dropdown-toggle" href="javascript:;"
                             data-bs-toggle="dropdown" aria-expanded="false">
                            &nbsp;{{ __('message.store') }}&nbsp;
                          </a>
                          <ul class="dropdown-menu border-light mt-n1">
                            <li v-for="group in productGroups" :key="group.id">
                              <RouterLink :to="'/store/' + group.id" class="dropdown-item">{{ group.name }}</RouterLink>
                            </li>
                          </ul>
                        </li>

                        <!-- My Account (authenticated) -->
                        <li v-if="isAuthenticated" class="dropdown">
                          <a class="nav-link dropdown-toggle" href="javascript:;"
                             data-bs-toggle="dropdown" aria-expanded="false">
                            &nbsp;{{ __('message.my_account') }}&nbsp;
                          </a>
                          <ul class="dropdown-menu border-light mt-n1">
                            <li v-if="isAdmin">
                              <a :href="adminDashboardUrl" class="dropdown-item">
                                {{ __('message.admin_dashboard') }}
                              </a>
                            </li>
                            <li>
                              <RouterLink to="/dashboard" class="dropdown-item">
                                {{ __('message.dashboard') }}
                              </RouterLink>
                            </li>
                            <li>
                              <RouterLink to="/orders" class="dropdown-item">
                                {{ __('message.my_orders') }}
                              </RouterLink>
                            </li>
                            <li>
                              <RouterLink to="/invoices" class="dropdown-item">
                                {{ __('message.my_invoices') }}
                              </RouterLink>
                            </li>
                            <li>
                              <RouterLink to="/profile" class="dropdown-item">
                                {{ __('message.my_profile') }}
                              </RouterLink>
                            </li>
                            <li>
                              <a :href="logoutUrl" class="dropdown-item">
                                {{ __('message.logout') }}
                              </a>
                            </li>
                          </ul>
                        </li>

                        <!-- Sign-up (guest) -->
                        <li v-else>
                          <a class="nav-link" :href="loginUrl">{{ __('message.sign-up') }}</a>
                        </li>

                        <!-- Free Trial / Demo (mobile only, shown in collapsed nav) -->
                        <li v-if="cloudEnabled" class="demo-icons d-lg-none">
                          <a class="nav-link btn open-createTenantDialog startFreeTrialBtn">
                            {{ __('message.start_free_trial') }}
                          </a>
                        </li>
                        <li v-if="demoEnabled" class="demo-icons d-lg-none">
                          <a class="nav-link" id="demo-req-mobile">
                            {{ __('message.request_for_demo') }}
                          </a>
                        </li>

                      </ul>
                    </nav>
                  </div>

                  <!-- Cart -->
                  <div
                      class="header-nav-features header-nav-features-no-border header-nav-features-lg-show-border order-1 order-lg-2 me-2 me-lg-0">
                    <div ref="cartRef" class="header-nav-feature header-nav-features-cart d-inline-flex ms-2 mx-3">
                      <a href="javascript:;"
                         class="header-nav-features-toggle text-decoration-none"
                         @click.stop="toggleCartDropdown">
                                                <span
                                                    class="text-dark opacity-8 font-weight-bold text-color-hover-primary">
                                                    {{ __('message.cart') }}<i class="fas fa-shopping-cart ms-1"></i>
                                                </span>
                        <span class="cart-info">
                                                    <span class="cart-qty">{{ badgeCount }}</span>
                                                </span>
                      </a>

                      <div class="header-nav-features-dropdown" :class="{ 'show': showCartDropdown }"
                           id="headerTopCartDropdown">
                        <!-- Guest -->
                        <div v-if="!isAuthenticated" class="text-center py-3">
                          <i class="fas fa-shopping-cart fa-2x mb-2 d-block text-muted"></i>
                          <a :href="loginUrl" class="btn btn-primary btn-sm mt-2">
                            {{ __('message.sign-up') }}
                          </a>
                        </div>

                        <!-- Authenticated — empty -->
                        <div v-else-if="!cartItems.length">
                          <ol class="mini-products-list">
                            <div
                                class="product-details d-flex justify-content-between align-items-center mb-4 fw-medium">
                              <span class="text-muted">0 ITEMS</span>
                              <RouterLink to="/cart" class="text-dark text-uppercase fw-bold"
                                          @click="showCartDropdown = false">
                                {{ __('message.view_cart') }}
                              </RouterLink>
                            </div>
                            <hr class="border-top my-0">
                            <span class="d-block text-center mt-3">{{ __('message.no_item_cart') }}</span>
                          </ol>
                        </div>

                        <!-- Authenticated — has items -->
                        <div v-else>
                          <ol class="mini-products-list">
                            <li v-for="item in cartItems" :key="item.id" class="item">
                              <a href="#" class="product-image" :title="item.name">
                                <img v-if="item.image" :src="item.image" :alt="item.name" width="70">
                              </a>
                              <div class="product-details">
                                <p class="product-name">
                                  <a href="#">{{ item.name }}</a><br>
                                  <span class="amount"><strong>{{ item.currency_symbol }}{{ item.unit_price }}</strong></span>
                                </p>
                                <a href="#" class="btn-remove" :title="__('message.remove')"
                                   @click.prevent="cartStore.removeItem(item.id)">
                                  <i class="fas fa-times"></i>
                                </a>
                              </div>
                            </li>
                          </ol>
                          <div class="totals">
                            <span class="label">{{ __('message.total') }}:</span>
                            <span class="price-total">
                                                            <span class="price">{{
                                                                cartStore.currencySymbol
                                                              }}{{ cartStore.total }}</span>
                                                        </span>
                          </div>
                          <div class="actions">
                            <RouterLink class="btn btn-dark" to="/cart" @click="showCartDropdown = false">
                              {{ __('message.view_cart') }}
                            </RouterLink>
                            <RouterLink class="btn btn-primary" to="/checkout" @click="showCartDropdown = false">
                              {{ __('message.checkout') }}
                            </RouterLink>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Language selector -->
                  <div
                      class="header-nav-features header-nav-features-no-border header-nav-features-lg-show-border order-1 order-lg-2 me-2 me-lg-0">
                    <div class="header-nav-feature header-nav-features-cart d-inline-flex ms-2 mx-3">
                      <a href="#" class="header-nav-features-toggle text-decoration-none"
                         @click="toggleLanguage">
                        <i :class="`flag-icon flag-icon-${flagCode}`" id="flagIcon"></i>
                      </a>
                      <div class="header-nav-features-dropdown right-15" id="language-dropdown"></div>
                    </div>
                  </div>

                  <!-- Hamburger -->
                  <button class="btn header-btn-collapse-nav"
                          data-bs-toggle="collapse"
                          data-bs-target=".header-nav-main nav">
                    <i class="fas fa-bars"></i>
                  </button>
                </div>
              </div>

              <div class="vr opacity-2 ms-auto d-none d-lg-inline-block"></div>

              <!-- Desktop CTA buttons -->
              <div class="px-4 d-none d-lg-inline-block ws-nowrap">
                <a v-if="cloudEnabled"
                   class="btn border-0 px-4 py-2 line-height-9 btn-dark me-2 text-white open-createTenantDialog startFreeTrialBtn">
                  {{ __('message.start_free_trial') }}
                </a>
                <a v-if="demoEnabled"
                   id="demo-req"
                   class="btn border-0 px-4 py-2 line-height-9 btn-primary text-white">
                  {{ __('message.request_for_demo') }}
                </a>
              </div>

            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {computed, onMounted, onUnmounted, ref} from 'vue'
import http from '@/plugins/axios'
import {useCartStore} from '@/core/stores/cart'
import {useNavFeatureToggle} from '../../composables/useNavFeatureToggle.js'
import {isStickyActive} from '../../composables/useStickyHeader.js'

const {toggle: toggleLanguage} = useNavFeatureToggle()

const cartStore = useCartStore()
const isScrolled = isStickyActive

const showCartDropdown = ref(false)
const cartRef = ref(null)

function toggleCartDropdown() {
  showCartDropdown.value = !showCartDropdown.value
}

function onClickOutside(e) {
  if (cartRef.value && !cartRef.value.contains(e.target)) {
    showCartDropdown.value = false
  }
}

const el = document.getElementById('app-client')

const isAuthenticated = computed(() => el?.dataset?.authenticated === 'true')
const logoUrl = computed(() => el?.dataset?.appLogo ?? '')
const appCompany = computed(() => el?.dataset?.company ?? '')
const baseUrl = computed(() => el?.dataset?.baseUrl ?? '')
const assetUrl = computed(() => el?.dataset?.assetUrl ?? '')
const logoutUrl = computed(() => `${baseUrl.value}/auth/logout`)
const loginUrl = computed(() => `${baseUrl.value}/login`)
const isAdmin = computed(() => el?.dataset?.userRole === 'admin')
const adminDashboardUrl = computed(() => `${baseUrl.value}/admin/dashboard`)

const phone = computed(() => el?.dataset?.phone ?? '')
const phoneCode = computed(() => el?.dataset?.phoneCode ?? '')
const companyEmail = computed(() => el?.dataset?.companyEmail ?? '')
const cloudEnabled = computed(() => el?.dataset?.cloud === 'true')
const demoEnabled = computed(() => el?.dataset?.demo === 'true')
const cartCount = ref(parseInt(el?.dataset?.cartCount ?? '0', 10))

// Authenticated users get the live DB-backed cart count; guests fall back to
// the server-rendered count attribute.
const cartItems = computed(() => Array.isArray(cartStore.items) ? cartStore.items : [])
const badgeCount = computed(() => isAuthenticated.value ? cartStore.itemCount : cartCount.value)

const socialMedia = computed(() => {
  try {
    return JSON.parse(el?.dataset?.social ?? '[]')
  } catch {
    return []
  }
})

const localeMap = {
  ar: 'ae', bsn: 'bs', de: 'de', en: 'us', 'en-gb': 'gb', es: 'es',
  fr: 'fr', id: 'id', it: 'it', kr: 'kr', mt: 'mt', nl: 'nl', no: 'no',
  pt: 'pt', ru: 'ru', vi: 'vn', 'zh-hans': 'cn', 'zh-hant': 'cn',
  ja: 'jp', ta: 'in', hi: 'in', he: 'il', tr: 'tr',
}
const locale = computed(() => el?.dataset?.locale ?? 'en')
const flagCode = computed(() => localeMap[locale.value] ?? 'us')

const productGroups = ref([])

onMounted(async () => {
  document.addEventListener('click', onClickOutside)
  if (isAuthenticated.value) {
    cartStore.fetchCart()
  }
  try {
    const {data} = await http.post('available-groups')
    productGroups.value = Object.entries(data.data ?? {}).map(([id, g]) => ({id: parseInt(id), ...g}))
  } catch {
  }
})

onUnmounted(() => {
  document.removeEventListener('click', onClickOutside)
})
</script>

<style scoped>
.navbar-logo-wrapper {
  width: 250px;
  height: 150px;
  padding: 18px 24px;
  overflow: hidden;
  transition: width 0.3s ease, height 0.3s ease;
}

.navbar-scrolled .navbar-logo-wrapper {
  width: 150px;
  height: 70px;
  padding: 10px 16px;
}

.navbar-logo-img {
  max-height: 90px;
  transition: max-height 0.3s ease;
}

.navbar-scrolled .navbar-logo-img {
  max-height: 44px;
}

.navbar-info-bar {
  max-height: 60px;
  opacity: 1;
  transition: max-height 0.3s ease, opacity 0.2s ease;
}

.navbar-scrolled .navbar-info-bar {
  max-height: 0;
  opacity: 0;
}

</style>
