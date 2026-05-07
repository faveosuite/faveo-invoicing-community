<template>
    <div>
        <AppAlert componentName="reports-index" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">Reports</h4>
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
import http from '@/plugins/axios'
import { errorHandler } from '@/helpers/responseHandler.js'

const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl = `${baseUrl}/reports`

const dtRef = ref(null)
const selected = ref([])
const deleting = ref(false)

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

async function bulkDelete() {
    if (!selected.value.length) return
    if (!confirm(`Delete ${selected.value.length} selected report(s)? This cannot be undone.`)) return
    deleting.value = true
    try {
        await http.delete(`${baseUrl}/reports`, { data: { select: selected.value } })
        selected.value = []
        dtRef.value?.refresh()
    } catch (e) {
        errorHandler(e, 'reports-index')
    } finally {
        deleting.value = false
    }
}

const columns = ['select', 'file', 'format', 'type', 'contact', 'created_at', 'action']

const tableOptions = reactive({
    headings: {
        select:     () => h('input', { type: 'checkbox', checked: allSelected.value, onChange: toggleAll }),
        file:       'File Name',
        format:     'Format',
        type:       'Type',
        contact:    'Contact',
        created_at: 'Created At',
        action:     'Action',
    },
    templates: {
        select:     (f, row) => h('input', { type: 'checkbox', checked: selected.value.includes(row.id), onChange: () => toggleRow(row.id) }),
        file:       (f, row) => row.file || '—',
        format:     (f, row) => row.format || '—',
        type:       (f, row) => row.type || '—',
        contact:    (f, row) => row.user ? `${row.user.first_name ?? ''} ${row.user.last_name ?? ''}`.trim() : '—',
        created_at: (f, row) => row.created_at ? row.created_at.substring(0, 10) : '—',
        action:     (f, row) => h('a', {
            href: `${baseUrl}/download-exported-file/${row.id}`,
            class: 'btn btn-light table_btn',
            title: 'Download',
            target: '_blank',
        }, h('i', { class: 'fas fa-download' })),
    },
    sortable: ['file', 'format', 'type', 'created_at'],
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
