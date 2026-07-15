<template>
    <div>
        <AppAlert componentName="products-index" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.products') }}</h4>
                <div class="card-tools">
                    <router-link to="/products/apply-build" class="btn btn-tool" v-tooltip="__('message.apply_build_to_products') || 'Apply Build to Products'">
                        <i class="fas fa-upload"></i>
                    </router-link>
                    <router-link to="/products/create" class="btn btn-tool" v-tooltip="__('message.add_product')">
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
                        <div v-if="selectedProducts.length > 0" class="dropdown">
                            <button
                                class="btn btn-sm btn-secondary dropdown-toggle"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
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
        v-if="pendingBulkDelete"
        :showModal="true"
        :onClose="() => pendingBulkDelete = null"
        :deleteUrl="`${baseUrl}/products`"
        :deleteData="pendingBulkDelete"
        :title="__('message.Delete')"
        :message="__('message.are_you_sure')"
        componentName="products-index"
        @deleted="() => { pendingBulkDelete = null; selectedProducts.value = []; dtRef.value?.refresh() }"
    />
</template>

<script setup>
import { h, ref, reactive } from 'vue'

import ProductTableActions from './components/ProductTableActions.vue'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import { useTableSelection } from '@/core/composables/useTableSelection'
import { makeRequestAdapter } from '@/helpers/tableUtils'

const baseUrl = useBaseUrl()
const apiUrl = `/products`

const dtRef = ref(null)
const { selected: selectedProducts, allSelected, toggleRow, toggleAll } = useTableSelection(dtRef)
const pendingBulkDelete = ref(null)

function confirmBulkDelete() {
    if (!selectedProducts.value.length) return
    pendingBulkDelete.value = { product_ids: [...selectedProducts.value] }
}

const columns = ['select', 'name', 'image', 'license_type', 'group', 'action']

const tableOptions = reactive({
    headings: {
        select:        () => h('input', { type: 'checkbox', checked: allSelected.value, onChange: toggleAll }),
        name:          __('message.name'),
        image:         __('message.image'),
        license_type:  __('message.license-type'),
        group:         __('message.group'),
        action:        __('message.actions'),
    },

    columnsClasses: {
        select: 'dt-select',
        name: 'dt-name',
        image: 'dt-code',
        license_type: 'dt-name',
        group: 'dt-name',
        action: 'dt-action',
    },

    templates: {
        select:       (f, row) => h('input', { type: 'checkbox', checked: selectedProducts.value.includes(row.id), onChange: () => toggleRow(row.id) }),
        name:         (f, row) => row.name || '—',
        image:        (f, row) => row.image ? h('img', { src: row.image, alt: row.name, style: 'width:50px;height:50px;object-fit:cover;border-radius:4px;' }) : '—',
        license_type: (f, row) => row.license_type || '—',
        group:        (f, row) => row.group || '—',
        action:       (f, row) => h(ProductTableActions, { productId: row.id, downloadUrl: row.action?.download_url }),
    },

    sortable: ['name', 'license_type', 'group'],
    filterable: true,

    requestAdapter: makeRequestAdapter('created_at'),

    orderBy: { column: 'created_at', ascending: false },
})
</script>
