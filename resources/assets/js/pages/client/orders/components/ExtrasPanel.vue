<template>
    <div>
        <Alert :componentName="COMPONENT" />
        <div v-if="loading" class="row justify-content-center py-3" role="status" aria-busy="true">
            <loader /><span class="visually-hidden">{{ __('message.extras_loading') }}</span>
        </div>
        <p v-else-if="!owned.length && !available.length" class="mb-0">{{ __('message.extra_none') }}</p>

        <template v-else>
            <!-- What the order has: one compact line each -->
            <template v-if="owned.length">
                <h2 class="extras-heading">{{ __('message.extras_yours') }}</h2>
                <ul class="list-unstyled extras-list mb-4">
                    <li v-for="item in owned" :key="item.id" class="d-flex flex-wrap align-items-center gap-2 py-2">
                        <div class="flex-grow-1 min-w-0">
                            <span class="fw-bold text-dark">{{ item.label }}</span>
                            <span v-if="item.type !== 'boolean'" class="text-muted"> · {{ __('message.extra_units', { count: item.quantity }) }}</span>
                            <span class="badge ms-2 extras-status" :class="status(item).cls">{{ status(item).text }}</span>
                            <div class="small text-muted">{{ priceText(item) }}</div>
                            <!-- Why more can't be bought (not for sale on the plan any more, unlimited…) -->
                            <div v-if="!item.can_buy && item.reason && item.type !== 'boolean'" class="small text-muted">{{ item.reason }}</div>
                        </div>
                        <div class="d-flex gap-2">
                            <button v-if="item.can_buy" type="button" class="btn btn-light btn-sm table_btn" @click="openBuy(item)">
                                <i class="fas fa-cart-plus me-1" aria-hidden="true"></i>{{ __('message.extra_buy_more') }}<span class="visually-hidden">: {{ item.label }}</span>
                            </button>
                            <button type="button" class="btn btn-light btn-sm table_btn" @click="openRenewal(item)">
                                <i class="fas fa-sliders me-1" aria-hidden="true"></i>{{ __('message.extra_renewal') }}<span class="visually-hidden">: {{ item.label }}</span>
                            </button>
                        </div>
                    </li>
                </ul>
            </template>

            <!-- What can still be added -->
            <template v-if="available.length">
                <h2 class="extras-heading">{{ __('message.extras_available') }}</h2>
                <div class="row g-2">
                    <div v-for="item in available" :key="item.id" class="col-sm-6">
                        <div class="extras-offer d-flex align-items-center gap-2 h-100">
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-bold text-dark">{{ item.label }}</div>
                                <div class="small text-muted">{{ priceText(item) }}</div>
                            </div>
                            <button type="button" class="btn btn-light btn-sm table_btn" @click="openBuy(item)">
                                <i class="fas fa-plus me-1" aria-hidden="true"></i>{{ __('message.extra_add') }}<span class="visually-hidden">: {{ item.label }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </template>

        <!-- Buy -->
        <AppModal :showModal="!!buying" :onClose="() => (buying = null)" :showCloseBtn="false">
            <template #title><h2 class="modal-title h5 fw-bold mb-0">{{ buying?.label }}</h2></template>
            <template #fields>
                <Alert :componentName="MODAL" />
                <template v-if="buying">
                    <TextField v-if="buying.type === 'number'" name="quantity" type="number" inputmode="numeric" autocomplete="off" :label="__('message.extra_how_many')"
                               :required="true" :value="String(buyQty)" :onChange="onQty" />
                    <p class="small">{{ __('message.extra_buy_help') }}</p>
                    <div v-if="quote" class="d-flex justify-content-between align-items-center border-top pt-3">
                        <span class="fw-bold text-dark">{{ __('message.price_to_be_paid') }}</span>
                        <span class="fw-bold text-dark fs-5">{{ quote.currency }} {{ quote.amount }}</span>
                    </div>
                    <p v-if="quote" class="small text-muted mb-0">{{ __('message.extra_quote_detail', { days: quote.days_left, period: quote.plan_days }) }}</p>
                    <!-- Why Pay is off: nothing to pay, or the quantity isn't valid yet -->
                    <p v-if="quote && quote.amount <= 0" class="small text-warning mb-0 mt-2">{{ __('message.extra_nothing_to_pay') }}</p>
                    <p v-else-if="!quote && !quoting" class="small text-muted mb-0 mt-2">{{ __('message.extra_enter_quantity') }}</p>
                </template>
            </template>
            <template #controls>
                <action-button action="cancel" @click="buying = null" />
                <action-button action="confirm" :label="__('message.extra_pay')" :loading="submitting" :disabled="!quote || quote.amount <= 0" @click="buy" />
            </template>
        </AppModal>

        <!-- Change how many renew -->
        <AppModal :showModal="!!changing" :onClose="() => (changing = null)" :showCloseBtn="false">
            <template #title><h2 class="modal-title h5 fw-bold mb-0">{{ changing?.label }}</h2></template>
            <template #fields>
                <Alert :componentName="MODAL" />
                <template v-if="changing">
                    <DynamicSelect v-if="changing.type === 'boolean'" name="renew" :label="__('message.extra_next_period')" :elements="KEEP_OPTIONS"
                                   :value="KEEP_OPTIONS.find(o => o.id === String(renewQty)) ?? null" :clearable="false" :searchable="false"
                                   :onChange="o => (renewQty = Number(o?.id ?? 1))" />
                    <TextField v-else name="renew" type="number" inputmode="numeric" autocomplete="off" :label="__('message.extra_next_period_units', { max: changing.quantity })"
                               :value="String(renewQty)" :error="renewError" :onChange="v => (renewQty = Number(v))" />
                    <p class="small mb-0">{{ __('message.extra_renewal_help') }}</p>
                </template>
            </template>
            <template #controls>
                <action-button action="cancel" @click="changing = null" />
                <action-button action="save" :label="__('message.extra_save_renewal')" :loading="submitting" @click="saveRenewal" />
            </template>
        </AppModal>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { __ } from '@/plugins/i18n'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import Alert from '@/components/Reusable/Alert.vue'
import TextField from '@/components/Reusable/FormField/TextField.vue'
import DynamicSelect from '@/components/Reusable/FormField/DynamicSelect.vue'

const KEEP_OPTIONS = [{ id: '1', name: __('message.extra_keep') }, { id: '0', name: __('message.extra_cancel') }]

const props = defineProps({
    orderId: { type: [String, Number], required: true },
})

const COMPONENT = 'order-extras'
const MODAL     = 'order-extras-modal'

const router     = useRouter()
const loading    = ref(true)
const submitting = ref(false)
const extras     = ref([])
const buying     = ref(null)
const buyQty     = ref(1)
const quote      = ref(null)
const changing   = ref(null)
const renewQty   = ref(0)
const renewError = ref('')
const quoting    = ref(false)

const priceText = item => (item.unit_price === null
    ? ''
    : __(item.type === 'boolean' ? 'message.extra_price_feature' : 'message.extra_price_unit', { price: `${item.currency} ${item.unit_price}` }))

// Owned extras first, then what can still be bought; ones neither owned nor for sale are left out.
const owned     = computed(() => extras.value.filter(e => e.quantity > 0))
const available = computed(() => extras.value.filter(e => !e.quantity && e.can_buy))

// What happens to an owned extra at the next renewal.
function status(item) {
    if (!item.renew_quantity) return { text: __('message.extra_ends_at_renewal'), cls: 'bg-light text-muted border' }
    if (item.renew_quantity < item.quantity) return { text: __('message.extra_partly_renews', { renews: item.renew_quantity, count: item.quantity }), cls: 'bg-warning-subtle text-dark' }
    return { text: __('message.extra_renews'), cls: 'bg-success-subtle text-success' }
}

async function load() {
    try {
        extras.value = (await http.get(`/my-order/${props.orderId}/extras`)).data?.data?.extras ?? []
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
}

onMounted(load)

// One quote per pause in typing; a reply for an older quantity is ignored.
let quoteTimer = null
let quoteSeq = 0
function fetchQuote() {
    quote.value = null
    clearTimeout(quoteTimer)
    if (!buying.value || !(buyQty.value >= 1)) return
    quoting.value = true
    quoteTimer = setTimeout(async () => {
        const seq = ++quoteSeq
        try {
            const res = await http.post(`/my-order/${props.orderId}/extras/quote`, { entitlement_id: buying.value.id, quantity: buyQty.value })
            if (seq === quoteSeq) quote.value = res.data?.data ?? null
        } catch (e) {
            if (seq === quoteSeq) errorHandler(e, MODAL)
        } finally {
            if (seq === quoteSeq) quoting.value = false
        }
    }, 300)
}

function openBuy(item) {
    buying.value = item
    buyQty.value = 1
    fetchQuote()
}

function onQty(value) {
    buyQty.value = Number(value)
    fetchQuote()
}

async function buy() {
    submitting.value = true
    try {
        const res = await http.post(`/my-order/${props.orderId}/extras`, { entitlement_id: buying.value.id, quantity: buyQty.value })
        const invoiceId = res.data?.data?.invoice_id
        if (invoiceId) router.push({ path: '/checkout', query: { invoice: invoiceId } })
    } catch (e) {
        errorHandler(e, MODAL)
    } finally {
        submitting.value = false
    }
}

function openRenewal(item) {
    changing.value = item
    renewQty.value = item.renew_quantity
    renewError.value = ''
}

async function saveRenewal() {
    // 0 cancels it; never more than the order has
    const max = changing.value.quantity
    renewError.value = Number.isInteger(renewQty.value) && renewQty.value >= 0 && renewQty.value <= max ? '' : __('message.extra_reduce_range', { min: 0, max })
    if (renewError.value) return
    submitting.value = true
    try {
        const res = await http.post(`/my-order/${props.orderId}/extras/${changing.value.id}/renewal`, { quantity: renewQty.value })
        successHandler(res, COMPONENT)
        changing.value = null
        await load()
    } catch (e) {
        errorHandler(e, MODAL)
    } finally {
        submitting.value = false
    }
}
</script>

<style scoped>
.extras-heading { font-size: .75rem; color: inherit; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; margin-bottom: .5rem; }
.extras-list li + li { border-top: 1px solid rgba(0, 0, 0, .06); }
.extras-status { font-weight: 600; vertical-align: middle; }
.extras-offer { padding: .6rem .8rem; border: 1px solid rgba(0, 0, 0, .09); }
.min-w-0 { min-width: 0; }
</style>
