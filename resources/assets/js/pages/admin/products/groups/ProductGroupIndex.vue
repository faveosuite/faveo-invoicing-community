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
                        <div v-if="selectedGroups.length > 0" class="dropdown">
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
        :deleteUrl="`${baseUrl}/group`"
        :deleteData="pendingBulkDelete"
        :title="__('message.Delete')"
        :message="__('message.are_you_sure')"
        componentName="groups-index"
        @deleted="() => { pendingBulkDelete = null; selectedGroups.value = []; dtRef.value?.refresh() }"
    />
</template>

<script setup>
import { h, ref, computed, reactive } from 'vue'
import { RouterLink } from 'vue-router'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'

const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl = `${baseUrl}/groups`

const dtRef = ref(null)
const selectedGroups = ref([])
const pendingBulkDelete = ref(null)

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
