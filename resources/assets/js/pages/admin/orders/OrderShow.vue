<template>
    <div>
        <AppAlert :componentName="COMPONENT" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.order') }}</h4>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body px-0 pt-0">

                    <!-- ── Stats bar ──────────────────────────────────────── -->
                    <div class="alert bg-light m-3">
                        <div class="d-flex flex-column flex-md-row justify-content-around text-center">
                            <div>
                                <strong>{{ __('message.order_no') }}</strong><br>
                                #{{ order?.number }}
                            </div>
                            <div class="mt-3 mt-md-0">
                                <strong>{{ __('message.date') }}</strong><br>
                                {{ order?.created_at ? formatDate(order.created_at) : '—' }}
                            </div>
                            <div class="mt-3 mt-md-0">
                                <strong>{{ __('message.product') }}</strong><br>
                                <router-link
                                    v-if="order?.product_relation"
                                    :to="`/products/${order.product_relation.id}/edit`"
                                >{{ order.product_relation.name }}</router-link>
                                <span v-else>—</span>
                            </div>
                            <div class="mt-3 mt-md-0">
                                <strong>{{ __('message.status') }}</strong><br>
                                <span class="badge" :class="statusBadgeClass">{{ order?.order_status || '—' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- ── Two-column layout ──────────────────────────────── -->
                    <div class="p-3">
                        <div class="row g-4">

                            <!-- Left column -->
                            <div class="col-lg-8">

                                <!-- License Details -->
                                <div class="card card-light mb-4">
                                    <div class="card-header">
                                        <h4 class="card-title">
                                            {{ __('message.license_details') }}
                                        </h4>
                                        <div class="card-tools">
                                            <button class="btn btn-tool" v-tooltip="__('message.edit')" @click="openLicenseEditModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        <ul class="list-group list-group-flush">

                                            <li class="list-group-item py-3 px-3">
                                                <div class="row align-items-center">
                                                    <div class="col-4 fw-semibold">{{ __('message.license_code') }}</div>
                                                    <div class="col-8 d-flex align-items-center gap-2">
                                                        <span class="font-monospace">{{ licenseDetails?.licence_code || '—' }}</span>
                                                        <button class="btn btn-light table_btn" v-tooltip="__('message.copy')" @click="copyLicenseCode">
                                                            <i :class="copied ? 'fas fa-check' : 'fas fa-clipboard'"></i>
                                                        </button>
                                                        <button class="btn btn-light table_btn" v-tooltip="__('message.reissue_license')" :disabled="saving.reissue" @click="reissueLicense">
                                                            <i class="fas fa-credit-card"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </li>

                                            <li class="list-group-item py-3 px-3">
                                                <div class="row align-items-center">
                                                    <div class="col-4 fw-semibold">{{ __('message.installation_limit') }}</div>
                                                    <div class="col-8">{{ licenseDetails?.installation_limit ?? '—' }}</div>
                                                </div>
                                            </li>

                                            <li class="list-group-item py-3 px-3">
                                                <div class="row align-items-center">
                                                    <div class="col-4 fw-semibold">{{ __('message.updates_expiry') }}</div>
                                                    <div class="col-8">
                                                        <span :class="expiryStatus('update_end') ? 'text-danger' : ''">{{ expiryDate('update_end') || '—' }}</span>
                                                        <span v-if="expiryStatus('update_end')" class="badge bg-danger ms-1">{{ expiryStatus('update_end') }}</span>
                                                    </div>
                                                </div>
                                            </li>

                                            <li class="list-group-item py-3 px-3">
                                                <div class="row align-items-center">
                                                    <div class="col-4 fw-semibold">{{ __('message.license_expiry') }}</div>
                                                    <div class="col-8">
                                                        <span :class="expiryStatus('subscription_end') ? 'text-danger' : 'text-muted'">{{ expiryDate('subscription_end') || 'Not set' }}</span>
                                                        <span v-if="expiryStatus('subscription_end')" class="badge bg-danger ms-1">{{ expiryStatus('subscription_end') }}</span>
                                                    </div>
                                                </div>
                                            </li>

                                            <li class="list-group-item py-3 px-3">
                                                <div class="row align-items-center">
                                                    <div class="col-4 fw-semibold">{{ __('message.support_expiry') }}</div>
                                                    <div class="col-8">
                                                        <span :class="expiryStatus('support_end') ? 'text-danger' : ''">{{ expiryDate('support_end') || '—' }}</span>
                                                        <span v-if="expiryStatus('support_end')" class="badge bg-danger ms-1">{{ expiryStatus('support_end') }}</span>
                                                    </div>
                                                </div>
                                            </li>

                                            <li class="list-group-item py-3 px-3">
                                                <div class="row align-items-center">
                                                    <div class="col-4 fw-semibold d-flex align-items-center gap-1">
                                                        {{ __('message.localized_license') }}
                                                        <Tooltip :message="__('message.localized_license_tooltip')" />
                                                    </div>
                                                    <div class="col-8 d-flex align-items-center gap-2">
                                                        <Switch
                                                            name="license_mode"
                                                            :value="order?.license_mode === 'File'"
                                                            :disabled="saving.licenseMode"
                                                            :onChange="toggleLicenseMode"
                                                        />
                                                        <button
                                                            v-if="order?.license_mode === 'File'"
                                                            class="btn btn-light table_btn"
                                                            v-tooltip="__('message.download_license_file')"
                                                            @click="handleDownloadClick"
                                                        >
                                                            <i class="fas fa-download"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </li>

                                        </ul>
                                    </div>
                                </div>

                                <!-- Invoice / Payments / Installations tabs -->
                                <div class="card card-light">
                                    <div class="card-header p-0 border-bottom-0">
                                        <ul class="nav nav-tabs px-3 pt-2" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link" :class="{ active: tab === 'installations' }" href="#" @click.prevent="tab = 'installations'">
                                                    <i class="fas fa-server me-1"></i>{{ __('message.installation_details') }}
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" :class="{ active: tab === 'invoices' }" href="#" @click.prevent="tab = 'invoices'">
                                                    <i class="fas fa-file-invoice me-1"></i>{{ __('message.invoice_list') }}
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" :class="{ active: tab === 'payments' }" href="#" @click.prevent="tab = 'payments'">
                                                    <i class="fas fa-receipt me-1"></i>{{ __('message.payment_receipts') }}
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="card-body table-responsive">
                                        <div v-show="tab === 'installations'">
                                            <DataTable
                                                :url="`${baseUrl}/get-installation-details/${orderId}`"
                                                :dataColumns="installColumns"
                                                :option="installTableOptions"
                                            />
                                        </div>
                                        <div v-show="tab === 'invoices'">
                                            <DataTable
                                                :url="`${baseUrl}/getOrderInvoices/${orderId}`"
                                                :dataColumns="invoiceColumns"
                                                :option="invoiceTableOptions"
                                            />
                                        </div>
                                        <div v-show="tab === 'payments'">
                                            <DataTable
                                                :url="`${baseUrl}/getOrderPayments/${orderId}`"
                                                :dataColumns="paymentColumns"
                                                :option="paymentTableOptions"
                                            />
                                        </div>
                                    </div>
                                </div>

                            </div><!-- /left col -->

                            <!-- Right sidebar -->
                            <div class="col-lg-4">

                                <!-- Customer card -->
                                <div class="card card-light mb-4">
                                    <div class="card-header">
                                        <h4 class="card-title">
                                            {{ __('message.customer') }}
                                        </h4>
                                    </div>
                                    <div class="card-body p-0">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item py-3 px-3">
                                                <div class="row align-items-center">
                                                    <div class="col-4 fw-semibold">{{ __('message.name') }}</div>
                                                    <div class="col-8">
                                                        <router-link v-if="order?.user?.id" :to="`/users/${order.user.id}/edit`">{{ userName }}</router-link>
                                                        <span v-else>{{ userName }}</span>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item py-3 px-3">
                                                <div class="row align-items-center">
                                                    <div class="col-4 fw-semibold">{{ __('message.email') }}</div>
                                                    <div class="col-8">{{ order?.user?.email || '—' }}</div>
                                                </div>
                                            </li>
                                            <li class="list-group-item py-3 px-3">
                                                <div class="row align-items-center">
                                                    <div class="col-4 fw-semibold">{{ __('message.mobile') }}</div>
                                                    <div class="col-8">
                                                        <span v-if="order?.user?.mobile_code">(+{{ order.user.mobile_code }}) </span>
                                                        {{ order?.user?.mobile || '—' }}
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item py-3 px-3">
                                                <div class="row align-items-center">
                                                    <div class="col-4 fw-semibold">{{ __('message.country') }}</div>
                                                    <div class="col-8">
                                                        <span v-if="order?.user?.country">
                                                            <span :class="`fi fi-${order.user.country.toLowerCase()} me-1`"></span>
                                                            {{ order.user.country_relation?.country_name || order.user.country }}
                                                            ({{ order.user.country }})
                                                        </span>
                                                        <span v-else>—</span>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Auto Renewal card -->
                                <div class="card card-light">
                                    <div class="card-header">
                                        <h4 class="card-title">
                                            {{ __('message.auto_renewal') }}
                                        </h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div>
                                                <div class="small fw-semibold">{{ __('message.auto_renewal_subscription') }}</div>
                                                <div class="text-muted text-renewal-hint">Automatically renew when subscription ends</div>
                                            </div>
                                            <Switch
                                                name="auto_renewal"
                                                :value="autorenewal == 1"
                                                :disabled="autorenewal != 1 || saving.renewal"
                                                :onChange="disableRenewal"
                                            />
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="small text-muted">{{ __('message.status') }}</span>
                                            <span v-if="isSubscribed" class="badge bg-success">{{ __('message.active') }}</span>
                                            <span v-else class="badge bg-secondary">{{ __('message.inactive') }}</span>
                                        </div>
                                        <template v-if="isSubscribed && paymentLog">
                                            <hr class="my-3">
                                            <div class="d-flex justify-content-between small mb-2">
                                                <span class="text-muted">{{ __('message.payment-method') }}</span>
                                                <span class="fw-semibold">{{ paymentLog?.payment_method ? capitalize(paymentLog.payment_method) : '—' }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between small">
                                                <span class="text-muted">{{ __('message.subscription_start_date') }}</span>
                                                <span class="fw-semibold">{{ paymentLog?.date ? formatDate(paymentLog.date) : '—' }}</span>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                            </div><!-- /right sidebar -->

                        </div><!-- /row -->
                    </div>

                </div><!-- /card-body -->

            </template>
        </div><!-- /card -->

        <!-- ── License Details Edit Modal ─────────────────────────────── -->
        <AppModal :showModal="licenseEditModal.show" :onClose="() => licenseEditModal.show = false" :showCloseBtn="false">
            <template #title><h4>{{ __('message.license_details') }}</h4></template>
            <template #fields>
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __('message.installation_limit') }}</label>
                    <input
                        type="number"
                        class="form-control"
                        :class="{ 'is-invalid': errors.limit }"
                        v-model="licenseEditModal.limit"
                        min="1"
                    />
                    <div v-if="errors.limit" class="invalid-feedback">{{ errors.limit }}</div>
                </div>
                <DatePicker
                    v-if="canEditExpiry.update_end"
                    name="update_end"
                    :label="__('message.updates_expiry')"
                    :value="licenseEditModal.update_end"
                    format="MM/DD/YYYY"
                    :onChange="(val) => licenseEditModal.update_end = val"
                />
                <DatePicker
                    v-if="canEditExpiry.subscription_end"
                    name="subscription_end"
                    :label="__('message.license_expiry')"
                    :value="licenseEditModal.subscription_end"
                    format="MM/DD/YYYY"
                    :onChange="(val) => licenseEditModal.subscription_end = val"
                />
                <DatePicker
                    v-if="canEditExpiry.support_end"
                    name="support_end"
                    :label="__('message.support_expiry')"
                    :value="licenseEditModal.support_end"
                    format="MM/DD/YYYY"
                    :onChange="(val) => licenseEditModal.support_end = val"
                />
                <p v-if="someExpiryFieldsHidden" class="text-muted small mb-0">
                    {{ __('message.some_dates_not_editable') || 'Some dates aren\'t editable for this license type.' }}
                </p>
            </template>
            <template #controls>
                <action-button action="save" :loading="saving.licenseEdit" @click="saveLicenseEdit" />
            </template>
        </AppModal>

        <!-- ── Bind license (domain + machine ID) before first download ── -->
        <AppModal :showModal="showBindingModal" :onClose="closeBindingModal" :showCloseBtn="false">
            <template #title><h4>{{ __('message.localized_license') }}</h4></template>
            <template #fields>
                <p class="text-muted mb-3">{{ __('message.machine_id_tooltip') }}</p>
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __('message.domain') }}</label>
                    <input type="text" class="form-control" v-model="bindingForm.domain" placeholder="example.com" />
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __('message.machine_id') }}</label>
                    <input type="text" class="form-control" v-model="bindingForm.machineId" :placeholder="__('message.enter_machine_id')" />
                </div>
            </template>
            <template #controls>
                <action-button action="confirm"
                               :label="__('message.save_and_download')"
                               :loading="bindingBusy"
                               :disabled="!bindingForm.domain || !bindingForm.machineId"
                               @click="submitBinding" />
            </template>
        </AppModal>

        <!-- ── Pick which license file to download (main product + entitled add-ons) ── -->
        <AppModal :showModal="showDownloadModal" :onClose="closeDownloadModal" modalBodyClass="download-modal-body">
            <template #title><h4>{{ __('message.localized_license') }}</h4></template>
            <template #fields>
                <div class="download-section-label">{{ __('message.product') }}</div>
                <ul class="list-group mb-3">
                    <li class="list-group-item d-flex align-items-center justify-content-between">
                        <span>{{ order?.product_relation?.name || __('message.product') }}</span>
                        <button
                            class="btn btn-light table_btn"
                            v-tooltip="__('message.download_license_file')"
                            @click="selectDownload(null)"
                        >
                            <i class="fas fa-download"></i>
                        </button>
                    </li>
                </ul>

                <template v-if="pluginLicenses.length">
                    <div class="download-section-label">{{ __('message.addons') }}</div>
                    <ul class="list-group">
                        <li
                            v-for="plugin in pluginLicenses"
                            :key="plugin.id"
                            class="list-group-item d-flex align-items-center justify-content-between"
                        >
                            <span>{{ plugin.name }}</span>
                            <button
                                class="btn btn-light table_btn"
                                v-tooltip="__('message.download_license_file')"
                                @click="selectDownload(plugin.id)"
                            >
                                <i class="fas fa-download"></i>
                            </button>
                        </li>
                    </ul>
                </template>
            </template>
        </AppModal>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, h } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import http from '@/plugins/axios'
import { errorHandler, successHandler } from '@/helpers/responseHandler.js'
import { useForm } from 'vee-validate'
import { validateForm } from '@/helpers/formUtils.js'
import { licenseDetailsSchema } from '@/validations/admin/orderValidations.js'
import Switch from '@/components/Reusable/FormField/Switch.vue'
import Tooltip from '@/components/Reusable/Tooltip.vue'
import { useDateTime } from '@/core/composables/useDateTime'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import { makeRequestAdapter } from '@/helpers/tableUtils'

const { formatDate, formatDateTime } = useDateTime()

const { errors, setErrors, resetForm } = useForm()

const COMPONENT = 'orders-show'

const baseUrl = useBaseUrl()

const route   = useRoute()
const orderId = route.params.id

const loading        = ref(true)
const copied         = ref(false)
const order          = ref(null)
const licenseDetails = ref(null)
const permissions    = ref({})
const autorenewal    = ref(0)
const isSubscribed   = ref(0)
const paymentLog     = ref(null)
const tab            = ref('installations')
const pluginLicenses = ref([])

const saving = reactive({
    reissue:     false,
    renewal:     false,
    licenseMode: false,
    licenseEdit: false,
})

const licenseEditModal = reactive({
    show:             false,
    limit:            null,
    update_end:       null,
    subscription_end: null,
    support_end:      null,
})


// ── Computed ───────────────────────────────────────────────────────────────
const userName = computed(() => {
    const u = order.value?.user
    if (!u) return '—'
    return `${u.first_name ?? ''} ${u.last_name ?? ''}`.trim() || '—'
})


const statusBadgeClass = computed(() => {
    const s = (order.value?.order_status ?? '').toLowerCase()
    if (s === 'executed') return 'bg-success'
    if (s === 'pending')  return 'bg-warning text-dark'
    if (s === 'failed')   return 'bg-danger'
    return 'bg-secondary'
})

// Which expiry-date fields this order's product license type actually lets
// an admin change — mirrors SubscriptionRenewalService::PERMISSION_MAP (the
// write side of this same check).
const canEditExpiry = computed(() => ({
    update_end:       !!permissions.value.generateUpdatesxpiryDate,
    subscription_end: !!permissions.value.generateLicenseExpiryDate,
    support_end:      !!permissions.value.generateSupportExpiryDate,
}))

const someExpiryFieldsHidden = computed(() => Object.values(canEditExpiry.value).some(v => !v))

// ── Helpers ────────────────────────────────────────────────────────────────
function capitalize(str) {
    return str ? str.charAt(0).toUpperCase() + str.slice(1) : ''
}

function expiryEntry(key) {
    return licenseDetails.value?.expiry_dates?.[key] ?? null
}

function expiryDate(key) {
    const d = expiryEntry(key)?.date
    return d ? formatDate(d) : null
}

function expiryRaw(key) {
    const d = expiryEntry(key)?.date
    if (!d) return null
    const dt   = new Date(d)
    const mm   = String(dt.getMonth() + 1).padStart(2, '0')
    const dd   = String(dt.getDate()).padStart(2, '0')
    const yyyy = dt.getFullYear()
    return `${mm}/${dd}/${yyyy}`
}

function expiryStatus(key) {
    return expiryEntry(key)?.status ?? null
}

// ── Actions ────────────────────────────────────────────────────────────────
async function copyLicenseCode(e) {
    const code = licenseDetails.value?.licence_code
    if (!code) return
    await navigator.clipboard.writeText(code)
    copied.value = true
    e.target.blur()
    setTimeout(() => { copied.value = false }, 2000)
}

async function reissueLicense() {
    saving.reissue = true
    try {
        const res = await http.patch(`/reissue-license`, { id: orderId })
        successHandler(res, COMPONENT)
        await reload()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.reissue = false
    }
}

async function disableRenewal() {
    saving.renewal = true
    try {
        const res = await http.post(`/auto-renewal/${orderId}/disable`)
        successHandler(res, COMPONENT)
        await reload()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.renewal = false
    }
}

async function toggleLicenseMode(checked) {
    saving.licenseMode = true
    try {
        const choose = checked ? 1 : 0
        const res = await http.post(`/switch-license-mode`, { choose, orderNo: order.value?.number })
        successHandler(res, COMPONENT)
        await reload()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.licenseMode = false
    }
}

// ── License binding (domain + machine ID) before first download ────────────
const showBindingModal = ref(false)
const bindingBusy      = ref(false)
const bindingForm      = reactive({ domain: '', machineId: '' })
// null = main product's license file; otherwise the plugin product_id whose
// download triggered the binding modal, so it resumes the right one after.
const pendingDownloadProductId = ref(null)

function isLicenseBound() {
    return !!(licenseDetails.value?.license_domain && licenseDetails.value?.license_machine_id)
}

function triggerDownload(productId = null) {
    const suffix = productId ? `/${productId}` : ''
    window.location.href = `${baseUrl}/LocalizedLicense/downloadLicense/${order.value.number}${suffix}`
}

function requestDownload(productId = null) {
    if (isLicenseBound()) {
        triggerDownload(productId)
        return
    }
    pendingDownloadProductId.value = productId
    bindingForm.domain    = licenseDetails.value?.license_domain ?? ''
    bindingForm.machineId = licenseDetails.value?.license_machine_id ?? ''
    showBindingModal.value = true
}

// ── Pick which license file to download (main product + entitled add-ons) ──
const showDownloadModal = ref(false)

function handleDownloadClick() {
    showDownloadModal.value = true
}

function closeDownloadModal() {
    showDownloadModal.value = false
}

function selectDownload(productId) {
    // Only hide the picker if binding is about to be needed - otherwise its
    // overlay would sit on top of the binding modal (both share the same
    // z-index, and the picker comes later in the DOM). It's restored in
    // submitBinding() once binding's done. If already bound, leave it open
    // so multiple items can be downloaded without reopening it each time.
    if (!isLicenseBound()) {
        showDownloadModal.value = false
    }
    requestDownload(productId)
}

function closeBindingModal() {
    showBindingModal.value = false
}

async function submitBinding() {
    if (!bindingForm.domain || !bindingForm.machineId) return
    bindingBusy.value = true
    try {
        const res = await http.post('/license-binding', {
            orderNo: order.value.number,
            domain: bindingForm.domain,
            machine_id: bindingForm.machineId,
        })
        licenseDetails.value.license_domain     = bindingForm.domain
        licenseDetails.value.license_machine_id = bindingForm.machineId
        successHandler(res, COMPONENT)
        closeBindingModal()
        triggerDownload(pendingDownloadProductId.value)
        showDownloadModal.value = true
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        bindingBusy.value = false
    }
}

// ── License details edit modal ─────────────────────────────────────────────
function openLicenseEditModal() {
    licenseEditModal.limit            = licenseDetails.value?.installation_limit ?? null
    licenseEditModal.update_end       = expiryRaw('update_end')
    licenseEditModal.subscription_end = expiryRaw('subscription_end')
    licenseEditModal.support_end      = expiryRaw('support_end')
    licenseEditModal.show             = true
    resetForm()
}

async function saveLicenseEdit() {
    if (!await validateForm(licenseDetailsSchema, licenseEditModal, setErrors)) return
    saving.licenseEdit = true
    try {
        // Only send the date fields actually shown/editable — sending a
        // hidden field's unchanged value would still count as "attempting"
        // it server-side, and trigger a bogus not-permitted warning.
        const res = await http.post(`/update-license-details`, {
            orderid:          orderId,
            limit:            licenseEditModal.limit,
            ...(canEditExpiry.value.update_end       ? { update_end: licenseEditModal.update_end } : {}),
            ...(canEditExpiry.value.subscription_end ? { subscription_end: licenseEditModal.subscription_end } : {}),
            ...(canEditExpiry.value.support_end      ? { support_end: licenseEditModal.support_end } : {}),
        })
        licenseEditModal.show = false
        successHandler(res, COMPONENT)
        await reload()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.licenseEdit = false
    }
}

// ── Data loading ───────────────────────────────────────────────────────────
async function reload() {
    const res = await http.get(`/order/${orderId}`)
    const d   = res.data?.data ?? res.data
    order.value          = d.order
    licenseDetails.value = d.license_details
    permissions.value    = d.permissions ?? {}
    autorenewal.value    = d.autorenewal
    isSubscribed.value   = d.is_subscribed
    paymentLog.value     = d.payment_log
    await loadPluginLicenses()
}

async function loadPluginLicenses() {
    if (!order.value?.number) return
    try {
        const res = await http.get(`/LocalizedLicense/${order.value.number}/plugins`)
        pluginLicenses.value = res.data?.data ?? res.data ?? []
    } catch (e) {
        pluginLicenses.value = []
    }
}

onMounted(async () => {
    try {
        await reload()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

// ── Installation table ─────────────────────────────────────────────────────
const installColumns = ['path', 'ip', 'version', 'status', 'last_active_date']
const installTableOptions = reactive({
    headings: {
        path:             __('message.installation_path'),
        ip:               __('message.installation_ip'),
        version:          'Version',
        status:           __('message.status'),
        last_active_date: __('message.last_active'),
    },
    columnsClasses: {
        path:             'dt-text',
        ip:               'dt-code',
        version:          'dt-code',
        status:           'dt-status',
        last_active_date: 'dt-date',
    },
    templates: {
        path:             (f, row) => row.path || '—',
        ip:               (f, row) => row.ip || '—',
        version:          (f, row) => row.version || '—',
        status:           (f, row) => h('span', { class: row.status === 'Active' ? 'badge bg-success' : 'badge bg-secondary' }, row.status || '—'),
        last_active_date: (f, row) => row.last_active_date || '—',
    },
    sortable:   [],
    filterable: false,
    requestAdapter(data) {
        return { page: data.page, limit: data.limit }
    },
    responseAdapter({ data }) {
        const rows = data?.data ?? []
        return { data: rows, count: rows.length }
    },
})

// ── Invoice table ──────────────────────────────────────────────────────────
const invoiceColumns = ['number', 'products', 'date', 'amount', 'status']
const invoiceTableOptions = reactive({
    headings: {
        number:   __('message.invoice_no'),
        products: __('message.products'),
        date:     __('message.date'),
        amount:   __('message.total'),
        status:   __('message.status'),
    },
    columnsClasses: {
        number:   'dt-number',
        products: 'dt-name',
        date:     'dt-date',
        amount:   'dt-amount',
        status:   'dt-status',
    },
    templates: {
        number:   (f, row) => row.number && row.id ? h(RouterLink, { to: '/invoices/' + row.id }, () => row.number) : (row.number || '—'),
        products: (f, row) => (row.products ?? []).join(', ') || '—',
        date:     (f, row) => row.date ? formatDateTime(row.date) : '—',
        status:   (f, row) => h('span', {
            class: row.status?.toLowerCase() === 'success' ? 'badge bg-success' : 'badge bg-secondary',
        }, row.status),
    },
    sortable:   ['date', 'number', 'amount', 'status'],
    filterable: false,
    requestAdapter(data) {
        const columnMap = { amount: 'grand_total' }
        return {
            'sort-field':   (columnMap[data.orderBy] ?? data.orderBy) || 'date',
            'sort-order':   data.orderBy ? (data.ascending ? 'asc' : 'desc') : 'desc',
            'search-query': '',
            page:           data.page,
            limit:          data.limit,
        }
    },
    orderBy: { column: 'date', ascending: false },
})

// ── Payment table ──────────────────────────────────────────────────────────
const paymentColumns = ['invoice_number', 'amount', 'payment_method', 'payment_status', 'created_at']
const paymentTableOptions = reactive({
    headings: {
        invoice_number: __('message.invoice_no'),
        amount:         __('message.total'),
        payment_method: __('message.method'),
        payment_status: __('message.status'),
        created_at:     __('message.payment_date'),
    },
    columnsClasses: {
        invoice_number: 'dt-number',
        amount:         'dt-amount',
        payment_method: 'dt-name',
        payment_status: 'dt-status',
        created_at:     'dt-date',
    },
    templates: {
        payment_status: (f, row) => h('span', {
            class: row.payment_status?.toLowerCase() === 'success' ? 'badge bg-success' : 'badge bg-secondary',
        }, row.payment_status),
        created_at: (f, row) => row.created_at ? formatDate(row.created_at) : '—',
    },
    sortable:   ['created_at', 'amount', 'payment_method', 'payment_status'],
    filterable: false,
    requestAdapter: makeRequestAdapter('created_at'),
    orderBy: { column: 'created_at', ascending: false },
})
</script>

<style scoped>
.text-renewal-hint { font-size: 11px; }
:deep(.download-modal-body) {
    max-height: 60vh;
    overflow-y: auto;
}
.download-section-label {
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #6c757d;
    margin-bottom: 0.5rem;
}
.order-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background-color: #17a2b8;
    color: #fff;
    font-weight: 700;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
</style>
