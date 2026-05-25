<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.google_analytics_javascript') }}</h4>
                <div class="card-tools">
                    <RouterLink
                        to="/settings/widgets/analytics/create"
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
                            >
                                {{ __('message.bulk_action') }}
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <button class="dropdown-item" @click="bulkDelete">
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
</template>

<script setup>
import { h, ref, computed, reactive } from 'vue'
import { RouterLink } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'analytics-widgets'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl  = `${baseUrl}/chat/list`

const dtRef    = ref(null)
const selected = ref([])

const allSelected = computed(() => {
    const data = dtRef.value?.tableData ?? []
    return data.length > 0 && data.every(row => selected.value.includes(row.id))
})

function toggleRow(id) {
    const idx = selected.value.indexOf(id)
    if (idx === -1) selected.value.push(id)
    else selected.value.splice(idx, 1)
}

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

async function deleteRow(id) {
    try {
        const res = await http.delete(`${baseUrl}/chat/delete`, { data: { select: [id] } })
        successHandler(res, COMPONENT)
        selected.value = selected.value.filter(s => s !== id)
        dtRef.value?.refresh()
    } catch (e) {
        errorHandler(e, COMPONENT)
    }
}

async function bulkDelete() {
    if (!selected.value.length) return
    try {
        const res = await http.delete(`${baseUrl}/chat/delete`, { data: { select: selected.value } })
        successHandler(res, COMPONENT)
        selected.value = []
        dtRef.value?.refresh()
    } catch (e) {
        errorHandler(e, COMPONENT)
    }
}

const columns = ['select', 'name', 'action']

const tableOptions = reactive({
    headings: {
        select: () => h('input', {
            type: 'checkbox',
            checked: allSelected.value,
            onChange: toggleAll,
        }),
        name:   __('message.name'),
        action: __('message.action'),
    },
    columnsClasses: {
        select: 'dt-select',
        name: 'dt-name',
        action: 'dt-action',
    },
    templates: {
        select: (f, row) => h('input', {
            type: 'checkbox',
            checked: selected.value.includes(row.id),
            onChange: () => toggleRow(row.id),
        }),
        name:   (f, row) => row.name || '—',
        action: (f, row) => h('div', { class: 'd-flex gap-1' }, [
            h(RouterLink, {
                to:    `/settings/widgets/analytics/${row.id}/edit`,
                class: 'btn btn-light table_btn',
                title: __('message.edit'),
            }, () => h('i', { class: 'fas fa-edit' })),
            h('button', {
                class:   'btn btn-light table_btn',
                title:   __('message.Delete'),
                onClick: () => deleteRow(row.id),
            }, h('i', { class: 'fas fa-trash' })),
        ]),
    },
    sortable:   ['name'],
    filterable: true,
    requestAdapter(data) {
        return {
            'sort-field':   data.orderBy  ?? 'created_at',
            'sort-order':   data.ascending ? 'asc' : 'desc',
            'search-query': (data.query   ?? '').trim(),
            page:           data.page,
            limit:          data.limit,
        }
    },
    orderBy: { column: 'created_at', ascending: false },
})
</script>
