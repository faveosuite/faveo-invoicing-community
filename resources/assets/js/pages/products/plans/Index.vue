<template>
    <div>
        <AppAlert componentName="plans-index" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.plans') }}</h4>
                <div class="card-tools">
                    <router-link to="/products/plans/create" class="btn btn-tool" :title="__('message.create_product_plan')" v-tooltip>
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
                                :disabled="deleting"
                            >
                                <spinner-loader v-if="deleting" :size="18" />
                                <span v-else>{{ __('message.bulk_action') }}</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <button class="dropdown-item" @click="bulkDelete">{{ __('message.Delete') }}</button>
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
const apiUrl = `${baseUrl}/plans`

const dtRef = ref(null)
const selectedPlans = ref([])
const deleting = ref(false)

const allSelected = computed(() => {
    const data = dtRef.value?.tableData ?? []
    return data.length > 0 && data.every(row => selectedPlans.value.includes(row.id))
})

function toggleRow(id) {
    const idx = selectedPlans.value.indexOf(id)
    if (idx === -1) selectedPlans.value.push(id)
    else selectedPlans.value.splice(idx, 1)
}

function toggleAll(e) {
    const data = dtRef.value?.tableData ?? []
    if (e.target.checked) {
        const ids = data.map(r => r.id).filter(id => !selectedPlans.value.includes(id))
        selectedPlans.value.push(...ids)
    } else {
        const ids = data.map(r => r.id)
        selectedPlans.value = selectedPlans.value.filter(id => !ids.includes(id))
    }
}

async function bulkDelete() {
    if (!selectedPlans.value.length) return
    if (!confirm(`Delete ${selectedPlans.value.length} selected plan(s)? This cannot be undone.`)) return
    deleting.value = true
    try {
        await http.delete(`${baseUrl}/plans`, { data: { select: selectedPlans.value } })
        selectedPlans.value = []
        dtRef.value?.refresh()
    } catch (e) {
        errorHandler(e, 'plans-index')
    } finally {
        deleting.value = false
    }
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
    templates: {
        select:     (f, row) => h('input', { type: 'checkbox', checked: selectedPlans.value.includes(row.id), onChange: () => toggleRow(row.id) }),
        name:       (f, row) => row.name || '—',
        product:    (f, row) => row.product || '—',
        period:     (f, row) => row.period || '—',
        currencies: (f, row) => (row.currencies ?? []).join(', ') || '—',
        action:     (f, row) => h(RouterLink, { to: `/products/plans/${row.id}/edit`, class: 'btn btn-light table_btn', title: __('message.edit') }, () => h('i', { class: 'fas fa-edit' })),
    },
    sortable: ['name', 'product', 'period'],
    filterable: true,
    requestAdapter(data) {
        const columnMap = { period: 'days' }
        return {
            'sort-field':   columnMap[data.orderBy] ?? data.orderBy ?? 'created_at',
            'sort-order':   data.ascending ? 'asc' : 'desc',
            'search-query': (data.query ?? '').trim(),
            page:           data.page,
            limit:          data.limit,
        }
    },
    orderBy: { column: 'created_at', ascending: false },
})
</script>
