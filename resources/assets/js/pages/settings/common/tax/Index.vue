<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0">Tax</h4>
                <div>
                    <button class="btn btn-sm btn-danger me-2" :disabled="!selected.length" @click="bulkDelete">
                        <i class="fas fa-trash"></i> Delete Selected
                    </button>
                    <button class="btn btn-sm btn-success" @click="showCreate = !showCreate">
                        <i class="fas fa-plus"></i> Add Tax
                    </button>
                </div>
            </div>

            <div v-if="showCreate" class="card-body border-bottom bg-light">
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Tax Class *</label>
                        <select class="form-select form-select-sm" v-model="createForm.name" @change="createForm.rate='';createForm.country='IN';createForm.state=''">
                            <option value="">— Select —</option>
                            <option v-for="c in taxClasses" :key="c" :value="c">{{ c }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Tax Name *</label>
                        <input class="form-control form-control-sm" v-model="createForm['tax-name']" placeholder="Tax name" />
                    </div>
                    <template v-if="createForm.name === 'Others'">
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Rate *</label>
                            <input class="form-control form-control-sm" v-model="createForm.rate" type="number" step="0.01" placeholder="0.00" />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Country</label>
                            <select class="form-select form-select-sm" v-model="createForm.country" @change="loadCreateStates">
                                <option v-for="c in countries" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">State</label>
                            <select class="form-select form-select-sm" v-model="createForm.state">
                                <option value="">— Any —</option>
                                <option v-for="s in createStates" :key="s.iso2" :value="s.iso2">{{ s.state_subdivision_name }}</option>
                            </select>
                        </div>
                    </template>
                    <div class="col-md-auto">
                        <button class="btn btn-primary btn-sm" @click="createTax" :disabled="creating">
                            <span v-if="creating" class="spinner-border spinner-border-sm me-1"></span>
                            Create
                        </button>
                        <button class="btn btn-secondary btn-sm ms-2" @click="showCreate = false">Cancel</button>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <DataTable
                    ref="dtRef"
                    :url="apiUrl"
                    :dataColumns="columns"
                    :option="tableOptions"
                    @row-select="onRowSelect"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { h, ref, reactive, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'tax-index'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl = `${baseUrl}/tax-tables`

const dtRef = ref(null)
const selected = ref([])
const showCreate = ref(false)
const creating = ref(false)
const taxClasses = ['CGST', 'SGST', 'IGST', 'UTGST', 'Others']
const countries = ref([])
const createStates = ref([])

const createForm = reactive({
    name: '', 'tax-name': '', rate: '', country: 'IN', state: '',
})

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/tax-options`)
        countries.value = Object.entries(res.data?.data?.countries ?? {}).map(([id, name]) => ({ id, name }))
    } catch (e) { /* countries optional */ }
})

async function loadCreateStates() {
    createForm.state = ''
    createStates.value = []
    if (!createForm.country) return
    try {
        const res = await http.get(`${baseUrl}/get-state/${createForm.country}`)
        createStates.value = res.data?.data?.states ?? []
    } catch (e) { /* ignore */ }
}

function onRowSelect(ids) { selected.value = ids }

async function createTax() {
    creating.value = true
    try {
        const res = await http.post(`${baseUrl}/create/tax-class`, createForm)
        successHandler(res, COMPONENT)
        showCreate.value = false
        Object.assign(createForm, { name: '', 'tax-name': '', rate: '', country: 'IN', state: '' })
        dtRef.value?.refresh()
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { creating.value = false }
}

async function bulkDelete() {
    if (!selected.value.length) return
    try {
        const res = await http.delete(`${baseUrl}/tax/delete`, { data: { select: selected.value } })
        successHandler(res, COMPONENT)
        selected.value = []
        dtRef.value?.refresh()
    } catch (e) { errorHandler(e, COMPONENT) }
}

const columns = ['select', 'name', 'country', 'state', 'rate', 'tax_class_name', 'action']

const tableOptions = reactive({
    headings: {
        select: '',
        name: 'Name',
        country: 'Country',
        state: 'State',
        rate: 'Rate',
        tax_class_name: 'Tax Class',
        action: 'Action',
    },
    templates: {
        select: (f, row) => h('input', {
            type: 'checkbox',
            checked: selected.value.includes(row.id),
            onChange: (e) => {
                if (e.target.checked) selected.value = [...selected.value, row.id]
                else selected.value = selected.value.filter(id => id !== row.id)
            },
        }),
        name: (f, row) => row.name || '—',
        country: (f, row) => row.country || '—',
        state: (f, row) => row.state || '—',
        rate: (f, row) => row.rate || '—',
        tax_class_name: (f, row) => row.tax_class_name || '—',
        action: (f, row) => h(RouterLink, {
            to: `/settings/common/tax/${row.id}/edit`,
            class: 'btn btn-light table_btn',
            title: 'Edit',
        }, () => h('i', { class: 'fas fa-edit' })),
    },
    sortable: ['name', 'country', 'rate'],
    filterable: true,
    requestAdapter(data) {
        return {
            'sort-field': data.orderBy ?? 'id',
            'sort-order': data.ascending ? 'asc' : 'desc',
            'search-query': (data.query ?? '').trim(),
            page: data.page,
            limit: data.limit,
        }
    },
    orderBy: { column: 'id', ascending: false },
})
</script>
