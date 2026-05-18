<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">Email Templates</h4>
            </div>

            <div class="card-body">
                <DataTable
                    :url="apiUrl"
                    :dataColumns="columns"
                    :option="tableOptions"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { h, reactive } from 'vue'
import { useRouter } from 'vue-router'

const COMPONENT = 'email-templates'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl  = `${baseUrl}/template/list`
const router  = useRouter()

const columns = ['name', 'type', 'action']

const tableOptions = reactive({
    headings: {
        name:   'Name',
        type:   'Type',
        action: 'Action',
    },
    templates: {
        action: (_, row) => h('button', {
            class:   'btn btn-light table_btn',
            title:   'Edit',
            onClick: () => router.push(`/settings/email/templates/${row.id}/edit`),
        }, h('i', { class: 'fas fa-edit' })),
    },
    sortable:   ['name'],
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
    orderBy: { column: 'name', ascending: true },
})
</script>
