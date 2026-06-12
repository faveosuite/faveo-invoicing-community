<template>
    <div>
        <AppAlert :componentName="COMPONENT" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.activity_logs') }}</h4>
                <div class="card-tools">
                    <button class="btn btn-tool" v-tooltip="__('message.filters')" @click="showFilter = !showFilter">
                        <i class="fas fa-filter"></i>
                    </button>
                    <button class="btn btn-tool" v-tooltip="__('log.delete_logs')" @click="showDeleteModal = true">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>

            <div class="card-body">
                <ActivityFilter
                    :show="showFilter"
                    :baseUrl="baseUrl"
                    @apply="onFilterApply"
                    @reset="onFilterReset"
                    @close="showFilter = false"
                />
                <DataTable
                    ref="dtRef"
                    :url="apiUrl"
                    :dataColumns="columns"
                    :option="tableOptions"
                />
            </div>
        </div>

        <!-- Read More modal -->
        <AppModal
            :showModal="showReadMoreModal"
            :onClose="() => showReadMoreModal = false"
            classname="modal-lg"
            :containerStyle="{ maxWidth: '800px' }"
        >
            <template #title><h4>{{ __('message.detailed_log_info') }}</h4></template>
            <template #fields>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered mb-0">
                        <tbody>
                            <tr v-for="(item, i) in readMoreItems" :key="i">
                                <td class="detail-cell">{{ item }}</td>
                            </tr>
                            <tr v-if="!readMoreItems.length">
                                <td class="text-muted text-center py-3">{{ __('message.no_data_available') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </AppModal>

        <!-- Delete logs modal -->
        <AppModal :showModal="showDeleteModal" :onClose="closeDeleteModal" classname="modal-md">
            <template #title><h4>{{ __('log.delete_logs') }}</h4></template>
            <template #fields>
                <DatePicker
                    name="delete_date"
                    :label="__('log.delete_logs_entries')"
                    :value="deleteDate"
                    :clearable="false"
                    :onChange="(val) => deleteDate = val"
                />
                <div v-if="deleteError" class="text-danger small mt-1">{{ deleteError }}</div>
            </template>
            <template #controls>
                <action-button action="delete" :loading="deleting" :label="__('log.delete_logs')" @click="confirmDelete" />
            </template>
        </AppModal>
    </div>
</template>

<script setup>
import { h, reactive, ref } from 'vue'
import { DateTime } from 'luxon'
import { useDateTimeStore } from '@/core/stores/dateTimeStore'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import ActivityFilter from './ActivityFilter.vue'

const COMPONENT = 'activity-logs'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl  = `${baseUrl}/get-activity-api`

// ── filter state ──────────────────────────────────────────────────────────────
const dtRef        = ref(null)
const showFilter   = ref(false)
const activeFilters = ref({})

function onFilterApply(params) {
    activeFilters.value = params
    showFilter.value    = false
    dtRef.value?.refresh()
}

function onFilterReset() {
    activeFilters.value = {}
    dtRef.value?.refresh()
}

// ── DataTable ─────────────────────────────────────────────────────────────────
const columns = ['module', 'event', 'role', 'performed_by', 'description', 'created_at']

const tableOptions = reactive({
    headings: {
        module:       __('message.module'),
        event:        __('message.event'),
        description:  __('message.description'),
        performed_by: __('message.performed_by'),
        role:         __('message.role'),
        created_at:   __('message.created_at'),
    },
    columnsClasses: {
        module: 'dt-name',
        event: 'dt-name',
        role: 'dt-name',
        performed_by: 'dt-name',
        description: 'dt-text',
        created_at: 'dt-date',
    },
    sortable: ['module', 'event', 'created_at', 'role', 'performed_by'],
    filterable: true,
    requestAdapter(data) {
        const columnMap = { module: 'module', event: 'event' }
        return {
            'sort-field':   columnMap[data.orderBy] ?? data.orderBy ?? 'created_at',
            'sort-order':   data.ascending ? 'asc' : 'desc',
            'search-query': (data.query ?? '').trim(),
            page:  data.page,
            limit: data.limit,
            ...activeFilters.value,
        }
    },
    orderBy: { column: 'created_at', ascending: false },
    templates: {
        performed_by: (f, row) => {
            if (!row.performed_by_id) return row.performed_by || '—'
            return h('a', { href: `${baseUrl}/admin/clients/${row.performed_by_id}` }, row.performed_by)
        },
        description: (f, row) => {
            const details    = row.detailed_properties
            const hasDetails = Array.isArray(details) && details.length > 0
            if (!hasDetails) return h('span', { innerHTML: row.description || '—' })
            return h('div', [
                h('span', { innerHTML: row.description || '' }),
                h('br'),
                h('a', {
                    href: '#',
                    class: 'text-primary',
                    onClick: (e) => { e.preventDefault(); openReadMore(details) },
                }, __('message.read_more_caps')),
            ])
        },
    },
})

// ── Read More modal ───────────────────────────────────────────────────────────
const showReadMoreModal = ref(false)
const readMoreItems     = ref([])

function openReadMore(details) {
    readMoreItems.value     = Array.isArray(details) ? details : []
    showReadMoreModal.value = true
}

// ── Delete modal ──────────────────────────────────────────────────────────────
const showDeleteModal = ref(false)
const deleteDate      = ref(DateTime.now().setZone(useDateTimeStore().timezone).toFormat('yyyy-MM-dd'))
const deleteError     = ref('')
const deleting        = ref(false)

function closeDeleteModal() {
    showDeleteModal.value = false
    deleteError.value     = ''
}

async function confirmDelete() {
    deleteError.value = ''
    if (!deleteDate.value) {
        deleteError.value = __('message.please_select_date')
        return
    }
    deleting.value = true
    try {
        const res = await http.delete(`${baseUrl}/logs/delete`, {
            data: { to_date: deleteDate.value, log_types: ['systemLogs'] },
        })
        successHandler(res, COMPONENT)
        closeDeleteModal()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        deleting.value = false
    }
}
</script>

<style scoped>
.detail-cell {
    font-size: 13px;
    line-height: 1.4;
    word-break: break-word;
    white-space: normal;
    padding: 6px 8px;
    vertical-align: top;
}
</style>
