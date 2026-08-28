<template>
    <div>
        <AppAlert :componentName="COMPONENT" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.email_validation_logs') }}</h4>
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

        <!-- Result detail modal -->
        <AppModal
            :showModal="showDetailModal"
            :onClose="() => showDetailModal = false"
            classname="modal-lg"
            :containerStyle="{ maxWidth: '800px' }"
            modalBodyClass="scrollable-body"
        >
            <template #title><h4>{{ __('message.email_validation_result') }}</h4></template>
            <template #fields>
                <div v-if="loadingDetail" class="row justify-content-center py-3"><loader /></div>
                <div v-else class="table-responsive">
                    <table class="table table-striped table-bordered mb-0">
                        <thead class="visually-hidden"><tr><th>Field</th><th>Value</th></tr></thead>
                        <tbody>
                            <tr v-for="(val, key) in detailData" :key="key">
                                <td class="fw-semibold text-capitalize col-key-width">{{ key }}</td>
                                <td>{{ val }}</td>
                            </tr>
                            <tr v-if="!Object.keys(detailData).length">
                                <td class="text-muted text-center py-3">{{ __('message.no_data_available') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </AppModal>
    </div>
</template>

<script setup>
import { h, reactive, ref } from 'vue'
import http from '@/plugins/axios'
import { errorHandler } from '@/helpers/responseHandler.js'
import { useDateTime } from '@/core/composables/useDateTime'
import { makeRequestAdapter } from '@/helpers/tableUtils'

const { formatDateTime } = useDateTime()

const COMPONENT = 'email-validation-logs'
const apiUrl  = `/settings/email-validation-logs`

const dtRef           = ref(null)
const showDetailModal = ref(false)
const loadingDetail   = ref(false)
const detailData      = ref({})

async function openDetail(id) {
    showDetailModal.value = true
    loadingDetail.value   = true
    detailData.value      = {}
    try {
        const res    = await http.get(`/get-email-validation-results`, { params: { id } })
        detailData.value = res.data?.data ?? {}
    } catch (e) {
        errorHandler(e, COMPONENT)
        showDetailModal.value = false
    } finally {
        loadingDetail.value = false
    }
}

const columns = ['email', 'method', 'status', 'registration', 'created_at', 'action']

const tableOptions = reactive({
    headings: {
        email:        __('message.email'),
        method:       __('message.method'),
        status:       __('message.status'),
        registration: __('message.registration'),
        created_at: __('message.created_at'),
        action:     __('message.action'),
    },
    columnsClasses: {
        email:        'dt-name',
        method:       'dt-name',
        status:       'dt-name',
        registration: 'dt-name',
        created_at:   'dt-date',
        action:       'dt-action',
    },
    templates: {
        status: (f, row) => {
            const s = row.status?.toLowerCase()
            const danger  = ['invalid', 'disposable', 'disabled', 'spamtrap']
            const warning = ['catch all', 'unknown', 'inbox full', 'role account']
            const cls = s === 'safe' || s === 'valid' ? 'bg-success'
                : danger.includes(s) ? 'bg-danger'
                : warning.includes(s) ? 'bg-warning'
                : 'bg-secondary'
            return h('span', { class: `badge ${cls}` }, row.status || '—')
        },
        created_at: (f, row) => formatDateTime(row.created_at),
        action: (f, row) => h('button', {
            class: 'btn btn-light table_btn',
            title: __('message.click_here_view'),
            onClick: () => openDetail(row.id),
        }, h('i', { class: 'fas fa-eye' })),
    },
    sortable:   ['email', 'method', 'status', 'registration', 'created_at'],
    filterable: true,
    requestAdapter: makeRequestAdapter('created_at'),
    orderBy: { column: 'created_at', ascending: false },
})
</script>

<style scoped>
.col-key-width { width: 40%; }
:deep(.scrollable-body) { max-height: 60vh; overflow-y: auto; }
</style>
