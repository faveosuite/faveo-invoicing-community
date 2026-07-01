<template>
    <div>
        <AppAlert componentName="coupons-index" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.coupons') }}</h4>
                <div class="card-tools">
                    <router-link to="/products/coupons/create" class="btn btn-tool" v-tooltip="__('message.create_coupon_title')">
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
        :deleteUrl="`${baseUrl}/promotions`"
        :deleteData="pendingBulkDelete"
        :title="__('message.Delete')"
        :message="__('message.are_you_sure')"
        componentName="coupons-index"
        @deleted="() => { pendingBulkDelete = null; selectedCoupons.value = []; dtRef.value?.refresh() }"
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
const apiUrl = `/promotions`

const dtRef = ref(null)
const { selected: selectedCoupons, allSelected, toggleRow, toggleAll } = useTableSelection(dtRef)
const pendingBulkDelete = ref(null)

function confirmBulkDelete() {
    if (!selectedCoupons.value.length) return
    pendingBulkDelete.value = { select: [...selectedCoupons.value] }
}

const columns = ['select', 'code', 'type', 'value', 'products', 'uses', 'start', 'expiry', 'action']

const tableOptions = reactive({
    headings: {
        select:   () => h('input', { type: 'checkbox', checked: allSelected.value, onChange: toggleAll }),
        code:     __('message.coupon-code'),
        type:     __('message.type'),
        value:    __('message.value'),
        products: __('message.products'),
        uses:     __('message.uses'),
        start:    __('message.start'),
        expiry:   __('message.expiry'),
        action:   __('message.actions'),
    },
    columnsClasses: {
        select: 'dt-select',
        code: 'dt-code',
        type: 'dt-code',
        value: 'dt-amount',
        products: 'dt-name',
        uses: 'dt-code',
        start: 'dt-date',
        expiry: 'dt-date',
        action: 'dt-action',
    },
    templates: {
        select:   (f, row) => h('input', { type: 'checkbox', checked: selectedCoupons.value.includes(row.id), onChange: () => toggleRow(row.id) }),
        code:     (f, row) => row.code || '—',
        type:     (f, row) => row.promotion_type?.name || '—',
        value:    (f, row) => row.value || '—',
        products: (f, row) => {
            // `products` is a hasOneThrough relation, so the backend serializes
            // it as a single object (not an array). Normalize to an array.
            const items = row.products == null ? [] : (Array.isArray(row.products) ? row.products : [row.products])
            if (!items.length) return '—'
            return h('span', {}, items.flatMap((p, i) => {
                const link = p.id ? h(RouterLink, { to: '/products/' + p.id + '/edit' }, () => p.name) : p.name
                return i < items.length - 1 ? [link, ', '] : [link]
            }))
        },
        uses:     (f, row) => row.uses ?? '—',
        start:    (f, row) => row.start ? row.start.substring(0, 10) : '—',
        expiry:   (f, row) => row.expiry ? row.expiry.substring(0, 10) : '—',
        action:   (f, row) => h(RouterLink, { to: `/products/coupons/${row.id}/edit`, class: 'btn btn-light table_btn', title: __('message.edit') }, () => h('i', { class: 'fas fa-edit' })),
    },
    sortable: ['code', 'type', 'value', 'uses', 'start', 'expiry'],
    filterable: true,
    requestAdapter: makeRequestAdapter('created_at'),
    orderBy: { column: 'created_at', ascending: false },
})
</script>
