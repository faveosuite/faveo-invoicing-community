<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h4 class="card-title">Third-Party Apps</h4>
                <div class="card-tools">
                    <button class="btn btn-tool" title="Add App" v-tooltip @click="openCreate">
                        <i class="fas fa-plus fw-bold"></i>
                    </button>
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
                        <div v-if="selected.length > 0" class="dropdown">
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

        <!-- Create Modal -->
        <modal :showModal="showCreate" :onClose="closeCreate" :showCloseBtn="false">
            <template #title>
                <h4>Add App</h4>
            </template>
            <template #fields>
                <TextField
                    name="app_name"
                    label="App Name"
                    :value="form.app_name"
                    :onChange="(val) => form.app_name = val"
                    placeholder="App Name"
                />
                <div class="mb-3">
                    <label class="form-label fw-bold">App Key (32 chars)</label>
                    <div class="input-group">
                        <input
                            type="text"
                            name="app_key"
                            :value="form.app_key"
                            @input="form.app_key = $event.target.value"
                            placeholder="32-character key"
                            :class="['form-control', { 'is-invalid': appKeyError }]"
                        />
                        <button type="button" class="btn btn-secondary" @click="generateKey" :disabled="generatingKey" title="Generate random key">
                            <span v-if="generatingKey" class="spinner-border spinner-border-sm me-1"></span>
                            <i v-else class="fas fa-sync-alt me-1"></i>Generate
                        </button>
                        <div v-if="appKeyError" class="invalid-feedback">{{ appKeyError }}</div>
                    </div>
                </div>
                <TextField
                    name="app_secret"
                    label="App Secret"
                    type="password"
                    :value="form.app_secret"
                    :onChange="(val) => form.app_secret = val"
                    placeholder="Secret"
                />
            </template>
            <template #controls>
                <button type="button" class="btn btn-primary" :disabled="saving" @click="saveApp">
                    <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                    <i v-else class="fas fa-save me-1"></i>
                    Save
                </button>
            </template>
        </modal>

        <!-- Edit Modal -->
        <modal :showModal="showEdit" :onClose="closeEdit" :showCloseBtn="false">
            <template #title>
                <h4>Edit App</h4>
            </template>
            <template #fields>
                <TextField
                    name="app_name"
                    label="App Name"
                    :value="form.app_name"
                    :onChange="(val) => form.app_name = val"
                    placeholder="App Name"
                />
                <div class="mb-3">
                    <label class="form-label fw-bold">App Key (32 chars)</label>
                    <div class="input-group">
                        <input
                            type="text"
                            name="app_key"
                            :value="form.app_key"
                            @input="form.app_key = $event.target.value"
                            placeholder="32-character key"
                            :class="['form-control', { 'is-invalid': appKeyError }]"
                        />
                        <button type="button" class="btn btn-secondary" @click="generateKey" :disabled="generatingKey" title="Generate random key">
                            <span v-if="generatingKey" class="spinner-border spinner-border-sm me-1"></span>
                            <i v-else class="fas fa-sync-alt me-1"></i>Generate
                        </button>
                        <div v-if="appKeyError" class="invalid-feedback">{{ appKeyError }}</div>
                    </div>
                </div>
                <TextField
                    name="app_secret"
                    label="App Secret"
                    :value="form.app_secret"
                    :onChange="(val) => form.app_secret = val"
                    placeholder="Secret"
                />
            </template>
            <template #controls>
                <button type="button" class="btn btn-primary" :disabled="saving" @click="saveApp">
                    <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                    <i v-else class="fas fa-save me-1"></i>
                    Update
                </button>
            </template>
        </modal>

        <!-- Single Delete Modal -->
        <DeleteModal
            v-if="deleteId !== null"
            :showModal="deleteId !== null"
            :onClose="closeDelete"
            :deleteUrl="`${baseUrl}/third-party-delete`"
            :deleteData="{ select: [deleteId] }"
            :componentName="COMPONENT"
            @deleted="onDeleted"
        />

        <!-- Bulk Delete Modal -->
        <DeleteModal
            v-if="showBulkDelete"
            :showModal="showBulkDelete"
            :onClose="() => showBulkDelete = false"
            :deleteUrl="`${baseUrl}/third-party-delete`"
            :deleteData="{ select: selected }"
            :componentName="COMPONENT"
            @deleted="onBulkDeleted"
        />
    </div>
</template>

<script setup>
import { h, ref, reactive, computed } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import DeleteModal from '@/themes/adminlte/components/common/DeleteModal.vue'
import { useAlertStore } from '@/core/stores/alert'

const COMPONENT = 'third-party-apps'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl = `${baseUrl}/get-third-party-app`

const dtRef    = ref(null)
const saving   = ref(false)
const deleting = ref(false)
const editId   = ref(null)
const selected = ref([])

const form = reactive({ app_name: '', app_key: '', app_secret: '' })
function resetForm() { Object.assign(form, { app_name: '', app_key: '', app_secret: '' }) }

const appKeyError = computed(() => useAlertStore().validation_errors['app_key'] ?? '')

const generatingKey = ref(false)
async function generateKey() {
    generatingKey.value = true
    try {
        const res = await http.get(`${baseUrl}/get-app-key`, { responseType: 'text' })
        form.app_key = res.data
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { generatingKey.value = false }
}

// Create
const showCreate = ref(false)
function openCreate() { resetForm(); showCreate.value = true }
function closeCreate() { showCreate.value = false; resetForm() }

// Edit
const showEdit = ref(false)
function openEdit(row) {
    editId.value = row.id
    Object.assign(form, { app_name: row.app_name, app_key: row.app_key, app_secret: row.app_secret })
    showEdit.value = true
}
function closeEdit() { showEdit.value = false; editId.value = null; resetForm() }

// Single Delete
const deleteId = ref(null)
function openDelete(id) { deleteId.value = id }
function closeDelete() { deleteId.value = null }
function onDeleted() { closeDelete(); dtRef.value?.refresh() }

// Bulk Delete
const showBulkDelete = ref(false)
function bulkDelete() { if (selected.value.length) showBulkDelete.value = true }
function onBulkDeleted() { showBulkDelete.value = false; selected.value = []; dtRef.value?.refresh() }

// Select all
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
    if (e.target.checked) selected.value.push(...data.map(r => r.id).filter(id => !selected.value.includes(id)))
    else { const ids = data.map(r => r.id); selected.value = selected.value.filter(id => !ids.includes(id)) }
}

async function saveApp() {
    saving.value = true
    try {
        const res = editId.value
            ? await http.put(`${baseUrl}/third-party-app-update/${editId.value}`, form)
            : await http.post(`${baseUrl}/third-party-app-create`, form)
        successHandler(res, COMPONENT)
        editId.value ? closeEdit() : closeCreate()
        dtRef.value?.refresh()
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { saving.value = false }
}

const columns = ['select', 'app_name', 'app_key', 'app_secret', 'action']

const tableOptions = reactive({
    headings: {
        select:     () => h('input', { type: 'checkbox', checked: allSelected.value, onChange: toggleAll }),
        app_name:   'App Name',
        app_key:    'App Key',
        app_secret: 'App Secret',
        action:     'Action',
    },
    templates: {
        select:     (f, row) => h('input', { type: 'checkbox', checked: selected.value.includes(row.id), onChange: () => toggleRow(row.id) }),
        app_name:   (f, row) => row.app_name   || '—',
        app_key:    (f, row) => row.app_key    || '—',
        app_secret: (f, row) => row.app_secret || '—',
        action:     (f, row) => h('div', { class: 'd-flex gap-1' }, [
            h('button', { class: 'btn btn-light table_btn', title: 'Edit',   onClick: () => openEdit(row)    }, [h('i', { class: 'fas fa-edit'  })]),
            h('button', { class: 'btn btn-light table_btn', title: 'Delete', onClick: () => openDelete(row.id) }, [h('i', { class: 'fas fa-trash' })]),
        ]),
    },
    sortable: ['app_name'],
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
    responseAdapter({ data }) {
        const res = data?.data?.third_party_apps
        return {
            data:  res?.data  ?? [],
            count: data?.data?.total ?? res?.total ?? 0,
        }
    },
    orderBy: { column: 'created_at', ascending: false },
})
</script>
