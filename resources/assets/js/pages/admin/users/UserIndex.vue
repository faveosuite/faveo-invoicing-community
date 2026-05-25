<template>
    <div>
        <AppAlert componentName="users-index" />
    <div class="card card-light">
        <div class="card-header">
            <h4 class="card-title">{{ __('message.users') }}</h4>
            <div class="card-tools">
                <button
                    class="btn btn-tool"
                    :title="__('message.filter')"
                    v-tooltip
                    @click="showFilter = !showFilter"
                >
                    <i class="fas fa-filter"></i>
                </button>
                <button
                    class="btn btn-tool"
                    :title="__('message.export')"
                    v-tooltip
                    :disabled="exporting"
                    @click="exportAll"
                >
                    <i class="fas fa-paper-plane"></i>
                </button>
                <router-link
                    to="/users/create"
                    class="btn btn-tool"
                    :title="__('message.create_new_user')"
                    v-tooltip
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
import { RouterLink } from 'vue-router'

import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import UserTableActions from './components/UserTableActions.vue'
import UserFilter from './components/UserFilter.vue'

const COMPONENT = 'users-index'

const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl = `${baseUrl}/users`

const dtRef = ref(null)
const selectedUsers = ref([])
const showFilter = ref(false)
const activeFilters = ref({})
const exporting = ref(false)

const allSelected = computed(() => {
    const data = dtRef.value?.tableData ?? []
    return data.length > 0 && data.every(row => selectedUsers.value.includes(row.id))
})

function toggleRow(id) {
    const idx = selectedUsers.value.indexOf(id)
    if (idx === -1) selectedUsers.value.push(id)
    else selectedUsers.value.splice(idx, 1)
}

function toggleAll(e) {
    const data = dtRef.value?.tableData ?? []
    if (e.target.checked) {
        const ids = data.map(r => r.id).filter(id => !selectedUsers.value.includes(id))
        selectedUsers.value.push(...ids)
    } else {
        const ids = data.map(r => r.id)
        selectedUsers.value = selectedUsers.value.filter(id => !ids.includes(id))
    }
}

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
        const res = await http.get(`${baseUrl}/export-users`, {
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
        const res = await http.get(`${baseUrl}/export-users`, {
            params: { search_params: { user_ids: selectedUsers.value } },
        })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    }
}

async function bulkDelete() {
    if (!selectedUsers.value.length) return
    if (!confirm(`Delete ${selectedUsers.value.length} selected user(s)? This cannot be undone.`)) return
    try {
        await http.delete(`${baseUrl}/users`, { data: { user_ids: selectedUsers.value } })
        selectedUsers.value = []
        dtRef.value?.refresh()
    } catch (e) {
        errorHandler(e, COMPONENT)
    }
}

const columns = ['select', 'name', 'email', 'mobile', 'country', 'created_at', 'account_info', 'action']

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
        mobile: (f, row) => row.mobile?.trim() || '—',
        country: (f, row) => row.country?.trim() || '—',
        created_at: (f, row) => row.created_at ? new Date(row.created_at).toLocaleDateString() : '—',
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

    requestAdapter(data) {
        return {
            'sort-field': data.orderBy || 'created_at',
            'sort-order': data.ascending ? 'asc' : 'desc',
            'search-query': (data.query ?? '').trim(),
            page: data.page,
            limit: data.limit,
            ...activeFilters.value,
        }
    },

    orderBy: { column: 'created_at', ascending: false },
})
</script>
