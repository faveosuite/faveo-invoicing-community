<template>
    <div>
        <AppAlert componentName="products-index" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.products') }}</h4>
                <div class="card-tools">
                    <router-link to="/products/create" class="btn btn-tool" :title="__('message.add_product')" v-tooltip>
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
                                :disabled="deleting"
                            >
                                <spinner-loader v-if="deleting" :size="18" />
                                <span v-else>{{ __('message.bulk_action') }}</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <button class="dropdown-item" @click="bulkDelete">
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
</template>

<script setup>
import { h, ref, computed, reactive } from 'vue'

import http from '@/plugins/axios'
import { errorHandler } from '@/helpers/responseHandler.js'
import ProductTableActions from './components/ProductTableActions.vue'

const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl = `${baseUrl}/products`

const dtRef = ref(null)
const selectedProducts = ref([])
const deleting = ref(false)

const allSelected = computed(() => {
    const data = dtRef.value?.tableData ?? []
    return data.length > 0 && data.every(row => selectedProducts.value.includes(row.id))
})

function toggleRow(id) {
    const idx = selectedProducts.value.indexOf(id)
    if (idx === -1) selectedProducts.value.push(id)
    else selectedProducts.value.splice(idx, 1)
}

function toggleAll(e) {
    const data = dtRef.value?.tableData ?? []
    if (e.target.checked) {
        const ids = data.map(r => r.id).filter(id => !selectedProducts.value.includes(id))
        selectedProducts.value.push(...ids)
    } else {
        const ids = data.map(r => r.id)
        selectedProducts.value = selectedProducts.value.filter(id => !ids.includes(id))
    }
}

async function bulkDelete() {
    if (!selectedProducts.value.length) return
    if (!confirm(`Delete ${selectedProducts.value.length} selected product(s)? This cannot be undone.`)) return
    
    deleting.value = true
    try {
        await http.delete(apiUrl, { data: { product_ids: selectedProducts.value } })
        selectedProducts.value = []
        dtRef.value?.refresh()
    } catch (e) {
        errorHandler(e, 'products-index')
    } finally {
        deleting.value = false
    }
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
