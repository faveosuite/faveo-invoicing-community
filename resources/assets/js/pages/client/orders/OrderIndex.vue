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
        >
            <template #title>
                <h5>{{ __('message.product_version') }}</h5>
            </template>
            <template #fields>
                <div v-if="downloadLoading" class="text-center py-4">
                    <inline-loader />
                </div>
                <div v-else-if="downloadError" class="alert alert-danger mb-0">{{ downloadError }}</div>
                <div v-else class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('message.version') }}</th>
                                <th>{{ __('message.title') }}</th>
                                <th>{{ __('message.description') }}</th>
                                <th>{{ __('message.file') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(v, i) in downloadVersions" :key="i">
                                <td v-html="v.version"></td>
                                <td v-html="v.title"></td>
                                <td v-html="v.description"></td>
                                <td v-html="v.file"></td>
                            </tr>
                            <tr v-if="!downloadVersions.length">
                                <td colspan="4" class="text-center text-muted py-3">{{ __('message.empty_table') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
            <template #controls>
                <action-button action="close" @click="closeDownloadModal" />
            </template>
        </AppModal>

        <!-- Renew Modal -->
        <AppModal
            :showModal="showRenewModal"
            :onClose="closeRenewModal"
            :showCloseBtn="false"
        >
            <template #title>
                <h5>{{ __('message.renew_your_order') }}</h5>
            </template>
            <template #fields>
                <div v-if="renewLoading" class="text-center py-4">
                    <inline-loader />
                </div>
                <template v-else>
                    <ClientField type="select" name="plan"
                                 :label="__('message.plans')"
                                 v-model="selectedPlan"
                                 @change="fetchRenewCost"
                                 :required="true">
                        <option value="">{{ __('message.Select') }}</option>
                        <option v-for="plan in renewPlans" :key="plan.id" :value="plan.id">
                            {{ plan.name }}
                        </option>
                    </ClientField>

                    <ClientField v-if="renewIsCloud"
                                 type="number" name="agents"
                                 :label="__('message.agents')"
                                 v-model="renewAgents"
                                 :required="true"
                                 placeholder="1" />

                    <p class="mb-0 mt-2">
                        <strong>{{ __('message.price_to_be_paid') }}</strong>
                        {{ renewPrice || '—' }}
                    </p>
                </template>
            </template>
            <template #controls>
                <action-button action="cancel" @click="closeRenewModal" />
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
                <action-button action="cancel" @click="closeDeleteModal" />
                <action-button action="delete" @click="confirmDelete" />
            </template>
        </AppModal>
    </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { __ } from '@/plugins/i18n'
import http from '@/plugins/axios'
import { errorHandler } from '@/helpers/responseHandler.js'

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
const showDownloadModal = ref(false)
const downloadLoading   = ref(false)
const downloadError     = ref('')
const downloadVersions  = ref([])
const downloadRow       = ref(null)

function openDownloadModal(row) {
    downloadRow.value      = row
    downloadVersions.value = []
    downloadError.value    = ''
    downloadLoading.value  = true
    showDownloadModal.value = true

    http.get(`${baseUrl}/get-versions/${row.product_id}/${row.client_id}/${row.invoice_number}`, {
        params: { draw: 1, start: 0, length: 100 },
    })
        .then(res  => { downloadVersions.value = res.data?.data ?? [] })
        .catch(err => { downloadError.value = err.response?.data?.message ?? __('message.something_went_wrong') })
        .finally(() => { downloadLoading.value = false })
}

function closeDownloadModal() {
    showDownloadModal.value = false
}

// ─── Renew Modal ─────────────────────────────────────────────
const showRenewModal  = ref(false)
const renewLoading    = ref(false)
const renewSubmitting = ref(false)
const renewRow        = ref(null)
const renewPlans      = ref([])
const renewIsCloud    = ref(false)
const selectedPlan    = ref('')
const renewAgents     = ref('')
const renewPrice      = ref('')

async function openRenewModal(row) {
    renewRow.value      = row
    renewPlans.value    = []
    selectedPlan.value  = ''
    renewAgents.value   = String(row.agents ?? '')
    renewPrice.value    = ''
    renewLoading.value  = true
    showRenewModal.value = true

    try {
        const res = await http.get(`${baseUrl}/renew-popup-details/${row.product_id}`)
        renewPlans.value   = res.data?.data?.plans   ?? []
        renewIsCloud.value = res.data?.data?.is_cloud ?? false
    } catch { /* silent */ }
    finally { renewLoading.value = false }
}

function closeRenewModal() {
    showRenewModal.value = false
}

watch(renewAgents, fetchRenewCost)

async function fetchRenewCost() {
    if (!selectedPlan.value) { renewPrice.value = ''; return }
    try {
        const params = { user: renewRow.value?.client_id, plan: selectedPlan.value }
        if (renewIsCloud.value && renewAgents.value) params.agents = renewAgents.value
        const res = await http.get(`${baseUrl}/get-renew-cost`, { params })
        renewPrice.value = res.data?.formatted_price ?? ''
    } catch { /* silent */ }
}

async function submitRenew() {
    if (!selectedPlan.value || renewSubmitting.value) return
    renewSubmitting.value = true
    try {
        const payload = { plan: selectedPlan.value, user: renewRow.value?.client_id }
        if (renewIsCloud.value && renewAgents.value) payload.agents = renewAgents.value

        const res = await http.post(`${baseUrl}/client/renew/${renewRow.value?.sub_id}`, payload)
        const redirectUrl = res.data?.data?.[0]
        if (redirectUrl) window.location.href = redirectUrl
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
