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
                                       v-tooltip :title="__('message.view')" />

                        <action-button v-if="!row.is_terminated"
                                       icon="fas fa-sync-alt" class="table_btn"
                                       v-tooltip :title="__('message.click_renew')"
                                       @click="openRenewModal(row)" />

                        <action-button v-if="row.show_download && !row.is_terminated"
                                       icon="fas fa-download" class="table_btn"
                                       v-tooltip :title="__('message.click_to_download')"
                                       @click="openDownloadModal(row)" />

                        <action-button v-if="row.show_cloud_delete && !row.is_terminated"
                                       icon="fas fa-trash" class="table_btn"
                                       v-tooltip :title="__('message.click_cloud')"
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
                <DataTable v-if="downloadVersionsUrl"
                           :key="downloadVersionsUrl"
                           :url="downloadVersionsUrl"
                           :dataColumns="versionColumns"
                           :option="versionOptions">
                    <template #version="{ row }"><span v-html="row.version" /></template>
                    <template #name="{ row }"><span v-html="row.name" /></template>
                    <template #description="{ row }"><span v-html="row.description" /></template>
                    <template #action="{ row }">
                        <a v-if="row.can_download && row.download_url"
                           :href="row.download_url"
                           class="btn btn-sm btn-primary">
                            <i class="fas fa-download me-1"></i>{{ __('message.download') }}
                        </a>
                        <button v-else class="btn btn-sm btn-danger disabled">
                            {{ __('message.please_renew') }}
                        </button>
                    </template>
                </DataTable>
            </template>
        </AppModal>

        <!-- Renew Modal -->
        <AppModal
            :showModal="showRenewModal"
            :onClose="closeRenewModal"
            :showCloseBtn="false"
        >
            <template #title>
                <h5 class="modal-title fw-bold">{{ __('message.renew_your_order') }}</h5>
            </template>
            <template #fields>
                <div v-if="renewLoading" class="text-center py-4">
                    <inline-loader />
                </div>
                <template v-else>
                    <!-- Current order info -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">{{ __('message.current_plan') }}</span>
                        <span class="fw-bold text-dark">{{ renewRow?.current_plan || '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                        <span class="text-muted">{{ __('message.agents') }}</span>
                        <span class="fw-bold text-dark">{{ renewRow?.agents || '—' }}</span>
                    </div>

                    <SelectField name="plan"
                                 :label="__('message.plans')"
                                 :elements="renewPlans"
                                 :value="selectedPlan"
                                 :onChange="onPlanChange"
                                 :required="true" />

                    <!-- Price summary -->
                    <div v-if="renewPrice" class="d-flex justify-content-between align-items-center border-top pt-3 mt-1">
                        <span class="text-muted">{{ __('message.price_to_be_paid') }}</span>
                        <span class="fw-bold text-dark fs-6">{{ renewPrice }}</span>
                    </div>
                </template>
            </template>
            <template #controls>
                <action-button
                    action="confirm"
                    :label="__('message.renew')"
                    :loading="renewSubmitting"
                    :disabled="!selectedPlan"
                    @click="submitRenew"
                />
            </template>
        </AppModal>

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
import { RouterLink, useRouter } from 'vue-router'
import { __ } from '@/plugins/i18n'
import http from '@/plugins/axios'
import { errorHandler } from '@/helpers/responseHandler.js'
import SelectField from '@/themes/porto/components/forms/SelectField.vue'

const router  = useRouter()
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

function formatDate(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
}

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
const showRenewModal  = ref(false)
const renewLoading    = ref(false)
const renewSubmitting = ref(false)
const renewRow        = ref(null)
const renewPlans      = ref([])
const selectedPlan    = ref(null)
const renewPrice      = ref('')

async function openRenewModal(row) {
    renewRow.value       = row
    renewPlans.value     = []
    selectedPlan.value   = null
    renewPrice.value     = ''
    renewLoading.value   = true
    showRenewModal.value = true

    try {
        const res = await http.get(`${baseUrl}/renew-popup-details/${row.product_id}`)
        renewPlans.value = res.data?.data?.plans ?? []
        if (renewPlans.value.length) await onPlanChange(renewPlans.value[0])
    } catch { /* silent */ }
    finally { renewLoading.value = false }
}

function closeRenewModal() {
    showRenewModal.value = false
}

async function onPlanChange(plan) {
    selectedPlan.value = plan
    await fetchRenewCost()
}

async function fetchRenewCost() {
    if (!selectedPlan.value) { renewPrice.value = ''; return }
    try {
        const res = await http.get(`${baseUrl}/get-renew-cost`, {
            params: { plan: selectedPlan.value.id, order: renewRow.value?.id },
        })
        renewPrice.value = res.data?.data?.formatted_price ?? ''
    } catch { /* silent */ }
}

async function submitRenew() {
    if (!selectedPlan.value || renewSubmitting.value) return
    renewSubmitting.value = true
    try {
        const res = await http.post(`${baseUrl}/client/renew/${renewRow.value?.sub_id}`, {
            plan: selectedPlan.value.id,
            user: renewRow.value?.client_id,
        })
        const invoiceId = res.data?.data?.invoice_id
        if (invoiceId) router.push({ path: '/checkout', query: { invoice: invoiceId } })
        else closeRenewModal()
    } catch (e) {
        errorHandler(e, 'client-page')
    } finally {
        renewSubmitting.value = false
    }
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
