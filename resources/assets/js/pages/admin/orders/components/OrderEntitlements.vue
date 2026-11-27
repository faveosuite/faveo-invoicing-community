<template>
    <div>
        <!-- Toolbar, laid out like DataTable's: give a feature at the start, search at the end -->
        <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
            <div v-if="available.length" class="col-12 col-md-5 px-0">
                <DynamicSelect :key="pickerKey" name="give" :value="null" :elements="availableOptions"
                               :label="__('message.entitlement_give')" :onChange="(o) => give(o?.id)" />
            </div>
            <!-- Same search box as every DataTable (same wrapper, so it stays the same width) -->
            <div class="d-flex align-items-center gap-2 ms-auto">
                <input v-model="searchInput" type="text" class="form-control globe-search" name="feature_search" @keyup.enter="search = searchInput"
                       :placeholder="__('message.search_placeholder')" :aria-label="__('message.entitlement_search')"
                       autocomplete="off" spellcheck="false" />
            </div>
        </div>

        <!-- Narrow by category -->
        <div v-if="categories.length > 1" class="d-flex flex-wrap gap-2 mb-3" role="group" :aria-label="__('message.entitlement_category')">
            <action-button v-for="c in ['', ...categories]" :key="c" variant="light" class="btn-xs" :class="{ active: category === c }"
                           :label="c || __('message.entitlement_all')" :aria-pressed="category === c" @click="category = c" />
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered align-middle">
                <thead>
                    <tr>
                        <th scope="col">{{ __('message.entitlement_name') }}</th>
                        <th scope="col">{{ __('message.entitlement_category') }}</th>
                        <th scope="col">{{ __('message.entitlement_final') }}</th>
                        <th scope="col">{{ __('message.entitlement_from') }}</th>
                        <th scope="col">{{ __('message.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Dates the plan gives (updates, support, …): when each right ends -->
                    <template v-for="row in pageRows" :key="row.isDate ? `date-${row.code}` : row.id">
                    <tr v-if="row.isDate">
                        <td :title="row.code">{{ row.label }}</td>
                        <td>{{ DATES }}</td>
                        <td>
                            {{ row.ends_at ? formatDate(row.ends_at) : __('message.order_date_never') }}
                            <span v-if="row.hint" class="badge ms-1" :class="{ good: 'bg-success', warn: 'bg-warning text-dark', bad: 'bg-danger' }[row.tone]">{{ row.hint }}</span>
                        </td>
                        <td class="text-muted">—</td>
                        <td class="text-nowrap">
                            <action-button action="edit" :label="`${__('message.order_change_date')}: ${row.label}`" v-tooltip="__('message.order_change_date')" @click="emit('edit-date', row)" />
                        </td>
                    </tr>
                    <tr v-else>
                        <td :title="row.code">{{ row.label }}</td>
                        <td>{{ categoryOf(row) }}</td>
                        <td :class="{ 'text-muted': row.value === false }">{{ show(row, row.value) }}</td>
                        <td>
                            <span v-for="part in sources(row)" :key="part.text" class="badge me-1" :class="part.class">{{ part.text }}</span>
                        </td>
                        <td class="text-nowrap">
                            <action-button action="edit" :label="`${__('message.edit')} ${row.label}`" v-tooltip="__('message.edit')" @click="openEditor(row)" />
                            <action-button v-if="row.overridden" variant="light" class="table_btn ms-1" icon="fas fa-rotate-left" :iconOnly="true"
                                           :label="`${__('message.entitlement_reset')} ${row.label}`" v-tooltip="__('message.entitlement_reset')" @click="resetting = row" />
                        </td>
                    </tr>
                    </template>
                    <tr v-if="!total">
                        <td colspan="5" class="text-center text-muted">
                            {{ items.length || dates.length ? __('message.entitlement_no_match') : __('message.entitlement_none_for_order') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-if="total" class="pagination-container d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <template v-if="total === 1">{{ __('message.one_record') }}</template>
                <template v-else-if="total <= PER_PAGE">{{ __('message.x_records', { count: total }) }}</template>
                <template v-else>{{ __('message.datatable_info').replace('_START_', from).replace('_END_', to).replace('_TOTAL_', total) }}</template>
            </div>
            <Pagination :currentPage="page" :totalPages="pages" @change="p => (page = p)" />
        </div>

        <!-- Override editor (also for "Give a feature") -->
        <AppModal :showModal="!!editing" :onClose="closeEditor" classname="modal-md">
            <template #title><h4>{{ editing?.label }}</h4></template>
            <template #alert><AppAlert :componentName="EDITOR" /></template>
            <template #fields>
                <template v-if="editing">
                    <p class="text-muted">{{ __('message.entitlement_plan_value_is', { value: showPlan(editing) }) }}</p>
                    <DynamicSelect v-if="editing.type === 'boolean' || editing.type === 'select'" name="value"
                                   :label="__('message.entitlement_override')" :required="true" :elements="choices(editing)"
                                   :value="choices(editing).find(o => o.id === editing.value) ?? null" :clearable="false"
                                   :onChange="(o) => set(o?.id ?? '', 'value')" />
                    <!-- A number is Limited (its limit beside it) or Unlimited, chosen -->
                    <div v-else-if="editing.type === 'number'" class="row">
                        <RadioButton name="value_kind" :label="__('message.entitlement_override')" :value="editing.unlimited ? 'unlimited' : 'limited'"
                                     :options="LIMIT_KINDS" :onChange="(k) => setLimit(editing.value, k === 'unlimited')" classname="col-md-6 mb-3" />
                        <TextField v-if="!editing.unlimited" name="value" type="number" :label="__('message.entitlement_limit')" :required="true"
                                   :value="String(editing.value ?? '')" :error="valueError" :onChange="set" classname="col-md-6" />
                    </div>
                    <TextField v-else name="value" :label="__('message.entitlement_override')" :required="true"
                               :value="String(editing.value ?? '')" :onChange="set" />
                    <DatePicker name="until" :label="__('message.entitlement_until')" :value="editing.until"
                                :disabledDate="d => d < today" :onChange="set" />
                    <small class="text-muted">{{ __('message.entitlement_until_help') }}</small>
                </template>
            </template>
            <template #controls>
                <action-button action="save" :loading="saving" @click="save" />
            </template>
        </AppModal>

        <DeleteModal
            v-if="resetting"
            :showModal="true"
            :onClose="() => (resetting = null)"
            :deleteUrl="`/order/${orderId}/entitlements/${resetting.id}`"
            :componentName="COMPONENT"
            :title="__('message.entitlement_reset_title', { name: resetting.label })"
            :message="__('message.entitlement_reset_body', { value: showPlan(resetting) })"
            :btnLabel="__('message.entitlement_reset')"
            btnVariant="primary"
            btnIcon="fa-rotate-left"
            @deleted="emit('changed')"
        />
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import TextField from '@/components/Reusable/FormField/TextField.vue'
import DynamicSelect from '@/components/Reusable/FormField/DynamicSelect.vue'
import DatePicker from '@/components/Reusable/FormField/DatePicker.vue'
import RadioButton from '@/components/Reusable/FormField/RadioButton.vue'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'
import Pagination from '@/components/Reusable/Pagination.vue'
import { useDateTime } from '@/core/composables/useDateTime'

// The order page loads the rights once (GET /order/{id}/entitlements) and passes them in.
const props = defineProps({
    orderId:   { type: [String, Number], required: true },
    items:     { type: Array, default: () => [] },
    available: { type: Array, default: () => [] },
    // End dates other than the license's own, with the page's hint/tone ({ code, label, ends_at, hint, tone })
    dates:     { type: Array, default: () => [] },
})
const emit = defineEmits(['changed', 'edit-date'])
const { formatDate } = useDateTime()

const COMPONENT = 'orders-show'
const EDITOR    = 'order-entitlement-editor'

const today     = new Date(new Date().setHours(0, 0, 0, 0))
const saving    = ref(false)
// Search, category and page live in the URL (?fq=, ?fcat=, ?fpage=) so a reload keeps them.
const route     = useRoute()
const router    = useRouter()
const search    = ref(route?.query?.fq ?? '')
const searchInput = ref(search.value) // applied on Enter, like DataTable
const editing   = ref(null)
const resetting = ref(null)
const pickerKey = ref(0)
const valueError = ref('')

// A number with no value is unlimited.
function show(item, value) {
    if (item.type === 'boolean') return value ? __('message.entitlement_on') : __('message.entitlement_off')
    if (item.type === 'number' && value === null) return __('message.unlimited')
    return value === null || value === '' ? '—' : String(value)
}

// What the plan gives; a feature the plan doesn't have gives nothing.
const showPlan = item => (item.in_plan ? show(item, item.plan_value) : '—')

/** Where the value comes from: the plan, bought units, an admin override (maybe until a date), or the license code. */
function sources(row) {
    if (row.from_code) return [{ text: __('message.entitlement_source_code'), class: 'text-bg-light border' }]
    const parts = []
    if (row.overridden) {
        parts.push({
            text: row.until ? __('message.entitlement_source_override_until', { date: row.until }) : __('message.entitlement_source_override'),
            class: 'text-bg-warning',
        })
    }
    if (row.extra) {
        parts.push({ text: row.type === 'number' ? __('message.entitlement_source_bought_units', { count: row.extra }) : __('message.entitlement_source_bought'), class: 'text-bg-info' })
    }
    if (row.in_plan && row.plan_value !== false && !row.overridden) {
        parts.push({ text: __('message.entitlement_source_plan'), class: 'text-bg-light border' })
    }
    return parts
}

function choices(item) {
    return item.type === 'boolean'
        ? [{ id: '1', name: __('message.entitlement_on') }, { id: '0', name: __('message.entitlement_off') }]
        : (item.options ?? []).map(o => ({ id: o, name: o }))
}

// One table: the dates first, then the features in their own order. The search matches name or code.
const category   = ref(route?.query?.fcat ?? '') // '' = all
const DATES      = __('message.order_dates')
const categoryOf = row => row.category || __('message.order_features')
const categories = computed(() => [...new Set([...(props.dates.length ? [DATES] : []), ...props.items.map(categoryOf)])])

const matches = row => {
    const q = search.value.trim().toLowerCase()
    return !q || row.label.toLowerCase().includes(q) || row.code.includes(q)
}
const dateRows = computed(() => (!category.value || category.value === DATES ? props.dates.filter(matches) : []))
const rows     = computed(() => props.items.filter(r => (!category.value || categoryOf(r) === category.value) && matches(r)))
const total    = computed(() => dateRows.value.length + rows.value.length)

// Paged like DataTable, 10 a page; a new search starts again at page 1.
const PER_PAGE = 10
const page     = ref(Math.max(1, Number(route?.query?.fpage) || 1))
const pages    = computed(() => Math.max(1, Math.ceil(total.value / PER_PAGE)))
const from     = computed(() => (page.value - 1) * PER_PAGE + 1)
const to       = computed(() => Math.min(page.value * PER_PAGE, total.value))
const pageRows = computed(() => [...dateRows.value.map(d => ({ ...d, isDate: true })), ...rows.value].slice(from.value - 1, to.value))
watch([search, category], () => (page.value = 1))
watch([search, category, page], ([q, cat, p]) => {
    router?.replace({ query: { ...route.query, fq: q.trim() || undefined, fcat: cat || undefined, fpage: p > 1 ? p : undefined } })
})

const availableOptions = computed(() => props.available.map(i => ({
    id: String(i.id),
    name: i.category ? `${i.label} (${i.category})` : i.label,
})))

function openEditor(row) {
    valueError.value = ''
    const current = row.overridden ? row.override : (row.type === 'boolean' ? (row.plan_value ? '1' : '0') : row.plan_value)
    const unlimited = row.type === 'number' && (row.overridden || row.in_plan) && current === null
    editing.value = { ...row, value: current === null ? '' : String(current), unlimited, until: row.until }
}

// "Give a feature": the same editor, starting switched on / empty.
function give(id) {
    const item = props.available.find(i => String(i.id) === String(id))
    pickerKey.value++ // reset the picker
    if (!item) return
    valueError.value = ''
    editing.value = { ...item, plan_value: null, in_plan: false, value: item.type === 'boolean' ? '1' : '', unlimited: false, until: null }
}

function closeEditor() {
    if (!saving.value) editing.value = null
}

function set(value, name) {
    editing.value[name] = value
}

// A number is Limited (with its limit beside it) or Unlimited, saved as an empty value.
const LIMIT_KINDS = [{ value: 'limited', name: __('message.limited') }, { value: 'unlimited', name: __('message.unlimited') }]

function setLimit(value, unlimited) {
    editing.value.unlimited = unlimited
    editing.value.value = unlimited ? '' : value
}

async function save() {
    const e = editing.value
    valueError.value = e.type === 'number' && !e.unlimited && String(e.value ?? '').trim() === '' ? __('message.entitlement_limit_required') : ''
    if (valueError.value) return
    saving.value = true
    try {
        const res = await http.put(`/order/${props.orderId}/entitlements/${e.id}`, {
            ...(e.unlimited ? { unlimited: true } : { value: e.value }),
            until: e.until || null,
        })
        successHandler(res, COMPONENT)
        editing.value = null
        emit('changed')
    } catch (e) {
        errorHandler(e, EDITOR)
    } finally {
        saving.value = false
    }
}

defineExpose({ searchInput, category, categories, rows, dateRows, total, page, pages, pageRows, availableOptions, give, editing, search, sources, save })
</script>
