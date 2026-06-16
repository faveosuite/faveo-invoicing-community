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
                        v-tooltip="__('message.create')"
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
        v-if="pendingDelete"
        :showModal="true"
        :onClose="() => pendingDelete = null"
        :deleteUrl="`${baseUrl}/chat/delete`"
        :deleteData="pendingDelete"
        :title="__('message.Delete')"
        :message="__('message.are_you_sure')"
        :componentName="COMPONENT"
        @deleted="() => { pendingDelete = null; dtRef?.refresh() }"
    />

    <DeleteModal
        v-if="pendingBulkDelete"
        :showModal="true"
        :onClose="() => pendingBulkDelete = null"
        :deleteUrl="`${baseUrl}/chat/delete`"
        :deleteData="pendingBulkDelete"
        :title="__('message.Delete')"
        :message="__('message.are_you_sure')"
        :componentName="COMPONENT"
        @deleted="() => { pendingBulkDelete = null; selected.value = []; dtRef?.refresh() }"
    />
</template>

<script setup>
import { h, ref, computed, reactive } from 'vue'
import { RouterLink } from 'vue-router'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'

const COMPONENT = 'analytics-widgets'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl  = `${baseUrl}/chat/list`

const dtRef    = ref(null)
const selected = ref([])
const pendingDelete     = ref(null)
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
        const ids = data.map(r => r.id).filter(id => !selected.value.includes(id))
        selected.value.push(...ids)
    } else {
        const ids = data.map(r => r.id)
        selected.value = selected.value.filter(id => !ids.includes(id))
    }
}

function confirmDelete(id) {
    pendingDelete.value = { select: [id] }
}

function confirmBulkDelete() {
    if (!selected.value.length) return
    pendingBulkDelete.value = { select: [...selected.value] }
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
                onClick: () => confirmDelete(row.id),
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
