<template>
    <div>
        <div v-if="loading" class="card card-light"><div class="card-body text-center py-3"><loader /></div></div>
        <template v-else>
            <!-- Agents: how many the plan price covers and what each extra one costs -->
            <div v-if="agents" class="card card-light">
                <div class="card-header"><h4 class="card-title">{{ __('message.agents') }}</h4></div>
                <div class="card-body pb-0">
                    <div class="row">
                        <div class="col-md-6">
                            <TextField name="agents_included" type="number" :label="__('message.plan_agents_included')"
                                       :value="agents.unlimited ? '' : (agents.value ?? '')" :disabled="agents.unlimited"
                                       :placeholder="agents.unlimited ? __('message.unlimited') : ''"
                                       :hint="__('message.entitlement_agents_included_help')"
                                       :onChange="(v) => { agents.value = v }" />
                            <Checkbox name="agents_unlimited" :label="__('message.plan_agents_unlimited')" :value="agents.unlimited"
                                      classname="mb-3" :onChange="(v) => { agents.unlimited = !!v }" />
                        </div>
                        <div v-if="!agents.unlimited" class="col-md-6 d-flex align-items-end align-self-start gap-2">
                            <TextField name="agent_price" :label="__('message.plan_price_per_agent')" classname="flex-grow-1"
                                       :value="priceText(agents) || '—'" :disabled="true" :onChange="() => {}" />
                            <action-button action="edit" class="mb-3" v-tooltip="__('message.edit')" @click="openPrices(agents)" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features -->
            <div class="card card-light">
                <div class="card-header">
                    <h4 class="card-title">
                        {{ __('message.features') }}
                        <ToolTip :message="`${__('message.plan_mode_help_included')} ${__('message.plan_mode_help_optional', { period: periodText })}`" size="small" />
                    </h4>
                </div>
                <div class="card-body">
            <p v-if="!shown.length" class="text-muted">{{ __('message.plan_features_none_yet') }}</p>
            <div v-else class="table-responsive"><div style="min-width: 720px">
            <v-client-table :data="shown" :columns="featureColumns" :options="featureTableOptions">
                <template #label="{ row }">
                    {{ row.label }}
                    <span v-if="!row.is_active" class="badge text-bg-secondary ms-1">{{ __('message.entitlement_archived') }}</span>
                    <div class="small text-muted">{{ row.code }}</div>
                </template>
                <template #mode="{ row }">
                    <StaticSelect :name="`mode_${row.id}`" classname="mb-0" :value="row.mode" :elements="modes(row)"
                                  :hideEmptySelect="true" :onChange="(v) => set(row, 'mode', v)" />
                </template>
                <template #value="{ row }">
                    <template v-if="row.mode === 'included'">
                        <span v-if="row.type === 'boolean'">{{ __('message.entitlement_on') }}</span>
                        <StaticSelect v-else-if="row.type === 'select'" :name="`val_${row.id}`" classname="mb-0" :value="row.value ?? ''"
                                      :elements="choices(row)" :hideEmptySelect="true" :onChange="(v) => set(row, 'value', v)" />
                        <div v-else-if="row.type === 'date'" class="d-flex flex-wrap gap-2">
                            <StaticSelect :name="`ends_${row.id}`" classname="mb-0" :value="row.value?.ends ?? 'subscription'"
                                          :elements="endOptions" :hideEmptySelect="true" :onChange="(v) => setEnds(row, { ends: v })" />
                            <template v-if="row.value?.ends === 'custom'">
                                <TextField :name="`every_${row.id}`" classname="mb-0" type="number" :value="row.value.every ?? ''"
                                           :onChange="(v) => setEnds(row, { every: v })" />
                                <StaticSelect :name="`unit_${row.id}`" classname="mb-0" :value="row.value.unit ?? 'day'"
                                              :elements="unitOptions" :hideEmptySelect="true" :onChange="(v) => setEnds(row, { unit: v })" />
                            </template>
                            <TextField v-if="row.value?.ends === 'fixed'" :name="`date_${row.id}`" classname="mb-0" type="date"
                                       :value="row.value.date ?? ''" :onChange="(v) => setEnds(row, { date: v })" />
                        </div>
                        <TextField v-else :name="`val_${row.id}`" classname="mb-0" :type="row.type === 'number' ? 'number' : 'text'"
                                   :value="row.value ?? ''" :onChange="(v) => set(row, 'value', v)" />
                    </template>
                    <span v-else class="text-muted">{{ row.type === 'number' ? '0' : __('message.entitlement_off') }}</span>
                </template>
                <template #price="{ row }">
                    <template v-if="priceLabel(row)">
                        <span v-if="priceText(row)" class="me-2">{{ priceText(row) }}</span>
                        <action-button action="edit" v-tooltip="__('message.edit')" @click="openPrices(row)" />
                    </template>
                    <span v-else class="text-muted">—</span>
                </template>
                <template #action="{ row }">
                    <action-button action="delete" variant="light" :iconOnly="true" class="table_btn" :label="__('message.plan_feature_remove')"
                                   v-tooltip="__('message.plan_feature_remove')" @click="set(row, 'mode', 'none')" />
                </template>
            </v-client-table>
            </div></div>
            <div v-if="available.length" class="row">
                <DynamicSelect :key="addKey" name="add_feature" :elements="available" :value="null"
                               :placeholder="__('message.plan_feature_add')" :onChange="addFeature" class="col-md-6" />
            </div>
                </div>
            </div>
        </template>

        <AppModal :showModal="!!pricing" :onClose="() => (pricing = null)" classname="modal-lg">
            <template #title><h4>{{ pricing?.title }} — {{ pricing?.label }}</h4></template>
            <template #fields>
                <template v-if="pricing">
                    <p class="small text-muted">{{ __('message.plan_extra_price_help', { period: periodText }) }}</p>
                    <p v-if="!currencies.length" class="text-muted">{{ __('message.plan_no_currencies') }}</p>
                    <v-client-table v-else :data="priceRows" :columns="priceColumns" :options="priceTableOptions" class="mb-2">
                        <template #currency="{ row }"><strong>{{ row.currency }}</strong></template>
                        <template #unit_price="{ row }">
                            <TextField :name="`unit_${row.currency}`" type="number" classname="mb-0" :value="row.unit_price" :placeholder="__('message.entitlement_not_sold')"
                                       :onChange="(v) => { pricing.prices[row.currency].unit_price = v }" />
                        </template>
                        <template #renew_price="{ row }">
                            <TextField :name="`renew_${row.currency}`" type="number" classname="mb-0" :value="row.renew_price" :placeholder="__('message.entitlement_same_as_price')"
                                       :onChange="(v) => { pricing.prices[row.currency].renew_price = v }" />
                        </template>
                        <template #offer_percent="{ row }">
                            <TextField :name="`offer_${row.currency}`" type="number" classname="mb-0" :value="row.offer_percent"
                                       :onChange="(v) => { pricing.prices[row.currency].offer_percent = v }" />
                        </template>
                    </v-client-table>
                    <div v-if="priceError" class="text-danger small">{{ priceError }}</div>
                </template>
            </template>
            <template #controls>
                <action-button action="save" @click="applyPrices" />
            </template>
        </AppModal>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import http from '@/plugins/axios'
import { errorHandler } from '@/helpers/responseHandler.js'
import TextField from '@/components/Reusable/FormField/TextField.vue'
import StaticSelect from '@/components/Reusable/FormField/StaticSelect.vue'
import Checkbox from '@/components/Reusable/FormField/Checkbox.vue'
import DynamicSelect from '@/components/Reusable/FormField/DynamicSelect.vue'
import ToolTip from '@/components/Reusable/Tooltip.vue'

/**
 * The plan's agents and features, shown as sections of the plan form. The form's Save calls
 * save() after the plan itself is saved, so prices follow the plan's currencies.
 */
const props = defineProps({
    planId:     { type: [String, Number], required: true },
    currencies: { type: Array, default: () => [] }, // the plan's currency codes, live from the Pricing table
    period:     { type: String, default: '' },      // the plan's period name
})

const COMPONENT = 'plans-edit'

const loading      = ref(true)
const items        = ref([])
const liveLicenses = ref(0)
const pricing      = ref(null) // { id, label, title, prices: { CUR: {...} } } being edited
const priceError   = ref('')
const addKey       = ref(0)

const periodText = computed(() => props.period || __('message.entitlement_billing_period'))
const agents     = computed(() => items.value.find(i => i.agents) ?? null)
// Only the features this plan uses are listed; the rest are picked from "Add feature".
const shown      = computed(() => items.value.filter(i => !i.agents && i.mode !== 'none'))
const available  = computed(() => items.value.filter(i => !i.agents && i.mode === 'none' && i.is_active)
    .map(i => ({ id: i.id, name: `${i.label} (${i.code})` })))

// Table cells get a copy of the row, so changes go to the item itself.
function set(row, field, value) {
    items.value.find(i => i.id === row.id)[field] = value
}

// A date feature's value is how the plan ends it: { ends, every?, unit?, date? }.
const END_KINDS = ['subscription', 'custom', 'month_end', 'year_end', 'fixed', 'never']
const endOptions = END_KINDS.map(k => ({ id: k, name: __(`message.plan_date_ends_${k}`) }))
const unitOptions = ['day', 'month', 'year'].map(u => ({ id: u, name: __(`message.plan_date_unit_${u}`) }))

function setEnds(row, patch) {
    const item = items.value.find(i => i.id === row.id)
    const next = { ...(item.value ?? {}), ...patch }
    if (patch.ends === 'custom') next.unit ??= 'day'
    item.value = next
}

function endsText(value) {
    const rule = value ?? { ends: 'subscription' }
    if (rule.ends === 'custom') return `${rule.every ?? '?'} ${__(`message.plan_date_unit_${rule.unit ?? 'day'}`)}`
    if (rule.ends === 'fixed') return rule.date ?? '—'
    return __(`message.plan_date_ends_${rule.ends}`)
}

const TABLE = { skin: 'table align-middle mb-2', sortable: [], filterable: false, pagination: { show: false }, perPage: 1000, perPageValues: [] }
const featureColumns = ['label', 'mode', 'value', 'price', 'action']
const featureTableOptions = {
    ...TABLE,
    headings: {
        label: __('message.entitlement_name'),
        mode:  __('message.plan_mode'),
        value: __('message.plan_mode_value'),
        price: __('message.price'),
        action: '',
    },
}
const priceColumns = ['currency', 'unit_price', 'renew_price', 'offer_percent']
const priceTableOptions = {
    ...TABLE,
    headings: {
        currency:      __('message.currency'),
        unit_price:    __('message.price'),
        renew_price:   __('message.renew-price'),
        offer_percent: __('message.offer_price') + ' (%)',
    },
}
const priceRows = computed(() => (pricing.value ? props.currencies.map(cur => ({ currency: cur, ...pricing.value.prices[cur] })) : []))

// One line per thing the customer gets, for the plan summary.
const summary = computed(() => {
    const lines = []
    const firstPrice = item => Object.entries(item.prices ?? {}).find(([cur, p]) => props.currencies.includes(cur) && p.unit_price !== '' && p.unit_price != null)
    if (agents.value) {
        const each = firstPrice(agents.value)
        lines.push({
            label: __('message.agents'),
            value: agents.value.unlimited ? __('message.unlimited') : (agents.value.value || '0'),
            extra: !agents.value.unlimited && each ? __('message.plan_summary_each_extra', { price: `${each[0]} ${each[1].unit_price}` }) : '',
        })
    }
    for (const item of shown.value) {
        const each = firstPrice(item)
        lines.push({
            label: item.label,
            value: item.mode === 'optional' ? __('message.plan_mode_optional_short')
                : item.type === 'date' ? endsText(item.value)
                : (item.type === 'boolean' ? __('message.entitlement_on') : (item.value || item.default_value || '—')),
            extra: each ? `${each[0]} ${each[1].unit_price}` : '',
        })
    }
    return lines
})

function addFeature(option) {
    const item = option && items.value.find(i => i.id === option.id)
    if (item) {
        item.mode = 'included'
        item.value ??= item.type === 'boolean' ? '1' : item.type === 'date' ? { ends: 'subscription' } : (item.default_value ?? '')
    }
    addKey.value++ // reset the picker
}

function choices(item) {
    return (item.options ?? []).map(o => ({ id: o, name: o }))
}

// Only numbers and on/off features can be bought, so only they can be optional.
function modes(item) {
    return [
        { id: 'included', name: __('message.plan_mode_included') },
        ...(item.sellable ? [{ id: 'optional', name: __('message.plan_mode_optional') }] : []),
    ]
}

// What the price column means for this row, or null when there is nothing to price.
function priceLabel(item) {
    if (item.mode === 'optional') return item.type === 'number' ? __('message.plan_price_per_unit') : __('message.plan_price_to_add')
    if (item.mode === 'included' && item.type === 'number') return __('message.plan_price_more_units')
    return null
}

function priceText(item) {
    const set = Object.entries(item.prices ?? {}).filter(([cur, p]) => props.currencies.includes(cur) && p.unit_price !== '' && p.unit_price !== null && p.unit_price !== undefined)
    return set.map(([cur, p]) => `${cur} ${p.unit_price}`).join(', ')
}

// One price row per currency the plan is sold in (kept for currencies added later).
function withAllCurrencies(prices) {
    return Object.fromEntries(props.currencies.map(cur => [cur, {
        unit_price:    prices?.[cur]?.unit_price ?? '',
        renew_price:   prices?.[cur]?.renew_price ?? '',
        offer_percent: prices?.[cur]?.offer_percent ?? '',
    }]))
}

function openPrices(item) {
    priceError.value = ''
    pricing.value = {
        id: item.id, label: item.label,
        title: item.agents ? __('message.plan_price_per_agent') : priceLabel(item),
        prices: withAllCurrencies(item.prices),
    }
}

function applyPrices() {
    const bad = Object.values(pricing.value.prices).some(p =>
        (p.unit_price !== '' && Number(p.unit_price) < 0)
        || (p.offer_percent !== '' && (Number(p.offer_percent) < 0 || Number(p.offer_percent) > 100)))
    if (bad) {
        priceError.value = __('message.entitlement_offer_invalid')
        return
    }
    const item = items.value.find(i => i.id === pricing.value.id)
    item.prices = { ...item.prices, ...pricing.value.prices }
    pricing.value = null
}

async function load() {
    try {
        const res = await http.get(`/plan/${props.planId}/entitlements`)
        liveLicenses.value = res.data?.data?.live_licenses ?? 0
        items.value = (res.data?.data?.entitlements ?? []).map(i => ({
            ...i,
            // Show what the plan actually includes, not a placeholder.
            value: i.mode === 'included' && !i.agents && i.type !== 'date' && (i.value === null || i.value === undefined) ? (i.default_value ?? '') : i.value,
        }))
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
}

/** Saves the plan's agents and features. Throws on failure, so the form shows the error. */
async function save() {
    const entitlements = items.value.map(i => ({
        id:        i.id,
        mode:      i.mode,
        value:     i.type === 'boolean' && i.mode === 'included' ? '1'
            : i.type === 'date' ? (i.mode === 'included' ? i.value : null)
            : (i.value === '' || i.value === null || i.value === undefined ? null : String(i.value)),
        unlimited: !!i.unlimited,
        prices:    Object.fromEntries(Object.entries(i.prices ?? {}).filter(([cur]) => props.currencies.includes(cur))),
    }))
    const res = await http.post(`/plan/${props.planId}/entitlements`, { entitlements })
    await load()
    return res
}

onMounted(load)

defineExpose({ save, liveLicenses, summary })
</script>
