<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.payment_logs') }}</h4>
                <div class="card-tools">
                    <button class="btn btn-tool" v-tooltip="__('message.filters')" @click="showFilter = !showFilter">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <PaymentFilter
                    :show="showFilter"
                    :baseUrl="baseUrl"
                    @apply="onFilterApply"
                    @reset="onFilterReset"
                    @close="showFilter = false"
                />
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
                                aria-expanded="false"
                            >
                                {{ __('message.bulk_action') }}
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <button class="dropdown-item" @click="showBulkDeleteModal = true">
                                        <i class="fas fa-trash me-1"></i> {{ __('message.Delete') }}
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>

        <!-- Exception detail modal -->
        <AppModal
            :showModal="showExceptionModal"
            :onClose="() => showExceptionModal = false"
            classname="modal-lg"
            :containerStyle="{ maxWidth: '900px' }"
        >
            <template #title><h4>{{ __('message.payment_failed_exception') }}</h4></template>
            <template #fields>
                <div class="code-container">
                    <button :class="['copy-btn', copied && 'copied']" @click="copyException">
                        <i :class="copied ? 'fas fa-check' : 'fas fa-copy'"></i>
                        <span>{{ copied ? __('log.copied') : __('log.copy') }}</span>
                    </button>
                    <pre class="code-block">{{ exceptionContent }}</pre>
                </div>
            </template>
        </AppModal>

        <!-- Single row delete -->
        <DeleteModal
            v-if="deleteTarget"
            :showModal="!!deleteTarget"
            :onClose="() => deleteTarget = null"
            :deleteUrl="`${baseUrl}/paymentlog-delete`"
            :deleteData="{ ids: [deleteTarget.id] }"
            :componentName="COMPONENT"
            @deleted="onDeleted"
        />

        <!-- Bulk delete -->
        <DeleteModal
            v-if="showBulkDeleteModal"
            :showModal="showBulkDeleteModal"
            :onClose="() => showBulkDeleteModal = false"
            :deleteUrl="`${baseUrl}/paymentlog-delete`"
            :deleteData="{ ids: selected }"
            :componentName="COMPONENT"
            @deleted="onBulkDeleted"
        />
    </div>
</template>

<script setup>
import { h, reactive, ref, computed } from 'vue'
import PaymentFilter from './PaymentFilter.vue'
import { useTableSelection } from '@/core/composables/useTableSelection'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import { makeRequestAdapter } from '@/helpers/tableUtils'

const COMPONENT = 'payment-log'
const baseUrl = useBaseUrl()
const apiUrl  = `/get-payment-log-api`

// ── filter ────────────────────────────────────────────────────────────────────
const dtRef         = ref(null)
const showFilter    = ref(false)
const activeFilters = ref({})

function onFilterApply(params) {
    activeFilters.value = params
    showFilter.value    = false
    dtRef.value?.refresh()
}

function onFilterReset() {
    activeFilters.value = {}
    dtRef.value?.refresh()
}

// ── selection ─────────────────────────────────────────────────────────────────
const { selected, allSelected, toggleRow, toggleAll } = useTableSelection(dtRef)

// ── delete ────────────────────────────────────────────────────────────────────
const deleteTarget       = ref(null)
const showBulkDeleteModal = ref(false)

function onDeleted() {
    deleteTarget.value = null
    dtRef.value?.refresh()
}

function onBulkDeleted() {
    showBulkDeleteModal.value = false
    selected.value = []
    dtRef.value?.refresh()
}

// ── exception modal ───────────────────────────────────────────────────────────
const showExceptionModal = ref(false)
const exceptionContent   = ref('')
const copied             = ref(false)

function openException(text) {
    exceptionContent.value   = text
    showExceptionModal.value = true
    copied.value             = false
}

async function copyException() {
    try {
        await navigator.clipboard.writeText(exceptionContent.value)
    } catch {
        const ta = document.createElement('textarea')
        ta.value = exceptionContent.value
        document.body.appendChild(ta)
        ta.select()
        document.execCommand('copy')
        document.body.removeChild(ta)
    }
    copied.value = true
    setTimeout(() => { copied.value = false }, 1500)
}

// ── table ─────────────────────────────────────────────────────────────────────
const columns = ['select', 'date', 'user', 'order', 'amount', 'payment_type', 'payment_method', 'status', 'action']

const tableOptions = reactive({
    headings: {
        select: () => h('input', {
            type: 'checkbox',
            checked: allSelected.value,
            onChange: toggleAll,
        }),
        date:           __('message.date'),
        user:           __('message.user'),
        order:          __('message.order_no'),
        amount:         __('message.amount'),
        payment_type:   __('message.description'),
        payment_method: __('message.payment-method'),
        status:         __('message.status'),
        action:         __('message.action'),
    },
    columnsClasses: {
        select: 'dt-select',
        date: 'dt-date',
        user: 'dt-name',
        order: 'dt-number',
        amount: 'dt-amount',
        payment_type: 'dt-name',
        payment_method: 'dt-name',
        status: 'dt-status',
        action: 'dt-action',
    },
    templates: {
        select: (f, row) => h('input', {
            type: 'checkbox',
            checked: selected.value.includes(row.id),
            onChange: () => toggleRow(row.id),
        }),
        user: (f, row) => {
            const name = row.user || '—'
            if (!row.user_id) return name
            return h('a', { href: `${baseUrl}/admin/clients/${row.user_id}` }, name)
        },
        order:          (f, row) => row.order          || '—',
        payment_type:   (f, row) => row.payment_type   || '—',
        payment_method: (f, row) => row.payment_method || '—',
        status: (f, row) => {
            const s = row.status?.toLowerCase()
            if (s === 'failed' && row.exception) {
                return h('span', {
                    class: 'badge bg-danger',
                    style: 'cursor:pointer',
                    onClick: () => openException(row.exception),
                }, row.status)
            }
            return h('span', {
                class: s === 'success' ? 'badge bg-success' : 'badge bg-secondary',
            }, row.status || '—')
        },
        action: (f, row) => h('button', {
            class: 'btn btn-light table_btn',
            title: __('message.Delete'),
            onClick: () => { deleteTarget.value = row },
        }, h('i', { class: 'fas fa-trash' })),
    },
    sortable: ['date', 'amount', 'status', 'order', 'payment_method', 'payment_type', 'user'],
    filterable: true,
    requestAdapter: makeRequestAdapter('date', activeFilters),
    orderBy: { column: 'date', ascending: false },
})
</script>

<style scoped>
.code-container {
    position: relative;
    background-color: #000;
    border-radius: 8px;
}
.code-block {
    background-color: #000 !important;
    color: #fff;
    font-family: 'Courier New', monospace;
    font-size: 13px;
    padding: 50px 20px 20px;
    border-radius: 8px;
    overflow: auto;
    max-height: 500px;
    border: none;
    margin: 0;
    white-space: pre-wrap;
    word-wrap: break-word;
}
.copy-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #333;
    border: 1px solid #555;
    color: #fff;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    z-index: 10;
    transition: all 0.3s ease;
}
.copy-btn:hover  { background: #555; border-color: #777; }
.copy-btn.copied { background: #28a745; border-color: #28a745; }
.copy-btn i      { margin-right: 5px; }
</style>
