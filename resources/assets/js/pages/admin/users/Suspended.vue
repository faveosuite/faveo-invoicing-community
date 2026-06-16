<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.suspended_users') }}</h4>
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
                                aria-expanded="false"
                            >
                                {{ __('message.bulk_action') }}
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <button class="dropdown-item" @click="bulkRestore">
                                        {{ __('message.restore') }}
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider"></li>
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
        :deleteUrl="`${baseUrl}/permanent-delete-client`"
        :deleteData="pendingBulkDelete"
        :title="__('message.Delete')"
        :message="__('message.are_you_sure')"
        :componentName="COMPONENT"
        @deleted="() => { pendingBulkDelete = null; selected.value = []; dtRef.value?.refresh() }"
    />
</template>

<script setup>
import { h, ref, computed, reactive } from 'vue'
import http from '@/plugins/axios'
import { errorHandler } from '@/helpers/responseHandler.js'
import { useDateTime } from '@/core/composables/useDateTime'
import SuspendedTableActions from './components/SuspendedTableActions.vue'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'

const { formatDate } = useDateTime()

const COMPONENT = 'suspended-index'

const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl  = `${baseUrl}/soft-delete`

const dtRef  = ref(null)
const selected = ref([])
const pendingBulkDelete = ref(null)

const allSelected = computed(() => {
    const data = dtRef.value?.tableData ?? []
    return data.length > 0 && data.every(row => selected.value.includes(row.id))
})

function toggleRow(id) {
    const idx = selected.value.indexOf(id)
    if (idx === -1) selected.value.push(id)
    else selected.value.splice(idx, 1)
}

function toggleAll(e) {
    const data = dtRef.value?.tableData ?? []
    if (e.target.checked) {
        const ids = data.map(r => r.id).filter(id => !selected.value.includes(id))
        selected.value.push(...ids)
    } else {
        const ids = data.map(r => r.id)
        selected.value = selected.value.filter(id => !ids.includes(id))
    }
}

async function bulkRestore() {
    if (!selected.value.length) return
    try {
        const promises = selected.value.map(id => http.get(`${baseUrl}/user/restore/${id}`))
        await Promise.all(promises)
        selected.value = []
        dtRef.value?.refresh()
    } catch (e) {
        errorHandler(e, COMPONENT)
    }
}

function confirmBulkDelete() {
    if (!selected.value.length) return
    pendingBulkDelete.value = { user_ids: [...selected.value] }
}

const columns = ['select', 'name', 'email', 'mobile', 'country', 'created_at', 'action']

const tableOptions = reactive({
    headings: {
        select:     () => h('input', {
            type: 'checkbox',
            checked: allSelected.value,
            onChange: toggleAll,
        }),
        name:       __('message.name'),
        email:      __('message.email'),
        mobile:     __('message.mobile'),
        country:    __('message.country'),
        created_at: __('message.registered_on'),
        action:     __('message.actions'),
    },

    columnsClasses: {
        select: 'dt-select',
        name: 'dt-name',
        email: 'dt-email',
        mobile: 'dt-mobile',
        country: 'dt-country',
        created_at: 'dt-date',
        action: 'dt-action',
    },

    templates: {
        select:     (f, row) => h('input', {
            type:     'checkbox',
            checked:  selected.value.includes(row.id),
            onChange: () => toggleRow(row.id),
        }),
        name:       (f, row) => `${row.first_name ?? ''} ${row.last_name ?? ''}`.trim() || '—',
        mobile:     (f, row) => row.mobile?.trim() || '—',
        country:    (f, row) => row.country?.trim() || '—',
        created_at: (f, row) => row.created_at ? formatDate(row.created_at) : '—',
        action:     (f, row) => h(SuspendedTableActions, {
            userId:        row.id,
            baseUrl:       baseUrl,
            componentName: COMPONENT,
            onRestored:    () => dtRef.value?.refresh(),
            onDeleted:     () => dtRef.value?.refresh(),
        }),
    },

    sortable:   ['email', 'mobile', 'country', 'created_at'],
    filterable: true,

    requestAdapter(data) {
        return {
            'sort-field':   data.orderBy || 'created_at',
            'sort-order':   data.ascending ? 'asc' : 'desc',
            'search-query': (data.query ?? '').trim(),
            page:           data.page,
            limit:          data.limit,
        }
    },

    orderBy: { column: 'created_at', ascending: false },
})
</script>
