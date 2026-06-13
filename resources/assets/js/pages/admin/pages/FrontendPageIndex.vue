<template>
    <div>
        <AppAlert componentName="pages-index" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.pages') }}</h4>
                <div class="card-tools">
                    <router-link to="/pages/create" class="btn btn-tool" v-tooltip="__('message.create_page')">
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
                        <div v-if="selected.length > 0" class="dropdown">
                            <button
                                class="btn btn-sm btn-secondary dropdown-toggle"
                                type="button"
                                data-bs-toggle="dropdown"
                            >
                                <span>{{ __('message.bulk_action') }}</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <button class="dropdown-item" @click="confirmBulkDelete">{{ __('message.Delete') }}</button>
                                </li>
                            </ul>
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>

        <DeleteModal
            v-if="pendingBulkDelete"
            :showModal="true"
            :onClose="() => pendingBulkDelete = null"
            :deleteUrl="`${baseUrl}/pages`"
            :deleteData="pendingBulkDelete"
            :title="__('message.Delete')"
            :message="__('message.are_you_sure')"
            componentName="pages-index"
            @deleted="() => { pendingBulkDelete = null; selected.value = []; dtRef?.refresh() }"
        />
    </div>
</template>

<script setup>
import { h, ref, computed, reactive } from 'vue'
import { RouterLink } from 'vue-router'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'
import { errorHandler } from '@/helpers/responseHandler.js'
import { useDateTime } from '@/core/composables/useDateTime'

const { formatDate } = useDateTime()

const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl = `${baseUrl}/pages`

const dtRef = ref(null)
const selected = ref([])
const pendingBulkDelete = ref(null)

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

function confirmBulkDelete() {
    if (!selected.value.length) return
    pendingBulkDelete.value = { page_ids: [...selected.value] }
}

const columns = ['select', 'name', 'url', 'created_at', 'action']

const tableOptions = reactive({
    headings: {
        select:     () => h('input', { type: 'checkbox', checked: allSelected.value, onChange: toggleAll }),
        name:       __('message.name'),
        url:        'URL',
        created_at: __('message.created_at'),
        action:     __('message.actions'),
    },
    columnsClasses: {
        select: 'dt-select',
        name: 'dt-name',
        url: 'dt-text',
        created_at: 'dt-date',
        action: 'dt-action',
    },
    templates: {
        select:     (f, row) => h('input', { type: 'checkbox', checked: selected.value.includes(row.id), onChange: () => toggleRow(row.id) }),
        name:       (f, row) => row.name || '—',
        url:        (f, row) => row.url || '—',
        created_at: (f, row) => formatDate(row.created_at),
        action:     (f, row) => h(RouterLink, { to: `/pages/${row.id}/edit`, class: 'btn btn-light table_btn', title: __('message.edit') }, () => h('i', { class: 'fas fa-edit' })),
    },
    sortable: ['name', 'url', 'created_at'],
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
