<template>
    <div>
        <AppAlert :componentName="COMPONENT" />

        <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

        <template v-else>
            <!-- License ended / ending soon: said once, at the top -->
            <div v-if="term && term.tone !== 'good'" class="alert d-flex align-items-start gap-2" :class="term.tone === 'bad' ? 'alert-danger' : 'alert-warning'" role="status">
                <i class="fas fa-triangle-exclamation mt-1" aria-hidden="true"></i>
                <div>
                    <strong>{{ term.tone === 'bad' ? __('message.order_alert_ended', { date: term.endText }) : __('message.order_alert_ending', { date: term.endText }) }}</strong>
                    <div class="small">{{ __('message.order_alert_body') }}</div>
                </div>
            </div>

            <!-- Announces "Copied" for the copy buttons -->
            <span class="visually-hidden" aria-live="polite">{{ copyNotice }}</span>

            <!-- ── 1. Order summary ── -->
            <div class="card card-light">
                <div class="card-header">
                    <h4 class="card-title d-flex flex-wrap align-items-center gap-2">
                        <span translate="no">{{ __('message.order_number_title', { number: order?.number }) }}</span>
                        <action-button variant="tool" class="p-0" :iconOnly="true" :icon="copiedFlags.number ? 'fas fa-check' : 'far fa-copy'"
                                       :label="__('message.order_copy_number')" v-tooltip="copiedFlags.number ? __('message.copied') : __('message.order_copy_number')"
                                       @click="copyText(order?.number, 'number')" />
                        <span class="badge" :class="BADGE[statusTone]">{{ order?.order_status || '—' }}</span>
                        <span class="badge bg-info"><i :class="isCloud ? 'fas fa-cloud' : 'fas fa-server'" class="me-1" aria-hidden="true"></i>{{ isCloud ? __('message.order_type_cloud') : __('message.order_type_self_hosted') }}</span>
                    </h4>
                    <div class="card-tools d-flex align-items-center gap-1">
                        <template v-if="canRenew">
                            <action-button v-if="!renewBlockedReason" :to="`/orders/${orderId}/renew`" variant="light" class="btn-xs" icon="fas fa-rotate" :label="__('message.renew_order')" />
                            <span v-else tabindex="0" v-tooltip="renewBlockedReason" :aria-label="`${__('message.renew_order')}: ${renewBlockedReason}`">
                                <action-button variant="light" class="btn-xs" icon="fas fa-rotate" :label="__('message.renew_order')" :disabled="true" />
                            </span>
                        </template>
                        <!-- Destructive, so it sits last and asks first -->
                        <action-button action="delete" variant="light" class="btn-xs" :label="__('message.order_delete_button')" @click="showDeleteModal = true" />
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Product -->
                        <div class="col-md-6 col-xl-3">
                            <div class="border rounded p-3 h-100">
                                <span class="form-label fw-bold d-block">{{ __('message.product') }}</span>
                                <router-link v-if="order?.product_relation" :to="`/products/${order.product_relation.id}/edit`">{{ order.product_relation.name }}</router-link>
                                <span v-else>—</span>
                                <div v-if="planName" class="small text-muted">
                                    {{ __('message.plan') }}:
                                    <router-link v-if="order.subscription.plan.id" :to="`/products/plans/${order.subscription.plan.id}/edit`">{{ planName }}</router-link>
                                    <template v-else>{{ planName }}</template>
                                </div>
                                <div class="small text-muted">{{ __('message.order_ordered_on') }}: {{ order?.created_at ? formatDate(order.created_at) : '—' }}</div>
                            </div>
                        </div>

                        <!-- Customer -->
                        <div class="col-md-6 col-xl-3">
                            <div class="border rounded p-3 h-100">
                                <span class="form-label fw-bold d-block">{{ __('message.customer') }}</span>
                                <router-link v-if="order?.user?.id" :to="`/users/${order.user.id}`">{{ userName }}</router-link>
                                <span v-else>{{ userName }}</span>
                                <div v-if="order?.user?.email" class="small text-muted d-flex align-items-center gap-1 text-break">
                                    {{ order.user.email }}
                                    <action-button variant="tool" class="p-0" :iconOnly="true" :icon="copiedFlags.email ? 'fas fa-check' : 'far fa-copy'"
                                                   :label="__('message.copy')" v-tooltip="copiedFlags.email ? __('message.copied') : __('message.copy')"
                                                   @click="copyText(order.user.email, 'email')" />
                                </div>
                            </div>
                        </div>

                        <!-- License ends -->
                        <div v-if="term" class="col-md-6 col-xl-3">
                            <div class="border rounded p-3 h-100">
                                <span class="form-label fw-bold d-flex justify-content-between">
                                    {{ __('message.order_subscription_ends') }}
                                    <action-button variant="tool" class="p-0" :iconOnly="true" icon="fas fa-pen" :label="__('message.order_change_date')" v-tooltip="__('message.order_change_date')" @click="openDate(term.date)" />
                                </span>
                                {{ term.endText }}
                                <span v-if="term.hint" class="badge ms-1" :class="BADGE[term.tone]">{{ term.hint }}</span>
                                <p v-if="!term.date.ends_at" class="text-muted small mb-0 mt-1">{{ __('message.order_no_end_date') }}</p>
                            </div>
                        </div>

                        <!-- Auto renewal: hidden when nothing on the order expires -->
                        <div v-if="canRenew" class="col-md-6 col-xl-3">
                            <div class="border rounded p-3 h-100">
                                <span class="form-label fw-bold d-flex justify-content-between align-items-center">
                                    <span id="order-auto-renewal-label">{{ __('message.auto_renewal') }}</span>
                                    <Switch :key="`renew-${switchKey}`" name="auto_renewal" labelledby="order-auto-renewal-label" :value="isSubscribed == 1"
                                            :disabled="isSubscribed != 1 || saving.renewal" :onChange="askRenewalOff" />
                                </span>
                                <span class="badge" :class="RENEW_BADGE[autoRenewState] ?? 'bg-secondary'">{{ renewStateLabel }}</span>
                                <p class="text-muted small mb-0 mt-1">
                                    <template v-if="isSubscribed && paymentLog?.payment_method">{{ capitalize(paymentLog.payment_method) }}<template v-if="paymentLog.date">, {{ __('message.order_since', { date: formatDate(paymentLog.date) }) }}</template></template>
                                    <template v-else>{{ __('message.order_manual_renewal') }}</template>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- ── 2. License and installations ── -->
                    <div v-if="hasLicense" class="row mt-4">
                        <div class="col-lg-5 d-flex">
                            <div class="card card-light flex-fill">
                                <div class="card-header">
                                    <h4 class="card-title"><i class="fas fa-key me-2 text-muted" aria-hidden="true"></i>{{ __('message.order_license_binding') }}</h4>
                                </div>
                                <div class="card-body">
                                    <div class="border rounded p-3 mb-3">
                                        <span class="form-label fw-bold d-flex justify-content-between align-items-center">
                                            {{ __('message.order_license_key') }}
                                            <action-button variant="tool" class="p-0" :icon="keyHidden ? 'far fa-eye' : 'far fa-eye-slash'" :aria-pressed="!keyHidden"
                                                           :label="keyHidden ? __('message.order_show') : __('message.order_hide')" @click="keyHidden = !keyHidden" />
                                        </span>
                                        <div class="d-flex align-items-center justify-content-between gap-2">
                                            <code class="fs-5 text-body text-break" translate="no" :aria-hidden="keyHidden || undefined">{{ keyText }}</code>
                                            <span v-if="keyHidden" class="visually-hidden">{{ __('message.order_key_hidden', { last: keyChunks.at(-1) }) }}</span>
                                            <action-button variant="light" class="btn-xs" :icon="copied ? 'fas fa-check' : 'far fa-copy'" :label="copied ? __('message.copied') : __('message.copy')" @click="copyLicenseCode" />
                                        </div>
                                    </div>

                                    <div v-if="!isCloud" class="border rounded p-3">
                                        <span class="form-label fw-bold d-flex justify-content-between align-items-center">
                                            <span class="d-flex align-items-center gap-1">
                                                <span id="order-license-mode-label">{{ __('message.localized_license') }}</span>
                                                <Tooltip :message="__('message.localized_license_tooltip')" />
                                            </span>
                                            <Switch :key="`mode-${switchKey}`" name="license_mode" labelledby="order-license-mode-label" :value="order?.license_mode === 'File'"
                                                    :disabled="saving.licenseMode" :onChange="askLicenseMode" />
                                        </span>
                                        <div v-if="order?.license_mode === 'File'" class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                            <span v-if="isLicenseBound()" class="badge bg-success text-wrap text-start">
                                                <i class="fas fa-link me-1" aria-hidden="true"></i>{{ __('message.order_bound_to') }} <span translate="no">{{ licenseDetails.license_domain || licenseDetails.license_ip }}</span>
                                            </span>
                                            <span v-else class="badge bg-warning text-dark text-wrap text-start"><i class="far fa-clock me-1" aria-hidden="true"></i>{{ __('message.order_not_bound') }}</span>
                                            <action-button action="download" variant="light" class="btn-xs" :label="__('message.download_license_file')" @click="handleDownloadClick" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7 d-flex">
                            <div class="card card-light flex-fill">
                                <div class="card-header">
                                    <h4 class="card-title">
                                        <i class="fas fa-server me-2 text-muted" aria-hidden="true"></i>{{ __('message.installations') }}
                                        <span class="badge ms-2" :class="limitReached ? 'bg-warning text-dark' : 'bg-secondary'">{{ __('message.order_installs_of', { used: installs.length, limit: limitLabel }) }}</span>
                                    </h4>
                                    <div class="card-tools d-flex gap-1">
                                        <action-button v-if="canReissue" variant="light" class="btn-xs" icon="fas fa-id-card-alt" :label="__('message.reissue_license')"
                                                       v-tooltip="__('message.reissuing_license')" @click="showReissueModal = true" />
                                        <action-button variant="light" class="btn-xs" icon="fas fa-sliders" :label="__('message.order_edit_limit')" @click="openLicenseEditModal" />
                                    </div>
                                </div>
                                <div class="card-body">
                                    <ul class="nav nav-tabs mb-3" role="tablist" :aria-label="__('message.installations')"
                                        @keydown="onTabKeys($event, INSTALL_TABS, installTab, selectInstallTab, 'installs-tab-')">
                                        <li v-for="t in INSTALL_TABS" :key="t" class="nav-item" role="presentation">
                                            <a href="#" class="nav-link" :class="{ active: installTab === t }" role="tab"
                                               :id="`installs-tab-${t}`" :aria-controls="`installs-panel-${t}`" :aria-selected="installTab === t"
                                               :tabindex="installTab === t ? 0 : -1" @click.prevent="selectInstallTab(t)">
                                                <i :class="t === 'current' ? 'fas fa-server' : 'fas fa-clock-rotate-left'" class="me-1" aria-hidden="true"></i>
                                                {{ t === 'current' ? __('message.order_current_installs') : __('message.order_tab_installations') }}
                                                <span v-if="t === 'current'" class="badge bg-secondary ms-1">{{ installs.length }}</span>
                                            </a>
                                        </li>
                                    </ul>

                                    <div v-show="installTab === 'current'" id="installs-panel-current" role="tabpanel" aria-labelledby="installs-tab-current">
                                        <div v-if="!installs.length" class="text-center text-muted py-4">
                                            <i class="fas fa-server fa-2x mb-2 opacity-50" aria-hidden="true"></i>
                                            <p class="fw-semibold mb-1">{{ __('message.order_not_installed') }}</p>
                                            <p class="small mb-0">{{ __('message.order_not_installed_hint') }}</p>
                                        </div>
                                        <div v-else class="table-responsive">
                                            <table class="table table-hover table-striped table-bordered align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('message.installation_path') }}</th>
                                                        <th>{{ __('message.version') }}</th>
                                                        <th>{{ __('message.status') }}</th>
                                                        <th>{{ __('message.last_active') }}</th>
                                                        <th>{{ __('message.action') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="i in installs" :key="i.id">
                                                        <td class="text-break" translate="no">
                                                            {{ i.domain || i.ip || '—' }}
                                                            <small v-if="i.domain && i.ip" class="d-block text-muted">{{ i.ip }}</small>
                                                        </td>
                                                        <td class="tabular-nums" translate="no">{{ i.version || '—' }}</td>
                                                        <td><span class="badge" :class="i.active ? 'bg-success' : 'bg-secondary'">{{ i.active ? __('message.active') : __('message.inactive') }}</span></td>
                                                        <td class="text-nowrap tabular-nums">{{ i.last_active ? formatDate(i.last_active) : '—' }}</td>
                                                        <td>
                                                            <action-button variant="light" class="table_btn" icon="fas fa-link-slash" :iconOnly="true"
                                                                           :label="`${__('message.order_unbind')} ${i.domain || i.ip}`" v-tooltip="__('message.order_unbind')" @click="unbinding = i" />
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Every server that ever checked in; loads on first open -->
                                    <div v-if="historySeen" v-show="installTab === 'history'" id="installs-panel-history" role="tabpanel" aria-labelledby="installs-tab-history">
                                        <DataTable ref="historyTable" :url="`${baseUrl}/get-installation-details/${orderId}`" :dataColumns="historyColumns" :option="historyTableOptions" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── 3. Billing and features ── -->
                    <div class="card card-light">
                        <div class="card-header p-0 border-bottom-0">
                            <ul class="nav nav-tabs px-3 pt-2" role="tablist" @keydown="onTabKeys($event, tabs.map(t => t.key), tab, selectTab, 'order-tab-')">
                                <li v-for="t in tabs" :key="t.key" class="nav-item" role="presentation">
                                    <a href="#" class="nav-link" :class="{ active: tab === t.key }" role="tab"
                                       :id="`order-tab-${t.key}`" :aria-controls="`order-panel-${t.key}`"
                                       :aria-selected="tab === t.key" :tabindex="tab === t.key ? 0 : -1" @click.prevent="selectTab(t.key)">
                                        <i :class="t.icon + ' me-1'" aria-hidden="true"></i>{{ t.label }}
                                        <span v-if="t.count" class="badge bg-secondary ms-1">{{ t.count }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <!-- Tables mount on first visit -->
                            <div v-if="seen.billing" v-show="tab === 'billing'" id="order-panel-billing" role="tabpanel" aria-labelledby="order-tab-billing">
                                <h5>{{ __('message.invoices') }}</h5>
                                <div class="table-responsive mb-4">
                                    <DataTable :url="`${baseUrl}/getOrderInvoices/${orderId}`" :dataColumns="invoiceColumns" :option="invoiceTableOptions" />
                                </div>
                                <h5>{{ __('message.order_tab_payments') }}</h5>
                                <div class="table-responsive">
                                    <DataTable :url="`${baseUrl}/getOrderPayments/${orderId}`" :dataColumns="paymentColumns" :option="paymentTableOptions" />
                                </div>
                            </div>

                            <div v-show="tab === 'features'" id="order-panel-features" role="tabpanel" aria-labelledby="order-tab-features">
                                <OrderEntitlements :orderId="orderId" :items="rights.entitlements" :available="rights.available" :dates="featureDates"
                                                   @changed="loadRights" @edit-date="openDate" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <DeleteModal
            v-if="showDeleteModal"
            :showModal="true"
            :onClose="() => (showDeleteModal = false)"
            :deleteUrl="`${baseUrl}/orders`"
            :deleteData="{ order_ids: [Number(orderId)] }"
            :title="__('message.confirm_delete')"
            :message="__('message.order_delete')"
            componentName="orders-index"
            @deleted="router.push('/orders')"
        />

        <!-- ── Unbind one install: frees its slot ── -->
        <DeleteModal
            v-if="unbinding"
            :showModal="true"
            :onClose="() => (unbinding = null)"
            :deleteUrl="`/order/${orderId}/installations/${unbinding.id}`"
            :componentName="COMPONENT"
            :title="__('message.order_unbind_title', { host: unbinding.domain || unbinding.ip })"
            :message="__('message.order_unbind_body')"
            :btnLabel="__('message.order_unbind')"
            btnIcon="fa-link-slash"
            @deleted="onUnbound"
        />

        <!-- ── Confirm a switch that changes the license or billing right away ── -->
        <AppModal :showModal="!!confirming" :onClose="cancelConfirm" :showCloseBtn="false">
            <template #title><h4>{{ confirming?.title }}</h4></template>
            <template #fields><p class="mb-0">{{ confirming?.body }}</p></template>
            <template #controls>
                <action-button action="cancel" @click="cancelConfirm" />
                <action-button action="confirm" :label="confirming?.label" :loading="saving.renewal || saving.licenseMode" @click="runConfirm" />
            </template>
        </AppModal>

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
            <template #title><h4>{{ __('message.installation_limit') }}</h4></template>
            <template #alert><AppAlert :componentName="MODAL_COMPONENT" /></template>
            <template #fields>
                <TextField name="limit" type="number" :label="__('message.installation_limit')"
                           :value="String(licenseEditModal.limit ?? '')" :error="errors.limit"
                           :onChange="v => (licenseEditModal.limit = v)" />
            </template>
            <template #controls>
                <action-button action="save" :loading="saving.licenseEdit" @click="saveLicenseEdit" />
            </template>
        </AppModal>

        <!-- ── Reissue License confirmation ── -->
        <AppModal :showModal="showReissueModal" :onClose="() => showReissueModal = false" :showCloseBtn="false">
            <template #title><h4>{{ __('message.order_reissue_title') }}</h4></template>
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
                <TextField name="activation_code" type="textarea" rows="4" :label="__('message.activation_code')"
                           :placeholder="__('message.enter_activation_code')" :value="bindingForm.activationCode"
                           :spellcheck="false" autocomplete="off" :error="bindingError"
                           :onChange="v => { bindingForm.activationCode = v; bindingError = '' }" />
            </template>
            <template #controls>
                <action-button action="confirm"
                               :label="__('message.save_and_download')"
                               :loading="bindingBusy"
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
                        <action-button action="download" variant="light" :iconOnly="true" :label="__('message.download_license_file')"
                            v-tooltip="__('message.download_license_file')" @click="selectDownload(null)" />
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
                            <action-button action="download" variant="light" :iconOnly="true" :label="__('message.download_license_file')"
                                v-tooltip="__('message.download_license_file')" @click="selectDownload(plugin.id)" />
                        </li>
                    </ul>
                </template>
            </template>
        </AppModal>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, h, nextTick } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'
import http from '@/plugins/axios'
import { errorHandler, successHandler } from '@/helpers/responseHandler.js'
import { useForm } from 'vee-validate'
import { validateForm } from '@/helpers/formUtils.js'
import { licenseDetailsSchema } from '@/validations/admin/orderValidations.js'
import Switch from '@/components/Reusable/FormField/Switch.vue'
import TextField from '@/components/Reusable/FormField/TextField.vue'
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
const MODAL_COMPONENT = 'orders-show-modal'

const baseUrl = useBaseUrl()

const route   = useRoute()
const router  = useRouter()
const orderId = route.params.id

const loading           = ref(true)
const copied            = ref(false)
const copiedFlags       = reactive({ number: false, email: false })
const keyHidden         = ref(false)
const order             = ref(null)
const licenseDetails    = ref(null)
const hasLicense        = ref(true)
const isSubscribed      = ref(0)
const autoRenewState    = ref('inactive')
const canRenew          = ref(false)
const canReissue        = ref(false)
const isCloud           = ref(false)
const paymentLog        = ref(null)
const installs          = ref([]) // [{ domain, ip, version, active, last_active }], most recently seen first
// Opening tab comes from ?tab=, defaulting to Billing.
const tab               = ref(route.query?.tab === 'features' ? 'features' : 'billing')
const pluginLicenses    = ref([])
const renewBlockedReason = ref('')
const rights            = reactive({ entitlements: [], available: [], dates: [] })
const dating            = ref(null)

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
})

// ── Computed ───────────────────────────────────────────────────────────────
const userName = computed(() => {
    const u = order.value?.user
    if (!u) return '—'
    return `${u.first_name ?? ''} ${u.last_name ?? ''}`.trim() || '—'
})

// The license and its installs sit above in plain view; the long lists go in tabs.
const tabs = computed(() => [
    { key: 'billing', icon: 'fas fa-file-invoice-dollar', label: __('message.order_tab_billing') },
    { key: 'features', icon: 'fas fa-list-check', label: __('message.order_tab_features'), count: rights.entitlements.length + featureDates.value.length },
])

// The open tab lives in the URL (?tab=billing) so a reload or a shared link keeps it.
const seen = reactive({})
function selectTab(key) {
    tab.value = key
    seen[key] = true
    router.replace({ query: { ...route.query, tab: key === 'billing' ? undefined : key } })
}

const planName = computed(() => order.value?.subscription?.plan?.name ?? null)

// Tone → Bootstrap badge background.
const BADGE = { good: 'bg-success', warn: 'bg-warning text-dark', bad: 'bg-danger', muted: 'bg-secondary' }

const statusTone = computed(() => ({ executed: 'good', pending: 'warn', failed: 'bad' }[(order.value?.order_status ?? '').toLowerCase()] ?? 'muted'))

// Keys are read in groups of four, so show them that way; copying still copies the raw code.
const keyChunks = computed(() => (licenseDetails.value?.licence_code || '—').match(/.{1,4}/g) ?? ['—'])

// One polite announcement for whichever copy button was used
const copyNotice = computed(() => (copied.value || copiedFlags.number || copiedFlags.email ? __('message.copied') : ''))

// Masked keeps the last group, so the key can still be told apart.
const keyText = computed(() => keyHidden.value
    ? [...keyChunks.value.slice(0, -1).map(() => '••••'), keyChunks.value.at(-1)].join(' ')
    : keyChunks.value.join(' '))

const RENEW_BADGE = { active: 'bg-success', pending: 'bg-warning text-dark', enabled: 'bg-info text-dark' }
const renewStateLabel = computed(() => ({
    active: __('message.active'), pending: __('message.pending_authorization'), enabled: __('message.enabled'),
}[autoRenewState.value] ?? __('message.inactive')))

const limitLabel = computed(() => {
    const l = licenseDetails.value?.installation_limit
    if (l === null || l === undefined) return '—'
    return Number(l) === 0 ? __('message.unlimited') : l
})

// Every slot taken (0 = unlimited, never full).
const limitReached = computed(() => {
    const l = Number(licenseDetails.value?.installation_limit)
    return l > 0 && installs.value.length >= l
})

// ── Helpers ────────────────────────────────────────────────────────────────
function capitalize(str) {
    return str ? str.charAt(0).toUpperCase() + str.slice(1) : ''
}

// ── Actions ────────────────────────────────────────────────────────────────
async function copyLicenseCode(e) {
    const code = licenseDetails.value?.licence_code
    if (!code) return
    try {
        await navigator.clipboard.writeText(code)
        copied.value = true
        if (e?.target?.blur) e.target.blur()
        setTimeout(() => { copied.value = false }, 2000)
    } catch {
        // clipboard fallback
    }
}

function copyText(text, flag) {
    if (!text) return
    navigator.clipboard?.writeText(String(text)).then(() => {
        copiedFlags[flag] = true
        setTimeout(() => { copiedFlags[flag] = false }, 2000)
    }).catch(() => {})
}

const unbinding    = ref(null) // the install whose Unbind is being confirmed
const historyTable = ref(null)
// Current / History inside the Installations card; History lives in the URL (?installs=history).
const INSTALL_TABS = ['current', 'history']
const installTab   = ref(route.query?.installs === 'history' ? 'history' : 'current')
const historySeen  = ref(installTab.value === 'history')

function selectInstallTab(key) {
    installTab.value = key
    if (key === 'history') historySeen.value = true
    router.replace({ query: { ...route.query, installs: key === 'history' ? key : undefined } })
}

// Arrow keys, Home and End move between tabs, as in any tab list.
function onTabKeys(e, keys, current, select, idPrefix) {
    const i = keys.indexOf(current)
    const next = { ArrowRight: i + 1, ArrowLeft: i - 1, Home: 0, End: keys.length - 1 }[e.key]
    if (next === undefined) return
    e.preventDefault()
    const key = keys[(next + keys.length) % keys.length]
    select(key)
    nextTick(() => document.getElementById(`${idPrefix}${key}`)?.focus())
}

// ── Switches that change billing or the license at once ask first ─────────
const confirming = ref(null) // { title, body, label, run }
const switchKey  = ref(0)    // bumped on cancel so the switch flips back

function askRenewalOff(on) {
    if (on) return
    confirming.value = {
        title: __('message.order_renew_off_title'),
        body:  __('message.order_renew_off_body'),
        label: __('message.order_renew_off_confirm'),
        run:   disableRenewal,
    }
}

function askLicenseMode(on) {
    confirming.value = {
        title: __(on ? 'message.order_file_on_title' : 'message.order_file_off_title'),
        body:  __(on ? 'message.order_file_on_body' : 'message.order_file_off_body'),
        label: __(on ? 'message.order_file_on_confirm' : 'message.order_file_off_confirm'),
        run:   () => toggleLicenseMode(on),
    }
}

function cancelConfirm() {
    if (saving.renewal || saving.licenseMode) return
    confirming.value = null
    switchKey.value++
}

async function runConfirm() {
    await confirming.value?.run()
    confirming.value = null
    switchKey.value++
}

async function onUnbound() {
    unbinding.value = null
    await reload()
    historyTable.value?.refresh()
}

const showReissueModal = ref(false)
const showDeleteModal  = ref(false)

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
const bindingError     = ref('')
const pendingDownloadProductId = ref(null)

function decodeActivationCode(code) {
    try { return JSON.parse(atob(code.trim().slice(5).replace(/-/g, '+').replace(/_/g, '/'))) } catch { return {} }
}

function isLicenseBound() {
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
    bindingError.value = ''
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
    if (!isLicenseBound()) {
        showDownloadModal.value = false
    }
    requestDownload(productId)
}

function closeBindingModal() {
    showBindingModal.value = false
}

async function submitBinding() {
    if (!bindingForm.activationCode.trim()) {
        bindingError.value = __('message.order_activation_code_required')
        return
    }
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
    installs.value       = d.installations ?? []
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
        if (e?.response?.status !== 404) errorHandler(e, COMPONENT)
        rights.entitlements = []
        rights.available    = []
        rights.dates        = []
    }
}

// ── Dates (license end, updates, support, …) ──────────────────────────────
const DAY = 24 * 60 * 60 * 1000

function dateHint(d) {
    if (!d.ends_at) return null
    const days = Math.ceil((new Date(`${d.ends_at}T23:59:59`) - new Date()) / DAY)
    if (days < 0) return { text: __('message.order_date_ended'), class: 'text-danger fw-semibold' }
    const text = days === 1 ? __('message.order_date_in_one_day') : __('message.order_date_in_days', { days })
    if (days <= 30) return { text, class: 'text-warning-emphasis' }
    return days <= 365 ? { text, class: 'text-muted' } : null
}

// Each end date with its hint and tone: ended is bad, within 30 days warn.
const toned = d => {
    const hint = dateHint(d)
    const tone = !hint ? 'good' : hint.class.includes('danger') ? 'bad' : hint.class.includes('warning') ? 'warn' : 'good'
    return { ...d, hint: hint?.text ?? null, tone }
}

// Updates, support, …: date features, listed and changed with the other features.
const featureDates = computed(() => rights.dates.filter(d => d.code !== 'license').map(toned))

// The license end, shown with the order: its date, and "in N days" / "Ended" when that matters.
const term = computed(() => {
    const date = rights.dates.find(d => d.code === 'license')
    if (!date) return null
    const t = toned(date)
    return { date, tone: t.tone, hint: t.hint, endText: date.ends_at ? formatDate(date.ends_at) : __('message.order_date_never') }
})

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
            date: dating.value.never ? null : (dating.value.date || null),
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
        seen[tab.value] = true
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        loading.value = false
    }
})

// ── Installation history table: every server that ever checked in ──────────
const historyColumns = ['domain', 'version', 'active', 'last_active']
const historyTableOptions = reactive({
    headings: {
        domain:      __('message.installation_path'),
        version:     __('message.version'),
        active:      __('message.status'),
        last_active: __('message.last_active'),
    },
    templates: {
        domain:      (f, row) => h('span', { translate: 'no' }, [row.domain || row.ip || '—', row.domain && row.ip ? h('small', { class: 'd-block text-muted' }, row.ip) : null]),
        version:     (f, row) => row.version || '—',
        active:      (f, row) => h('span', { class: row.active ? 'badge bg-success' : 'badge bg-secondary' }, row.active ? __('message.active') : __('message.inactive')),
        last_active: (f, row) => row.last_active ? formatDate(row.last_active) : '—',
    },
    sortable:   [],
    filterable: false,
    requestAdapter: data => ({ page: data.page, limit: data.limit }),
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
/* Digits line up in dates, versions and amounts */
.tabular-nums { font-variant-numeric: tabular-nums; }
:deep(.download-modal-body) {
    max-height: 60vh;
    overflow-y: auto;
}
.download-section-label {
    font-weight: 600;
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 0.5rem;
}
</style>
