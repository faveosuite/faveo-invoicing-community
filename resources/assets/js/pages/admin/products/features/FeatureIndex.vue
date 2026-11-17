<template>
    <AppAlert :componentName="COMPONENT" />
    <div class="card card-light">
        <div class="card-header">
            <h4 class="card-title">{{ __('message.features') }}</h4>
            <div class="card-tools">
                <button class="btn btn-tool" v-tooltip="__('message.entitlement_add')" :aria-label="__('message.entitlement_add')" @click="openEditor()">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <p class="text-muted">{{ __('message.feature_list_help') }}</p>
            <div v-if="loading" class="text-center py-4"><loader /></div>
            <v-client-table v-else :data="items" :columns="columns" :options="tableOptions" />
        </div>

        <AppModal :showModal="!!editing" :onClose="closeEditor" classname="modal-lg">
            <template #title><h4>{{ editing?.id ? __('message.entitlement_edit') : __('message.entitlement_add') }}</h4></template>
            <template #alert><AppAlert :componentName="EDITOR" /></template>
            <template #fields>
                <template v-if="editing">
                    <div class="row">
                        <TextField name="label" :label="__('message.entitlement_name')" :required="true" :value="editing.label"
                                   :onChange="set" :error="errors.label" classname="col-md-6" />
                        <StaticSelect name="type" :label="__('message.entitlement_type')" :required="true" :value="editing.type"
                                      :elements="typeOptions" :hideEmptySelect="true" :disabled="editing.in_use || editing.system"
                                      :hint="editing.system ? __('message.entitlement_agents_builtin') : (editing.in_use ? __('message.entitlement_locked_help') : '')"
                                      :onChange="onTypeChange" classname="col-md-6" />

                        <TextField v-if="editing.type === 'select'" name="optionsText" :label="__('message.entitlement_options')" :required="true"
                                   :value="editing.optionsText" :placeholder="__('message.entitlement_options_hint')"
                                   :onChange="set" :error="errors.options" classname="col-md-12" />

                        <StaticSelect v-if="editing.type === 'boolean'" :key="`d-${editing.type}`" name="default_value"
                                      :label="__('message.entitlement_default')" :value="editing.default_value"
                                      :elements="onOff" :hideEmptySelect="true" :onChange="set" classname="col-md-6" />
                        <StaticSelect v-else-if="editing.type === 'select'" :key="`d-${editing.type}-${editing.optionsText}`" name="default_value"
                                      :label="__('message.entitlement_default')" :value="editing.default_value"
                                      :elements="splitOptions(editing.optionsText).map(o => ({ id: o, name: o }))"
                                      :onChange="set" classname="col-md-6" />
                        <TextField v-else :key="`d-${editing.type}`" name="default_value" :label="__('message.entitlement_default')"
                                   :type="editing.type === 'number' ? 'number' : 'text'" :value="editing.default_value"
                                   :onChange="set" :error="errors.default_value" classname="col-md-6" />

                        <TextField name="feature" :label="__('message.entitlement_feature')" :value="editing.feature"
                                   :placeholder="slugify(editing.label) || 'chat'" :disabled="editing.in_use || editing.system"
                                   :hint="__('message.entitlement_code_help')" :onChange="set" :error="errors.code" classname="col-md-3" />
                        <TextField :key="`k-${editing.type}`" name="key" :label="__('message.entitlement_key')" :value="editing.key"
                                   :placeholder="KEYS[editing.type]" :disabled="editing.in_use || editing.system"
                                   :onChange="set" classname="col-md-3" />

                        <div v-if="editing.id && !editing.system" class="col-md-12">
                            <Checkbox name="is_active" :label="__('message.entitlement_active_label')"
                                      :hint="__('message.entitlement_active_help')" :value="editing.is_active" :onChange="set" />
                        </div>

                    </div>
                </template>
            </template>
            <template #controls>
                <action-button action="save" :loading="saving" @click="saveEditor" />
            </template>
        </AppModal>

        <DeleteModal
            v-if="removing"
            :showModal="true"
            :onClose="() => (removing = null)"
            :deleteUrl="`/entitlements/${removing.id}`"
            :componentName="COMPONENT"
            :title="removing.in_use ? __('message.entitlement_archive_title', { name: removing.label }) : __('message.entitlement_delete_title', { name: removing.label })"
            :message="removing.in_use ? __('message.entitlement_archive_body') : __('message.entitlement_delete_body')"
            :btnLabel="removing.in_use ? __('message.entitlement_archive') : __('message.delete')"
            :btnIcon="removing.in_use ? 'fa-box-archive' : 'fa-trash'"
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
import Checkbox from '@/components/Reusable/FormField/Checkbox.vue'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'

const COMPONENT = 'features'
const EDITOR    = 'entitlement-editor'
const SLUG      = /^[a-z][a-z0-9_]*$/
const KEYS      = { boolean: 'enabled', number: 'limit', select: 'value', text: 'value' }

const typeOptions = Object.keys(KEYS).map(t => ({ id: t, name: __(`message.entitlement_type_${t}`) }))
const onOff       = [{ id: '1', name: __('message.entitlement_on') }, { id: '0', name: __('message.entitlement_off') }]

const loading  = ref(true)
const saving   = ref(false)
const items    = ref([])
const editing  = ref(null)
const removing = ref(null)
const errors   = reactive({})

const slugify      = s => (s || '').toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^[^a-z]+|_+$/g, '').slice(0, 50)
const splitOptions = s => [...new Set((s || '').split(',').map(o => o.trim()).filter(Boolean))]
const showDefault  = i => (i.type === 'boolean'
    ? (i.default_value === '1' ? __('message.entitlement_on') : __('message.entitlement_off'))
    : (i.default_value ?? '—'))

const columns = ['label', 'code', 'type', 'default_value', 'action']

const tableOptions = reactive({
    skin: 'table table-hover table-striped table-bordered',
    headings: {
        label:         __('message.entitlement_name'),
        code:          __('message.entitlement_code'),
        type:          __('message.entitlement_type'),
        default_value: __('message.entitlement_default'),
        action:        __('message.action'),
    },
    templates: {
        label: (f, row) => h('span', {}, [
            row.label,
            row.is_active ? null : h('span', { class: 'badge text-bg-secondary ms-1' }, __('message.entitlement_archived')),
        ]),
        code:          (f, row) => h('code', {}, `${row.feature}.${row.key}`),
        type:          (f, row) => __(`message.entitlement_type_${row.type}`) + (row.type === 'select' ? ` (${(row.options || []).join(', ')})` : ''),
        default_value: (f, row) => showDefault(row),
        action: (f, row) => h('div', { class: 'text-nowrap' }, [
            h('button', { type: 'button', class: 'btn btn-light table_btn me-1', title: __('message.edit'), 'aria-label': __('message.edit'), onClick: () => openEditor(row) },
                h('i', { class: 'fas fa-edit' })),
            !row.system && (row.is_active || !row.in_use)
                ? h('button', {
                    type: 'button', class: 'btn btn-light table_btn',
                    title: row.in_use ? __('message.entitlement_archive') : __('message.delete'),
                    'aria-label': row.in_use ? __('message.entitlement_archive') : __('message.delete'),
                    onClick: () => { removing.value = row },
                }, h('i', { class: row.in_use ? 'fas fa-box-archive' : 'fas fa-trash' }))
                : null,
        ]),
    },
    sortable:      ['label', 'type'],
    filterable:    false,
    pagination:    { show: false },
    perPage:       1000,
    perPageValues: [],
    texts:         { noResults: __('message.entitlement_none') },
})

async function load() {
    try {
        const res = await http.get('/entitlements')
        items.value = res.data?.data?.entitlements ?? []
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
}

onMounted(load)

function openEditor(item = null) {
    Object.keys(errors).forEach(k => delete errors[k])
    editing.value = item
        ? { ...item, optionsText: (item.options ?? []).join(', '), default_value: item.default_value ?? '' }
        : { id: null, label: '', feature: '', key: '', type: 'boolean', optionsText: '', default_value: '0', is_active: true, in_use: false }
}

function closeEditor() {
    if (!saving.value) editing.value = null
}

function set(value, name) {
    editing.value[name] = value
}

function onTypeChange(value) {
    editing.value.type = value
    editing.value.default_value = value === 'boolean' ? '0' : ''
}

function validate(e) {
    Object.keys(errors).forEach(k => delete errors[k])
    if (!e.label.trim()) errors.label = __('message.entitlement_name_required')
    if (!SLUG.test(e.feature) || !SLUG.test(e.key)) errors.code = __('message.entitlement_code_invalid')
    else if (items.value.some(i => i.id !== e.id && i.feature === e.feature && i.key === e.key)) {
        errors.code = __('message.entitlement_duplicate', { key: `${e.feature}.${e.key}` })
    }
    if (e.type === 'select' && !splitOptions(e.optionsText).length) errors.options = __('message.entitlement_options_required')
    if (e.type === 'number' && e.default_value !== '' && !(Number.isInteger(Number(e.default_value)) && Number(e.default_value) >= 0)) {
        errors.default_value = __('message.entitlement_number_invalid')
    }
    return !Object.keys(errors).length
}

const toPayload = i => ({
    id:            i.id,
    feature:       i.feature,
    key:           i.key,
    label:         i.label.trim(),
    type:          i.type,
    options:       i.type === 'select' ? (i.optionsText !== undefined ? splitOptions(i.optionsText) : i.options) : null,
    default_value: i.default_value === '' || i.default_value === null ? null : String(i.default_value),
    is_active:     !!i.is_active,
})

// The API saves the whole list, so an add or edit sends the list with that one change.
async function saveEditor() {
    const e = editing.value
    e.feature ||= slugify(e.label)
    e.key ||= KEYS[e.type]
    if (!validate(e)) return

    saving.value = true
    try {
        const list = e.id ? items.value.map(i => (i.id === e.id ? e : i)) : [...items.value, e]
        const res = await http.post('/entitlements', { entitlements: list.map(toPayload) })
        successHandler(res, COMPONENT)
        editing.value = null
        await load()
    } catch (err) {
        errorHandler(err, EDITOR)
    } finally {
        saving.value = false
    }
}
</script>
