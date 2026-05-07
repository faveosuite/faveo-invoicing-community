<template>
    <div>
        <AppAlert componentName="coupons-index" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">Coupons</h4>
                <div class="card-tools">
                    <router-link to="/products/coupons/create" class="btn btn-tool" title="Create Coupon" v-tooltip>
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
                        <div v-if="selectedCoupons.length > 0" class="dropdown">
                            <button
                                class="btn btn-sm btn-secondary dropdown-toggle"
                                type="button"
                                data-bs-toggle="dropdown"
                                :disabled="deleting"
                            >
                                <span v-if="deleting" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <span v-else>Bulk Action</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <button class="dropdown-item" @click="bulkDelete">Delete</button>
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
import { RouterLink } from 'vue-router'
import http from '@/plugins/axios'
import { errorHandler } from '@/helpers/responseHandler.js'

const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl = `${baseUrl}/promotions`

const dtRef = ref(null)
const selectedCoupons = ref([])
const deleting = ref(false)

const allSelected = computed(() => {
    const data = dtRef.value?.tableData ?? []
    return data.length > 0 && data.every(row => selectedCoupons.value.includes(row.id))
})

function toggleRow(id) {
    const idx = selectedCoupons.value.indexOf(id)
    if (idx === -1) selectedCoupons.value.push(id)
    else selectedCoupons.value.splice(idx, 1)
}

function toggleAll(e) {
    const data = dtRef.value?.tableData ?? []
    if (e.target.checked) {
        const ids = data.map(r => r.id).filter(id => !selectedCoupons.value.includes(id))
        selectedCoupons.value.push(...ids)
    } else {
        const ids = data.map(r => r.id)
        selectedCoupons.value = selectedCoupons.value.filter(id => !ids.includes(id))
    }
}

async function bulkDelete() {
    if (!selectedCoupons.value.length) return
    if (!confirm(`Delete ${selectedCoupons.value.length} selected coupon(s)? This cannot be undone.`)) return
    deleting.value = true
    try {
        await http.delete(`${baseUrl}/promotions`, { data: { select: selectedCoupons.value } })
        selectedCoupons.value = []
        dtRef.value?.refresh()
    } catch (e) {
        errorHandler(e, 'coupons-index')
    } finally {
        deleting.value = false
    }
}

const columns = ['select', 'code', 'type', 'value', 'products', 'uses', 'start', 'expiry', 'action']

const tableOptions = reactive({
    headings: {
        select:   () => h('input', { type: 'checkbox', checked: allSelected.value, onChange: toggleAll }),
        code:     'Code',
        type:     'Type',
        value:    'Value',
        products: 'Products',
        uses:     'Uses',
        start:    'Start',
        expiry:   'Expiry',
        action:   'Actions',
    },
    templates: {
        select:   (f, row) => h('input', { type: 'checkbox', checked: selectedCoupons.value.includes(row.id), onChange: () => toggleRow(row.id) }),
        code:     (f, row) => row.code || '—',
        type:     (f, row) => row.promotion_type?.name || '—',
        value:    (f, row) => row.value || '—',
        products: (f, row) => row.products?.name || '—',
        uses:     (f, row) => row.uses ?? '—',
        start:    (f, row) => row.start ? row.start.substring(0, 10) : '—',
        expiry:   (f, row) => row.expiry ? row.expiry.substring(0, 10) : '—',
        action:   (f, row) => h(RouterLink, { to: `/products/coupons/${row.id}/edit`, class: 'btn btn-light table_btn', title: 'Edit' }, () => h('i', { class: 'fas fa-pen' })),
    },
    sortable: ['code', 'type', 'start', 'expiry'],
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
