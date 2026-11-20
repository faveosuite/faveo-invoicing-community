<template>
    <div>
        <!-- Toolbar: find a feature, or give one the order doesn't have -->
        <div class="d-flex flex-wrap align-items-end gap-3 mb-3">
            <div class="flex-grow-1" style="min-width: 220px; max-width: 360px">
                <label class="form-label" for="feature-search">{{ __('message.entitlement_search') }}</label>
                <input id="feature-search" v-model="search" type="search" class="form-control"
                       :placeholder="__('message.entitlement_search_hint')">
            </div>
            <div v-if="available.length" style="min-width: 240px">
                <StaticSelect :key="pickerKey" name="give" classname="mb-0" :value="''" :elements="availableOptions"
                              :label="__('message.entitlement_give')" :onChange="give" />
            </div>
        </div>

        <p v-if="!items.length" class="text-muted mb-0">{{ __('message.entitlement_none_for_order') }}</p>
        <p v-else-if="!groups.length" class="text-muted mb-0">{{ __('message.entitlement_no_match') }}</p>

        <section v-for="(group, i) in groups" :key="group.name" class="mb-3">
            <button type="button" class="btn btn-link text-body text-decoration-none p-0 mb-2 fw-semibold d-flex align-items-center gap-2"
                    :aria-expanded="isOpen(group, i)" @click="toggle(group, i)">
                <i class="fas fa-chevron-right small group-chevron" :class="{ open: isOpen(group, i) }"></i>
                {{ group.name }} <span class="text-muted fw-normal">({{ group.rows.length }})</span>
            </button>
            <div v-show="isOpen(group, i)" class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0 order-features">
                    <colgroup>
                        <col>
                        <col style="width: 18%">
                        <col style="width: 30%">
                        <col style="width: 4.5rem">
                    </colgroup>
                    <thead class="table-light">
                        <tr>
                            <th scope="col">{{ __('message.entitlement_name') }}</th>
                            <th scope="col" class="text-nowrap">{{ __('message.entitlement_final') }}</th>
                            <th scope="col">{{ __('message.entitlement_from') }}</th>
                            <th scope="col" class="text-end"><span class="visually-hidden">{{ __('message.action') }}</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in group.rows" :key="row.id">
                            <td :title="row.code">{{ row.label }}</td>
                            <td class="fw-semibold text-nowrap" :class="{ 'text-muted': row.value === false }">{{ show(row, row.value) }}</td>
                            <td>
                                <span v-for="part in sources(row)" :key="part.text" class="badge me-1" :class="part.class">{{ part.text }}</span>
                            </td>
                            <td class="text-end text-nowrap">
                                <template v-if="!row.locked">
                                    <button type="button" class="btn btn-link btn-sm p-0 text-muted" v-tooltip="__('message.edit')" :aria-label="`${__('message.edit')} ${row.label}`" @click="openEditor(row)">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button v-if="row.override !== null" type="button" class="btn btn-link btn-sm p-0 ms-2 text-muted" v-tooltip="__('message.entitlement_reset')"
                                            :aria-label="`${__('message.entitlement_reset')} ${row.label}`" @click="resetting = row">
                                        <i class="fas fa-rotate-left"></i>
                                    </button>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Override editor (also for "Give a feature") -->
        <AppModal :showModal="!!editing" :onClose="closeEditor" classname="modal-md">
            <template #title><h4>{{ editing?.label }}</h4></template>
            <template #alert><AppAlert :componentName="EDITOR" /></template>
            <template #fields>
                <template v-if="editing">
                    <p class="text-muted">{{ __('message.entitlement_plan_value_is', { value: show(editing, editing.plan_value) }) }}</p>
                    <StaticSelect v-if="editing.type === 'boolean' || editing.type === 'select'" name="value"
                                  :label="__('message.entitlement_override')" :required="true" :value="editing.value"
                                  :elements="choices(editing)" :hideEmptySelect="true" :onChange="set" />
                    <TextField v-else name="value" :label="__('message.entitlement_override')" :required="true"
                               :type="editing.type === 'number' ? 'number' : 'text'" :value="String(editing.value ?? '')" :onChange="set" />
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
            :message="__('message.entitlement_reset_body', { value: show(resetting, resetting.plan_value) })"
            :btnLabel="__('message.entitlement_reset')"
            btnVariant="primary"
            btnIcon="fa-rotate-left"
            @deleted="emit('changed')"
        />
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import TextField from '@/components/Reusable/FormField/TextField.vue'
import StaticSelect from '@/components/Reusable/FormField/StaticSelect.vue'
import DatePicker from '@/components/Reusable/FormField/DatePicker.vue'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'

// The order page loads the rights once (GET /order/{id}/entitlements) and passes them in.
const props = defineProps({
    orderId:   { type: [String, Number], required: true },
    items:     { type: Array, default: () => [] },
    available: { type: Array, default: () => [] },
})
const emit = defineEmits(['changed'])

const COMPONENT = 'orders-show'
const EDITOR    = 'order-entitlement-editor'

const today     = new Date(new Date().setHours(0, 0, 0, 0))
const saving    = ref(false)
const search    = ref('')
const editing   = ref(null)
const resetting = ref(null)
const pickerKey = ref(0)

function show(item, value) {
    if (item.type === 'boolean') return value ? __('message.entitlement_on') : __('message.entitlement_off')
    if (item.type === 'number' && Number(value) === -1) return __('message.unlimited')
    return value === null || value === '' ? '—' : String(value)
}

/** Where the value comes from: the plan, bought units, an admin override (maybe until a date), or the license code. */
function sources(row) {
    if (row.from_code) return [{ text: __('message.entitlement_source_code'), class: 'text-bg-light border' }]
    const parts = []
    if (row.override !== null) {
        parts.push({
            text: row.until ? __('message.entitlement_source_override_until', { date: row.until }) : __('message.entitlement_source_override'),
            class: 'text-bg-warning',
        })
    }
    if (row.extra) {
        parts.push({ text: row.type === 'number' ? __('message.entitlement_source_bought_units', { count: row.extra }) : __('message.entitlement_source_bought'), class: 'text-bg-info' })
    }
    if (row.plan_value !== null && row.plan_value !== false && row.override === null) {
        parts.push({ text: __('message.entitlement_source_plan'), class: 'text-bg-light border' })
    }
    return parts
}

function choices(item) {
    return item.type === 'boolean'
        ? [{ id: '1', name: __('message.entitlement_on') }, { id: '0', name: __('message.entitlement_off') }]
        : (item.options ?? []).map(o => ({ id: o, name: o }))
}

// One table per category, in the features' own order; the search matches name or code.
const groups = computed(() => {
    const q = search.value.trim().toLowerCase()
    const byName = new Map()
    for (const row of props.items) {
        if (q && !row.label.toLowerCase().includes(q) && !row.code.includes(q)) continue
        const name = row.category || __('message.order_features')
        if (!byName.has(name)) byName.set(name, [])
        byName.get(name).push(row)
    }
    return [...byName].map(([name, rows]) => ({ name, rows }))
})

// The first group starts open, and so does any short one; a search opens them all.
const opened = ref({})
function isOpen(group, index) {
    if (search.value.trim()) return true
    return opened.value[group.name] ?? (index === 0 || group.rows.length <= 8)
}
function toggle(group, index) {
    opened.value = { ...opened.value, [group.name]: !isOpen(group, index) }
}

const availableOptions = computed(() => props.available.map(i => ({
    id: String(i.id),
    name: i.category ? `${i.label} (${i.category})` : i.label,
})))

function openEditor(row) {
    const current = row.override ?? (row.type === 'boolean' ? (row.plan_value ? '1' : '0') : row.plan_value)
    editing.value = { ...row, value: current === null ? '' : String(current), until: row.until }
}

// "Give a feature": the same editor, starting switched on / empty.
function give(id) {
    const item = props.available.find(i => String(i.id) === String(id))
    pickerKey.value++ // reset the picker
    if (!item) return
    editing.value = { ...item, plan_value: null, value: item.type === 'boolean' ? '1' : '', until: null }
}

function closeEditor() {
    if (!saving.value) editing.value = null
}

function set(value, name) {
    editing.value[name] = value
}

async function save() {
    saving.value = true
    try {
        const res = await http.put(`/order/${props.orderId}/entitlements/${editing.value.id}`, {
            value: editing.value.value,
            until: editing.value.until || null,
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

defineExpose({ groups, availableOptions, give, editing, search, sources, isOpen, toggle, save })
</script>

<style scoped>
.group-chevron { transition: transform 0.15s ease; }
.group-chevron.open { transform: rotate(90deg); }
@media (prefers-reduced-motion: reduce) { .group-chevron { transition: none; } }
.order-features td, .order-features th { padding-top: 0.45rem; padding-bottom: 0.45rem; }
</style>
