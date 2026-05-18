<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.whatsapp_users') }}</h4>
            </div>
            <div class="card-body">
                <DataTable ref="tableRef" :url="apiUrl" :dataColumns="columns" :option="options" />
            </div>
        </div>

        <AppModal :showModal="!!editRow" :onClose="closeEdit" classname="modal-md">
            <template #title>
                <h4>Edit Webhook URL</h4>
            </template>

            <template #alert>
                <AppAlert :componentName="COMPONENT" />
            </template>

            <template #fields>
                <div class="mb-0">
                    <label class="form-label fw-semibold">Webhook URL</label>
                    <input
                        v-model="editWebhookUrl"
                        type="text"
                        class="form-control"
                        placeholder="Enter webhook URL"
                    />
                </div>
            </template>

            <template #controls>
                <button class="btn btn-primary" :disabled="saving" @click="saveWebhook">
                    <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                    Save
                </button>
            </template>
        </AppModal>
    </div>
</template>

<script setup>
import { h, ref } from 'vue'
import DataTable from '@/themes/adminlte/components/common/DataTable.vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'whatsapp-users'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl  = `${baseUrl}/whatsapp-users-api`
const tableRef = ref(null)

const editRow        = ref(null)
const editWebhookUrl = ref('')
const saving         = ref(false)
const copiedId       = ref(null)

function openEdit(row) {
    editRow.value        = row
    editWebhookUrl.value = row.callback_url ?? ''
}

function closeEdit() {
    editRow.value        = null
    editWebhookUrl.value = ''
}

async function saveWebhook() {
    saving.value = true
    try {
        const res = await http.post(`${baseUrl}/webhook-url-edit`, {
            id:  editRow.value.id,
            url: editWebhookUrl.value,
        })
        successHandler(res, COMPONENT)
        closeEdit()
        tableRef.value?.refresh()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}

async function copyToClipboard(id, text) {
    try {
        await navigator.clipboard.writeText(text)
    } catch {
        const el = document.createElement('textarea')
        el.value = text
        document.body.appendChild(el)
        el.select()
        document.execCommand('copy')
        document.body.removeChild(el)
    }
    copiedId.value = id
    setTimeout(() => { copiedId.value = null }, 2000)
}

async function remove(row) {
    if (!confirm(`Delete WhatsApp user ${row.phone_number || row.user_name}?`)) return
    try {
        const res = await http.post(`${baseUrl}/whatsapp-deregister`, { id: row.id })
        successHandler(res, COMPONENT)
        tableRef.value?.refresh()
    } catch (e) {
        errorHandler(e, COMPONENT)
    }
}

const columns = ['user_name', 'phone_number', 'waba_id', 'phone_number_id', 'business_id', 'created_at', 'action']

const options = {
    sortable: ['phone_number', 'waba_id', 'phone_number_id', 'business_id', 'created_at'],
    filterable: true,
    headings: {
        user_name:       'User',
        phone_number:    'Phone Number',
        waba_id:         'WABA ID',
        phone_number_id: 'Phone Number ID',
        business_id:     'Business ID',
        created_at:      'Created At',
        action:          'Action',
    },
    templates: {
        user_name: (f, row) => row.user_id
            ? h('a', { href: `${baseUrl}/clients/${row.user_id}` }, row.user_name)
            : row.user_name,

        phone_number_id: (f, row) => h('div', { class: 'd-flex align-items-center gap-2' }, [
            h('span', '****'),
            h('button', {
                class:   'btn btn-light table_btn',
                title:   copiedId.value === row.id ? 'Copied!' : 'Copy Phone Number ID',
                onClick: () => copyToClipboard(row.id, row.phone_number_id),
            }, h('i', { class: copiedId.value === row.id ? 'fas fa-check text-success' : 'fas fa-copy' })),
        ]),

        action: (f, row) => h('div', { class: 'd-flex gap-1' }, [
            h('button', {
                class:   'btn btn-light table_btn',
                title:   'Edit Webhook URL',
                onClick: () => openEdit(row),
            }, h('i', { class: 'fas fa-edit' })),
            h('button', {
                class:   'btn btn-light table_btn',
                title:   'Delete',
                onClick: () => remove(row),
            }, h('i', { class: 'fas fa-trash' })),
        ]),
    },
}
</script>
