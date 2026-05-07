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
    </div>
</template>

<script setup>
import { h, ref } from 'vue'
import DataTable from '@/themes/adminlte/components/common/DataTable.vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'whatsapp-users'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl = `${baseUrl}/whatsapp-users-api`
const tableRef = ref(null)

const columns = ['user_name', 'phone_number', 'waba_id', 'phone_number_id', 'business_id', 'created_at', 'action']

const options = {
    sortable: ['phone_number', 'waba_id', 'phone_number_id', 'business_id', 'created_at'],
    filterable: true,
    headings: {
        user_name: 'User',
        phone_number: 'Phone Number',
        waba_id: 'WABA ID',
        phone_number_id: 'Phone Number ID',
        business_id: 'Business ID',
        created_at: 'Created At',
        action: 'Action',
    },
    templates: {
        user_name: (hFn, row) => row.user_id
            ? h('a', { href: `${baseUrl}/clients/${row.user_id}` }, row.user_name)
            : row.user_name,
        phone_number_id: (hFn, row) => h('code', row.phone_number_id || '--'),
        action: (hFn, row) => h('button', {
            class: 'btn btn-light table_btn',
            title: 'Delete',
            onClick: () => remove(row),
        }, [h('i', { class: 'fas fa-trash' })]),
    },
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
</script>
