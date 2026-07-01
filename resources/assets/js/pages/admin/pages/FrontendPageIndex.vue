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
import { h, ref, reactive } from 'vue'
import { RouterLink } from 'vue-router'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'
import { useDateTime } from '@/core/composables/useDateTime'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import { useTableSelection } from '@/core/composables/useTableSelection'
import { makeRequestAdapter } from '@/helpers/tableUtils'

const { formatDate } = useDateTime()

const baseUrl = useBaseUrl()
const apiUrl = `/pages`

const dtRef = ref(null)
const { selected, allSelected, toggleRow, toggleAll } = useTableSelection(dtRef)
const pendingBulkDelete = ref(null)

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
    requestAdapter: makeRequestAdapter('created_at'),
    orderBy: { column: 'created_at', ascending: false },
})
</script>
