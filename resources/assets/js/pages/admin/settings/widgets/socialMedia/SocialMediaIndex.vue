<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.social_media') }}</h4>
                <div class="card-tools">
                    <RouterLink
                        to="/settings/widgets/social-media/create"
                        class="btn btn-tool"
                        v-tooltip="__('message.create')"
                    >
                        <i class="fas fa-plus fw-bold"></i>
                    </RouterLink>
                </div>
            </div>
            <div class="card-body">
                <DataTable
                    ref="dtRef"
                    :url="apiUrl"
                    :dataColumns="columns"
                    :option="tableOptions"
                >
                    <template #bulk-actions>
                        <BulkActionIcons v-if="selected.length > 0" :actions="[{ icon: 'fas fa-trash', label: __('message.Delete'), onClick: confirmBulkDelete }]" />
                    </template>
                </DataTable>
            </div>
        </div>

        <DeleteModal
            v-if="pendingDeleteRow"
            :showModal="true"
            :onClose="() => pendingDeleteRow = null"
            :deleteUrl="`${baseUrl}/social-media/delete`"
            :deleteData="pendingDeleteRow"
            :title="__('message.Delete')"
            :message="__('message.are_you_sure')"
            :componentName="COMPONENT"
            @deleted="() => { pendingDeleteRow = null; dtRef?.refresh() }"
        />

        <DeleteModal
            v-if="pendingBulkDelete"
            :showModal="true"
            :onClose="() => pendingBulkDelete = null"
            :deleteUrl="`${baseUrl}/social-media/delete`"
            :deleteData="pendingBulkDelete"
            :title="__('message.Delete')"
            :message="__('message.are_you_sure')"
            :componentName="COMPONENT"
            @deleted="() => { pendingBulkDelete = null; selected.value = []; dtRef?.refresh() }"
        />
    </div>
</template>

<script setup>
import { h, ref, reactive } from 'vue'
import { RouterLink } from 'vue-router'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'
import BulkActionIcons from '@/components/Reusable/BulkActionIcons.vue'
import { useTableSelection } from '@/core/composables/useTableSelection'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import { makeRequestAdapter } from '@/helpers/tableUtils'
import { SOCIAL_PLATFORMS, findPlatformByClass } from './platforms'

const COMPONENT = 'social-media-index'
const baseUrl = useBaseUrl()
const apiUrl  = `/social-media/list`

function resolveFaClass(faClass, className = '') {
    if (faClass) {
        let fa = faClass
        if (fa.startsWith('fa fa-')) fa = fa.replace(/^fa fa-/, 'fab fa-')
        else if (fa.startsWith('fa-')) fa = `fab ${fa}`
        if (fa === 'fab fa-twitter' && className === 'social-icons-x') return 'fab fa-x-twitter'
        return fa
    }
    const p = findPlatformByClass(className)
    return SOCIAL_PLATFORMS[p]?.fa_class || 'fas fa-icons'
}

function resolveColor(className = '', faClass = '') {
    const p = findPlatformByClass(className, faClass)
    return SOCIAL_PLATFORMS[p]?.color || '#6c757d'
}

const dtRef    = ref(null)
const { selected, allSelected, toggleRow, toggleAll } = useTableSelection(dtRef)
const pendingDeleteRow  = ref(null)
const pendingBulkDelete = ref(null)

function confirmDeleteRow(id) {
    pendingDeleteRow.value = { select: [id] }
}

function confirmBulkDelete() {
    if (!selected.value.length) return
    pendingBulkDelete.value = { select: [...selected.value] }
}

const columns = ['select', 'name', 'link', 'action']

const tableOptions = reactive({
    headings: {
        select: () => h('input', {
            type: 'checkbox',
            checked: allSelected.value,
            onChange: toggleAll,
        }),
        name:   __('message.name'),
        link:   __('message.link'),
        action: __('message.action'),
    },
    columnsClasses: {
        select: 'dt-select',
        name: 'dt-name',
        link: 'dt-text',
        action: 'dt-action',
    },
    templates: {
        select: (f, row) => h('input', {
            type: 'checkbox',
            checked: selected.value.includes(row.id),
            onChange: () => toggleRow(row.id),
        }),
        name:   (f, row) => {
            const icon = resolveFaClass(row.fa_class, row.class)
            const color = resolveColor(row.class, row.fa_class)
            return h('div', { class: 'd-flex align-items-center gap-2' }, [
                h('span', {
                    class: 'd-inline-flex align-items-center justify-content-center rounded-circle border flex-shrink-0',
                    style: { width: '28px', height: '28px', backgroundColor: '#f8f9fa' },
                }, [
                    h('i', { class: icon, style: { fontSize: '0.85rem', color } }),
                ]),
                h('span', { class: 'fw-semibold' }, row.name || '—'),
            ])
        },
        link:   (f, row) => row.link || '—',
        action: (f, row) => h('div', { class: 'd-flex gap-1' }, [
            h(RouterLink, {
                to:    `/settings/widgets/social-media/${row.id}/edit`,
                class: 'btn btn-light table_btn',
                title: __('message.edit'),
            }, () => h('i', { class: 'fas fa-edit' })),
            h('button', {
                class:   'btn btn-light table_btn',
                title:   __('message.Delete'),
                onClick: () => confirmDeleteRow(row.id),
            }, h('i', { class: 'fas fa-trash' })),
        ]),
    },
    sortable:   ['name', 'link'],
    filterable: true,
    requestAdapter: makeRequestAdapter('created_at'),
    orderBy: { column: 'created_at', ascending: false },
})
</script>
