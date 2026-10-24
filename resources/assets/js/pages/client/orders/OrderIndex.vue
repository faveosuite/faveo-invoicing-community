<template>
    <div>
        <!-- Outside the delete modal, so the result survives the modal closing. -->
        <Alert componentName="client-orders" />
        <AppCard :title="__('message.my_orders')">
            <div v-if="renewalOnly" class="mb-2">
                <RouterLink to="/my-orders">{{ __('message.view_all') }}</RouterLink>
            </div>
            <DataTable :key="apiUrl" :url="apiUrl" :dataColumns="columns" :option="tableOptions">
                <template #number="{ row }">
                    <div class="d-flex flex-column align-items-start">
                        <RouterLink :to="'/my-order/' + row.id" class="fw-semibold">{{ row.number || '—' }}</RouterLink>
                        <span v-if="row.is_terminated" class="badge bg-danger mt-1 w-auto">{{ __('message.terminated') }}</span>
                        <!-- Otherwise Renew just vanishes with no explanation. -->
                        <span v-else-if="row.is_cloud_deleted" class="badge bg-secondary mt-1 w-auto">{{ __('message.instance_deleted') }}</span>
                    </div>
                </template>
                <template #order_date="{ row }">{{ formatDate(row.order_date) }}</template>
                <!-- 0/null agents is the "unlimited" sentinel, same as the invoice views. -->
                <template #agents="{ row }">{{ row.agents || __('message.unlimited') }}</template>
                <template #license_ends_at="{ row }">
                    <div class="d-flex flex-column align-items-start">
                        <span :class="row.expiry_status ? 'text-danger' : ''">{{ formatDate(row.license_ends_at) }}</span>
                        <span v-if="row.expiry_status" class="badge bg-danger mt-1 w-auto">{{ row.expiry_status }}</span>
                    </div>
                </template>
                <template #action="{ row }">
                    <div class="d-flex align-items-center gap-1 flex-nowrap">
                        <action-button action="view" :to="'/my-order/' + row.id"
                                       :label="__('message.view')" :icon-only="true"
                                       v-tooltip="__('message.view')" />

                        <action-button v-if="row.can_renew"
                                       icon="fas fa-sync-alt" class="table_btn"
                                       v-tooltip="__('message.click_renew')"
                                       :label="__('message.click_renew')" :icon-only="true"
                                       @click="openRenewModal(row)" />

                        <action-button v-if="row.can_download"
                                       icon="fas fa-download" class="table_btn"
                                       v-tooltip="__('message.click_to_download')"
                                       :label="__('message.click_to_download')" :icon-only="true"
                                       @click="openDownloadModal(row)" />

                        <action-button v-if="row.can_delete_cloud"
                                       icon="fas fa-trash" class="table_btn"
                                       v-tooltip="__('message.click_cloud')"
                                       :label="__('message.click_cloud')" :icon-only="true"
                                       @click="openDeleteModal(row)" />
                    </div>
                </template>
            </DataTable>
        </AppCard>

        <!-- Download Versions Modal: latest release up front, older ones on request. Bootstrap/Porto only. -->
        <AppModal
            :showModal="showDownloadModal"
            :onClose="closeDownloadModal"
            classname="modal-lg modal-dialog-scrollable"
            :showCloseBtn="false"
            :showControls="false"
        >
            <template #title>
                <h5 class="modal-title fw-semibold">{{ __('message.download_product', { product: downloadRow?.product_name ?? '' }) }}</h5>
            </template>
            <template #fields>
                <Alert componentName="order-download" />

                <div v-if="versionsLoading" class="row justify-content-center py-4"><loader /></div>

                <div v-else-if="!featured.length" class="text-center py-5 px-3">
                    <i class="fa-solid fa-box-open fs-1 text-body-tertiary mb-3 d-block" aria-hidden="true"></i>
                    <h6 class="fw-semibold mb-1">{{ __('message.no_versions_yet') }}</h6>
                    <p class="text-body-secondary small mb-0">{{ __('message.no_versions_yet_hint') }}</p>
                </div>

                <template v-else>
                    <!-- A notice, not an error: older versions still download. One line on wider screens,
                         with the single main Renew action at the end. -->
                    <div v-if="versionsMeta.updates_ended_on" class="alert alert-warning d-flex flex-wrap flex-sm-nowrap align-items-center gap-2 py-2">
                        <i class="fa-solid fa-circle-exclamation flex-shrink-0" aria-hidden="true"></i>
                        <span class="flex-grow-1">
                            {{ __('message.updates_ended_download_older', { date: formatDate(versionsMeta.updates_ended_on) }) }}
                        </span>
                        <button type="button" class="btn btn-sm btn-warning flex-shrink-0 text-nowrap ms-sm-auto" @click="renewFromVersions">{{ __('message.renew_now') }}</button>
                    </div>

                    <!-- Latest release; when that's an RC/Beta, the latest official one follows it. -->
                    <!-- With two cards, make the safe pick obvious: Official = recommended badge + solid button,
                         RC/Beta = outline button + an amber "not for live use" line. -->
                    <div v-for="version in featured" :key="version.version" class="border rounded-3 p-4 mb-3">
                        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                            <div class="flex-grow-1">
                                <div class="text-body-secondary mb-1">
                                    {{ featuredLabel(version) }}
                                    <span v-if="featured.length > 1 && version.release_type === 'official'" class="badge bg-success ms-2">{{ __('message.recommended') }}</span>
                                </div>
                                <div class="fs-3 fw-semibold text-body-emphasis lh-1 mb-2">{{ version.version }}</div>
                                <div class="d-flex flex-wrap gap-3 text-body-secondary">
                                    <span v-if="releaseTypes[version.release_type]">
                                        <i :class="[releaseTypes[version.release_type].icon, 'me-1']" aria-hidden="true"></i>{{ releaseTypes[version.release_type].label }}
                                    </span>
                                    <span>{{ __('message.released_on', { date: formatDate(version.created_at) }) }}</span>
                                </div>
                                <div v-if="featured.length > 1" :class="['mt-2', isPreview(version) ? 'text-warning-emphasis' : 'text-body-secondary']">
                                    <i v-if="isPreview(version)" class="fa-solid fa-triangle-exclamation me-1" aria-hidden="true"></i>{{ releaseHint(version) }}
                                </div>
                            </div>
                            <button v-if="version.can_download && version.download_url" type="button"
                                    :class="['btn btn-lg btn-primary flex-shrink-0 align-self-start align-self-md-center', { 'btn-outline': isPreview(version) }]"
                                    @click="downloadVersion(version)">
                                <i class="fa-solid fa-download me-2" aria-hidden="true"></i>{{ __('message.download') }}
                            </button>
                            <button v-else type="button" class="btn btn-lg btn-warning flex-shrink-0 align-self-start align-self-md-center" @click="renewFromVersions">
                                <i class="fa-solid fa-lock me-2" aria-hidden="true"></i>{{ __('message.renew_to_download') }}
                            </button>
                        </div>
                        <!-- One heading for every card (the upload's free-typed title varied card to card).
                             Notes as plain text so "Show more/less" sits at the end of the text, not a new line. -->
                        <div v-if="version.description" class="mt-3 pt-3 border-top">
                            <div class="fw-medium text-body-emphasis mb-1">{{ __('message.release_notes') }}</div>
                            <p class="text-body-secondary mb-0">
                                {{ openNotes.has(version.version) ? notesText(version.description) : notesPreview(version.description) }}
                                <a v-if="notesTruncated(version.description)" href="#" class="text-decoration-none" @click.prevent="toggleNotes(version.version)">
                                    {{ openNotes.has(version.version) ? __('message.show_less') : __('message.show_more') }}
                                </a>
                            </p>
                        </div>
                    </div>

                    <!-- Only versions other than the cards above; hidden when there are none. -->
                    <button v-if="versionsMeta.has_previous" type="button" class="btn btn-link px-0 text-decoration-none" @click="showPrevious = !showPrevious">
                        {{ showPrevious ? __('message.hide_previous_versions') : __('message.show_previous_versions') }}
                        <i :class="[showPrevious ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down', 'ms-2 small']" aria-hidden="true"></i>
                    </button>

                    <div v-if="showPrevious && versionsMeta.has_previous" class="mt-2">
                        <DataTable :key="downloadVersionsUrl"
                                   :url="downloadVersionsUrl"
                                   :dataColumns="versionColumns"
                                   :option="versionOptions">
                            <template #version="{ row }">
                                <span class="text-nowrap">
                                    <i v-if="releaseTypes[row.release_type]" :class="[releaseTypes[row.release_type].icon, 'me-2']"
                                       v-tooltip="releaseTypes[row.release_type].label" :aria-label="releaseTypes[row.release_type].label"></i>{{ row.version }}
                                </span>
                            </template>
                            <template #created_at="{ row }"><span class="text-nowrap">{{ formatDate(row.created_at) }}</span></template>
                            <template #action="{ row }">
                                <button v-if="row.can_download && row.download_url" type="button" class="btn btn-sm btn-primary text-nowrap"
                                        @click="downloadVersion(row)">
                                    <i class="fa-solid fa-download me-1" aria-hidden="true"></i>{{ __('message.download') }}
                                </button>
                                <button v-else type="button" class="btn btn-sm btn-warning text-nowrap" @click="renewFromVersions">
                                    <i class="fa-solid fa-lock me-1" aria-hidden="true"></i>{{ __('message.renew_to_download') }}
                                </button>
                            </template>
                        </DataTable>
                    </div>
                </template>
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
                <h5 class="modal-title fw-bold">{{ __('message.delete_confirm') }}</h5>
            </template>
            <template #fields>
                <Alert componentName="order-delete" />
                <p class="mb-0">{{ __('message.delete_cloud') }}</p>
            </template>
            <template #controls>
                <action-button action="cancel" @click="closeDeleteModal" />
                <action-button action="delete" :loading="deleteLoading" @click="confirmDelete" />
            </template>
        </AppModal>
    </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { __ } from '@/plugins/i18n'
import RenewModal from './components/RenewModal.vue'
import Alert from '@/components/Reusable/Alert.vue'
import { useDateTime } from '@/core/composables/useDateTime'
import { useDownload } from '@/core/composables/useDownload'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const { formatDate }  = useDateTime()
const { downloadFile } = useDownload()

const baseUrl = useBaseUrl()
const route   = useRoute()
// Dashboard's "Order Renewals" widget links here with ?renewal=1
const renewalOnly = computed(() => !!route.query.renewal)
const apiUrl      = computed(() => renewalOnly.value ? `/get-my-orders?renewal=1` : `/get-my-orders`)

const columns = ['product_name', 'order_date', 'number', 'agents', 'license_ends_at', 'action']

const tableOptions = reactive({
    headings: {
        product_name:   () => __('message.product_name'),
        order_date:     () => __('message.purchase_date'),
        number:         () => __('message.order_no'),
        agents:         () => __('message.agents'),
        license_ends_at: () => __('message.license_expiry'),
        action:         () => __('message.actions'),
    },
    columnsClasses: {
        product_name:   'dt-product-col',
        order_date:     'dt-date-col',
        number:         'dt-number-col',
        agents:         'dt-agents-col',
        license_ends_at: 'dt-expiry-col',
        action:         'dt-action-col',
    },
    sortable:   ['product_name', 'number', 'order_date', 'license_ends_at'],
    filterable: true,
})


// ─── Download Modal ──────────────────────────────────────────
const showDownloadModal   = ref(false)
const downloadRow         = ref(null)
const downloadVersionsUrl = computed(() => downloadRow.value ? `${baseUrl}/get-versions/${downloadRow.value.id}` : null)

const versionColumns = ['version', 'created_at', 'action']
// Icon + tooltip instead of a badge squeezed into the version text.
const releaseTypes = {
    official:    { icon: 'fa-solid fa-circle-check text-success', label: __('message.official') },
    pre_release: { icon: 'fa-solid fa-flask text-warning',        label: __('message.release_candidate') },
    beta:        { icon: 'fa-solid fa-bug text-info',             label: __('message.beta') },
}
const versionOptions = reactive({
    headings: {
        version:    () => __('message.version'),
        created_at: () => __('message.released'),
        action:     () => __('message.file'),
    },
    sortable:   ['version', 'created_at'],
    filterable: true,
})

// Featured cards + "updates ended" notice come with page 1 of the same list.
const versionsLoading = ref(false)
const featured        = ref([])
const versionsMeta    = ref({})
const showPrevious    = ref(false)
const openNotes       = ref(new Set())

async function loadFeatured() {
    versionsLoading.value = true
    try {
        const res  = await http.get(downloadVersionsUrl.value, { params: { limit: 1 } })
        const data = res.data?.data ?? {}
        featured.value     = data.featured ?? []
        versionsMeta.value = { updates_ended_on: data.updates_ended_on, has_previous: !!data.has_previous }
    } catch (e) {
        errorHandler(e, 'order-download')
    } finally {
        versionsLoading.value = false
    }
}

const isPreview = (version) => ['pre_release', 'beta'].includes(version.release_type)

function releaseHint(version) {
    if (version.release_type === 'beta') return __('message.release_hint_beta')
    return isPreview(version) ? __('message.release_hint_pre_release') : __('message.release_hint_official')
}

function featuredLabel(version) {
    if (featured.value.length === 1) return __('message.latest_version')
    if (version.release_type === 'official') return __('message.latest_official_version')
    return version.release_type === 'beta' ? __('message.latest_beta_version') : __('message.latest_release_candidate')
}

// Release notes may contain HTML: preview the plain text, cut at a word.
const NOTES_PREVIEW = 150
const notesText = (html) => new DOMParser().parseFromString(html ?? '', 'text/html').body.textContent.trim()
const notesTruncated = (html) => notesText(html).length > NOTES_PREVIEW
function notesPreview(html) {
    const text = notesText(html)
    if (text.length <= NOTES_PREVIEW) return text
    const cut = text.slice(0, NOTES_PREVIEW)
    let end = cut.length
    while (end && !/\s/.test(cut[end - 1])) end--
    return `${end ? cut.slice(0, end).trimEnd() : cut}…`
}

function toggleNotes(key) {
    const next = new Set(openNotes.value)
    next.has(key) ? next.delete(key) : next.add(key)
    openNotes.value = next
}

function openDownloadModal(row) {
    downloadRow.value       = row
    showDownloadModal.value = true
    featured.value     = []
    versionsMeta.value = {}
    showPrevious.value = false
    openNotes.value    = new Set()
    loadFeatured()
}

function closeDownloadModal() {
    showDownloadModal.value = false
    downloadRow.value       = null
}

// Google-Drive style: close the popup and let the download tray show progress (the tray
// can't sit above a modal with Bootstrap classes alone). Name first — closing clears the row.
function downloadVersion(version) {
    const name = `${downloadRow.value?.product_name ?? ''} ${version.version}`.trim()
    closeDownloadModal()
    downloadFile(version.download_url, name)
}

function renewFromVersions() {
    const row = downloadRow.value
    closeDownloadModal()
    openRenewModal(row)
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
const deleteLoading   = ref(false)

function openDeleteModal(row) {
    deleteRow.value      = row
    showDeleteModal.value = true
}

function closeDeleteModal() {
    showDeleteModal.value = false
}

async function confirmDelete() {
    if (!deleteRow.value?.number) return
    deleteLoading.value = true
    try {
        const res = await http.delete(`/delete/domain/${deleteRow.value.number}/1`)
        successHandler(res, 'client-orders')
        closeDeleteModal()
        globalThis.emitter?.emit('refreshData')
    } catch (e) { errorHandler(e, 'order-delete') }
    finally { deleteLoading.value = false }
}
</script>
