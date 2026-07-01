<template>
    <div>
        <AppAlert componentName="plans-index" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.plans') }}</h4>
                <div class="card-tools">
                    <router-link to="/products/plans/create" class="btn btn-tool" v-tooltip="__('message.create_product_plan')">
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
                        <div v-if="selectedPlans.length > 0" class="dropdown">
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
        :deleteUrl="`${baseUrl}/plans`"
        :deleteData="pendingBulkDelete"
        :title="__('message.Delete')"
        :message="__('message.are_you_sure')"
        componentName="plans-index"
        @deleted="() => { pendingBulkDelete = null; selectedPlans.value = []; dtRef.value?.refresh() }"
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
const apiUrl = `/plans`

const dtRef = ref(null)
const { selected: selectedPlans, allSelected, toggleRow, toggleAll } = useTableSelection(dtRef)
const pendingBulkDelete = ref(null)

function confirmBulkDelete() {
    if (!selectedPlans.value.length) return
    pendingBulkDelete.value = { select: [...selectedPlans.value] }
}

const columns = ['select', 'name', 'product', 'period', 'currencies', 'action']

const tableOptions = reactive({
    headings: {
        select:     () => h('input', { type: 'checkbox', checked: allSelected.value, onChange: toggleAll }),
        name:       __('message.name'),
        product:    __('message.product'),
        period:     __('message.period'),
        currencies: __('message.currency'),
        action:     __('message.actions'),
    },
    columnsClasses: {
        select: 'dt-select',
        name: 'dt-name',
        product: 'dt-name',
        period: 'dt-code',
        currencies: 'dt-text',
        action: 'dt-action',
    },
    templates: {
        select:     (f, row) => h('input', { type: 'checkbox', checked: selectedPlans.value.includes(row.id), onChange: () => toggleRow(row.id) }),
        name:       (f, row) => row.name || '—',
        product:    (f, row) => row.product && row.product_id ? h(RouterLink, { to: `/products/${row.product_id}/edit` }, () => row.product) : (row.product || '—'),
        period:     (f, row) => row.period || '—',
        currencies: (f, row) => (row.currencies ?? []).join(', ') || '—',
        action:     (f, row) => h(RouterLink, { to: `/products/plans/${row.id}/edit`, class: 'btn btn-light table_btn', title: __('message.edit') }, () => h('i', { class: 'fas fa-edit' })),
    },
    sortable: ['name', 'product', 'period'],
    filterable: true,
    requestAdapter: makeRequestAdapter('created_at', null, { period: 'days' }),
    orderBy: { column: 'created_at', ascending: false },
})
</script>
