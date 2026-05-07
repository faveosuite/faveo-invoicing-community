<template>
    <div>
        <AppAlert componentName="license-type-index" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">License Types</h4>
            </div>

            <div class="card-body">
                <!-- Inline create form -->
                <form class="row g-2 mb-3" @submit.prevent="create">
                    <div class="col-md-4">
                        <input type="text" class="form-control" v-model="newName" placeholder="New license type name" required />
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary" :disabled="creating">
                            <span v-if="creating" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            Add
                        </button>
                    </div>
                </form>

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
                                :disabled="deleting"
                            >
                                <span v-if="deleting" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <span v-else>Bulk Action</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <button class="dropdown-item" @click="bulkDelete">Delete</button>
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

const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl = `${baseUrl}/get-license-type`

const dtRef = ref(null)
const selected = ref([])
const deleting = ref(false)
const newName = ref('')
const creating = ref(false)

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
        selected.value.push(...data.map(r => r.id).filter(id => !selected.value.includes(id)))
    } else {
        const ids = data.map(r => r.id)
        selected.value = selected.value.filter(id => !ids.includes(id))
    }
}

async function create() {
    creating.value = true
    try {
        const res = await http.post(`${baseUrl}/create-license-type`, { name: newName.value })
        successHandler(res, 'license-type-index')
        newName.value = ''
        dtRef.value?.refresh()
    } catch (e) {
        errorHandler(e, 'license-type-index')
    } finally {
        creating.value = false
    }
}

async function bulkDelete() {
    if (!selected.value.length) return
    if (!confirm(`Delete ${selected.value.length} selected license type(s)? This cannot be undone.`)) return
    deleting.value = true
    try {
        await http.delete(`${baseUrl}/delete-license-type`, { data: { select: selected.value } })
        selected.value = []
        dtRef.value?.refresh()
    } catch (e) {
        errorHandler(e, 'license-type-index')
    } finally {
        deleting.value = false
    }
}

const columns = ['select', 'name', 'action']

const tableOptions = reactive({
    headings: {
        select: () => h('input', { type: 'checkbox', checked: allSelected.value, onChange: toggleAll }),
        name:   'Name',
        action: 'Action',
    },
    templates: {
        select: (f, row) => h('input', { type: 'checkbox', checked: selected.value.includes(row.id), onChange: () => toggleRow(row.id) }),
        name:   (f, row) => row.name || '—',
        action: (f, row) => h(RouterLink, { to: `/settings/license-type/${row.id}/edit`, class: 'btn btn-light table_btn', title: 'Edit' }, () => h('i', { class: 'fas fa-pen' })),
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
