<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0">Third-Party Apps</h4>
                <button class="btn btn-sm btn-success" @click="openCreate">
                    <i class="fas fa-plus"></i> Add App
                </button>
            </div>

            <div v-if="showForm" class="card-body border-bottom bg-light">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">App Name *</label>
                        <input class="form-control form-control-sm" v-model="form.app_name" placeholder="App Name" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">App Key (32 chars) *</label>
                        <input class="form-control form-control-sm" v-model="form.app_key" placeholder="32-character key" maxlength="32" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">App Secret *</label>
                        <input type="password" class="form-control form-control-sm" v-model="form.app_secret" placeholder="Secret" autocomplete="new-password" />
                    </div>
                    <div class="col-md-auto">
                        <button class="btn btn-primary btn-sm" @click="saveApp" :disabled="saving">
                            <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                            {{ editId ? 'Update' : 'Create' }}
                        </button>
                        <button class="btn btn-secondary btn-sm ms-2" @click="cancelForm">Cancel</button>
                    </div>
                </div>
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
    </div>
</template>

<script setup>
import { h, ref, reactive } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'third-party-apps'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl = `${baseUrl}/get-third-party-app`

const dtRef = ref(null)
const showForm = ref(false)
const saving = ref(false)
const editId = ref(null)

const form = reactive({ app_name: '', app_key: '', app_secret: '' })

function openCreate() {
    editId.value = null
    Object.assign(form, { app_name: '', app_key: '', app_secret: '' })
    showForm.value = true
}

function openEdit(row) {
    editId.value = row.id
    Object.assign(form, { app_name: row.app_name, app_key: row.app_key, app_secret: '' })
    showForm.value = true
}

function cancelForm() {
    showForm.value = false
    editId.value = null
}

async function saveApp() {
    saving.value = true
    try {
        const res = editId.value
            ? await http.put(`${baseUrl}/third-party-app-update/${editId.value}`, form)
            : await http.post(`${baseUrl}/third-party-app-create`, form)
        successHandler(res, COMPONENT)
        cancelForm()
        dtRef.value?.refresh()
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { saving.value = false }
}

async function deleteApp(id) {
    try {
        const res = await http.delete(`${baseUrl}/third-party-delete`, { data: { id } })
        successHandler(res, COMPONENT)
        dtRef.value?.refresh()
    } catch (e) { errorHandler(e, COMPONENT) }
}

const columns = ['app_name', 'app_key', 'action']

const tableOptions = reactive({
    headings: {
        app_name: 'App Name',
        app_key:  'App Key',
        action:   'Action',
    },
    templates: {
        app_name: (f, row) => row.app_name || '—',
        app_key:  (f, row) => row.app_key || '—',
        action:   (f, row) => h('div', { class: 'd-flex gap-1' }, [
            h('button', {
                class: 'btn btn-light table_btn',
                title: 'Edit',
                onClick: () => openEdit(row),
            }, h('i', { class: 'fas fa-edit' })),
            h('button', {
                class: 'btn btn-light table_btn',
                title: 'Delete',
                onClick: () => deleteApp(row.id),
            }, h('i', { class: 'fas fa-trash' })),
        ]),
    },
    sortable: ['app_name'],
    filterable: true,
    requestAdapter(data) {
        return {
            'sort-field': data.orderBy ?? 'created_at',
            'sort-order': data.ascending ? 'asc' : 'desc',
            'search-query': (data.query ?? '').trim(),
            page: data.page,
            limit: data.limit,
        }
    },
    responseAdapter({ data }) {
        const res = data?.data?.third_party_apps
        return {
            data: res?.data ?? [],
            count: data?.data?.total ?? res?.total ?? 0,
        }
    },
    orderBy: { column: 'created_at', ascending: false },
})
</script>
