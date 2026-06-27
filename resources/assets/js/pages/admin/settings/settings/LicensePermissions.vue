<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.license_permission') }}</h4>
            </div>
            <div class="card-body">
                <DataTable
                    ref="dtRef"
                    :url="apiUrl"
                    :dataColumns="columns"
                    :option="tableOptions"
                />
            </div>
        </div>

        <!-- Add/Edit Permissions Modal -->
        <AppModal
            v-if="editLicense"
            :showModal="!!editLicense"
            :onClose="closeModal"
            :showCloseBtn="false"
        >
            <template #title>
                <h4>{{ editLicense.name }}</h4>
            </template>
            <template #fields>
                <div v-for="perm in editPerms" :key="perm.id" class="mb-1">
                    <Checkbox :name="`perm-${perm.id}`" :label="perm.permissions" :value="!!perm.assigned" :onChange="(val) => perm.assigned = val" />
                </div>
            </template>
            <template #controls>
                <action-button action="save" type="button" :loading="saving" @click="savePerms" />
            </template>
        </AppModal>
    </div>
</template>

<script setup>
import { h, ref, reactive } from 'vue'
import Checkbox from '@/components/Reusable/FormField/Checkbox.vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'license-permissions'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl = `${baseUrl}/get-license-permission`

const dtRef = ref(null)
const editLicense = ref(null)
const editPerms = ref([])
const saving = ref(false)

function openEdit(license) {
    editLicense.value = license
    editPerms.value = (license.all_permissions ?? []).map(p => ({ ...p }))
}

function closeModal() {
    editLicense.value = null
    editPerms.value = []
}

async function savePerms() {
    saving.value = true
    const permissionid = editPerms.value.filter(p => p.assigned).map(p => p.id)
    try {
        const res = await http.delete(`${baseUrl}/add-permission`, {
            data: { licenseId: editLicense.value.id, permissionid },
        })
        successHandler(res, COMPONENT)
        closeModal()
        dtRef.value?.refresh()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}

const columns = ['name', 'permissions', 'action']

const tableOptions = reactive({
    headings: {
        name:        __('message.license-type'),
        permissions: __('message.license_permission'),
        action:      __('message.action'),
    },
    columnsClasses: {
        name: 'dt-name',
        permissions: 'dt-text',
        action: 'dt-action',
    },
    templates: {
        name: (f, row) => row.name || '—',
        permissions: (f, row) => {
            const perms = row.permissions ?? []
            if (!perms.length) return h('span', { class: 'text-muted fst-italic' }, __('message.no_permissions_selected'))
            return h('ul', { class: 'mb-0 ps-3' }, perms.map(p => h('li', { class: 'fw-bold' }, p)))
        },
        action: (f, row) => h('button', {
            class: 'btn btn-secondary btn-sm',
            onClick: () => openEdit(row),
        }, [h('i', { class: 'fas fa-plus me-1' }), __('message.add-permissions')]),
    },
    sortable: ['name'],
    filterable: true,
    requestAdapter(data) {
        return {
            'sort-field':   data.orderBy ?? 'name',
            'sort-order':   data.orderBy ? (data.ascending ? 'asc' : 'desc') : 'desc',
            'search-query': (data.query ?? '').trim(),
            page:           data.page,
            limit:          data.limit,
        }
    },
    responseAdapter({ data }) {
        const types = data?.data?.license_types
        return {
            data:  types?.data ?? [],
            count: types?.to   ?? types?.data?.length ?? 0,
        }
    },
    orderBy: { column: 'name', ascending: true },
})
</script>
