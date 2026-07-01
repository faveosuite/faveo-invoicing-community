<template>
    <div>
        <AppAlert componentName="users-index" />
    <div class="card card-light">
        <div class="card-header">
            <h4 class="card-title">{{ __('message.users') }}</h4>
            <div class="card-tools">
                <button
                    class="btn btn-tool"
                    v-tooltip="__('message.filter')"
                    @click="showFilter = !showFilter"
                >
                    <i class="fas fa-filter"></i>
                </button>
                <button
                    class="btn btn-tool"
                    v-tooltip="__('message.export')"
                    :disabled="exporting"
                    @click="exportAll"
                >
                    <i class="fas fa-paper-plane"></i>
                </button>
                <router-link
                    to="/users/create"
                    class="btn btn-tool"
                    v-tooltip="__('message.create_new_user')"
                >
                    <i class="fas fa-plus fw-bold"></i>
                </router-link>
            </div>
        </div>

        <div class="card-body">
            <UserFilter
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
                <template #table-tools>
                    <ColumnSelector
                        :entityType="'users'"
                        :labels="columnLabels"
                        :componentName="COMPONENT"
                        @change="onColumnsChange"
                    />
                </template>
                <template #bulk-actions>
                    <div v-if="selectedUsers.length > 0" class="dropdown">
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
                                <button class="dropdown-item" @click="bulkExport">
                                    {{ __('message.export_selected_records') }}
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
        :deleteUrl="`${baseUrl}/users`"
        :deleteData="pendingBulkDelete"
        :title="__('message.Delete')"
        :message="__('message.are_you_sure')"
        :componentName="COMPONENT"
        @deleted="() => { pendingBulkDelete = null; selectedUsers.value = []; dtRef.value?.refresh() }"
    />
</template>

<script setup>
import { h, ref, computed, reactive } from 'vue'
import { RouterLink } from 'vue-router'

import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { useDateTime } from '@/core/composables/useDateTime'
import UserTableActions from './components/UserTableActions.vue'
import UserFilter from './components/UserFilter.vue'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'
import ColumnSelector from '@/components/Reusable/ColumnSelector.vue'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import { useTableSelection } from '@/core/composables/useTableSelection'
import { makeRequestAdapter } from '@/helpers/tableUtils'

const { formatDate } = useDateTime()

const COMPONENT = 'users-index'

const baseUrl = useBaseUrl()
const apiUrl = `/users`

const dtRef = ref(null)
const { selected: selectedUsers, allSelected, toggleRow, toggleAll } = useTableSelection(dtRef)
const showFilter = ref(false)
const activeFilters = ref({})
const exporting = ref(false)
const pendingBulkDelete = ref(null)

function onFilterApply(params) {
    activeFilters.value = params
    showFilter.value = false
    dtRef.value?.refresh()
}

function onFilterReset() {
    activeFilters.value = {}
    dtRef.value?.refresh()
}

async function exportAll() {
    if (exporting.value) return
    exporting.value = true
    try {
        const res = await http.get(`/export-users`, {
            params: { search_params: activeFilters.value },
        })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        exporting.value = false
    }
}

async function bulkExport() {
    if (!selectedUsers.value.length) return
    try {
        const res = await http.get(`/export-users`, {
            params: { search_params: { user_ids: selectedUsers.value } },
        })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    }
}

function confirmBulkDelete() {
    if (!selectedUsers.value.length) return
    pendingBulkDelete.value = { user_ids: [...selectedUsers.value] }
}

// report_columns keys (type 'users') ↔ this table's internal column names.
const REPORT_TO_COL = {
    checkbox: 'select',
    name: 'name',
    email: 'email',
    mobile: 'mobile',
    country: 'country',
    created_at: 'created_at',
    active: 'account_info',
    action: 'action',
}

// Labels shown in the ColumnSelector dropdown (keyed by report_columns key).
const columnLabels = {
    name: __('message.name'),
    email: __('message.email'),
    mobile: __('message.mobile'),
    country: __('message.country'),
    created_at: __('message.registered_on'),
    active: __('message.account_info'),
}

const DEFAULT_COLUMNS = ['select', 'name', 'email', 'mobile', 'country', 'created_at', 'account_info', 'action']
const columns = ref([...DEFAULT_COLUMNS])

// ColumnSelector emits ordered, visible report_columns keys — map them onto
// this table's column names so the DataTable shows/orders columns accordingly.
function onColumnsChange(reportKeys) {
    const mapped = reportKeys.map(k => REPORT_TO_COL[k]).filter(Boolean)
    columns.value = mapped.length ? mapped : [...DEFAULT_COLUMNS]
}

const statusIcon = (iconClass, active, activeTitle, inactiveTitle) =>
    h('i', {
        class: `${iconClass} ${active ? 'text-success' : 'text-danger'}`,
        title: active ? activeTitle : inactiveTitle,
    })

const tableOptions = reactive({
    headings: {
        select: () => h('input', {
            type: 'checkbox',
            checked: allSelected.value,
            onChange: toggleAll,
        }),
        name: __('message.name'),
        email: __('message.email'),
        mobile: __('message.mobile'),
        country: __('message.country'),
        created_at: __('message.registered_on'),
        account_info: __('message.account_info'),
        action: __('message.actions'),
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
        select: (f, row) => h('input', {
            type: 'checkbox',
            checked: selectedUsers.value.includes(row.id),
            onChange: () => toggleRow(row.id),
        }),
        name: (f, row) => {
            const fullName = `${row.first_name ?? ''} ${row.last_name ?? ''}`.trim()
            if (fullName && row.id) return h(RouterLink, { to: '/users/' + row.id }, () => fullName)
            return '—'
        },
        email: (f, row) => {
            if (row.email && row.id) return h(RouterLink, { to: '/users/' + row.id }, () => row.email)
            return '—'
        },
        mobile: (f, row) => {
            if (!row.mobile?.trim()) return '—'
            const code = row.mobile_code?.trim()
            return code ? `+${code} ${row.mobile.trim()}` : row.mobile.trim()
        },
        country: (f, row) => row.country?.trim() || '—',
        created_at: (f, row) => row.created_at ? formatDate(row.created_at) : '—',
        account_info: (f, row) => h('div', { class: 'd-flex gap-2' }, [
            statusIcon('fas fa-envelope-circle-check', row.email_verified,  "User's email address is verified",  "User's email address is not verified"),
            statusIcon('fas fa-phone',                 row.mobile_verified, 'User has verified mobile',           'User has not verified mobile'),
            statusIcon('fas fa-shield-halved',         row.is_2fa_enabled,  'User has enabled 2FA',               'User has not enabled 2FA'),
        ]),
        action: (f, row) => h(UserTableActions, {
            userId: row.id,
            baseUrl: baseUrl,
            onDeleted: () => dtRef.value?.refresh(),
        }),
    },

    sortable: ['email', 'mobile', 'country', 'created_at'],
    filterable: true,

    requestAdapter: makeRequestAdapter('created_at', activeFilters),

    orderBy: { column: 'created_at', ascending: false },
})
</script>
