<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.social_media') }}</h4>
                <div class="card-tools">
                    <RouterLink
                        to="/settings/widgets/social-media/create"
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

        <DeleteModal
            v-if="pendingDeleteRow"
            :showModal="true"
            :onClose="() => pendingDeleteRow = null"
            :deleteUrl="`${baseUrl}/social-media/delete`"
            :deleteData="pendingDeleteRow"
            :title="__('message.Delete')"
            :message="__('message.are_you_sure')"
            :componentName="COMPONENT"
            @deleted="() => { pendingDeleteRow = null; dtRef?.refresh() }"
        />

        <DeleteModal
            v-if="pendingBulkDelete"
            :showModal="true"
            :onClose="() => pendingBulkDelete = null"
            :deleteUrl="`${baseUrl}/social-media/delete`"
            :deleteData="pendingBulkDelete"
            :title="__('message.Delete')"
            :message="__('message.are_you_sure')"
            :componentName="COMPONENT"
            @deleted="() => { pendingBulkDelete = null; selected.value = []; dtRef?.refresh() }"
        />
    </div>
</template>

<script setup>
import { h, ref, computed, reactive } from 'vue'
import { RouterLink } from 'vue-router'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'
import { useTableSelection } from '@/core/composables/useTableSelection'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import { makeRequestAdapter } from '@/helpers/tableUtils'

const COMPONENT = 'social-media-index'
const baseUrl = useBaseUrl()
const apiUrl  = `/social-media/list`

const dtRef    = ref(null)
const { selected, allSelected, toggleRow, toggleAll } = useTableSelection(dtRef)
const pendingDeleteRow  = ref(null)
const pendingBulkDelete = ref(null)

function confirmDeleteRow(id) {
    pendingDeleteRow.value = { id }
}

function confirmBulkDelete() {
    if (!selected.value.length) return
    pendingBulkDelete.value = { id: [...selected.value] }
}

const columns = ['select', 'name', 'link', 'action']

const tableOptions = reactive({
    headings: {
        select: () => h('input', {
            type: 'checkbox',
            checked: allSelected.value,
            onChange: toggleAll,
        }),
        name:   __('message.name'),
        link:   __('message.link'),
        action: __('message.action'),
    },
    columnsClasses: {
        select: 'dt-select',
        name: 'dt-name',
        link: 'dt-text',
        action: 'dt-action',
    },
    templates: {
        select: (f, row) => h('input', {
            type: 'checkbox',
            checked: selected.value.includes(row.id),
            onChange: () => toggleRow(row.id),
        }),
        name:   (f, row) => row.name || '—',
        link:   (f, row) => row.link || '—',
        action: (f, row) => h('div', { class: 'd-flex gap-1' }, [
            h(RouterLink, {
                to:    `/settings/widgets/social-media/${row.id}/edit`,
                class: 'btn btn-light table_btn',
                title: __('message.edit'),
            }, () => h('i', { class: 'fas fa-edit' })),
            h('button', {
                class:   'btn btn-light table_btn',
                title:   __('message.Delete'),
                onClick: () => confirmDeleteRow(row.id),
            }, h('i', { class: 'fas fa-trash' })),
        ]),
    },
    sortable:   ['name', 'link'],
    filterable: true,
    requestAdapter: makeRequestAdapter('created_at'),
    orderBy: { column: 'created_at', ascending: false },
})
</script>
