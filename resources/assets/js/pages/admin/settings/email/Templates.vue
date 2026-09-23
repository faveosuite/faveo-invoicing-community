<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.email_templates') }}</h4>
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
import { makeRequestAdapter } from '@/helpers/tableUtils'

const COMPONENT = 'email-templates'
const apiUrl  = `/template/list`
const router  = useRouter()

const columns = ['name', 'type', 'action']

const tableOptions = reactive({
    headings: {
        name:   __('message.name'),
        type:   __('message.type'),
        action: __('message.action'),
    },
    columnsClasses: {
        name: 'dt-name',
        type: 'dt-code',
        action: 'dt-action',
    },
    templates: {
        action: (_, row) => h('button', {
            class:   'btn btn-light table_btn',
            title:   __('message.edit'),
            onClick: () => router.push(`/settings/email/templates/${row.id}/edit`),
        }, h('i', { class: 'fas fa-edit' })),
    },
    sortable:   ['name'],
    filterable: true,
    requestAdapter: makeRequestAdapter('name'),
    orderBy: { column: 'name', ascending: true },
})
</script>
