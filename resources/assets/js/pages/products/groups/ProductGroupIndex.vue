<template>
    <div>
        <AppAlert componentName="groups-index" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.product_groups') }}</h4>
                <div class="card-tools">
                    <router-link to="/products/groups/create" class="btn btn-tool" :title="__('message.create_group')" v-tooltip>
                        <i class="fas fa-plus"></i>
                    </router-link>
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
                        <div v-if="selectedGroups.length > 0" class="dropdown">
                            <button
                                class="btn btn-sm btn-secondary dropdown-toggle"
                                type="button"
                                data-bs-toggle="dropdown"
                                :disabled="deleting"
                            >
                                <spinner-loader v-if="deleting" :size="18" />
                                <span v-else>{{ __('message.bulk_action') }}</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <button class="dropdown-item" @click="bulkDelete">{{ __('message.Delete') }}</button>
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
import { errorHandler } from '@/helpers/responseHandler.js'

const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl = `${baseUrl}/groups`

const dtRef = ref(null)
const selectedGroups = ref([])
const deleting = ref(false)

const allSelected = computed(() => {
    const data = dtRef.value?.tableData ?? []
    return data.length > 0 && data.every(row => selectedGroups.value.includes(row.id))
})

function toggleRow(id) {
    const idx = selectedGroups.value.indexOf(id)
    if (idx === -1) selectedGroups.value.push(id)
    else selectedGroups.value.splice(idx, 1)
}

function toggleAll(e) {
    const data = dtRef.value?.tableData ?? []
    if (e.target.checked) {
        const ids = data.map(r => r.id).filter(id => !selectedGroups.value.includes(id))
        selectedGroups.value.push(...ids)
    } else {
        const ids = data.map(r => r.id)
        selectedGroups.value = selectedGroups.value.filter(id => !ids.includes(id))
    }
}

async function bulkDelete() {
    if (!selectedGroups.value.length) return
    if (!confirm(`Delete ${selectedGroups.value.length} selected group(s)? This cannot be undone.`)) return
    deleting.value = true
    try {
        await http.delete(`${baseUrl}/group`, { data: { select: selectedGroups.value } })
        selectedGroups.value = []
        dtRef.value?.refresh()
    } catch (e) {
        errorHandler(e, 'groups-index')
    } finally {
        deleting.value = false
    }
}

const columns = ['select', 'name', 'action']

const tableOptions = reactive({
    headings: {
        select: () => h('input', { type: 'checkbox', checked: allSelected.value, onChange: toggleAll }),
        name:   __('message.name'),
        action: __('message.actions'),
    },
    columnsClasses: {
        select: 'dt-select',
        name: 'dt-name',
        action: 'dt-action',
    },
    templates: {
        select: (f, row) => h('input', { type: 'checkbox', checked: selectedGroups.value.includes(row.id), onChange: () => toggleRow(row.id) }),
        name:   (f, row) => row.name || '—',
        action: (f, row) => h(RouterLink, { to: `/products/groups/${row.id}/edit`, class: 'btn btn-light table_btn', title: __('message.edit') }, () => h('i', { class: 'fas fa-edit' })),
    },
    sortable: ['name'],
    filterable: true,
    requestAdapter(data) {
        return {
            'sort-field':   data.orderBy ?? 'created_at',
            'sort-order':   data.ascending ? 'asc' : 'desc',
            'search-query': (data.query ?? '').trim(),
            page:           data.page,
            limit:          data.limit,
        }
    },
    orderBy: { column: 'created_at', ascending: false },
})
</script>
