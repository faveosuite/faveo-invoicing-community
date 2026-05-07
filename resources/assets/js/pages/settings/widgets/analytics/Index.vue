<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0">Analytics Widgets</h4>
                <RouterLink to="/settings/widgets/analytics/create" class="btn btn-sm btn-success">
                    <i class="fas fa-plus"></i> Add Widget
                </RouterLink>
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
import { RouterLink } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'analytics-widgets'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl = `${baseUrl}/widgets/list`

const dtRef = ref(null)

async function deleteWidget(id) {
    try {
        const res = await http.delete(`${baseUrl}/widgets/delete`, { data: { id } })
        successHandler(res, COMPONENT)
        dtRef.value?.refresh()
    } catch (e) { errorHandler(e, COMPONENT) }
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
        action: (f, row) => h('div', { class: 'd-flex gap-1' }, [
            h(RouterLink, {
                to: `/settings/widgets/analytics/${row.id}/edit`,
                class: 'btn btn-light table_btn',
                title: 'Edit',
            }, () => h('i', { class: 'fas fa-pen' })),
            h('button', {
                class: 'btn btn-light table_btn',
                title: 'Delete',
                onClick: () => deleteWidget(row.id),
            }, h('i', { class: 'fas fa-trash' })),
        ]),
    },
    sortable: ['name', 'type'],
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
        const res = data?.data?.pages
        return {
            data: res?.data ?? [],
            count: data?.data?.total ?? res?.total ?? 0,
        }
    },
    orderBy: { column: 'created_at', ascending: false },
})
</script>
