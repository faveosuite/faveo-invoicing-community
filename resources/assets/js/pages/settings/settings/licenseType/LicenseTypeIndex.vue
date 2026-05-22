<template>
    <div>
        <AppAlert componentName="license-type-index" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.license_types') }}</h4>
                <div class="card-tools">
                    <button class="btn btn-tool" :title="__('message.add_license_type_btn')" v-tooltip @click="openCreate">
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

        <!-- Create Modal -->
        <modal :showModal="showCreate" :onClose="closeCreate" :showCloseBtn="false">
            <template #title>
                <h4>{{ __('message.add_license_type_btn') }}</h4>
            </template>
            <template #fields>
                <TextField
                    name="license_type_name"
                    :label="__('message.name')"
                    :value="newName"
                    :onChange="(val) => { newName = val; setFieldError('license_type_name', undefined) }"
                    :placeholder="__('message.name')"
                    :error="errors.license_type_name"
                />
            </template>
            <template #controls>
                <action-button action="create" type="button" :loading="creating" @click="create" />
            </template>
        </modal>

        <!-- Edit Modal -->
        <modal :showModal="showEdit" :onClose="closeEdit" :showCloseBtn="false">
            <template #title>
                <h4>{{ __('message.edit-license-type') }}</h4>
            </template>
            <template #fields>
                <inline-loader v-if="editLoading" />
                <TextField
                    v-else
                    name="license_type_edit_name"
                    :label="__('message.name')"
                    :value="editName"
                    :onChange="(val) => { editName = val; setFieldError('license_type_edit_name', undefined) }"
                    :placeholder="__('message.name')"
                    :error="errors.license_type_edit_name"
                />
            </template>
            <template #controls>
                <action-button action="update" type="button" :loading="saving" :disabled="saving || editLoading" @click="update" />
            </template>
        </modal>

        <!-- Delete Modal -->
        <DeleteModal
            v-if="deleteId !== null"
            :showModal="deleteId !== null"
            :onClose="closeDelete"
            :deleteUrl="`${baseUrl}/delete-license-type`"
            :deleteData="{ select: [deleteId] }"
            componentName="license-type-index"
            @deleted="onDeleted"
        />

        <!-- Bulk Delete Modal -->
        <DeleteModal
            v-if="showBulkDelete"
            :showModal="showBulkDelete"
            :onClose="() => showBulkDelete = false"
            :deleteUrl="`${baseUrl}/delete-license-type`"
            :deleteData="{ select: selected }"
            componentName="license-type-index"
            @deleted="onBulkDeleted"
        />

    </div>
</template>

<script setup>
import { h, ref, computed, reactive } from 'vue'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import DeleteModal from '@/themes/adminlte/components/common/DeleteModal.vue'
import { licenseTypeCreateSchema, licenseTypeEditSchema } from '@/validations/licenseTypeValidations'

const { errors, setErrors, setFieldError, resetForm } = useForm()

const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl = `${baseUrl}/get-license-type`

const dtRef = ref(null)
const selected = ref([])
const deleting = ref(false)

// Create
const showCreate = ref(false)
const newName = ref('')
const creating = ref(false)

function openCreate() { resetForm(); showCreate.value = true }
function closeCreate() { showCreate.value = false; newName.value = '' }

// Edit
const showEdit = ref(false)
const editId = ref(null)
const editName = ref('')
const editLoading = ref(false)
const saving = ref(false)

async function openEdit(id) {
    resetForm()
    editId.value = id
    editName.value = ''
    showEdit.value = true
    editLoading.value = true
    try {
        const res = await http.get(`${baseUrl}/get-license-type/${id}`)
        const d = res.data?.data ?? res.data
        editName.value = d.name ?? ''
    } catch (e) {
        errorHandler(e, 'license-type-index')
        closeEdit()
    } finally {
        editLoading.value = false
    }
}

function closeEdit() { showEdit.value = false; editId.value = null; editName.value = '' }

// Delete
const deleteId = ref(null)

function openDelete(id) { deleteId.value = id }
function closeDelete() { deleteId.value = null }
function onDeleted() { closeDelete(); dtRef.value?.refresh() }

// Bulk Delete
const showBulkDelete = ref(false)
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
    if (e.target.checked) {
        selected.value.push(...data.map(r => r.id).filter(id => !selected.value.includes(id)))
    } else {
        const ids = data.map(r => r.id)
        selected.value = selected.value.filter(id => !ids.includes(id))
    }
}

async function create() {
    try {
        licenseTypeCreateSchema.validateSync({ license_type_name: newName.value }, { abortEarly: false })
    } catch (err) {
        const errMap = {}
        err.inner?.forEach(e => { if (e.path && !errMap[e.path]) errMap[e.path] = e.message })
        setErrors(errMap)
        return
    }
    creating.value = true
    try {
        const res = await http.post(`${baseUrl}/create-license-type`, { name: newName.value })
        successHandler(res, 'license-type-index')
        closeCreate()
        dtRef.value?.refresh()
    } catch (e) {
        errorHandler(e, 'license-type-index')
    } finally {
        creating.value = false
    }
}

async function update() {
    try {
        licenseTypeEditSchema.validateSync({ license_type_edit_name: editName.value }, { abortEarly: false })
    } catch (err) {
        const errMap = {}
        err.inner?.forEach(e => { if (e.path && !errMap[e.path]) errMap[e.path] = e.message })
        setErrors(errMap)
        return
    }
    saving.value = true
    try {
        const res = await http.put(`${baseUrl}/update-license-type/${editId.value}`, { name: editName.value })
        successHandler(res, 'license-type-index')
        closeEdit()
        dtRef.value?.refresh()
    } catch (e) {
        errorHandler(e, 'license-type-index')
    } finally {
        saving.value = false
    }
}

function bulkDelete() {
    if (!selected.value.length) return
    showBulkDelete.value = true
}

const columns = ['select', 'name', 'action']

const tableOptions = reactive({
    headings: {
        select: () => h('input', { type: 'checkbox', checked: allSelected.value, onChange: toggleAll }),
        name:   __('message.name'),
        action: __('message.action'),
    },
    columnsClasses: {
        select: 'dt-select',
        name: 'dt-name',
        action: 'dt-action',
    },
    templates: {
        select: (f, row) => h('input', { type: 'checkbox', checked: selected.value.includes(row.id), onChange: () => toggleRow(row.id) }),
        name:   (f, row) => row.name || '—',
        action: (f, row) => h('div', { class: 'd-flex gap-1' }, [
            h('button', { class: 'btn btn-light table_btn', title: __('message.edit'),   onClick: () => openEdit(row.id)   }, [h('i', { class: 'fas fa-edit' })]),
            h('button', { class: 'btn btn-light table_btn', title: __('message.Delete'), onClick: () => openDelete(row.id) }, [h('i', { class: 'fas fa-trash' })]),
        ]),
    },
    sortable: ['name'],
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
    orderBy: { column: 'created_at', ascending: false },
})
</script>
