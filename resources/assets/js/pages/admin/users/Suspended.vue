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
import { h, ref, reactive } from 'vue'
import http from '@/plugins/axios'
import { errorHandler } from '@/helpers/responseHandler.js'
import { useDateTime } from '@/core/composables/useDateTime'
import SuspendedTableActions from './components/SuspendedTableActions.vue'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'
import { useTableSelection } from '@/core/composables/useTableSelection'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import { makeRequestAdapter } from '@/helpers/tableUtils'

const { formatDate } = useDateTime()

const COMPONENT = 'suspended-index'

const baseUrl = useBaseUrl()
const apiUrl  = `/soft-delete`

const dtRef  = ref(null)
const { selected, allSelected, toggleRow, toggleAll } = useTableSelection(dtRef)
const pendingBulkDelete = ref(null)

async function bulkRestore() {
    if (!selected.value.length) return
    try {
        const promises = selected.value.map(id => http.post(`/user/restore/${id}`))
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
        mobile: (f, row) => {
            if (!row.mobile?.trim()) return '—'
            const code = row.mobile_code?.trim()
            return code ? `+${code} ${row.mobile.trim()}` : row.mobile.trim()
        },
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

    requestAdapter: makeRequestAdapter('created_at'),

    orderBy: { column: 'created_at', ascending: false },
})
</script>
