<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">License Permissions</h4>
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
        <modal
            v-if="editLicense"
            :showModal="!!editLicense"
            :onClose="closeModal"
            :showCloseBtn="false"
        >
            <template #title>
                <h4>{{ editLicense.name }}</h4>
            </template>
            <template #fields>
                <div v-for="perm in editPerms" :key="perm.id" class="form-check mb-2">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        :id="`perm-${perm.id}`"
                        v-model="perm.assigned"
                    />
                    <label class="form-check-label" :for="`perm-${perm.id}`">
                        {{ perm.permissions }}
                    </label>
                </div>
            </template>
            <template #controls>
                <button type="button" class="btn btn-primary" :disabled="saving" @click="savePerms">
                    <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                    <i v-else class="fas fa-save me-1"></i>
                    Save
                </button>
            </template>
        </modal>
    </div>
</template>

<script setup>
import { h, ref, reactive } from 'vue'
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
        name:        'License Type',
        permissions: 'License Permissions',
        action:      'Action',
    },
    templates: {
        name: (f, row) => row.name || '—',
        permissions: (f, row) => {
            const perms = row.permissions ?? []
            if (!perms.length) return h('span', { class: 'text-muted fst-italic' }, 'No Permissions Selected')
            return h('ul', { class: 'mb-0 ps-3' }, perms.map(p => h('li', { class: 'fw-bold' }, p)))
        },
        action: (f, row) => h('button', {
            class: 'btn btn-secondary btn-sm',
            onClick: () => openEdit(row),
        }, [h('i', { class: 'fas fa-plus me-1' }), 'Add Permissions']),
    },
    sortable: ['name'],
    filterable: true,
    requestAdapter(data) {
        return {
            'sort-field':   data.orderBy ?? 'name',
            'sort-order':   data.ascending ? 'asc' : 'desc',
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
