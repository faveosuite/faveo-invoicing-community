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
                <h4>{{ __('message.edit_webhook_url') }}</h4>
            </template>

            <template #alert>
                <AppAlert :componentName="COMPONENT" />
            </template>

            <template #fields>
                <TextField
                    name="editWebhookUrl"
                    :label="__('message.webhook_url')"
                    :value="editWebhookUrl"
                    :onChange="(val) => editWebhookUrl = val"
                    :placehold="__('message.enter_webhook_url')"
                />
            </template>

            <template #controls>
                <action-button action="save" :loading="saving" @click="saveWebhook" />
            </template>
        </AppModal>
    </div>
</template>

<script setup>
import { h, ref } from 'vue'
import TextField from '@/components/Reusable/FormField/TextField.vue'
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
    if (!confirm(`${__('message.delete_whatsapp_user_confirm')} ${row.phone_number || row.user_name}?`)) return
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
    headings: {
        user_name:       __('message.user'),
        phone_number:    __('message.phone_number'),
        waba_id:         __('message.waba_id'),
        phone_number_id: __('message.phone_number_id'),
        business_id:     __('message.business_id'),
        created_at:      __('message.created_at'),
        action:          __('message.action'),
    },
    columnsClasses: {
        user_name: 'dt-name',
        phone_number: 'dt-mobile',
        waba_id: 'dt-code',
        phone_number_id: 'dt-code',
        business_id: 'dt-code',
        created_at: 'dt-date',
        action: 'dt-action',
    },
    templates: {
        user_name: (f, row) => row.user_id
            ? h('a', { href: `${baseUrl}/clients/${row.user_id}` }, row.user_name)
            : row.user_name,

        phone_number_id: (f, row) => h('div', { class: 'd-flex align-items-center gap-2' }, [
            h('span', '****'),
            h('button', {
                class:   'btn btn-light table_btn',
                title:   copiedId.value === row.id ? __('message.copied') : __('message.copy_phone_number_id'),
                onClick: () => copyToClipboard(row.id, row.phone_number_id),
            }, h('i', { class: copiedId.value === row.id ? 'fas fa-check text-success' : 'fas fa-copy' })),
        ]),

        action: (f, row) => h('div', { class: 'd-flex gap-1' }, [
            h('button', {
                class:   'btn btn-light table_btn',
                title:   __('message.edit_webhook_url'),
                onClick: () => openEdit(row),
            }, h('i', { class: 'fas fa-edit' })),
            h('button', {
                class:   'btn btn-light table_btn',
                title:   __('message.Delete'),
                onClick: () => remove(row),
            }, h('i', { class: 'fas fa-trash' })),
        ]),
    },
}
</script>
