<template>
    <div>
        <AppAlert componentName="reports-index" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.reports') }}</h4>
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
                                    <button class="dropdown-item" @click="confirmBulkDelete">{{ __('message.Delete') }}</button>
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
        :deleteUrl="`${baseUrl}/reports`"
        :deleteData="pendingBulkDelete"
        :title="__('message.Delete')"
        :message="__('message.are_you_sure')"
        componentName="reports-index"
        @deleted="() => { pendingBulkDelete = null; selected.value = []; dtRef.value?.refresh() }"
    />
</template>

<script setup>
import { h, ref, computed, reactive } from 'vue'
import { RouterLink } from 'vue-router'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import { useTableSelection } from '@/core/composables/useTableSelection'
import { makeRequestAdapter } from '@/helpers/tableUtils'

const baseUrl = useBaseUrl()
const apiUrl = `/reports`

const dtRef = ref(null)
const { selected, allSelected, toggleRow, toggleAll } = useTableSelection(dtRef)
const pendingBulkDelete = ref(null)

function confirmBulkDelete() {
    if (!selected.value.length) return
    pendingBulkDelete.value = { select: [...selected.value] }
}

const columns = ['select', 'file', 'format', 'type', 'contact', 'created_at', 'action']

const tableOptions = reactive({
    headings: {
        select:     () => h('input', { type: 'checkbox', checked: allSelected.value, onChange: toggleAll }),
        file:       __('message.file_name'),
        format:     __('message.format'),
        type:       __('message.type'),
        contact:    __('message.contact'),
        created_at: __('message.created_at'),
        action:     __('message.action'),
    },
    columnsClasses: {
        select: 'dt-select',
        file: 'dt-name',
        format: 'dt-code',
        type: 'dt-code',
        contact: 'dt-name',
        created_at: 'dt-date',
        action: 'dt-action',
    },
    templates: {
        select:     (f, row) => h('input', { type: 'checkbox', checked: selected.value.includes(row.id), onChange: () => toggleRow(row.id) }),
        file:       (f, row) => row.file || '—',
        format:     (f, row) => row.format || '—',
        type:       (f, row) => row.type || '—',
        contact:    (f, row) => {
            if (!row.user) return '—'
            const fullName = `${row.user.first_name ?? ''} ${row.user.last_name ?? ''}`.trim()
            if (fullName && row.user.id) return h(RouterLink, { to: '/users/' + row.user.id }, () => fullName)
            return '—'
        },
        created_at: (f, row) => row.created_at ? row.created_at.substring(0, 10) : '—',
        action:     (f, row) => h('a', {
            href: `${baseUrl}/download-exported-file/${row.id}`,
            class: 'btn btn-light table_btn',
            title: __('message.download'),
            target: '_blank',
        }, h('i', { class: 'fas fa-download' })),
    },
    sortable: ['file', 'format', 'type', 'created_at'],
    filterable: true,
    requestAdapter: makeRequestAdapter('created_at'),
    orderBy: { column: 'created_at', ascending: false },
})
</script>
