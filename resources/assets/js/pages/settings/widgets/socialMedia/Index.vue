<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0">Social Media</h4>
                <RouterLink to="/settings/widgets/social-media/create" class="btn btn-sm btn-success">
                    <i class="fas fa-plus"></i> Add
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

const COMPONENT = 'social-media-index'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl = `${baseUrl}/social-media/list`

const dtRef = ref(null)

async function deleteMedia(id) {
    try {
        const res = await http.delete(`${baseUrl}/social-media/delete`, { data: { id } })
        successHandler(res, COMPONENT)
        dtRef.value?.refresh()
    } catch (e) { errorHandler(e, COMPONENT) }
}

const columns = ['name', 'link', 'action']

const tableOptions = reactive({
    headings: {
        name:   'Name',
        link:   'Link',
        action: 'Action',
    },
    templates: {
        name:   (f, row) => row.name || '—',
        link:   (f, row) => row.link || '—',
        action: (f, row) => h('div', { class: 'd-flex gap-1' }, [
            h(RouterLink, {
                to: `/settings/widgets/social-media/${row.id}/edit`,
                class: 'btn btn-light table_btn',
                title: 'Edit',
            }, () => h('i', { class: 'fas fa-pen' })),
            h('button', {
                class: 'btn btn-light table_btn',
                title: 'Delete',
                onClick: () => deleteMedia(row.id),
            }, h('i', { class: 'fas fa-trash' })),
        ]),
    },
    sortable: ['name'],
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
    orderBy: { column: 'created_at', ascending: false },
})
</script>
