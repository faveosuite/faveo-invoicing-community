<template>
  <div class="card border-radius-0 bg-color-light anim-hover-translate-top-10px transition-3ms">
    <div class="card-body py-5">
      <div class="pricing-block">

        <div class="text-center">

          <!-- Product name -->
          <h4 :class="['font-weight-bold', product.highlighted ? 'text-color-primary' : '']">
            {{ product.name }}
          </h4>

          <!-- Short description -->
          <p v-if="product.short_description" class="text-muted text-3 mb-3" v-html="product.short_description"></p> <!-- nosemgrep: javascript.vue.security.audit.xss.templates.avoid-v-html.avoid-v-html -->

          <!-- Custom Pricing label for Contact Sales products -->
          <div v-if="product.button.type === 'contact'" class="plan-price bg-transparent mb-2">
            <span :class="['price', product.highlighted ? 'text-color-primary' : '']">
              {{ __('message.custom_pricing') }}
            </span>
          </div>

          <!-- Price / plan selector — hidden for Contact Sales products -->
          <template v-if="product.button.type !== 'contact'">

            <div class="plan-price bg-transparent mb-2">
              <span :class="['price', product.highlighted ? 'text-color-primary' : '']">
                <span v-if="currentPlan && currentPlan.price_raw !== 0" class="price-unit">{{ currencySymbol }}</span>{{ priceAmount }}
              </span>
              <label class="price-label">{{ currentLabel }}</label>
            </div>

            <!-- Strike-through original price -->
            <div v-if="currentStrikePrice" class="mb-3">
              <s class="text-muted text-4">{{ currentStrikePrice }}</s>
            </div>

            <!-- Plan selector dropdown (shown only when no global billingCycle toggle) -->
            <div v-if="product.plans.length && !billingCycle" class="mb-4 px-3">
              <select v-model="selectedPlanId" class="form-select form-select-sm text-center">
                <option v-for="plan in product.plans" :key="plan.id" :value="plan.id">
                  {{ plan.option_label }}
                </option>
              </select>
            </div>

          </template>

        </div>

        <!-- Order button -->
        <div class="text-center mt-4 pt-2 mb-4">
          <button
              :class="['btn px-4 py-2', product.button.class]"
              :disabled="cartStore.loading && product.button.type !== 'contact'"
              @click="handleOrder"
          >
            {{ product.button.label }}
          </button>
        </div>

        <!-- Features / description -->
        <!-- nosemgrep: javascript.vue.security.audit.xss.templates.avoid-v-html.avoid-v-html -->
        <div
            ref="descriptionEl"
            class="product-description"
            v-if="product.description"
            v-html="product.description"
        ></div>

      </div>
    </div>
  </div>

  <!-- Cloud domain modal — uses the shared Modal component -->
  <Modal
    :showModal="showDomainModal"
    :onClose="() => { showDomainModal = false; domainError = '' }"
    :closeLabel="__('message.close')"
    :closeOnBackdrop="true"
    classname="modal-md"
  >
    <template #title>
      <h5 class="modal-title fw-bold">{{ __('message.cloud_heading') }}</h5>
    </template>

    <template #fields>
      <!-- Domain input -->
      <ClientField
        name="domain"
        :label="__('message.cloud_field_label')"
        :placeholder="__('message.cloud_domain')"
        :modelValue="domain"
        :error="domainError"
        @update:modelValue="domain = $event; domainError = ''"
        @keyup.enter="onDomainConfirmed"
      >
        <template #append>
          <span class="input-group-text bg-primary text-white border-primary fw-semibold">
            .{{ cloudSubdomain }}
          </span>
        </template>
      </ClientField>

      <!-- Data center -->
      <DynamicSelect
        v-if="dataCenters.length"
        name="data_center_id"
        :label="__('message.choose_data_center')"
        :elements="dataCenters"
        :value="selectedDataCenter"
        :onChange="(val) => selectedDataCenter = val"
        :clearable="false"
        optionLabel="name"
      />
    </template>

    <template #controls>
      <button
        type="button"
        class="btn btn-primary"
        :disabled="modalLoading"
        @click="onDomainConfirmed"
      >
        <span v-if="modalLoading" class="spinner-border spinner-border-sm me-1" />
        <i v-else class="fas fa-check me-1" />
        {{ __('message.submit') }}
      </button>
    </template>
  </Modal>
</template>

<script setup>
import {ref, computed, watch, onMounted, onBeforeUnmount, nextTick} from 'vue'
import { useRouter } from 'vue-router'
import { __ } from '@/plugins/i18n'
import { useCartStore } from '@/core/stores/cart'
import Modal from '../common/Modal.vue'
import ClientField from '../forms/ClientField.vue'
import DynamicSelect from '@/components/Reusable/FormField/DynamicSelect.vue'

const props = defineProps({
  product:        { type: Object,  required: true },
  currencySymbol: { type: String,  default: '$' },
  cloudSubdomain: { type: String,  default: '' },
  dataCenters:    { type: Array,   default: () => [] },
  billingCycle:   { type: String,  default: null },
})

const cartStore = useCartStore()
const router    = useRouter()

const descriptionEl      = ref(null)
const showDomainModal    = ref(false)
const modalLoading       = ref(false)
const domain             = ref('')
const domainError        = ref('')
const selectedDataCenter = ref(null)

function initTooltips() {
  if (!descriptionEl.value || !globalThis.bootstrap?.Tooltip) return
  descriptionEl.value.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(node => {
    globalThis.bootstrap.Tooltip.getInstance(node)?.dispose()
    const _tooltip = new globalThis.bootstrap.Tooltip(node, { customClass: 'porto-tooltip', offset: [0, 10] })
  })
}

function applyListClasses() {
  if (!descriptionEl.value) return
  const classes = ['list', 'list-icons', 'list-icons-style-3', 'list-dark', 'list-icons-sm', 'ms-3']
  descriptionEl.value.querySelectorAll('ul').forEach(ul => ul.classList.add(...classes))
}

function destroyTooltips() {
  descriptionEl.value?.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(node => {
    globalThis.bootstrap.Tooltip.getInstance(node)?.dispose()
  })
}

onMounted(() => nextTick(() => { initTooltips(); applyListClasses() }))
watch(() => props.product.description, () => nextTick(() => { initTooltips(); applyListClasses() }))
onBeforeUnmount(destroyTooltips)

function findPlanForCycle(cycle) {
  if (!cycle) return null
  return props.product.plans.find(p => {
    // Match on period (actual billing period e.g. "1 Month", "1 Year")
    // NOT description which is the price label and may be identical across periods
    const period = (p.period ?? '').toLowerCase()
    if (cycle === 'yearly')  return period.includes('year') || period.includes('annual')
    if (cycle === 'monthly') return period.includes('month')
    return false
  }) ?? null
}

const fallbackPlan = props.product.plans.find(p => p.is_default) ?? props.product.plans[0] ?? null
const selectedPlanId = ref((findPlanForCycle(props.billingCycle) ?? fallbackPlan)?.id ?? null)

watch(() => props.billingCycle, cycle => {
  const match = findPlanForCycle(cycle) ?? fallbackPlan
  selectedPlanId.value = match?.id ?? null
})

const currentPlan = computed(() =>
    props.product.plans.find(p => p.id === selectedPlanId.value) ?? fallbackPlan
)

const priceAmount = computed(() => {
  const p = currentPlan.value
  if (!p) return props.product.display_price ?? '—'
  if (p.price_raw === 0) return __('message.free')
  // Yearly toggle → show per-month equivalent; monthly → show actual price
  return props.billingCycle === 'yearly'
    ? (p.price_per_month ?? p.price_display ?? p.price_raw)
    : (p.price_display ?? p.price_raw)
})

const currentStrikePrice = computed(() => {
  const p = currentPlan.value
  if (!p || p.original_price_raw == null) return null
  const amount = props.billingCycle === 'yearly'
    ? (p.original_price_per_month ?? p.original_display ?? p.original_price_raw)
    : (p.original_display ?? p.original_price_raw)
  return `${props.currencySymbol}${amount}`
})

const currentLabel = computed(() => {
  const p = currentPlan.value
  if (!p) return (props.product.display_label ?? '').toUpperCase()
  if (p.price_raw === 0) return __('message.free').toUpperCase()
  // In toggle mode show the requested cycle label, not whatever the fallback plan's description is
  if (props.billingCycle) return (p.description ?? '').toUpperCase()
  return (p.description ?? '').toUpperCase()
})

// Map a plan's billing period to the cart's allowed cycle values.
function resolveCycle(plan) {
  if (props.billingCycle) return props.billingCycle
  const period = (plan?.period ?? '').toLowerCase()
  if (period.includes('year') || period.includes('annual')) return 'yearly'
  if (period.includes('month')) return 'monthly'
  return 'onetime'
}

async function handleOrder() {
  const btn = props.product.button

  if (btn.type === 'contact') {
    window.open(btn.url, '_blank')
    return
  }

  if (props.product.is_cloud) {
    domain.value             = ''
    domainError.value        = ''
    selectedDataCenter.value = props.dataCenters[0] ?? null
    showDomainModal.value    = true
    return
  }

  await addToCart()
}

async function addToCart(domain = null, dataCenterId = null) {
  const plan      = currentPlan.value
  const productId = props.product.button.product_id ?? props.product.id

  try {
    await cartStore.addItem({
      product_id:     productId,
      plan_id:        plan?.id ?? null,
      quantity:       1,
      agents:         1,
      billing_cycle:  resolveCycle(plan),
      ...(domain       ? { domain }                         : {}),
      ...(dataCenterId ? { data_center_id: dataCenterId }   : {}),
    })
    router.push('/cart')
  } catch {
    // cartStore.error holds the failure message for display.
  }
}

async function onDomainConfirmed() {
  domain.value = domain.value.trim()
  if (! domain.value) {
    domainError.value = 'Domain name is required.'
    return
  }
  if (!/^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/i.test(domain.value)) {
    domainError.value = 'Only letters, numbers, and hyphens are allowed.'
    return
  }
  modalLoading.value = true
  try {
    await addToCart(domain.value, selectedDataCenter.value?.id ?? null)
    showDomainModal.value = false
  } finally {
    modalLoading.value = false
  }
}
</script>

<style scoped>

:deep(.product-description ul li) {
  list-style: none;
  position: relative;
  color: #777;
}

:deep(.product-description ul li [data-bs-toggle="tooltip"]) {
  text-decoration: underline;
  text-decoration-style: dotted;
  text-underline-offset: 3px;
  cursor: help;
}

:deep(.product-description ul li)::before {
  content: "\f00c";
  font-family: "Font Awesome 7 Free", sans-serif;
  font-weight: 900;
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 20px;
  height: 20px;
  background-color: black;
  border-radius: 50%;
  text-align: center;
  line-height: 20px;
  color: white;
}

</style>

<style>
.porto-tooltip .tooltip-inner {
  color: #fff;
  border-radius: 4px;
  padding: 8px 14px;
  font-size: 1rem;
  line-height: 1.5;
  max-width: 300px;
  text-align: left;
}
</style>
