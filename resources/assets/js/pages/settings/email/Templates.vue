<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">Email Templates</h4>
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

        <!-- Edit panel -->
        <div v-if="editing" class="card card-light mt-3">
            <div class="card-header">
                <h4 class="card-title">Edit: {{ editForm.name }}</h4>
                <div class="card-tools">
                    <button class="btn btn-sm btn-tool" @click="editing = false" title="Close">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>
            </div>

            <div v-if="editLoading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <TextField name="name" label="Name *" :value="editForm.name" :onChange="onEditChange" />
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Type *</label>
                                <select class="form-select" v-model="editForm.type">
                                    <option v-for="(name, id) in editForm.types" :key="id" :value="id">{{ name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <TextField name="reply_email" label="Reply Email" :value="editForm.reply_email" :onChange="onEditChange" />
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Content *</label>
                        <TinyMCE name="data" id="editor-template" :value="editForm.data" :onChange="onEditChange" />
                    </div>

                    <div v-if="editForm.codes" class="mt-3">
                        <label class="form-label fw-bold">Available Shortcodes</label>
                        <div class="d-flex flex-wrap gap-1">
                            <span
                                v-for="(desc, code) in editForm.codes"
                                :key="code"
                                class="badge bg-secondary user-select-none"
                                :title="desc"
                            >{{ code }}</span>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button class="btn btn-primary" @click="saveTemplate" :disabled="editSaving">
                        <span v-if="editSaving" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        Update
                    </button>
                    <button class="btn btn-secondary ms-2" @click="editing = false">Cancel</button>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { h, ref, reactive } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'email-templates'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl = `${baseUrl}/template/list`

const dtRef = ref(null)
const editing = ref(false)
const editLoading = ref(false)
const editSaving = ref(false)
const editId = ref(null)

const editForm = reactive({
    name: '', type: '', reply_email: '', data: '', types: {}, codes: null,
})

function onEditChange(val, name) { editForm[name] = val }

async function openEdit(id) {
    editing.value = true
    editLoading.value = true
    editId.value = id
    try {
        const res = await http.get(`${baseUrl}/template/edit/${id}`)
        const d = res.data?.data ?? {}
        editForm.name        = d.template?.name ?? ''
        editForm.type        = String(d.template?.type ?? '')
        editForm.reply_email = d.template?.reply_email ?? ''
        editForm.data        = d.template?.data ?? ''
        editForm.types       = d.type ?? {}
        editForm.codes       = d.codes ?? null
    } catch (e) {
        errorHandler(e, COMPONENT)
        editing.value = false
    } finally {
        editLoading.value = false
    }
}

async function saveTemplate() {
    editSaving.value = true
    try {
        const res = await http.put(`${baseUrl}/template/update/${editId.value}`, {
            name:        editForm.name,
            type:        editForm.type,
            reply_email: editForm.reply_email,
            data:        editForm.data,
        })
        successHandler(res, COMPONENT)
        editing.value = false
        dtRef.value?.refresh()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        editSaving.value = false
    }
}

const columns = ['name', 'type', 'action']

const tableOptions = reactive({
    headings: {
        name:   'Name',
        type:   'Type',
        action: 'Action',
    },
    templates: {
        name:   (f, row) => row.name || '—',
        type:   (f, row) => row.type || '—',
        action: (f, row) => h('button', {
            class: 'btn btn-light table_btn',
            title: 'Edit',
            onClick: () => openEdit(row.id),
        }, h('i', { class: 'fas fa-pen' })),
    },
    sortable: ['name', 'type'],
    filterable: true,
    requestAdapter(data) {
        return {
            'sort-field':   data.orderBy ?? 'id',
            'sort-order':   data.ascending ? 'asc' : 'desc',
            'search-query': (data.query ?? '').trim(),
            page:           data.page,
            limit:          data.limit,
        }
    },
    orderBy: { column: 'id', ascending: true },
})
</script>
