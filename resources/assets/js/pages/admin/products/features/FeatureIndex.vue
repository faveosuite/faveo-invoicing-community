<template>
    <AppAlert :componentName="COMPONENT" />
    <div class="card card-light">
        <div class="card-header">
            <h4 class="card-title">{{ __('message.features') }}</h4>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" v-tooltip="__('message.entitlement_add')" :aria-label="__('message.entitlement_add')" @click="openEditor()">
                    <i class="fas fa-plus" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <DataTable ref="dtRef" url="/entitlements" :dataColumns="columns" :option="tableOptions" />
        </div>

        <AppModal :showModal="!!editing" :onClose="closeEditor" classname="modal-lg">
            <template #title><h4>{{ editing?.id ? __('message.entitlement_edit') : __('message.entitlement_add') }}</h4></template>
            <template #alert><AppAlert :componentName="EDITOR" /></template>
            <template #fields>
                <template v-if="editing">
                    <div class="row">
                        <TextField name="label" :label="__('message.entitlement_name')" :required="true" :value="editing.label" autocomplete="off"
                                   :onChange="set" :error="errors.label" classname="col-md-6" />
                        <DynamicSelect name="type" :label="__('message.entitlement_type')" :required="true" :elements="typeOptions"
                                       :value="typeOptions.find(o => o.id === editing.type) ?? null" :clearable="false" :searchable="false"
                                       :disabled="!!(editing.in_use || editing.system)"
                                       :tooltip="editing.system ? __('message.entitlement_agents_builtin') : (editing.in_use ? __('message.entitlement_locked_help') : '')"
                                       :onChange="(o) => o && onTypeChange(o.id)" class="col-md-6" />

                        <TextField v-if="editing.type === 'select'" name="optionsText" :label="__('message.entitlement_options')" :required="true"
                                   :value="editing.optionsText" :placeholder="__('message.entitlement_options_hint')" autocomplete="off" :spellcheck="false"
                                   :onChange="set" :error="errors.options" classname="col-md-12" />

                        <!-- Every feature has a default (dates are set per plan; Agents start at one on a plan) -->
                        <DynamicSelect v-if="editing.type === 'boolean'" :key="`d-${editing.type}`" name="default_value"
                                       :label="__('message.entitlement_default')" :required="true" :elements="onOff"
                                       :value="onOff.find(o => o.id === editing.default_value) ?? null" :clearable="false" :searchable="false"
                                       :onChange="(o) => set(o?.id ?? '0', 'default_value')" class="col-md-6" />
                        <DynamicSelect v-else-if="editing.type === 'select'" :key="`d-${editing.type}-${editing.optionsText}`" name="default_value"
                                       :label="__('message.entitlement_default')" :required="true" :elements="defaultChoices"
                                       :value="defaultChoices.find(o => o.id === editing.default_value) ?? null"
                                       :tooltip="__('message.entitlement_default_fallback_help')"
                                       :onChange="(o) => set(o?.id ?? '', 'default_value')" :error="errors.default_value" class="col-md-6" />
                        <template v-else-if="editing.type === 'number' && !editing.system">
                            <RadioButton name="default_kind" :label="__('message.entitlement_default')" :hint="__('message.entitlement_default_number_help')"
                                         :value="editing.unlimited ? 'unlimited' : 'limited'" :options="LIMIT_KINDS"
                                         :onChange="(k) => setLimit(editing.default_value, k === 'unlimited')" classname="col-md-6 mb-3" />
                            <TextField v-if="!editing.unlimited" name="default_value" type="number" inputmode="numeric" autocomplete="off" :label="__('message.entitlement_limit')" :required="true"
                                       :value="editing.default_value" :onChange="set" :error="errors.default_value" classname="col-md-6" />
                        </template>
                        <TextField v-else-if="editing.type === 'text'" :key="`d-${editing.type}`" name="default_value" :label="__('message.entitlement_default')"
                                   :required="true" :value="editing.default_value" :hint="__('message.entitlement_default_fallback_help')"
                                   :onChange="set" :error="errors.default_value" classname="col-md-6" />

                        <TextField name="code" :label="__('message.entitlement_code')" :value="editing.code" autocomplete="off" :spellcheck="false" translate="no"
                                   :placeholder="slugify(editing.label) || __('message.entitlement_code_placeholder')" :disabled="editing.in_use || editing.system"
                                   :hint="__('message.entitlement_code_help')" :onChange="set" :error="errors.code" classname="col-md-6" />
                        <!-- Pick an existing category, or type a new one: no near-duplicates from typos -->
                        <DynamicSelect name="category" :label="__('message.entitlement_category')" :tooltip="__('message.entitlement_category_help')"
                                       :elements="categoryOptions" :value="editing.category ? { name: editing.category } : null"
                                       :taggable="true" :createOption="toCategory" :placeholder="__('message.entitlement_category_placeholder')"
                                       :onChange="(o) => set(o?.name ?? '', 'category')" class="col-md-6" />

                        <div v-if="editing.id && !editing.system" class="col-md-12">
                            <Checkbox name="is_active" :label="__('message.entitlement_active_label')"
                                      :hint="__('message.entitlement_active_help')" :value="editing.is_active" :onChange="set" />
                        </div>

                    </div>
                </template>
            </template>
            <template #controls>
                <action-button action="save" :label="__('message.entitlement_save')" :loading="saving" @click="saveEditor" />
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
            @deleted="reload"
        />
    </div>
</template>

<script setup>
import { ref, reactive, computed, h } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { scrollToFirstError } from '@/helpers/formUtils.js'
import TextField from '@/components/Reusable/FormField/TextField.vue'
import DynamicSelect from '@/components/Reusable/FormField/DynamicSelect.vue'
import Checkbox from '@/components/Reusable/FormField/Checkbox.vue'
import RadioButton from '@/components/Reusable/FormField/RadioButton.vue'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'

const COMPONENT = 'features'
const EDITOR    = 'entitlement-editor'
const SLUG      = /^[a-z][a-z0-9_]*$/
const TYPES     = ['boolean', 'number', 'select', 'text', 'date'] // date: each plan sets how it ends

const typeOptions = TYPES.map(t => ({ id: t, name: __(`message.entitlement_type_${t}`) }))
const onOff       = [{ id: '1', name: __('message.entitlement_on') }, { id: '0', name: __('message.entitlement_off') }]

const dtRef    = ref(null)
const saving   = ref(false)
const editing  = ref(null)
const removing = ref(null)
const errors   = reactive({})

const slugify      = s => (s || '').toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^[^a-z]+|_+$/g, '').slice(0, 50)
const splitOptions = s => [...new Set((s || '').split(',').map(o => o.trim()).filter(Boolean))]
const showDefault  = i => {
    if (i.type === 'boolean') return i.default_value === '1' ? __('message.entitlement_on') : __('message.entitlement_off')
    if (i.type === 'number' && !i.system && i.default_value === null) return __('message.unlimited')
    return i.default_value ?? '—'
}

const columns = ['label', 'code', 'category', 'type', 'default_value', 'action']

const tableOptions = reactive({
    headings: {
        label:         __('message.entitlement_name'),
        code:          __('message.entitlement_code'),
        category:      __('message.entitlement_category'),
        type:          __('message.entitlement_type'),
        default_value: __('message.entitlement_default'),
        action:        __('message.action'),
    },
    templates: {
        label: (f, row) => h('span', {}, [
            row.label,
            row.is_active ? null : h('span', { class: 'badge text-bg-secondary ms-1' }, __('message.entitlement_archived')),
        ]),
        code:          (f, row) => h('code', { translate: 'no' }, row.code),
        category:      (f, row) => row.category ?? '—',
        // A long option list wraps instead of stretching the column
        type:          (f, row) => h('span', { class: 'text-break' }, __(`message.entitlement_type_${row.type}`) + (row.type === 'select' ? ` (${(row.options || []).join(', ')})` : '')),
        default_value: (f, row) => showDefault(row),
        action: (f, row) => h('div', { class: 'text-nowrap' }, [
            h('button', { type: 'button', class: 'btn btn-light table_btn me-1', title: __('message.edit'), 'aria-label': `${__('message.edit')} ${row.label}`, onClick: () => openEditor(row) },
                h('i', { class: 'fas fa-edit', 'aria-hidden': 'true' })),
            !row.system && (row.is_active || !row.in_use)
                ? h('button', {
                    type: 'button', class: 'btn btn-light table_btn',
                    title: row.in_use ? __('message.entitlement_archive') : __('message.delete'),
                    'aria-label': `${row.in_use ? __('message.entitlement_archive') : __('message.delete')} ${row.label}`,
                    onClick: () => { removing.value = row },
                }, h('i', { class: row.in_use ? 'fas fa-box-archive' : 'fas fa-trash', 'aria-hidden': 'true' }))
                : null,
            row.is_active ? null : h('button', {
                type: 'button', class: 'btn btn-light table_btn', title: __('message.restore'), 'aria-label': `${__('message.restore')} ${row.label}`,
                disabled: restoring.value === row.id, onClick: () => restore(row), // one click, one save
            }, h('i', { class: restoring.value === row.id ? 'fas fa-spinner fa-spin' : 'fas fa-rotate-left', 'aria-hidden': 'true' })),
        ]),
    },
    sortable:      ['label', 'category', 'type'],
    filterable:    true,
    syncUrl:       true, // search and page in the URL, so a reload keeps them
    texts:         { noResults: __('message.entitlement_none') },
})

const reload = () => dtRef.value?.refresh()
// A choice feature's options, for picking its default.
const defaultChoices = computed(() => splitOptions(editing.value?.optionsText).map(o => ({ id: o, name: o })))

// The categories already in use, offered in the Category box.
const categoryOptions = ref([])

async function loadCategories() {
    try {
        const names = (await fetchAll()).map(i => i.category?.trim()).filter(Boolean)
        categoryOptions.value = [...new Set(names)].sort((a, b) => a.localeCompare(b)).map(name => ({ name }))
    } catch {
        categoryOptions.value = [] // only suggestions: typing still works
    }
}

// A typed category that matches an existing one apart from case uses the existing one.
function toCategory(text) {
    const name = String(typeof text === 'string' ? text : text?.name ?? '').trim()
    return categoryOptions.value.find(c => c.name.toLowerCase() === name.toLowerCase()) ?? { name }
}

// The editor as opened, to warn before closing it with unsaved changes.
let opened = ''

function openEditor(item = null) {
    Object.keys(errors).forEach(k => delete errors[k])
    editing.value = item
        ? { ...item, optionsText: (item.options ?? []).join(', '), default_value: item.default_value ?? '', unlimited: item.type === 'number' && item.default_value === null }
        : { id: null, label: '', code: '', category: '', type: 'boolean', optionsText: '', default_value: '0', unlimited: false, is_active: true, in_use: false }
    opened = JSON.stringify(editing.value)
    loadCategories()
}

function closeEditor() {
    if (saving.value) return
    if (JSON.stringify(editing.value) !== opened && !confirm(__('message.unsaved_changes_confirm'))) return
    editing.value = null
}

function set(value, name) {
    editing.value[name] = value
}

function onTypeChange(value) {
    editing.value.type = value
    editing.value.default_value = value === 'boolean' ? '0' : ''
    editing.value.unlimited = false
}

// A number is Limited (with its limit beside it) or Unlimited, saved as an empty value.
const LIMIT_KINDS = [{ value: 'limited', name: __('message.limited') }, { value: 'unlimited', name: __('message.unlimited') }]

function setLimit(value, unlimited) {
    editing.value.unlimited = unlimited
    editing.value.default_value = unlimited ? '' : value
}

function validate(e, items) {
    Object.keys(errors).forEach(k => delete errors[k])
    if (!e.label.trim()) errors.label = __('message.entitlement_name_required')
    if (!SLUG.test(e.code)) errors.code = __('message.entitlement_code_invalid')
    else if (items.some(i => i.id !== e.id && i.code === e.code)) {
        errors.code = __('message.entitlement_duplicate', { key: e.code })
    }
    if (e.type === 'select' && !splitOptions(e.optionsText).length) errors.options = __('message.entitlement_options_required')
    const needsDefault = ['select', 'text'].includes(e.type) || (e.type === 'number' && !e.system && !e.unlimited)
    if (needsDefault && (e.default_value === '' || e.default_value === null)) {
        errors.default_value = e.type === 'number' ? __('message.entitlement_limit_required') : __('message.entitlement_default_required')
    } else if (e.type === 'number' && e.default_value !== '' && !(Number.isInteger(Number(e.default_value)) && Number(e.default_value) >= 0)) {
        errors.default_value = __('message.entitlement_number_invalid')
    }
    return !Object.keys(errors).length
}

const toPayload = i => ({
    id:            i.id,
    code:          i.code,
    category:      i.category?.trim() || null,
    label:         i.label.trim(),
    type:          i.type,
    options:       i.type === 'select' ? (i.optionsText !== undefined ? splitOptions(i.optionsText) : i.options) : null,
    default_value: i.default_value === '' || i.default_value === null ? null : String(i.default_value),
    is_active:     !!i.is_active,
})

// The API saves the whole list (the table shows one page), so a change fetches the full
// list and sends it back with that one item changed (or added).
const fetchAll = async () => (await http.get('/entitlements')).data?.data?.entitlements ?? []
const saveList = (items, e) => http.post('/entitlements', {
    entitlements: (e.id ? items.map(i => (i.id === e.id ? e : i)) : [...items, e]).map(toPayload),
})

async function saveEditor() {
    const e = editing.value
    e.code ||= slugify(e.label)

    saving.value = true
    try {
        const items = await fetchAll()
        if (!validate(e, items)) {
            await scrollToFirstError() // focus the first field to fix
            return
        }
        successHandler(await saveList(items, e), COMPONENT)
        editing.value = null
        reload()
    } catch (err) {
        errorHandler(err, EDITOR)
    } finally {
        saving.value = false
    }
}

const restoring = ref(null)

async function restore(row) {
    if (restoring.value) return
    restoring.value = row.id
    try {
        successHandler(await saveList(await fetchAll(), { ...row, is_active: true }), COMPONENT)
        reload()
    } catch (err) {
        errorHandler(err, COMPONENT)
    } finally {
        restoring.value = null
    }
}
</script>
