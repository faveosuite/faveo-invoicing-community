<template>
    <div>
        <AppAlert :componentName="COMPONENT" />

        <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

        <template v-else>
            <!-- Order header: what was bought, its state, when it ends, and the main action -->
            <div class="card card-light mb-4">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                        <div>
                            <h2 class="h4 mb-1">
                                {{ __('message.order_number_title', { number: order?.number }) }}
                                <span class="badge align-middle ms-2 order-status" :class="statusBadgeClass">{{ order?.order_status || '—' }}</span>
                            </h2>
                            <p v-if="order?.product_relation" class="mb-0">
                                <router-link :to="`/products/${order.product_relation.id}/edit`">{{ order.product_relation.name }}</router-link>
                            </p>
                        </div>
                        <div v-if="canRenew">
                            <router-link v-if="!renewBlockedReason" class="btn btn-primary" :to="`/orders/${orderId}/renew`">
                                <i class="fas fa-rotate me-1"></i>{{ __('message.renew_order') }}
                            </router-link>
                            <span v-else class="d-inline-block" tabindex="0" v-tooltip="renewBlockedReason">
                                <button type="button" class="btn btn-primary" disabled><i class="fas fa-rotate me-1"></i>{{ __('message.renew_order') }}</button>
                            </span>
                        </div>
                    </div>

                    <!-- Key dates: when the order was placed, and when the license and each date feature end -->
                    <dl class="row g-3 mt-2 mb-0 order-facts">
                        <div v-if="planName" class="col-12 col-md">
                            <dt>{{ __('message.plan') }}</dt>
                            <dd class="mb-0">{{ planName }}</dd>
                        </div>
                        <div class="col-6 col-md">
                            <dt>{{ __('message.order_ordered_on') }}</dt>
                            <dd class="mb-0">{{ order?.created_at ? formatDate(order.created_at) : '—' }}</dd>
                        </div>
                        <div v-for="d in rights.dates" :key="d.code" class="col-6 col-md">
                            <dt>{{ __('message.order_until', { label: d.label }) }}</dt>
                            <dd class="mb-0 d-flex align-items-center gap-2">
                                <span>{{ d.ends_at ? formatDate(d.ends_at) : __('message.order_date_never') }}</span>
                                <button type="button" class="btn btn-link btn-sm p-0 text-muted" v-tooltip="__('message.edit')"
                                        :aria-label="`${__('message.edit')} ${d.label}`" @click="openDate(d)">
                                    <i class="fas fa-pen"></i>
                                </button>
                            </dd>
                            <dd v-if="dateHint(d)" class="mb-0 small" :class="dateHint(d).class">{{ dateHint(d).text }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card card-light">
                        <div class="card-header p-0 border-bottom-0">
                            <ul class="nav nav-tabs flex-nowrap overflow-x-auto px-3 pt-2 order-tabs" role="tablist">
                                <li v-if="hasLicense" class="nav-item">
                                    <a class="nav-link" :class="{ active: tab === 'license' }" href="#" @click.prevent="tab = 'license'">
                                        <i class="fas fa-key me-1"></i>{{ __('message.order_tab_license') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" :class="{ active: tab === 'features' }" href="#" @click.prevent="tab = 'features'">
                                        <i class="fas fa-list-check me-1"></i>{{ __('message.order_features') }}
                                        <span class="badge text-bg-light border ms-1">{{ rights.entitlements.length }}</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" :class="{ active: tab === 'installations' }" href="#" @click.prevent="tab = 'installations'">
                                        <i class="fas fa-server me-1"></i>{{ __('message.order_tab_installations') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" :class="{ active: tab === 'invoices' }" href="#" @click.prevent="tab = 'invoices'">
                                        <i class="fas fa-file-invoice me-1"></i>{{ __('message.order_tab_invoices') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" :class="{ active: tab === 'payments' }" href="#" @click.prevent="tab = 'payments'">
                                        <i class="fas fa-receipt me-1"></i>{{ __('message.order_tab_payments') }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <!-- License: the installation's code and limits (products that issue one) -->
                            <div v-if="hasLicense" v-show="tab === 'license'">
                                <ul class="list-group list-group-flush">

                                    <li class="list-group-item py-3 px-3">
                                        <div class="row align-items-center">
                                            <div class="col-4 fw-semibold">{{ __('message.order_license_code') }}</div>
                                            <div class="col-8 d-flex align-items-center gap-2">
                                                <span class="font-monospace license-code">{{ licenseDetails?.licence_code || '—' }}</span>
                                                <button class="btn btn-light table_btn flex-shrink-0" v-tooltip="__('message.copy')" @click="copyLicenseCode">
                                                    <i :class="copied ? 'fas fa-check' : 'fas fa-clipboard'"></i>
                                                </button>
                                                <button v-if="canReissue" class="btn btn-light table_btn flex-shrink-0" v-tooltip="__('message.reissue_license')" :disabled="saving.reissue" @click="showReissueModal = true">
                                                    <i class="fas fa-credit-card"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </li>

                                    <li class="list-group-item py-3 px-3">
                                        <div class="row align-items-center">
                                            <div class="col-4 fw-semibold">{{ __('message.installation_limit') }}</div>
                                            <div class="col-8 d-flex align-items-center gap-2">
                                                {{ licenseDetails?.installation_limit ?? '—' }}
                                                <button type="button" class="btn btn-link btn-sm p-0 text-muted" v-tooltip="__('message.edit')"
                                                        :aria-label="`${__('message.edit')} ${__('message.installation_limit')}`" @click="openLicenseEditModal">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </li>

                                    <li class="list-group-item py-3 px-3">
                                        <div class="row align-items-center">
                                            <div class="col-4 fw-semibold">{{ __('message.agents') }}</div>
                                            <div class="col-8 d-flex align-items-center gap-2">
                                                {{ agentsLabel }}
                                                <button type="button" class="btn btn-link btn-sm p-0 text-muted" v-tooltip="__('message.edit')"
                                                        :aria-label="`${__('message.edit')} ${__('message.agents')}`" @click="openLicenseEditModal">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </li>

                                    <li v-if="!isCloud" class="list-group-item py-3 px-3">
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

                            <div v-show="tab === 'features'">
                                <OrderEntitlements :orderId="orderId" :items="rights.entitlements" :available="rights.available" @changed="loadRights" />
                            </div>

                            <div v-show="tab === 'installations'" class="table-responsive">
                                <DataTable
                                    :url="`${baseUrl}/get-installation-details/${orderId}`"
                                    :dataColumns="installColumns"
                                    :option="installTableOptions"
                                />
                            </div>
                            <div v-show="tab === 'invoices'" class="table-responsive">
                                <DataTable
                                    :url="`${baseUrl}/getOrderInvoices/${orderId}`"
                                    :dataColumns="invoiceColumns"
                                    :option="invoiceTableOptions"
                                />
                            </div>
                            <div v-show="tab === 'payments'" class="table-responsive">
                                <DataTable
                                    :url="`${baseUrl}/getOrderPayments/${orderId}`"
                                    :dataColumns="paymentColumns"
                                    :option="paymentTableOptions"
                                />
                            </div>
                        </div>
                    </div>
                </div>

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
                                            <router-link v-if="order?.user?.id" :to="`/users/${order.user.id}`">{{ userName }}</router-link>
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

                    <!-- Auto Renewal card — hidden when nothing on the order expires (lifetime) -->
                    <div v-if="canRenew" class="card card-light">
                        <div class="card-header">
                            <h4 class="card-title">
                                {{ __('message.auto_renewal') }}
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <div class="small fw-semibold">{{ __('message.auto_renewal_subscription') }}</div>
                                    <div class="text-muted text-renewal-hint">{{ __('message.auto_renewal_subscription_hint') }}</div>
                                </div>
                                <Switch
                                    name="auto_renewal"
                                    :value="isSubscribed == 1"
                                    :disabled="isSubscribed != 1 || saving.renewal"
                                    :onChange="disableRenewal"
                                />
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="small text-muted">{{ __('message.status') }}</span>
                                <span v-if="autoRenewState === 'active'" class="badge bg-success">{{ __('message.active') }}</span>
                                <span v-else-if="autoRenewState === 'pending'" class="badge bg-warning text-dark">{{ __('message.pending_authorization') }}</span>
                                <span v-else-if="autoRenewState === 'enabled'" class="badge bg-info text-dark">{{ __('message.enabled') }}</span>
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
        </template>

        <!-- ── Date edit: license end or a date feature (updates, support, …) ── -->
        <AppModal :showModal="!!dating" :onClose="closeDate" :showCloseBtn="false" classname="modal-md">
            <template #title><h4>{{ dating?.label }}</h4></template>
            <template #alert><AppAlert :componentName="MODAL_COMPONENT" /></template>
            <template #fields>
                <template v-if="dating">
                    <Checkbox name="never" :label="__('message.order_date_never')" :value="dating.never" :onChange="v => (dating.never = v)" />
                    <DatePicker v-if="!dating.never" name="date" :label="__('message.order_date_ends')" :value="dating.date"
                                :onChange="v => (dating.date = v)" />
                </template>
            </template>
            <template #controls>
                <action-button action="cancel" @click="closeDate" />
                <action-button action="save" :loading="saving.date" @click="saveDate" />
            </template>
        </AppModal>

        <!-- ── License Details Edit Modal ─────────────────────────────── -->
        <AppModal :showModal="licenseEditModal.show" :onClose="() => licenseEditModal.show = false" :showCloseBtn="false">
            <template #title><h4>{{ __('message.license_details') }}</h4></template>
            <template #alert><AppAlert :componentName="MODAL_COMPONENT" /></template>
            <template #fields>
                <div class="mb-3">
                    <label class="form-label fw-bold" for="license-installation-limit">{{ __('message.installation_limit') }}</label>
                    <input
                        id="license-installation-limit"
                        type="number"
                        class="form-control"
                        :class="{ 'is-invalid': errors.limit }"
                        v-model="licenseEditModal.limit"
                        min="1"
                    />
                    <div v-if="errors.limit" class="invalid-feedback">{{ errors.limit }}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold" for="license-agents">{{ __('message.agents') }}</label>
                    <input
                        id="license-agents"
                        type="number"
                        class="form-control"
                        :class="{ 'is-invalid': errors.agents }"
                        v-model="licenseEditModal.agents"
                        min="0"
                    />
                    <div v-if="errors.agents" class="invalid-feedback">{{ errors.agents }}</div>
                    <small class="text-muted">{{ __('message.agents_unlimited_hint') }}</small>
                </div>
            </template>
            <template #controls>
                <action-button action="save" :loading="saving.licenseEdit" @click="saveLicenseEdit" />
            </template>
        </AppModal>

        <!-- ── Reissue License confirmation ── -->
        <AppModal :showModal="showReissueModal" :onClose="() => showReissueModal = false" :showCloseBtn="false">
            <template #title><h4>{{ __('message.are_you_sure') }}</h4></template>
            <template #fields>
                <p class="mb-0">{{ __('message.reissuing_license') }}</p>
            </template>
            <template #controls>
                <action-button action="cancel" @click="showReissueModal = false" />
                <action-button action="confirm" icon="fas fa-id-card-alt"
                               :label="__('message.reissue_license')"
                               :loading="saving.reissue"
                               @click="reissueLicense" />
            </template>
        </AppModal>

        <!-- ── Bind license (domain + machine ID) before first download ── -->
        <AppModal :showModal="showBindingModal" :onClose="closeBindingModal" :showCloseBtn="false">
            <template #title><h4>{{ __('message.localized_license') }}</h4></template>
            <template #alert><AppAlert :componentName="MODAL_COMPONENT" /></template>
            <template #fields>
                <p class="text-muted mb-3">{{ __('message.activation_code_tooltip') }}</p>
                <div class="mb-3">
                    <label class="form-label fw-bold" for="binding-activation-code">{{ __('message.activation_code') }}</label>
                    <textarea id="binding-activation-code" class="form-control" rows="4" v-model="bindingForm.activationCode" :placeholder="__('message.enter_activation_code')"></textarea>
                </div>
            </template>
            <template #controls>
                <action-button action="confirm"
                               :label="__('message.save_and_download')"
                               :loading="bindingBusy"
                               :disabled="!bindingForm.activationCode"
                               @click="submitBinding" />
            </template>
        </AppModal>

        <!-- ── Pick which license file to download (main product + entitled add-ons) ── -->
        <AppModal :showModal="showDownloadModal" :onClose="closeDownloadModal" modalBodyClass="download-modal-body">
            <template #title><h4>{{ __('message.localized_license') }}</h4></template>
            <template #alert><AppAlert :componentName="MODAL_COMPONENT" /></template>
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
import Checkbox from '@/components/Reusable/FormField/Checkbox.vue'
import DatePicker from '@/components/Reusable/FormField/DatePicker.vue'
import OrderEntitlements from './components/OrderEntitlements.vue'
import Tooltip from '@/components/Reusable/Tooltip.vue'
import { useDateTime } from '@/core/composables/useDateTime'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import { makeRequestAdapter, invoiceTypeBadge } from '@/helpers/tableUtils'

const { formatDate } = useDateTime()

const { errors, setErrors, resetForm } = useForm()

const COMPONENT = 'orders-show'
// Separate name for the three modals below — keeps their alerts from also
// firing the page-level AppAlert (same store, matched by name) at the same time.
const MODAL_COMPONENT = 'orders-show-modal'

const baseUrl = useBaseUrl()

const route   = useRoute()
const orderId = route.params.id

const loading        = ref(true)
const copied         = ref(false)
const order          = ref(null)
const licenseDetails = ref(null)
const hasLicense     = ref(true) // false: the product issues no license (a service)
const isSubscribed   = ref(0)
const autoRenewState = ref('inactive')
const canRenew       = ref(false)
const canReissue     = ref(false)
const isCloud        = ref(false)
const paymentLog     = ref(null)
const tab            = ref('license')
const pluginLicenses = ref([])
const renewBlockedReason = ref('')
// The order's rights (GET /order/{id}/entitlements): features it has, features it could be given, dates.
const rights         = reactive({ entitlements: [], available: [], dates: [] })
const dating         = ref(null)

const saving = reactive({
    reissue:     false,
    renewal:     false,
    licenseMode: false,
    licenseEdit: false,
    date:        false,
})

const licenseEditModal = reactive({
    show:             false,
    limit:            null,
    agents:           null,
})


// ── Computed ───────────────────────────────────────────────────────────────
const userName = computed(() => {
    const u = order.value?.user
    if (!u) return '—'
    return `${u.first_name ?? ''} ${u.last_name ?? ''}`.trim() || '—'
})


const planName = computed(() => order.value?.subscription?.plan?.name ?? null)

const statusBadgeClass = computed(() => {
    const s = (order.value?.order_status ?? '').toLowerCase()
    if (s === 'executed') return 'bg-success'
    if (s === 'pending')  return 'bg-warning text-dark'
    if (s === 'failed')   return 'bg-danger'
    return 'bg-secondary'
})


// 0 in the license's last four digits means unlimited seats, not zero seats.
const agentsLabel = computed(() => {
    const a = licenseDetails.value?.agents
    if (a === null || a === undefined) return '—'
    return a === 0 ? __('message.unlimited') : a
})

// ── Helpers ────────────────────────────────────────────────────────────────
function capitalize(str) {
    return str ? str.charAt(0).toUpperCase() + str.slice(1) : ''
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

const showReissueModal = ref(false)

async function reissueLicense() {
    saving.reissue = true
    try {
        const res = await http.patch(`/reissue-license`, { id: orderId })
        successHandler(res, COMPONENT)
        await reload()
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        saving.reissue = false
        showReissueModal.value = false
    }
}

async function disableRenewal() {
    saving.renewal = true
    try {
        const res = await http.post(`/auto-renewal/${orderId}/disable`)
        successHandler(res, COMPONENT)
        await reload()
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
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
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        saving.licenseMode = false
    }
}

// ── License binding (domain + machine ID) before first download ────────────
const showBindingModal = ref(false)
const bindingBusy      = ref(false)
const bindingForm      = reactive({ activationCode: '' })
// null = main product's license file; otherwise the plugin product_id whose
// download triggered the binding modal, so it resumes the right one after.
const pendingDownloadProductId = ref(null)

// The activation code is "FLC1." + base64url(JSON {domain, machine_id, ...}); decoded only to
// refresh the bound state locally — billing re-validates and decodes it itself.
function decodeActivationCode(code) {
    try { return JSON.parse(atob(code.trim().slice(5).replace(/-/g, '+').replace(/_/g, '/'))) } catch { return {} }
}

function isLicenseBound() {
    // Binding stores either a domain OR an IP (LicenseService::parseIpAndDomain
    // puts an IP address in license_ip, not license_domain) — a domain-only
    // check wrongly says "not bound" for anyone who bound with an IP.
    return !!((licenseDetails.value?.license_domain || licenseDetails.value?.license_ip) && licenseDetails.value?.license_machine_id)
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
    bindingForm.activationCode = ''
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
    if (!bindingForm.activationCode) return
    bindingBusy.value = true
    try {
        const res = await http.post('/license-binding', {
            orderNo: order.value.number,
            activation_code: bindingForm.activationCode.trim(),
        })
        const bound = decodeActivationCode(bindingForm.activationCode)
        licenseDetails.value.license_domain     = bound.domain
        licenseDetails.value.license_machine_id = bound.machine_id
        successHandler(res, MODAL_COMPONENT)
        closeBindingModal()
        triggerDownload(pendingDownloadProductId.value)
        showDownloadModal.value = true
    } catch (e) {
        errorHandler(e, MODAL_COMPONENT, { setErrors })
    } finally {
        bindingBusy.value = false
    }
}

// ── License details edit modal ─────────────────────────────────────────────
function openLicenseEditModal() {
    licenseEditModal.limit            = licenseDetails.value?.installation_limit ?? null
    licenseEditModal.agents           = licenseDetails.value?.agents ?? null
    licenseEditModal.show             = true
    resetForm()
}

async function saveLicenseEdit() {
    if (!await validateForm(licenseDetailsSchema, licenseEditModal, setErrors)) return
    saving.licenseEdit = true
    try {
        const res = await http.post(`/update-license-details`, {
            orderid:          orderId,
            limit:            licenseEditModal.limit,
            agents:           licenseEditModal.agents,
        })
        licenseEditModal.show = false
        successHandler(res, COMPONENT)
        await reload()
    } catch (e) {
        errorHandler(e, MODAL_COMPONENT, { setErrors })
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
    hasLicense.value     = d.has_license !== false
    isSubscribed.value   = d.is_subscribed
    autoRenewState.value = d.auto_renew_state
    canRenew.value       = !!d.can_renew
    renewBlockedReason.value = d.renew_blocked_reason || ''
    canReissue.value     = !!d.can_reissue
    isCloud.value        = !!d.is_cloud
    paymentLog.value     = d.payment_log
    await Promise.all([loadPluginLicenses(), loadRights()])
}

async function loadRights() {
    try {
        const res = await http.get(`/order/${orderId}/entitlements`)
        const d = res.data?.data ?? {}
        rights.entitlements = d.entitlements ?? []
        rights.available    = d.available ?? []
        rights.dates        = d.dates ?? []
    } catch (e) {
        // An order with neither a license nor a subscription has no rights to show.
        if (e?.response?.status !== 404) errorHandler(e, COMPONENT)
        rights.entitlements = []
        rights.available    = []
        rights.dates        = []
    }
}

// ── Dates (license end, updates, support, …) ──────────────────────────────
const DAY = 24 * 60 * 60 * 1000

/** "Ended", or how long is left when it ends within a year; nothing for "never". */
function dateHint(d) {
    if (!d.ends_at) return null
    const days = Math.ceil((new Date(`${d.ends_at}T23:59:59`) - new Date()) / DAY)
    if (days < 0) return { text: __('message.order_date_ended'), class: 'text-danger fw-semibold' }
    if (days <= 30) return { text: __('message.order_date_in_days', { days }), class: 'text-warning-emphasis' }
    return days <= 365 ? { text: __('message.order_date_in_days', { days }), class: 'text-muted' } : null
}

function openDate(d) {
    dating.value = { ...d, never: !d.ends_at, date: d.ends_at }
}

function closeDate() {
    if (!saving.date) dating.value = null
}

async function saveDate() {
    saving.date = true
    try {
        const res = await http.put(`/order/${orderId}/dates/${dating.value.code}`, {
            date: dating.value.never ? null : (dating.value.date || null), // DatePicker gives YYYY-MM-DD
        })
        successHandler(res, COMPONENT)
        dating.value = null
        await loadRights()
    } catch (e) {
        errorHandler(e, MODAL_COMPONENT)
    } finally {
        saving.date = false
    }
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
        tab.value = hasLicense.value ? 'license' : 'features'
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
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
        version:          __('message.version'),
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
        last_active_date: (f, row) => formatDate(row.last_active_date),
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
const invoiceColumns = ['number', 'products', 'date', 'amount', 'status', 'type']
const invoiceTableOptions = reactive({
    headings: {
        number:   __('message.invoice_no'),
        products: __('message.products'),
        date:     __('message.date'),
        amount:   __('message.total'),
        status:   __('message.status'),
        type:     __('message.type'),
    },
    columnsClasses: {
        number:   'dt-number',
        products: 'dt-name',
        date:     'dt-date',
        amount:   'dt-amount',
        status:   'dt-status',
        type:     'dt-status',
    },
    templates: {
        number:   (f, row) => row.number && row.id ? h(RouterLink, { to: '/invoices/' + row.id }, () => row.number) : (row.number || '—'),
        products: (f, row) => (row.products ?? []).join(', ') || '—',
        date:     (f, row) => formatDate(row.date),
        status:   (f, row) => h('span', {
            class: row.status?.toLowerCase() === 'success' ? 'badge bg-success' : 'badge bg-secondary',
        }, row.status),
        type:     (f, row) => invoiceTypeBadge(row.type),
    },
    sortable:   ['date', 'number', 'amount', 'status'],
    filterable: false,
    requestAdapter(data) {
        const columnMap = { amount: 'grand_total' }
        return {
            'sort-field':   (columnMap[data.orderBy] ?? data.orderBy) || 'date',
            'sort-order':   data.orderBy && data.ascending ? 'asc' : 'desc',
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
        invoice_number: (f, row) => (row.invoices ?? []).length
            ? h('span', (row.invoices ?? []).flatMap((inv, i) => [
                ...(i ? [', '] : []),
                h(RouterLink, { to: '/invoices/' + inv.id }, () => inv.number),
            ]))
            : (row.invoice_number || '—'),
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
/* Key facts under the order title: a quiet label, then the value people come for. */
.order-facts dt { font-size: 0.8rem; font-weight: 500; color: var(--bs-secondary-color); margin-bottom: 0.15rem; }
.order-facts dd { font-size: 1rem; font-weight: 600; }
.order-facts dd.small { font-weight: 400; font-size: 0.8rem; }
.order-status { font-size: 0.8rem; font-weight: 600; }
.order-tabs .nav-link { white-space: nowrap; }
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
.license-code {
    /* A flex item won't shrink below its content's natural width by default
       (min-width: auto), so a long unbroken license code just overflows the
       row instead of wrapping — override that and let it break anywhere. */
    min-width: 0;
    overflow-wrap: anywhere;
    word-break: break-all;
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
