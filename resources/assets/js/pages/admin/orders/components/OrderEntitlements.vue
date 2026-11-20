<template>
    <div>
        <div v-if="loading" class="text-center py-4"><loader /></div>
        <template v-else>
            <!-- Dates: when the license and each date feature (updates, support, …) end -->
            <h5 class="mb-2">{{ __('message.order_dates') }}</h5>
            <v-client-table :data="dates" :columns="dateColumns" :options="dateTableOptions" class="mb-4" />

            <!-- Features the order has, by category -->
            <div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-2">
                <h5 class="mb-0">{{ __('message.entitlements') }}</h5>
                <div v-if="available.length" style="min-width: 260px">
                    <StaticSelect :key="pickerKey" name="give" classname="mb-0" :value="''" :elements="availableOptions"
                                  :label="__('message.entitlement_give')" :onChange="give" />
                </div>
            </div>
            <p v-if="!items.length" class="text-muted">{{ __('message.entitlement_none_for_order') }}</p>
            <div v-for="group in groups" :key="group.name" class="mb-3">
                <h6 class="text-muted text-uppercase small mb-1">{{ group.name }}</h6>
                <v-client-table :data="group.rows" :columns="columns" :options="tableOptions" />
            </div>
        </template>

        <!-- Feature override -->
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

        <!-- Date edit -->
        <AppModal :showModal="!!dating" :onClose="closeDate" classname="modal-md">
            <template #title><h4>{{ dating?.label }}</h4></template>
            <template #alert><AppAlert :componentName="DATE_EDITOR" /></template>
            <template #fields>
                <template v-if="dating">
                    <Checkbox name="never" :label="__('message.order_date_never')" :value="dating.never" :onChange="v => (dating.never = v)" />
                    <DatePicker v-if="!dating.never" name="date" :label="__('message.order_date_ends')" :value="dating.date"
                                :onChange="v => (dating.date = v)" />
                </template>
            </template>
            <template #controls>
                <action-button action="save" :loading="saving" @click="saveDate" />
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
            @deleted="load"
        />
    </div>
</template>

<script setup>
import { ref, reactive, computed, h, onMounted } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import TextField from '@/components/Reusable/FormField/TextField.vue'
import StaticSelect from '@/components/Reusable/FormField/StaticSelect.vue'
import DatePicker from '@/components/Reusable/FormField/DatePicker.vue'
import Checkbox from '@/components/Reusable/FormField/Checkbox.vue'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'

const props = defineProps({
    orderId: { type: [String, Number], required: true },
})

const COMPONENT   = 'orders-show'
const EDITOR      = 'order-entitlement-editor'
const DATE_EDITOR = 'order-date-editor'

const today     = new Date(new Date().setHours(0, 0, 0, 0))
const loading   = ref(true)
const saving    = ref(false)
const items     = ref([])
const available = ref([])
const dates     = ref([])
const editing   = ref(null)
const dating    = ref(null)
const resetting = ref(null)
const pickerKey = ref(0)

function show(item, value) {
    if (item.type === 'boolean') return value ? __('message.entitlement_on') : __('message.entitlement_off')
    if (item.type === 'number' && Number(value) === -1) return __('message.unlimited')
    return value === null || value === '' ? '—' : String(value)
}

function choices(item) {
    return item.type === 'boolean'
        ? [{ id: '1', name: __('message.entitlement_on') }, { id: '0', name: __('message.entitlement_off') }]
        : (item.options ?? []).map(o => ({ id: o, name: o }))
}

// One table per category, in the order the features come (their sort order).
const groups = computed(() => {
    const byName = new Map()
    for (const row of items.value) {
        const name = row.category || __('message.entitlements')
        if (!byName.has(name)) byName.set(name, [])
        byName.get(name).push(row)
    }
    return [...byName].map(([name, rows]) => ({ name, rows }))
})

const availableOptions = computed(() => available.value.map(i => ({
    id: String(i.id),
    name: i.category ? `${i.category} — ${i.label}` : i.label,
})))

const columns = ['label', 'plan_value', 'extra', 'override', 'value', 'action']
const TABLE = { skin: 'table table-hover table-bordered mb-0', sortable: [], filterable: false, pagination: { show: false }, perPage: 1000, perPageValues: [] }

const tableOptions = reactive({
    ...TABLE,
    headings: {
        label:      __('message.entitlement_name'),
        plan_value: __('message.entitlement_plan_value'),
        extra:      __('message.extra_bought'),
        override:   __('message.entitlement_override'),
        value:      __('message.entitlement_final'),
        action:     __('message.action'),
    },
    templates: {
        label:      (f, row) => h('span', { title: row.code }, row.label),
        plan_value: (f, row) => (row.from_code ? __('message.entitlement_from_agents') : show(row, row.plan_value)),
        extra:      (f, row) => (row.extra ? (row.type === 'boolean' ? __('message.entitlement_on') : `+${row.extra}`) : '—'),
        override:   (f, row) => {
            if (row.override === null) return '—'
            const value = show(row, row.type === 'boolean' ? row.override === '1' : row.override)
            return row.until ? `${value} (${__('message.entitlement_until_date', { date: row.until })})` : value
        },
        value:  (f, row) => h('strong', {}, show(row, row.value)),
        action: (f, row) => (row.locked ? '' : h('div', { class: 'text-nowrap' }, [
            h('button', { type: 'button', class: 'btn btn-light table_btn me-1', title: __('message.edit'), 'aria-label': __('message.edit'), onClick: () => openEditor(row) },
                h('i', { class: 'fas fa-edit' })),
            row.override !== null
                ? h('button', { type: 'button', class: 'btn btn-light table_btn', title: __('message.entitlement_reset'), 'aria-label': __('message.entitlement_reset'), onClick: () => { resetting.value = row } },
                    h('i', { class: 'fas fa-rotate-left' }))
                : null,
        ])),
    },
    texts: { noResults: __('message.entitlement_none_for_order') },
})

const dateColumns = ['label', 'ends_at', 'action']

const dateTableOptions = reactive({
    ...TABLE,
    headings: { label: __('message.entitlement_name'), ends_at: __('message.order_date_ends'), action: __('message.action') },
    templates: {
        ends_at: (f, row) => {
            if (!row.ends_at) return __('message.order_date_never')
            const ended = new Date(`${row.ends_at}T23:59:59`) < new Date()
            return h('span', { class: ended ? 'text-danger' : '' }, [
                row.ends_at,
                ended ? h('span', { class: 'badge bg-danger ms-1' }, __('message.order_date_ended')) : null,
            ])
        },
        action: (f, row) => h('button', { type: 'button', class: 'btn btn-light table_btn', title: __('message.edit'), 'aria-label': __('message.edit'), onClick: () => openDate(row) },
            h('i', { class: 'fas fa-edit' })),
    },
})

async function load() {
    try {
        const res = await http.get(`/order/${props.orderId}/entitlements`)
        const data = res.data?.data ?? {}
        items.value     = data.entitlements ?? []
        available.value = data.available ?? []
        dates.value     = data.dates ?? []
    } catch (e) {
        // An order without a subscription simply has nothing to show.
        if (e?.response?.status !== 404) errorHandler(e, COMPONENT)
        items.value = []
        available.value = []
        dates.value = []
    } finally {
        loading.value = false
    }
}

onMounted(load)

function openEditor(row) {
    const current = row.override ?? (row.type === 'boolean' ? (row.plan_value ? '1' : '0') : row.plan_value)
    editing.value = { ...row, value: current === null ? '' : String(current), until: row.until }
}

// "Give a feature": the same override editor, starting from on / empty.
function give(id) {
    const item = available.value.find(i => String(i.id) === String(id))
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
        await load()
    } catch (e) {
        errorHandler(e, EDITOR)
    } finally {
        saving.value = false
    }
}

function openDate(row) {
    dating.value = { ...row, never: !row.ends_at, date: row.ends_at }
}

function closeDate() {
    if (!saving.value) dating.value = null
}

async function saveDate() {
    saving.value = true
    try {
        const res = await http.put(`/order/${props.orderId}/dates/${dating.value.code}`, {
            date: dating.value.never ? null : (dating.value.date || null), // DatePicker gives YYYY-MM-DD
        })
        successHandler(res, COMPONENT)
        dating.value = null
        await load()
    } catch (e) {
        errorHandler(e, DATE_EDITOR)
    } finally {
        saving.value = false
    }
}
</script>
