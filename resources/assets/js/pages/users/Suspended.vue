<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">Suspended Users</h4>
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
                                <li>
                                    <button class="dropdown-item" @click="bulkRestore">
                                        Restore
                                    </button>
                                </li>
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
import { h, ref, computed, reactive } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import SuspendedTableActions from './components/SuspendedTableActions.vue'

const COMPONENT = 'suspended-index'

const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl  = `${baseUrl}/soft-delete`

const dtRef  = ref(null)
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

async function bulkRestore() {
    if (!selected.value.length) return
    try {
        const promises = selected.value.map(id => http.get(`${baseUrl}/user/restore/${id}`))
        await Promise.all(promises)
        selected.value = []
        dtRef.value?.refresh()
    } catch (e) {
        errorHandler(e, COMPONENT)
    }
}

async function bulkDelete() {
    if (!selected.value.length) return
    if (!confirm(`Permanently delete ${selected.value.length} selected user(s)? This cannot be undone.`)) return
    try {
        await http.delete(`${baseUrl}/permanent-delete-client`, { data: { user_ids: selected.value } })
        selected.value = []
        dtRef.value?.refresh()
    } catch (e) {
        errorHandler(e, COMPONENT)
    }
}

const columns = ['select', 'name', 'email', 'mobile', 'country', 'created_at', 'action']

const tableOptions = reactive({
    headings: {
        select:     () => h('input', {
            type: 'checkbox',
            checked: allSelected.value,
            onChange: toggleAll,
        }),
        name:       'Name',
        email:      'Email',
        mobile:     'Mobile',
        country:    'Country',
        created_at: 'Registered On',
        action:     'Actions',
    },

    templates: {
        select:     (f, row) => h('input', {
            type:     'checkbox',
            checked:  selected.value.includes(row.id),
            onChange: () => toggleRow(row.id),
        }),
        name:       (f, row) => `${row.first_name ?? ''} ${row.last_name ?? ''}`.trim() || '—',
        mobile:     (f, row) => row.mobile?.trim() || '—',
        country:    (f, row) => row.country?.trim() || '—',
        created_at: (f, row) => row.created_at ? new Date(row.created_at).toLocaleDateString() : '—',
        action:     (f, row) => h(SuspendedTableActions, {
            userId:        row.id,
            baseUrl:       baseUrl,
            componentName: COMPONENT,
            onRestored:    () => dtRef.value?.refresh(),
            onDeleted:     () => dtRef.value?.refresh(),
        }),
    },

    sortable:   ['email', 'created_at'],
    filterable: true,

    requestAdapter(data) {
        return {
            'sort-field':   data.orderBy || 'created_at',
            'sort-order':   data.ascending ? 'asc' : 'desc',
            'search-query': (data.query ?? '').trim(),
            page:           data.page,
            limit:          data.limit,
        }
    },

    orderBy: { column: 'created_at', ascending: false },
})
</script>
