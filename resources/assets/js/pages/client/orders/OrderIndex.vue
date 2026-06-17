<template>
    <div>
        <AppCard :title="__('message.my_orders')">
            <DataTable :url="apiUrl" :dataColumns="columns" :option="tableOptions">
                <template #number="{ row }">
                    <RouterLink :to="'/my-order/' + row.id" class="fw-semibold">{{ row.number || '—' }}</RouterLink>
                </template>
                <template #order_date="{ row }">{{ formatDate(row.order_date) }}</template>
                <template #update_ends_at="{ row }">{{ formatDate(row.update_ends_at) }}</template>
                <template #action="{ row }">
                    <div class="d-flex align-items-center gap-1 flex-nowrap">
                        <action-button action="view" :to="'/my-order/' + row.id"
                                       v-tooltip="__('message.view')" />

                        <action-button v-if="!row.is_terminated"
                                       icon="fas fa-sync-alt" class="table_btn"
                                       v-tooltip="__('message.click_renew')"
                                       @click="openRenewModal(row)" />

                        <action-button v-if="row.show_download && !row.is_terminated"
                                       icon="fas fa-download" class="table_btn"
                                       v-tooltip="__('message.click_to_download')"
                                       @click="openDownloadModal(row)" />

                        <action-button v-if="row.show_cloud_delete && !row.is_terminated"
                                       icon="fas fa-trash" class="table_btn"
                                       v-tooltip="__('message.click_cloud')"
                                       @click="openDeleteModal(row)" />
                    </div>
                </template>
            </DataTable>
        </AppCard>

        <!-- Download Versions Modal -->
        <AppModal
            :showModal="showDownloadModal"
            :onClose="closeDownloadModal"
            classname="modal-xl"
            :showCloseBtn="false"
            :showControls="false"
        >
            <template #title>
                <h5>{{ __('message.product_version') }}</h5>
            </template>
            <template #fields>
                <Alert componentName="order-download" />
                <DataTable v-if="downloadVersionsUrl"
                           :key="downloadVersionsUrl"
                           :url="downloadVersionsUrl"
                           :dataColumns="versionColumns"
                           :option="versionOptions">
                    <template #version="{ row }"><span v-html="row.version" /></template> <!-- nosemgrep: javascript.vue.security.audit.xss.templates.avoid-v-html.avoid-v-html -->
                    <template #name="{ row }"><span v-html="row.name" /></template> <!-- nosemgrep: javascript.vue.security.audit.xss.templates.avoid-v-html.avoid-v-html -->
                    <template #description="{ row }"><span v-html="row.description" /></template> <!-- nosemgrep: javascript.vue.security.audit.xss.templates.avoid-v-html.avoid-v-html -->
                    <template #action="{ row }">
                        <button v-if="row.can_download && row.download_url"
                                class="btn btn-sm btn-primary"
                                @click="downloadFile(row.download_url)">
                            <i class="fas fa-download me-1"></i>{{ __('message.download') }}
                        </button>
                        <button v-else class="btn btn-sm btn-danger disabled">
                            {{ __('message.please_renew') }}
                        </button>
                    </template>
                </DataTable>
            </template>
        </AppModal>

        <!-- Renew Modal (shared with the order view page) -->
        <RenewModal v-model:show="showRenewModal" :order="renewRow" />

        <!-- Delete Cloud Confirmation Modal -->
        <AppModal
            :showModal="showDeleteModal"
            :onClose="closeDeleteModal"
            :showCloseBtn="false"
        >
            <template #title>
                <h5>{{ __('message.delete_confirm') }}</h5>
            </template>
            <template #fields>
                <p class="mb-0">{{ __('message.delete_cloud') }}</p>
            </template>
            <template #controls>
                <action-button action="delete" @click="confirmDelete" />
            </template>
        </AppModal>
    </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { __ } from '@/plugins/i18n'
import RenewModal from './components/RenewModal.vue'
import Alert from '@/components/Reusable/Alert.vue'
import { useDateTime } from '@/core/composables/useDateTime'
import { useDownload } from '@/core/composables/useDownload'

const { formatDate }  = useDateTime()
const { downloadFile } = useDownload('order-download')

const el      = document.getElementById('app-client')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl  = `${baseUrl}/get-my-orders`

const columns = ['product_name', 'order_date', 'number', 'agents', 'update_ends_at', 'action']

const tableOptions = reactive({
    headings: {
        product_name:   () => __('message.product_name'),
        order_date:     () => __('message.purchase_date'),
        number:         () => __('message.order_no'),
        agents:         () => __('message.agents'),
        update_ends_at: () => __('message.expiry_date'),
        action:         () => __('message.actions'),
    },
    sortable:   ['number', 'order_date', 'update_ends_at'],
    filterable: true,
})


// ─── Download Modal ──────────────────────────────────────────
const showDownloadModal   = ref(false)
const downloadRow         = ref(null)
const downloadVersionsUrl = computed(() => downloadRow.value ? `${baseUrl}/get-versions/${downloadRow.value.id}` : null)

const versionColumns = ['version', 'name', 'description', 'action']
const versionOptions = reactive({
    headings: {
        version:     () => __('message.version'),
        name:        () => __('message.title'),
        description: () => __('message.description'),
        action:      () => __('message.file'),
    },
    sortable:   ['version', 'name'],
    filterable: true,
})

function openDownloadModal(row) {
    downloadRow.value       = row
    showDownloadModal.value = true
}

function closeDownloadModal() {
    showDownloadModal.value = false
    downloadRow.value       = null
}

// ─── Renew Modal ─────────────────────────────────────────────
const showRenewModal = ref(false)
const renewRow       = ref(null)

function openRenewModal(row) {
    renewRow.value       = row
    showRenewModal.value = true
}

// ─── Delete Cloud Modal ──────────────────────────────────────
const showDeleteModal = ref(false)
const deleteRow       = ref(null)

function openDeleteModal(row) {
    deleteRow.value      = row
    showDeleteModal.value = true
}

function closeDeleteModal() {
    showDeleteModal.value = false
}

function confirmDelete() {
    if (deleteRow.value?.number) {
        window.location.href = `${baseUrl}/delete/domain/${deleteRow.value.number}/1`
    }
}
</script>
