<template>
    <div>
        <div v-if="loading" class="text-center py-4"><loader /></div>
        <v-client-table v-else :data="items" :columns="columns" :options="tableOptions" />

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
            @deleted="load"
        />
    </div>
</template>

<script setup>
import { ref, reactive, h, onMounted } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import TextField from '@/components/Reusable/FormField/TextField.vue'
import StaticSelect from '@/components/Reusable/FormField/StaticSelect.vue'
import DatePicker from '@/components/Reusable/FormField/DatePicker.vue'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'

const props = defineProps({
    orderId: { type: [String, Number], required: true },
})

const COMPONENT = 'orders-show'
const EDITOR    = 'order-entitlement-editor'

const today     = new Date(new Date().setHours(0, 0, 0, 0))
const loading   = ref(true)
const saving    = ref(false)
const items     = ref([])
const editing   = ref(null)
const resetting = ref(null)

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

const columns = ['label', 'plan_value', 'extra', 'override', 'value', 'action']

const tableOptions = reactive({
    skin: 'table table-hover table-striped table-bordered',
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
    sortable:      [],
    filterable:    false,
    pagination:    { show: false },
    perPage:       1000,
    perPageValues: [],
    texts:         { noResults: __('message.entitlement_none_for_order') },
})

async function load() {
    try {
        const res = await http.get(`/order/${props.orderId}/entitlements`)
        items.value = res.data?.data?.entitlements ?? []
    } catch (e) {
        // An order without a license simply has nothing to show.
        if (e?.response?.status !== 404) errorHandler(e, COMPONENT)
        items.value = []
    } finally {
        loading.value = false
    }
}

onMounted(load)

function openEditor(row) {
    const current = row.override ?? (row.type === 'boolean' ? (row.plan_value ? '1' : '0') : row.plan_value)
    editing.value = { ...row, value: current === null ? '' : String(current), until: row.until }
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
</script>
