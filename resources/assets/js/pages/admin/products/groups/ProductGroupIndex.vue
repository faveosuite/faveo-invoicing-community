<template>
    <div>
        <AppAlert componentName="groups-index" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.product_groups') }}</h4>
                <div class="card-tools">
                    <router-link to="/products/groups/create" class="btn btn-tool" v-tooltip="__('message.create_group')">
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
                        <BulkActionIcons v-if="selectedGroups.length > 0" :actions="[{ icon: 'fas fa-trash', label: __('message.Delete'), onClick: confirmBulkDelete }]" />
                    </template>
                </DataTable>
            </div>
        </div>
    </div>

    <DeleteModal
        v-if="pendingBulkDelete"
        :showModal="true"
        :onClose="() => pendingBulkDelete = null"
        :deleteUrl="`${baseUrl}/group`"
        :deleteData="pendingBulkDelete"
        :title="__('message.Delete')"
        :message="__('message.are_you_sure')"
        componentName="groups-index"
        @deleted="() => { pendingBulkDelete = null; selectedGroups.value = []; dtRef.value?.refresh() }"
    />
</template>

<script setup>
import { h, ref, reactive } from 'vue'
import { RouterLink } from 'vue-router'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'
import BulkActionIcons from '@/components/Reusable/BulkActionIcons.vue'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import { useTableSelection } from '@/core/composables/useTableSelection'
import { makeRequestAdapter } from '@/helpers/tableUtils'

const baseUrl = useBaseUrl()
const apiUrl = `/groups`

const dtRef = ref(null)
const { selected: selectedGroups, allSelected, toggleRow, toggleAll } = useTableSelection(dtRef)
const pendingBulkDelete = ref(null)

function confirmBulkDelete() {
    if (!selectedGroups.value.length) return
    pendingBulkDelete.value = { select: [...selectedGroups.value] }
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
    requestAdapter: makeRequestAdapter('created_at'),
    orderBy: { column: 'created_at', ascending: false },
})
</script>
