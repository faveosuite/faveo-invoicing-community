<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.localized_license') }}</h4>
            </div>
            <div class="card-body">
                <DataTable
                    ref="dtRef"
                    :url="`${baseUrl}/localized-license/files`"
                    :dataColumns="columns"
                    :option="tableOptions"
                />
            </div>
        </div>

        <DeleteModal
            v-if="deleteFileName !== null"
            :showModal="deleteFileName !== null"
            :onClose="closeDelete"
            :deleteUrl="`${baseUrl}/localized-license/files`"
            :deleteData="{ file_name: deleteFileName }"
            :componentName="COMPONENT"
            @deleted="onDeleted"
        />
    </div>
</template>

<script setup>
import { h, ref, reactive } from 'vue'
import DeleteModal from '@/themes/adminlte/components/common/DeleteModal.vue'

const COMPONENT = 'localized-license'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const dtRef          = ref(null)
const deleteFileName = ref(null)

function openDelete(fileName) { deleteFileName.value = fileName }
function closeDelete()        { deleteFileName.value = null }
function onDeleted()          { closeDelete(); dtRef.value?.refresh() }

const columns = ['file_name', 'order_number', 'action']

const tableOptions = reactive({
    headings: {
        file_name:    __('message.license_file_name'),
        order_number: __('message.order_no'),
        action:       __('message.action'),
    },
    templates: {
        file_name:    (f, row) => h('code', {}, row.file_name    || '—'),
        order_number: (f, row) => row.order_number               || '—',
        action:       (f, row) => h('div', { class: 'd-flex gap-1' }, [
            h('a', { class: 'btn btn-sm btn-secondary', href: row.download_url },
                [h('i', { class: 'fas fa-download me-1' }), __('message.download_license_file')]),
            h('a', { class: 'btn btn-sm btn-secondary', href: row.private_key_url },
                [h('i', { class: 'fas fa-key me-1' }), __('message.download_license_key')]),
            h('button', {
                class:   'btn btn-light table_btn',
                title:   __('message.Delete'),
                onClick: () => openDelete(row.file_name),
            }, [h('i', { class: 'fas fa-trash' })]),
        ]),
    },
    sortable:   ['file_name', 'order_number'],
    filterable: true,
    requestAdapter(data) {
        return {
            'sort-field':   data.orderBy  ?? 'file_name',
            'sort-order':   data.ascending ? 'asc' : 'desc',
            'search-query': (data.query   ?? '').trim(),
            page:           data.page,
            limit:          data.limit,
        }
    },
    orderBy: { column: 'file_name', ascending: true },
})
</script>
