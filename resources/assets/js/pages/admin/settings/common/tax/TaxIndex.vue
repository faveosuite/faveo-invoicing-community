<template>
    <div>
        <AppAlert :componentName="COMPONENT" />

        <!-- Tax Options -->
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.options') }}</h4>
            </div>
            <div v-if="optionsLoading" class="row justify-content-center py-3"><loader /></div>
            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <SelectField
                                name="tax_enable"
                                :label="__('message.tax-enable')"
                                :tooltip="__('message.tt_tax_enable')"
                                :elements="enabledOptions"
                                :value="enabledOptions.find(o => o.id === options.tax_enable) ?? null"
                                :onChange="(val) => options.tax_enable = val?.id ?? 0"
                                :clearable="false"
                                :searchable="false"
                            />
                        </div>
                        <div class="col-md-4">
                            <SelectField
                                name="inclusive"
                                :label="__('message.prices-entered-with-tax')"
                                :tooltip="__('message.tt_inclusive')"
                                :elements="inclusiveOptions"
                                :value="inclusiveOptions.find(o => o.id === options.inclusive) ?? null"
                                :onChange="(val) => options.inclusive = val?.id ?? 0"
                                :clearable="false"
                                :searchable="false"
                            />
                        </div>
                        <div class="col-md-4">
                            <SelectField
                                name="rounding"
                                :label="__('message.rounding')"
                                :tooltip="__('message.tt_rounding')"
                                :elements="enabledOptions"
                                :value="enabledOptions.find(o => o.id === options.rounding) ?? null"
                                :onChange="(val) => options.rounding = val?.id ?? 0"
                                :clearable="false"
                                :searchable="false"
                            />
                        </div>
                        <div class="col-md-4">
                            <SelectField
                                name="tax_based_on"
                                :label="__('message.calculate_tax_based_on')"
                                :tooltip="__('message.tt_based_on')"
                                :elements="basedOnOptions"
                                :value="basedOnOptions.find(o => o.id === options.tax_based_on) ?? basedOnOptions[0]"
                                :onChange="(val) => options.tax_based_on = val?.id ?? 'billing'"
                                :clearable="false"
                                :searchable="false"
                            />
                        </div>
                        <div class="col-md-8">
                            <SelectField
                                name="additional_tax_classes"
                                :label="__('message.additional_tax_classes')"
                                :tooltip="__('message.additional_tax_classes_hint')"
                                :elements="noClassOptions"
                                :value="additionalClasses"
                                :onChange="onClassesChange"
                                :createOption="(t) => ({ name: (typeof t === 'string' ? t : t.name).trim() })"
                                :multiple="true"
                                :taggable="true"
                                :searchable="true"
                                :clearable="true"
                                :closeOnSelect="false"
                                placeholder="Type a class name and press Enter"
                            />
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <action-button action="save" :loading="savingOptions" @click="saveOptions" />
                </div>
            </template>
        </div>

        <!-- Tax Rates (tabbed by class) -->
        <div class="card card-light mt-3">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.tax_rates') }}</h4>
                <div class="card-tools">
                    <RouterLink
                        :to="createTo"
                        class="btn btn-tool"
                        v-tooltip="__('message.create')"
                    >
                        <i class="fas fa-plus fw-bold"></i>
                    </RouterLink>
                </div>
            </div>

            <div class="card-body">
                <ul class="nav nav-tabs mb-3">
                    <li class="nav-item" v-for="c in orderedClasses" :key="c.slug">
                        <a
                            href="#"
                            class="nav-link"
                            :class="{ active: activeClass === c.slug }"
                            @click.prevent="setActiveClass(c.slug)"
                        >{{ c.name }}</a>
                    </li>
                </ul>

                <DataTable
                    ref="dtRef"
                    :url="apiUrl"
                    :dataColumns="columns"
                    :option="tableOptions"
                >
                    <template #bulk-actions>
                        <div v-if="selected.length > 0" class="dropdown">
                            <button
                                class="btn btn-sm btn-secondary dropdown-toggle"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                            >
                                {{ __('message.bulk_action') }}
                            </button>
                            <ul class="dropdown-menu">
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button class="dropdown-item" @click="confirmBulkDelete">
                                        {{ __('message.Delete') }}
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>

    </div>

    <DeleteModal
        v-if="pendingBulkDelete"
        :showModal="true"
        :onClose="() => pendingBulkDelete = null"
        :deleteUrl="`${baseUrl}/tax/delete`"
        :deleteData="pendingBulkDelete"
        :title="__('message.Delete')"
        :message="__('message.are_you_sure')"
        :componentName="COMPONENT"
        @deleted="() => { pendingBulkDelete = null; selected.value = []; dtRef.value?.refresh() }"
    />
</template>

<script setup>
import { h, ref, reactive, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'
import { useBaseUrl } from '@/core/composables/useBaseUrl'

const COMPONENT = 'tax-index'
const baseUrl = useBaseUrl()
const apiUrl  = `/tax-tables`

const dtRef          = ref(null)
const selected       = ref([])
const optionsLoading = ref(true)
const savingOptions  = ref(false)
const pendingBulkDelete = ref(null)

const allSelected = computed(() => {
    const data = dtRef.value?.tableData ?? []
    return data.length > 0 && data.every(row => selected.value.includes(row.id))
})

function toggleAll(e) {
    const data = dtRef.value?.tableData ?? []
    if (e.target.checked) {
        const ids = data.map(r => r.id).filter(id => !selected.value.includes(id))
        selected.value.push(...ids)
    } else {
        const ids = new Set(data.map(r => r.id))
        selected.value = selected.value.filter(id => !ids.has(id))
    }
}

const enabledOptions   = [{ id: 1, name: __('message.caps_enabled') }, { id: 0, name: __('message.caps_disabled') }]
const inclusiveOptions = [{ id: 1, name: __('message.caps_inclusive') }, { id: 0, name: __('message.caps_exclusive') }]
const basedOnOptions   = [{ id: 'billing', name: __('message.billing_address') }, { id: 'base', name: __('message.base_address') }]

const options = reactive({ tax_enable: 0, inclusive: 0, rounding: 0, tax_based_on: 'billing' })

// Additional tax classes as tags ({ name } objects), edited via a taggable multiselect.
// Stable empty options list: tags come from the value, not from a preset dropdown
// (binding the value here would trip SelectField's elements-watch and clear them).
const noClassOptions = []
const additionalClasses = ref([])

function onClassesChange(val) {
    additionalClasses.value = (val || []).map(v => ({
        name: (typeof v === 'string' ? v : (v?.name ?? '')).trim(),
    })).filter(c => c.name !== '')
}

// --- Tax classes (drive the rate tabs) ---
const classes     = ref([{ slug: '', name: 'Standard' }])
const activeClass  = ref('')

const orderedClasses = computed(() => {
    return [...classes.value].sort((a, b) => {
        if (a.slug === '') return -1
        if (b.slug === '') return 1
        return a.name.localeCompare(b.name)
    })
})

const createTo = computed(() => ({
    path: '/settings/common/tax/create',
    query: activeClass.value ? { class: activeClass.value } : {},
}))

function setActiveClass(slug) {
    activeClass.value = slug
    selected.value = []
    dtRef.value?.refresh()
}

async function loadOptions() {
    try {
        const res = await http.get(`/tax-options`)
        const d   = res.data?.data ?? {}
        const o   = d.options ?? {}
        options.tax_enable             = o.tax_enable ?? 0
        options.inclusive              = o.inclusive  ?? 0
        options.rounding               = o.rounding   ?? 0
        options.tax_based_on           = o.tax_based_on ?? 'billing'
        additionalClasses.value = (d.additional_tax_classes ?? '')
            .split('\n').map(s => s.trim()).filter(Boolean).map(name => ({ name }))
        classes.value = (d.classes ?? []).length ? d.classes : [{ slug: '', name: 'Standard' }]
        if (!classes.value.some(c => c.slug === activeClass.value)) activeClass.value = ''
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        optionsLoading.value = false
    }
}

onMounted(loadOptions)

async function saveOptions() {
    savingOptions.value = true
    try {
        const res = await http.post(`/taxes/option`, {
            tax_enable:             options.tax_enable,
            inclusive:              options.inclusive,
            rounding:               options.rounding,
            tax_based_on:           options.tax_based_on,
            additional_tax_classes: additionalClasses.value.map(c => c.name).join('\n'),
        })
        successHandler(res, COMPONENT)
        await loadOptions()   // refresh tabs in case classes changed
        dtRef.value?.refresh()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        savingOptions.value = false
    }
}

function confirmBulkDelete() {
    if (!selected.value.length) return
    pendingBulkDelete.value = { select: [...selected.value] }
}

const columns = ['select', 'name', 'country', 'state', 'rate', 'priority', 'compound', 'active', 'action']

const tableOptions = reactive({
    headings: {
        select: () => h('input', {
            type:     'checkbox',
            checked:  allSelected.value,
            onChange: toggleAll,
        }),
        name:     __('message.name'),
        country:  __('message.country'),
        state:    __('message.state'),
        rate:     __('message.rate') + ' (%)',
        priority: __('message.priority'),
        compound: __('message.compound'),
        active:   __('message.status'),
        action:   __('message.action'),
    },
    columnsClasses: {
        select: 'dt-select',
        name: 'dt-name',
        country: 'dt-country',
        state: 'dt-name',
        rate: 'dt-code',
        priority: 'dt-code',
        compound: 'dt-code',
        active: 'dt-name',
        action: 'dt-action',
    },
    templates: {
        select: (f, row) => h('input', {
            type:     'checkbox',
            checked:  selected.value.includes(row.id),
            onChange: (e) => {
                if (e.target.checked) selected.value = [...selected.value, row.id]
                else selected.value = selected.value.filter(id => id !== row.id)
            },
        }),
        name:     (f, row) => row.name     || '—',
        country:  (f, row) => row.country  || '—',
        state:    (f, row) => row.state    || '—',
        rate:     (f, row) => row.rate ?? '—',
        priority: (f, row) => row.priority ?? '—',
        compound: (f, row) => row.compound || '—',
        active:   (f, row) => row.active   || '—',
        action: (f, row) => h(RouterLink, {
            to:    `/settings/common/tax/${row.id}/edit`,
            class: 'btn btn-light table_btn',
            title: __('message.edit'),
        }, () => h('i', { class: 'fas fa-edit' })),
    },
    sortable:   ['name', 'country', 'state', 'rate', 'priority'],
    filterable: true,
    requestAdapter(data) {
        return {
            'sort-field':   data.orderBy   ?? 'created_at',
            'sort-order':   data.orderBy ? (data.ascending ? 'asc' : 'desc') : 'desc',
            'search-query': (data.query ?? '').trim(),
            tax_class:      activeClass.value,
            page:  data.page,
            limit: data.limit,
        }
    },
    orderBy: { column: 'created_at', ascending: false },
})
</script>
