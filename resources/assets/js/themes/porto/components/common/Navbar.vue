<template>
  <div class="header-body border-0 border-bottom-light" :class="{ 'navbar-scrolled': isScrolled }">
    <div class="header-container container-fluid p-0">
      <div class="header-row">

        <!-- Logo column -->
        <div class="header-column header-column-border-right flex-grow-0">
          <div class="header-row">
            <div id="main-logo"
                 class="header-logo p-relative m-0 d-flex align-items-center justify-content-center navbar-logo-wrapper">
              <RouterLink :to="isAuthenticated ? '/client-dashboard' : '/'" class="d-flex align-items-center justify-content-center w-100 h-100 text-decoration-none">
                <img v-if="logoUrl" :src="logoUrl" alt="Logo"
                     class="img-fluid navbar-logo-img">
                <span v-else class="brand-text fw-bold text-dark">{{ appCompany }}</span>
              </RouterLink>
            </div>
          </div>
        </div>

        <!-- Main column -->
        <div class="header-column flex-grow-1 min-w-0">

          <!-- Top info bar: phone, email, social -->
          <div class="border-bottom-light w-100 navbar-info-bar">
            <div class="hstack gap-2 gap-xl-4 px-3 px-xl-4 py-1 py-xl-2 font-weight-semi-bold d-none d-lg-flex">
              <div v-if="phone" class="d-none d-lg-inline-block ps-1">
                <a class="text-color-default text-color-hover-primary text-2 text-decoration-none"
                   :href="`tel:+${phoneCode} ${phone}`">
                  <i class="fas fa-phone text-3 text-xl-4 p-relative top-2"></i>&nbsp;+{{ phoneCode }} {{ phone }}
                </a>
              </div>
              <div class="vr d-lg-inline-block opacity-2 d-none d-xl-inline-block"></div>
              <div v-if="companyEmail" class="d-none d-xl-inline-block">
                <a class="text-color-default text-color-hover-primary text-2 text-decoration-none"
                   :href="`mailto:${companyEmail}`">
                  <i class="fas fa-envelope text-3 text-xl-4 p-relative top-2"></i>&nbsp;{{ companyEmail }}
                </a>
              </div>
              <div class="ms-auto d-none d-lg-inline-block"></div>
              <div class="vr opacity-2 d-none d-lg-inline-block"></div>
              <div class="d-none d-lg-inline-block">
                <ul class="nav nav-pills me-1">
                  <li v-for="media in socialMedia" :key="media.name" class="nav-item pe-2 mx-1">
                    <a :href="media.link" target="_blank"
                       :title="media.name"
                       class="text-color-default text-color-hover-primary text-3 text-xl-4">
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
              <div class="h-100 w-100 flex-grow-1 min-w-0">
                <div
                    class="header-nav header-nav-links h-100 justify-content-end justify-content-lg-start me-1 me-sm-2 me-lg-0 ms-lg-2 ms-xl-3">
                  <div
                      class="header-nav-main header-nav-main-square header-nav-main-dropdown-no-borders header-nav-main-text-capitalize header-nav-main-text-size-4 header-nav-main-arrows header-nav-main-full-width-mega-menu header-nav-main-mega-menu-bg-hover header-nav-main-effect-5">
                    <nav class="collapse">
                      <ul class="nav nav-pills" id="mainNav">

                        <!-- Store -->
                        <li class="dropdown" :class="{ open: openDropdownKey === 'store' }">
                          <a class="nav-link dropdown-toggle d-flex align-items-center justify-content-between justify-content-lg-start" href="javascript:;"
                             :aria-expanded="openDropdownKey === 'store'" @click="toggleDropdown('store', $event)">
                            <span>&nbsp;{{ __('message.store') }}&nbsp;</span>
                            <i class="fas fa-chevron-down d-lg-none nav-dropdown-arrow" :class="{ 'rotate-180': openDropdownKey === 'store' }"></i>
                          </a>
                          <ul class="dropdown-menu border-light mt-n1">
                            <li v-for="group in productGroups" :key="group.id">
                              <RouterLink :to="'/store/' + group.id" class="dropdown-item">{{ group.name }}</RouterLink>
                            </li>
                          </ul>
                        </li>

                        <!-- CMS Pages (published) -->
                        <template v-for="page in topLevelPages" :key="page.id">
                          <!-- Page with children -> the page itself is still a real link;
                               a separate caret toggles the dropdown of its sub-pages. -->
                          <li v-if="childPages(page.id).length" class="dropdown page-dropdown-item" :class="{ open: openDropdownKey === page.id }">
                            <RouterLink :to="pageLink(page)" class="nav-link pe-0">&nbsp;{{ ucfirst(page.name) }}</RouterLink>
                            <!-- Desktop (>=992px): theme draws its own arrow via CSS (li > a.dropdown-toggle:after)
                                 and hides this icon. Mobile (<992px): theme shows/positions this icon instead
                                 and disables the CSS arrow — so the icon still has to be here for mobile.
                                 ps-0: this is a separate <a> from the label above, so the theme's own 1rem
                                 left padding would otherwise stack on top of the label's right padding.
                                 page-dropdown-caret: see scoped style below — Bootstrap's .nav-link is
                                 display:block, so on mobile (no Porto override there) this and the label
                                 above stack as two full-width rows instead of sitting on one line. -->
                            <a class="nav-link dropdown-toggle page-dropdown-caret ps-0" href="javascript:;"
                               :aria-expanded="openDropdownKey === page.id" @click="toggleDropdown(page.id, $event)">
                              <i class="fas fa-chevron-down nav-dropdown-arrow" :class="{ 'rotate-180': openDropdownKey === page.id }"></i>
                            </a>
                            <ul class="dropdown-menu border-light mt-n1">
                              <li v-for="child in childPages(page.id)" :key="child.id">
                                <RouterLink :to="pageLink(child)" class="dropdown-item">{{ ucfirst(child.name) }}</RouterLink>
                              </li>
                            </ul>
                          </li>
                          <!-- Simple page -->
                          <li v-else>
                            <RouterLink :to="pageLink(page)" class="nav-link">&nbsp;{{ ucfirst(page.name) }}&nbsp;</RouterLink>
                          </li>
                        </template>

                        <!-- My Account (authenticated) -->
                        <li v-if="isAuthenticated" class="dropdown" :class="{ open: openDropdownKey === 'account' }">
                          <a class="nav-link dropdown-toggle d-flex align-items-center justify-content-between justify-content-lg-start" href="javascript:;"
                             :aria-expanded="openDropdownKey === 'account'" @click="toggleDropdown('account', $event)">
                            <span>&nbsp;{{ __('message.my_account') }}&nbsp;</span>
                            <i class="fas fa-chevron-down d-lg-none nav-dropdown-arrow" :class="{ 'rotate-180': openDropdownKey === 'account' }"></i>
                          </a>
                          <ul class="dropdown-menu border-light mt-n1">
                            <li v-if="isAdmin">
                              <a :href="adminDashboardUrl" class="dropdown-item">
                                {{ __('message.admin_dashboard') }}
                              </a>
                            </li>
                            <li>
                              <RouterLink to="/client-dashboard" class="dropdown-item">
                                {{ __('message.dashboard') }}
                              </RouterLink>
                            </li>
                            <li>
                              <RouterLink to="/my-orders" class="dropdown-item">
                                {{ __('message.my_orders') }}
                              </RouterLink>
                            </li>
                            <li>
                              <RouterLink to="/my-invoices" class="dropdown-item">
                                {{ __('message.my_invoices') }}
                              </RouterLink>
                            </li>
                            <li>
                              <RouterLink to="/my-profile" class="dropdown-item">
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
                          <RouterLink class="nav-link" to="/login">{{ __('message.sign-up') }}</RouterLink>
                        </li>

                        <!-- Free Trial / Demo (mobile only, shown in collapsed nav) -->
                        <li v-if="cloudEnabled || demoEnabled" class="mobile-nav-cta-wrapper d-lg-none mt-2 pt-2 border-top">
                          <div class="d-flex flex-column flex-sm-row gap-2">
                            <a v-if="cloudEnabled"
                               class="btn btn-dark text-white w-100 py-2 px-3 text-2 fw-semibold d-flex align-items-center justify-content-center text-center text-decoration-none"
                               href="javascript:;"
                               @click="showCloudTrialModal = true">
                              {{ __('message.start_free_trial') }}
                            </a>
                            <a v-if="demoEnabled"
                               class="btn btn-primary text-white w-100 py-2 px-3 text-2 fw-semibold d-flex align-items-center justify-content-center text-center text-decoration-none"
                               href="javascript:;"
                               @click="showDemoModal = true">
                              {{ __('message.request_for_demo') }}
                            </a>
                          </div>
                        </li>

                        <!-- Mobile Contact Info & Social Links -->
                        <li v-if="phone || companyEmail || socialMedia.length" class="mobile-nav-contact d-lg-none mt-2 pt-2 border-top">
                          <div class="py-2">
                            <div v-if="phone" class="mb-2">
                              <a class="text-color-default text-2 text-decoration-none d-flex align-items-center gap-2"
                                 :href="`tel:+${phoneCode} ${phone}`">
                                <i class="fas fa-phone text-3 text-primary"></i>
                                <span>+{{ phoneCode }} {{ phone }}</span>
                              </a>
                            </div>
                            <div v-if="companyEmail" class="mb-2">
                              <a class="text-color-default text-2 text-decoration-none d-flex align-items-center gap-2"
                                 :href="`mailto:${companyEmail}`">
                                <i class="fas fa-envelope text-3 text-primary"></i>
                                <span class="text-break">{{ companyEmail }}</span>
                              </a>
                            </div>
                            <div v-if="socialMedia.length" class="mt-2 pt-1">
                              <ul class="nav nav-pills gap-2 p-0 m-0 d-flex flex-row align-items-center">
                                <li v-for="media in socialMedia" :key="media.name" class="nav-item">
                                  <a :href="media.link" target="_blank"
                                     :title="media.name"
                                     class="btn btn-light rounded-circle text-3 social-media-circle-btn">
                                    <i :class="`fab fa-${media.name.toLowerCase()}`"></i>
                                  </a>
                                </li>
                              </ul>
                            </div>
                          </div>
                        </li>

                      </ul>
                    </nav>
                  </div>

                  <!-- Action icons: Cart, Language, Hamburger -->
                  <div class="header-nav-actions d-flex align-items-center">
                    <!-- Cart -->
                    <div
                        class="header-nav-features header-nav-features-no-border header-nav-features-lg-show-border m-0 p-0">
                      <div ref="cartRef" class="header-nav-feature header-nav-features-cart d-inline-flex m-0 p-0">
                        <a href="javascript:;"
                           class="header-nav-features-toggle text-decoration-none d-flex align-items-center navbar-feature-toggle"
                           @click.stop="toggleCartDropdown"
                           :aria-label="__('message.cart')">
                          <span class="cart-toggle-content text-dark opacity-8 font-weight-bold text-color-hover-primary d-inline-flex align-items-center">
                            <span class="cart-label d-none d-xl-inline me-1">{{ __('message.cart') }}</span>
                            <span class="cart-icon-wrapper position-relative d-inline-flex align-items-center justify-content-center">
                              <i class="fas fa-shopping-cart cart-icon-main"></i>
                              <span class="cart-qty-badge">{{ badgeCount }}</span>
                            </span>
                          </span>
                        </a>

                        <!-- Cart dropdown -->
                        <div class="header-nav-features-dropdown header-nav-features-dropdown-mobile-fixed" :class="{ 'show': showCartDropdown }"
                             id="headerTopCartDropdown">
                          <!-- Empty cart -->
                          <div v-if="!cartItems.length">
                            <ol class="mini-products-list">
                              <div class="product-details d-flex justify-content-between align-items-center mb-4 fw-medium">
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

                          <!-- Cart has items -->
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
                                <span class="price">{{ cartStore.currencySymbol }}{{ cartStore.total }}</span>
                              </span>
                            </div>
                            <div class="actions">
                              <RouterLink class="btn btn-dark" to="/cart" @click="showCartDropdown = false">
                                {{ __('message.view_cart') }}
                              </RouterLink>
                              <button class="btn btn-primary" @click="handleCheckout">
                                {{ __('message.checkout') }}
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Language selector -->
                    <div v-if="languages.length"
                        class="header-nav-features header-nav-features-no-border header-nav-features-lg-show-border m-0 p-0">
                      <div class="header-nav-feature header-nav-features-cart d-inline-flex m-0 p-0">
                        <a href="javascript:;" class="header-nav-features-toggle text-decoration-none d-flex align-items-center gap-1 navbar-feature-toggle"
                           @click="toggleLanguage" :aria-label="`Change language, current: ${currentLocale}`">
                          <span :class="`fi fi-${flagCodeFor(currentLocale)}`"></span>
                          <span class="text-dark opacity-8 font-weight-bold text-2 d-none d-xl-inline">{{ currentLocale.toUpperCase() }}</span>
                        </a>
                        <div class="header-nav-features-dropdown header-nav-features-dropdown-mobile-fixed right-15 lang-dropdown" id="language-dropdown">
                          <ul class="list-unstyled m-0">
                            <li v-for="lang in languages" :key="lang.locale">
                              <a href="javascript:;" class="lang-item d-flex align-items-center gap-2"
                                  :class="{ active: lang.locale.toLowerCase() === currentLocale }"
                                  @click.prevent="selectLang(lang)">
                                <span :class="`fi fi-${flagCodeFor(lang.locale)}`"></span>
                                <span>{{ lang.name }}</span>
                              </a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>

                    <!-- Hamburger -->
                    <button class="btn header-btn-collapse-nav m-0 d-lg-none"
                            data-bs-toggle="collapse"
                            data-bs-target=".header-nav-main nav"
                            aria-label="Toggle navigation">
                      <i class="fas fa-bars"></i>
                    </button>
                  </div>
                </div>
              </div>

              <div class="vr opacity-2 ms-auto d-none d-lg-inline-block"></div>

              <!-- Desktop CTA buttons -->
              <div class="px-2 px-xl-3 px-xxl-4 d-none d-lg-inline-block ws-nowrap navbar-cta-group">
                <a v-if="cloudEnabled"
                   href="javascript:;"
                   class="btn border-0 px-2 px-xl-4 py-1 py-xl-2 line-height-9 btn-dark me-2 text-white text-1 text-xl-2 fw-semibold"
                   @click="showCloudTrialModal = true">
                  {{ __('message.start_free_trial') }}
                </a>
                <a v-if="demoEnabled"
                   href="javascript:;"
                   class="btn border-0 px-2 px-xl-4 py-1 py-xl-2 line-height-9 btn-primary text-white text-1 text-xl-2 fw-semibold"
                   @click="showDemoModal = true">
                  {{ __('message.request_for_demo') }}
                </a>
              </div>

            </div>
          </div>

        </div>
      </div>
    </div>
  </div>



  <!-- Cloud trial modal -->
  <CloudTrialModal
    :show="showCloudTrialModal"
    @close="showCloudTrialModal = false"
  />

  <!-- Book a demo modal -->
  <BookDemoModal
    :show="showDemoModal"
    @close="showDemoModal = false"
  />
</template>

<script setup>
import {computed, onMounted, onUnmounted, ref} from 'vue'
import 'flag-icons/css/flag-icons.min.css'
import http from '@/plugins/axios'
import {useCartStore} from '@/core/stores/cart'
import {useAlertStore} from '@/core/stores/alert'
import {useNavFeatureToggle} from '../../composables/useNavFeatureToggle.js'
import {isStickyActive} from '../../composables/useStickyHeader.js'
import CloudTrialModal from '../store/CloudTrialModal.vue'
import BookDemoModal from '../store/BookDemoModal.vue'
import { useRouter } from 'vue-router'
import { __ } from '@/plugins/i18n'
import { useAuthStore } from '@/core/stores/auth'
import { useBaseUrl } from '@/core/composables/useBaseUrl'

const {toggle: toggleLanguage} = useNavFeatureToggle()

const router     = useRouter()
const cartStore  = useCartStore()
const alertStore = useAlertStore()
const isScrolled = isStickyActive

const showCloudTrialModal = ref(false)
const showDemoModal       = ref(false)
const showCartDropdown    = ref(false)
const cartRef             = ref(null)
const openDropdownKey     = ref(null)

function toggleCartDropdown() {
  showCartDropdown.value = !showCartDropdown.value
}

// Porto's mobile CSS only reveals a nav dropdown-menu when its parent <li>
// has class "open" (accordion-style, no hover on touch) — Bootstrap's own
// dropdown JS only ever toggles "show" on the menu itself, which Porto's
// mobile stylesheet doesn't key off, so it never becomes visible on mobile.
//
// Desktop (>=992px) already reveals it on hover via theme.css's own
// `li.dropdown:hover > .dropdown-menu` — untouched, still there — so the
// click-toggle only needs to run below that breakpoint, same as Porto's own
// theme.js (`if ($window.width() < 992) { ... toggleClass('open') ... }`).
// Without this, a stray desktop click could set the "open" class and leave
// the menu stuck open after the mouse moves away.
function toggleDropdown(key, event) {
  event?.preventDefault()
  if (globalThis.innerWidth >= 992) return
  openDropdownKey.value = openDropdownKey.value === key ? null : key
}

function handleCheckout() {
  if (!isAuthenticated.value) {
    showCartDropdown.value = false
    router.push('/login').then(() => {
      alertStore.setAlert({
        message: __('message.please_login_to_checkout'),
        type: 'warning',
        component_name: 'client-page',
      })
    })
    return
  }
  showCartDropdown.value = false
  router.push('/checkout')
}

function onClickOutside(e) {
  if (cartRef.value && !cartRef.value.contains(e.target)) {
    showCartDropdown.value = false
  }
  if (openDropdownKey.value !== null && !e.target.closest('.header-nav-main li.dropdown')) {
    openDropdownKey.value = null
  }
}

// When navigating between pages:
// 1) Close any open mobile nav collapse drawer
// 2) Close any open mobile submenu accordion
// 3) Close any open cart dropdown
const stopCloseMobileNav = router.afterEach(() => {
  openDropdownKey.value = null
  showCartDropdown.value = false
  const collapseEl = document.querySelector('.header-nav-main nav')
  if (collapseEl?.classList.contains('show')) {
    globalThis.bootstrap?.Collapse?.getOrCreateInstance(collapseEl, { toggle: false }).hide()
  }
})
onUnmounted(stopCloseMobileNav)

const el        = document.getElementById('app-client')
const authStore = useAuthStore()

const isAuthenticated = computed(() => authStore.isAuthenticated)
const isAdmin         = computed(() => authStore.isAdmin)
const logoUrl         = computed(() => el?.dataset?.appLogo ?? '')
const appCompany      = computed(() => el?.dataset?.company ?? '')
const baseUrl           = useBaseUrl()
const logoutUrl         = computed(() => `${baseUrl}/auth/logout`)
const adminDashboardUrl = computed(() => `${baseUrl}/admin/dashboard`)

const phone = computed(() => el?.dataset?.phone ?? '')
const phoneCode = computed(() => el?.dataset?.phoneCode ?? '')
const companyEmail = computed(() => el?.dataset?.companyEmail ?? '')
const cloudEnabled = computed(() => el?.dataset?.cloud === 'true')
const demoEnabled = computed(() => el?.dataset?.demo === 'true')
const cartCount = ref(parseInt(el?.dataset?.cartCount ?? '0', 10))

// Authenticated users get the live DB-backed cart count; guests fall back to
// the server-rendered count attribute.
const cartItems = computed(() => Array.isArray(cartStore.items) ? cartStore.items : [])
const badgeCount = computed(() => cartStore.itemCount || cartCount.value)

const socialMedia = computed(() => {
  try {
    return JSON.parse(el?.dataset?.social ?? '[]')
  } catch {
    return []
  }
})

// Maps language locale codes → ISO 3166-1 alpha-2 country codes used by flag-icons.
const localeMap = {
  ar: 'sa', bsn: 'ba', bs: 'ba', de: 'de', en: 'us', 'en-gb': 'gb', 'en-us': 'us', es: 'es',
  fr: 'fr', id: 'id', it: 'it', ko: 'kr', kr: 'kr', mt: 'mt', nl: 'nl', no: 'no',
  pt: 'pt', 'pt-br': 'br', ru: 'ru', vi: 'vn', zh: 'cn', 'zh-hans': 'cn', 'zh-hant': 'tw', 'zh-cn': 'cn', 'zh-tw': 'tw',
  ja: 'jp', ta: 'in', hi: 'in', he: 'il', tr: 'tr', pl: 'pl', sv: 'se', da: 'dk', cs: 'cz', uk: 'ua',
}

function flagCodeFor(loc) {
  const lc = String(loc ?? '').toLowerCase()
  return localeMap[lc] ?? localeMap[lc.slice(0, 2)] ?? 'un'
}

const currentLocale = computed(() => (el?.dataset?.locale ?? 'en').toLowerCase())

const languages = ref(
  (JSON.parse(el?.dataset?.languages ?? '[]')).filter(l => Number(l.status) === 1)
)

async function selectLang(lang) {
  try {
    await http.post('lang/update', {language: lang.locale})
    globalThis.location.reload()
  } catch (e) {
    console.error('Language switch failed', e)
  }
}

const productGroups = ref(
  Object.entries(JSON.parse(el?.dataset?.productGroups ?? '{}')).map(([id, g]) => ({ id: parseInt(id), ...g }))
)

// Published CMS pages shown in the navbar (public).
const publishedPages = ref(JSON.parse(el?.dataset?.publishedPages ?? '[]'))
const ucfirst = (s) => (s ? s.charAt(0).toUpperCase() + s.slice(1) : s)
const topLevelPages = computed(() =>
    publishedPages.value.filter(p => !p.parent_page_id || p.parent_page_id === 0)
)
const childPages = (parentId) =>
    publishedPages.value.filter(p => p.parent_page_id === parentId)
const pageLink = (page) => page.type === 'contactus' ? '/contact-us' : '/pages/' + page.slug

function onWindowResize() {
  if (globalThis.innerWidth >= 992) {
    openDropdownKey.value = null
    showCartDropdown.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', onClickOutside)
  window.addEventListener('resize', onWindowResize)
  cartStore.fetchCart()
})

onUnmounted(() => {
  document.removeEventListener('click', onClickOutside)
  window.removeEventListener('resize', onWindowResize)
})
</script>

<style scoped>
/* --------------------------------------------------
   1. Brand Logo & Name Responsiveness
   -------------------------------------------------- */
.navbar-logo-wrapper {
  width: 230px;
  height: 115px;
  padding: 14px 20px;
  overflow: hidden;
  transition: width 0.3s ease, height 0.3s ease, padding 0.3s ease;
}

/* Laptops & medium desktop (1200px - 1399px) */
@media (min-width: 1200px) and (max-width: 1399px) {
  .navbar-logo-wrapper {
    width: 195px;
    height: 95px;
    padding: 10px 16px;
  }
}

/* Tablets landscape & compact laptops (992px - 1199px) */
@media (min-width: 992px) and (max-width: 1199px) {
  .navbar-logo-wrapper {
    width: 165px;
    height: 80px;
    padding: 8px 14px;
  }
}

/* Tablets portrait (768px - 991px) */
@media (min-width: 768px) and (max-width: 991px) {
  .navbar-logo-wrapper {
    width: 150px;
    height: 70px;
    padding: 8px 14px;
  }
}

/* Large phones / phablets (481px - 767px) */
@media (min-width: 481px) and (max-width: 767px) {
  .navbar-logo-wrapper {
    width: 135px;
    height: 64px;
    padding: 6px 12px;
  }
}

/* Standard phones (361px - 480px) */
@media (max-width: 480px) {
  .navbar-logo-wrapper {
    width: 118px;
    height: 58px;
    padding: 6px 10px;
  }
}

/* Small phones (<= 360px, e.g. iPhone SE 320/360/375px) */
@media (max-width: 360px) {
  .navbar-logo-wrapper {
    width: 100px;
    height: 54px;
    padding: 4px 8px;
  }
}

/* Sticky / Scrolled Header logo */
.navbar-scrolled .navbar-logo-wrapper {
  width: 145px !important;
  height: 64px !important;
  padding: 6px 12px !important;
}

@media (max-width: 576px) {
  .navbar-scrolled .navbar-logo-wrapper {
    width: 110px !important;
    height: 54px !important;
    padding: 4px 8px !important;
  }
}

/* Logo Image */
.navbar-logo-img {
  max-height: 80px;
  max-width: 100%;
  object-fit: contain;
  transition: max-height 0.3s ease;
}

@media (max-width: 1399px) {
  .navbar-logo-img {
    max-height: 68px;
  }
}

@media (max-width: 1199px) {
  .navbar-logo-img {
    max-height: 56px;
  }
}

@media (max-width: 991px) {
  .navbar-logo-img {
    max-height: 48px;
  }
}

@media (max-width: 576px) {
  .navbar-logo-img {
    max-height: 40px;
  }
}

@media (max-width: 380px) {
  .navbar-logo-img {
    max-height: 34px;
  }
}

.navbar-scrolled .navbar-logo-img {
  max-height: 42px !important;
}

@media (max-width: 576px) {
  .navbar-scrolled .navbar-logo-img {
    max-height: 32px !important;
  }
}

/* Brand Text Fallback */
.brand-text {
  display: inline-block;
  max-width: 100%;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-size: 1.25rem;
  line-height: 1.2;
}

@media (max-width: 1199px) {
  .brand-text {
    font-size: 1.1rem;
  }
}

@media (max-width: 576px) {
  .brand-text {
    font-size: 0.95rem;
  }
}

@media (max-width: 360px) {
  .brand-text {
    font-size: 0.85rem;
  }
}

/* --------------------------------------------------
   2. Top Info Bar (Desktop & Scrolled)
   -------------------------------------------------- */
.navbar-info-bar {
  max-height: 60px;
  opacity: 1;
  transition: max-height 0.3s ease, opacity 0.2s ease;
}

.navbar-scrolled .navbar-info-bar {
  max-height: 0;
  opacity: 0;
}

/* --------------------------------------------------
   3. Desktop Nav Links & CTA on Laptops (992px - 1199px)
   -------------------------------------------------- */
@media (min-width: 992px) and (max-width: 1199px) {
  :deep(#header .header-nav-main nav > ul > li > a),
  .header-nav-main nav > ul > li > a {
    padding-left: 7px !important;
    padding-right: 7px !important;
    font-size: 0.85rem !important;
  }
  .navbar-cta-group {
    padding-left: 6px !important;
    padding-right: 6px !important;
  }
}

@media (min-width: 1200px) and (max-width: 1399px) {
  :deep(#header .header-nav-main nav > ul > li > a),
  .header-nav-main nav > ul > li > a {
    padding-left: 10px !important;
    padding-right: 10px !important;
    font-size: 0.92rem !important;
  }
}

/* --------------------------------------------------
   4. Mobile & Tablet Navigation Header Row (< 992px)
   -------------------------------------------------- */
@media (max-width: 991px) {
  .header-nav-links {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-end !important;
  }

  .header-nav-links > .header-nav-main {
    flex-basis: 100%;
    order: 2;
  }

  .header-nav-links > .header-nav-actions {
    order: 1;
  }

  /* Expand drawer to 75vh with smooth touch scrolling */
  :deep(#header .header-nav-main nav),
  .header-nav-main nav {
    max-height: 75vh !important;
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 20px !important;
  }

  :deep(#header .header-nav-main nav > ul li > a.nav-link),
  .header-nav-main nav > ul li > a.nav-link {
    padding: 9px 12px !important;
    min-height: 40px;
    display: flex;
    align-items: center;
  }
}

/* --------------------------------------------------
   5. Action Icons (Cart, Language, Hamburger) Spacing
   -------------------------------------------------- */
.header-nav-actions {
  display: inline-flex;
  align-items: center;
  gap: 14px;
}

@media (min-width: 576px) {
  .header-nav-actions {
    gap: 16px;
  }
}

@media (min-width: 768px) {
  .header-nav-actions {
    gap: 18px;
  }
}

@media (min-width: 992px) {
  .header-nav-actions {
    gap: 28px;
    margin-left: 20px;
  }
}

@media (min-width: 1200px) {
  .header-nav-actions {
    gap: 32px;
    margin-left: 24px;
  }
}

/* Reset any inherited margins/paddings from Porto so ONLY the gap controls spacing */
:deep(#header .header-nav-actions .header-nav-features),
.header-nav-actions .header-nav-features {
  margin: 0 !important;
  padding: 0 !important;
}

:deep(#header .header-nav-actions .header-nav-features:before),
:deep(#header .header-nav-actions .header-nav-features:after),
.header-nav-actions .header-nav-features:before,
.header-nav-actions .header-nav-features:after {
  content: none !important;
  display: none !important;
}

:deep(#header .header-nav-actions .header-nav-feature),
.header-nav-actions .header-nav-feature {
  margin: 0 !important;
  padding: 0 !important;
  position: relative;
}

/* Equal touch targets for feature buttons (Cart & Language) */
.navbar-feature-toggle {
  min-height: 38px;
  min-width: 32px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  position: relative;
  cursor: pointer;
}

/* Hamburger collapse button: ONLY show on mobile & tablet (< 992px), HIDE on laptop & desktop (>= 992px) */
@media (max-width: 991px) {
  :deep(#header .header-nav-actions .header-btn-collapse-nav),
  .header-nav-actions .header-btn-collapse-nav {
    float: none !important;
    margin: 0 !important;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    min-width: 38px;
    min-height: 36px;
    padding: 6px 10px;
    border-radius: 4px;
  }
}

@media (min-width: 992px) {
  :deep(#header .header-nav-actions .header-btn-collapse-nav),
  .header-nav-actions .header-btn-collapse-nav {
    display: none !important;
  }
}

/* Cart icon wrapper & tightly anchored superscript badge */
.cart-toggle-content {
  line-height: 1;
}

.cart-icon-wrapper {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
}

.cart-icon-main {
  font-size: 1.05rem;
  color: inherit;
}

/* Superscript badge anchored directly to the top-right corner of the cart icon */
.cart-qty-badge {
  position: absolute !important;
  top: -7px !important;
  right: -9px !important;
  background-color: #ed5348 !important;
  color: #ffffff !important;
  font-weight: 700 !important;
  font-size: 9px !important;
  min-width: 16px !important;
  height: 16px !important;
  line-height: 13px !important;
  text-align: center;
  border-radius: 8px !important;
  border: 1.5px solid #ffffff !important;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25) !important;
  padding: 0 3px !important;
  pointer-events: none;
  z-index: 2;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
}

/* Hide legacy cart-info container */
:deep(#header .header-nav-features .header-nav-features-cart .cart-info) {
  display: none !important;
}

/* --------------------------------------------------
   6. Dropdowns: Cart & Language
   -------------------------------------------------- */
/* Cart dropdown on mobile (< 768px): clean dropdown panel below header */
@media (max-width: 767px) {
  #header .header-nav-features .header-nav-features-cart .header-nav-features-dropdown.show {
    position: fixed !important;
    top: 65px !important;
    left: 50% !important;
    right: auto !important;
    transform: translateX(-50%) !important;
    margin-top: 0 !important;
    margin-right: 0 !important;
    max-height: 80vh !important;
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch;
    width: calc(100vw - 24px) !important;
    max-width: 350px !important;
    z-index: 10005 !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.18) !important;
    border: 1px solid rgba(0, 0, 0, 0.08) !important;
    border-radius: 8px !important;
    background: #ffffff !important;
    opacity: 1 !important;
    pointer-events: auto !important;
  }
}

/* Tablets (768px - 991px): aligned to cart icon */
@media (min-width: 768px) and (max-width: 991px) {
  #header .header-nav-features .header-nav-features-cart .header-nav-features-dropdown.show {
    position: absolute !important;
    top: 100% !important;
    right: 0 !important;
    left: auto !important;
    transform: none !important;
    margin-top: 10px !important;
    width: 340px !important;
    max-height: 80vh !important;
    overflow-y: auto !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
    background: #ffffff !important;
    opacity: 1 !important;
    pointer-events: auto !important;
    z-index: 10005 !important;
  }
}

/* Desktop (>= 992px) */
@media (min-width: 992px) {
  .header-nav-features-dropdown {
    right: 0 !important;
    left: auto !important;
    max-width: 360px;
    max-height: 80vh;
    overflow-y: auto;
  }
}

/* Clearfix for cart actions */
:deep(#header .header-nav-features .header-nav-features-cart .actions),
.actions {
  overflow: hidden !important;
  clear: both !important;
}

/* Language dropdown */
.lang-dropdown {
  min-width: 200px;
  max-height: 320px;
  overflow-y: auto;
  padding: 6px 0;
}

@media (max-width: 991px) {
  #header .header-nav-features .lang-dropdown.show {
    top: 100% !important;
    margin-top: 10px !important;
    right: 0 !important;
    left: auto !important;
    position: absolute !important;
    z-index: 10005 !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
    background: #ffffff !important;
  }
}

.lang-dropdown .lang-item {
  padding: 10px 16px;
  color: #333;
  text-decoration: none;
  font-size: 0.9rem;
  white-space: nowrap;
  display: flex;
  align-items: center;
  gap: 8px;
}

.lang-dropdown .lang-item:hover {
  background-color: #f5f5f5;
  color: var(--primary, #0088CC);
}

.lang-dropdown .lang-item.active {
  font-weight: 600;
  color: var(--primary, #0088CC);
}

.lang-dropdown .fi {
  width: 1.33em;
  line-height: 1em;
}

/* --------------------------------------------------
   7. Dropdown Carets & Accordion Icons
   -------------------------------------------------- */
.nav-dropdown-arrow {
  font-size: 0.7rem;
  transition: transform 0.25s ease;
}

.rotate-180 {
  transform: rotate(180deg) !important;
}

/* CMS Page with children: separate link and toggle button */
@media (max-width: 991px) {
  .page-dropdown-item {
    position: relative;
  }

  .page-dropdown-item > .page-dropdown-caret {
    position: absolute !important;
    top: 0;
    right: 0;
    width: 44px;
    height: 40px;
    display: flex !important;
    align-items: center;
    justify-content: center;
    z-index: 2;
  }

  html[dir="rtl"] .page-dropdown-item > .page-dropdown-caret {
    right: auto;
    left: 0;
  }
}

/* --------------------------------------------------
   8. Mobile Drawer Action Buttons & Contact Info
   -------------------------------------------------- */
.mobile-nav-cta-wrapper {
  list-style: none;
}

/* Center mobile Free Trial & Demo button text */
:deep(#header .mobile-nav-cta-wrapper .btn),
.mobile-nav-cta-wrapper .btn {
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  text-align: center !important;
  min-height: 42px !important;
  border-radius: 6px !important;
}

.mobile-nav-contact {
  list-style: none;
}

/* Mobile Social Media Icons: Force true circle shape */
:deep(#header .mobile-nav-contact .social-media-circle-btn),
.mobile-nav-contact .social-media-circle-btn {
  width: 36px !important;
  height: 36px !important;
  min-width: 36px !important;
  min-height: 36px !important;
  max-width: 36px !important;
  max-height: 36px !important;
  border-radius: 50% !important;
  padding: 0 !important;
  margin: 0 !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  background-color: #f8f9fa !important;
  border: 1px solid #e2e8f0 !important;
  color: #495057 !important;
  transition: all 0.2s ease;
}

:deep(#header .mobile-nav-contact .social-media-circle-btn:hover),
.mobile-nav-contact .social-media-circle-btn:hover {
  background-color: var(--primary, #0088CC) !important;
  border-color: var(--primary, #0088CC) !important;
  color: #ffffff !important;
}

.mini-products-list .product-details .btn-remove {
  width: 28px;
  height: 28px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
</style>
