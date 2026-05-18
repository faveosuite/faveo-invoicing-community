<template>
    <div>
        <AppAlert :componentName="COMPONENT" />

        <!-- Tax Options -->
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.options') }}</h4>
            </div>
            <div v-if="optionsLoading" class="card-body text-center py-4">
                <span class="spinner-border text-secondary"></span>
            </div>
            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <SelectField
                                name="tax_enable"
                                :label="__('message.tax-enable')"
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
                                :elements="enabledOptions"
                                :value="enabledOptions.find(o => o.id === options.rounding) ?? null"
                                :onChange="(val) => options.rounding = val?.id ?? 0"
                                :clearable="false"
                                :searchable="false"
                            />
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary" @click="saveOptions" :disabled="savingOptions">
                        <span v-if="savingOptions" class="spinner-border spinner-border-sm me-1"></span>
                        <i v-else class="fas fa-save me-1"></i>
                        {{ __('message.save') }}
                    </button>
                </div>
            </template>
        </div>

        <!-- Tax Classes -->
        <div class="card card-light mt-3">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.tax_classes') }}</h4>
                <div class="card-tools">
                    <RouterLink
                        to="/settings/common/tax/create"
                        class="btn btn-tool"
                        :title="__('message.create')"
                        v-tooltip
                    >
                        <i class="fas fa-plus fw-bold"></i>
                    </RouterLink>
                </div>
            </div>

            <div class="card-body">
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
                                Bulk Action
                            </button>
                            <ul class="dropdown-menu">
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button class="dropdown-item" @click="bulkDelete">
                                        Delete
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>

    </div>
</template>

<script setup>
import { h, ref, reactive, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'tax-index'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl  = `${baseUrl}/tax-tables`

const dtRef          = ref(null)
const selected       = ref([])
const optionsLoading = ref(true)
const savingOptions  = ref(false)

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
        const ids = data.map(r => r.id)
        selected.value = selected.value.filter(id => !ids.includes(id))
    }
}

const enabledOptions  = [{ id: 1, name: __('message.caps_enabled') }, { id: 0, name: __('message.caps_disabled') }]
const inclusiveOptions = [{ id: 1, name: __('message.caps_inclusive') }, { id: 0, name: __('message.caps_exclusive') }]

const options = reactive({ tax_enable: 0, inclusive: 0, rounding: 0 })

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/tax-options`)
        const d   = res.data?.data ?? {}
        const o   = d.options ?? {}
        options.tax_enable = o.tax_enable ?? 0
        options.inclusive  = o.inclusive  ?? 0
        options.rounding   = o.rounding   ?? 0
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        optionsLoading.value = false
    }
})

async function saveOptions() {
    savingOptions.value = true
    try {
        const res = await http.post(`${baseUrl}/taxes/option`, {
            tax_enable: options.tax_enable,
            inclusive:  options.inclusive,
            rounding:   options.rounding,
        })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        savingOptions.value = false
    }
}

async function bulkDelete() {
    if (!selected.value.length) return
    try {
        const res = await http.delete(`${baseUrl}/tax/delete`, { data: { select: selected.value } })
        successHandler(res, COMPONENT)
        selected.value = []
        dtRef.value?.refresh()
    } catch (e) {
        errorHandler(e, COMPONENT)
    }
}

const columns = ['select', 'tax_class_name', 'name', 'country', 'state', 'rate', 'action']

const tableOptions = reactive({
    headings: {
        select: () => h('input', {
            type:     'checkbox',
            checked:  allSelected.value,
            onChange: toggleAll,
        }),
        tax_class_name: __('message.tax-type'),
        name:           __('message.name_page'),
        country:        __('message.country'),
        state:          __('message.state'),
        rate:           __('message.rate') + ' (%)',
        action:         __('message.action'),
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
        tax_class_name: (f, row) => row.tax_class_name || '—',
        name:           (f, row) => row.name            || '—',
        country:        (f, row) => row.country          || '—',
        state:          (f, row) => row.state            || '—',
        rate:           (f, row) => row.rate             || '—',
        action: (f, row) => h(RouterLink, {
            to:    `/settings/common/tax/${row.id}/edit`,
            class: 'btn btn-light table_btn',
            title: __('message.edit'),
        }, () => h('i', { class: 'fas fa-edit' })),
    },
    sortable:   ['name', 'country', 'rate'],
    filterable: true,
    requestAdapter(data) {
        return {
            'sort-field':   data.orderBy   ?? 'id',
            'sort-order':   data.ascending ? 'asc' : 'desc',
            'search-query': (data.query ?? '').trim(),
            page:  data.page,
            limit: data.limit,
        }
    },
    orderBy: { column: 'id', ascending: false },
})
</script>
