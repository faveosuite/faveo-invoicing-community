<template>
    <div>
        <AppAlert :componentName="COMPONENT" />

        <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

        <div v-else-if="user" class="row">

            <!-- ── Left sidebar ────────────────────────────────────────────── -->
            <div class="col-md-4">
                <div class="card card-light card-outline">
                    <div class="card-body box-profile">

                        <div class="text-center">
                            <img
                                :src="user.profile_pic || fallbackAvatar"
                                class="profile-user-img img-fluid img-circle"
                                alt="User avatar"
                                @error="e => { e.target.onerror = null; e.target.src = fallbackAvatar }"
                            />
                        </div>

                        <h3 class="profile-username text-center" v-tooltip="user.full_name">
                            {{ user.full_name }}
                        </h3>

                        <p class="text-muted text-center">
                            <span
                                class="fas fa-envelope"
                                :class="user.email_verified ? 'icon-success' : 'icon-danger'"
                                v-tooltip="user.email_verified ? __('message.email_verified') : __('message.email_not_verified')"
                            ></span>
                            &nbsp;
                            <span
                                class="fas fa-mobile-alt"
                                :class="user.mobile_verified ? 'icon-success' : 'icon-danger'"
                                v-tooltip="user.mobile_verified ? __('message.mobile_verified') : __('message.mobile_not_verified')"
                            ></span>
                            &nbsp;
                            <span
                                class="fas fa-shield-alt"
                                :class="user.is_2fa_enabled ? 'icon-success' : 'icon-danger'"
                                v-tooltip="user.is_2fa_enabled ? __('message.2fa_enabled') : __('message.2fa_not_enabled')"
                            ></span>
                        </p>

                        <!-- Financial summary — shown above user details, all in
                             one row like before. Only change from the original:
                             every label now has a tooltip explaining what the
                             figure means (previously only Unused Amount did). -->
                        <div v-if="!loadingSummary" class="row text-center g-0 border rounded mb-3">
                            <div class="col py-2 border-end">
                                <div class="small text-muted" v-tooltip="__('message.invoice_total_description')">{{ __('message.invoice-total') || 'Invoiced' }}</div>
                                <div class="fw-bold small">{{ formatMoney(summary.invoice_total, summary.currency) }}</div>
                            </div>
                            <div class="col py-2 border-end">
                                <div class="small text-muted" v-tooltip="__('message.paid_description')">{{ __('message.paid') }}</div>
                                <div class="fw-bold small text-success">{{ formatMoney(summary.amount_paid, summary.currency) }}</div>
                            </div>
                            <div class="col py-2 border-end">
                                <div class="small text-muted" v-tooltip="__('message.balance_description')">{{ __('message.balance') }}</div>
                                <div class="fw-bold small" :class="summary.balance > 0 ? 'text-danger' : 'text-success'">
                                    {{ formatMoney(summary.balance, summary.currency) }}
                                </div>
                            </div>
                            <div class="col py-2 border-end" v-tooltip="__('message.unapplied_payment')">
                                <div class="small text-muted">{{ __('message.unapplied_balance') }}</div>
                                <div class="fw-bold small text-primary">{{ formatMoney(summary.unapplied_balance, summary.currency) }}</div>
                            </div>
                            <div class="col py-2" v-tooltip="__('message.credit_balance_description')">
                                <div class="small text-muted">{{ __('message.credit_balance') }}</div>
                                <div class="fw-bold small text-info">{{ formatMoney(summary.credit_balance, summary.currency) }}</div>
                            </div>
                        </div>
                        <div v-else class="row justify-content-center py-3"><loader :size="30" /></div>

                        <ul class="list-group list-group-unbordered mb-3">

                            <li class="list-group-item">
                                <div class="row align-items-center">
                                    <div class="col-sm-5">
                                        <span class="text-truncate mb-0 fw-bold" v-tooltip="__('message.user_name')">{{ __('message.user_name') }}</span>
                                    </div>
                                    <div class="col-sm-7 text-end nowrap-hidden">
                                        <span class="d-inline-block text-truncate align-middle truncate-with-copy" v-tooltip="user.user_name">{{ user.user_name }}</span>
                                        <a href="javascript:;" v-tooltip="__('message.copy')" @click.prevent="copy('username', user.user_name)" class="align-middle">
                                            <i class="far fa-copy"></i>
                                        </a>
                                        <span v-if="copied === 'username'" class="copy-clip">{{ __('message.copied') || 'Copied' }}</span>
                                    </div>
                                </div>
                            </li>

                            <li class="list-group-item">
                                <div class="row align-items-center">
                                    <div class="col-sm-5">
                                        <span class="text-truncate mb-0 fw-bold" v-tooltip="__('message.email')">{{ __('message.email') }}</span>
                                    </div>
                                    <div class="col-sm-7 text-end nowrap-hidden">
                                        <span class="d-inline-block text-truncate align-middle truncate-with-copy" v-tooltip="user.email">{{ user.email }}</span>
                                        <a href="javascript:;" v-tooltip="__('message.copy')" @click.prevent="copy('email', user.email)" class="align-middle">
                                            <i class="far fa-copy"></i>
                                        </a>
                                        <span v-if="copied === 'email'" class="copy-clip">{{ __('message.copied') || 'Copied' }}</span>
                                    </div>
                                </div>
                            </li>

                            <li v-if="user.role" class="list-group-item">
                                <div class="row">
                                    <div class="col-sm-5">
                                        <label class="text-truncate mb-0" v-tooltip="__('message.role')">{{ __('message.role') }}</label>
                                    </div>
                                    <div class="col-sm-7 text-end text-capitalize">{{ user.role }}</div>
                                </div>
                            </li>

                            <li v-if="user.company" class="list-group-item">
                                <div class="row">
                                    <div class="col-sm-5">
                                        <label class="text-truncate mb-0" v-tooltip="__('message.company')">{{ __('message.company') }}</label>
                                    </div>
                                    <div class="col-sm-7 text-end text-truncate" v-tooltip="user.company">{{ user.company }}</div>
                                </div>
                            </li>

                            <li v-if="user.bussiness" class="list-group-item">
                                <div class="row">
                                    <div class="col-sm-5">
                                        <label class="text-truncate mb-0">{{ __('message.business') }}</label>
                                    </div>
                                    <div class="col-sm-7 text-end">{{ user.bussiness?.name }}</div>
                                </div>
                            </li>

                            <li v-if="user.mobile" class="list-group-item">
                                <div class="row">
                                    <div class="col-sm-5">
                                        <label class="text-truncate mb-0" v-tooltip="__('message.mobile')">{{ __('message.mobile') }}</label>
                                    </div>
                                    <div class="col-sm-7 text-end">
                                        {{ user.mobile_code ? '+' + user.mobile_code + ' ' : '' }}{{ user.mobile }}
                                    </div>
                                </div>
                            </li>

                            <li v-if="user.address" class="list-group-item">
                                <div class="row">
                                    <div class="col-sm-5">
                                        <label class="text-truncate mb-0" v-tooltip="__('message.address')">{{ __('message.address') }}</label>
                                    </div>
                                    <div class="col-sm-7 text-end text-truncate" v-tooltip="user.address">{{ user.address }}</div>
                                </div>
                            </li>

                            <li v-if="user.town" class="list-group-item">
                                <div class="row">
                                    <div class="col-sm-5">
                                        <label class="text-truncate mb-0" v-tooltip="__('message.town')">{{ __('message.town') }}</label>
                                    </div>
                                    <div class="col-sm-7 text-end">{{ user.town }}</div>
                                </div>
                            </li>

                            <li v-if="user.state" class="list-group-item">
                                <div class="row">
                                    <div class="col-sm-5">
                                        <label class="text-truncate mb-0" v-tooltip="__('message.state')">{{ __('message.state') }}</label>
                                    </div>
                                    <div class="col-sm-7 text-end text-truncate" v-tooltip="user.state?.name">{{ user.state?.name }}</div>
                                </div>
                            </li>

                            <li v-if="user.country" class="list-group-item">
                                <div class="row">
                                    <div class="col-sm-5">
                                        <label class="text-truncate mb-0" v-tooltip="__('message.country')">{{ __('message.country') }}</label>
                                    </div>
                                    <div class="col-sm-7 text-end text-truncate" v-tooltip="user.country?.name">{{ user.country?.name }}</div>
                                </div>
                            </li>

                            <li v-if="user.zip" class="list-group-item">
                                <div class="row">
                                    <div class="col-sm-5">
                                        <label class="text-truncate mb-0" v-tooltip="__('message.zip')">{{ __('message.zip') }}</label>
                                    </div>
                                    <div class="col-sm-7 text-end">{{ user.zip }}</div>
                                </div>
                            </li>

                            <li v-if="user.timezone_id" class="list-group-item">
                                <div class="row">
                                    <div class="col-sm-5">
                                        <label class="text-truncate mb-0" v-tooltip="__('message.timezone')">{{ __('message.timezone') }}</label>
                                    </div>
                                    <div class="col-sm-7 text-end text-truncate" v-tooltip="user.timezone_id?.name">{{ user.timezone_id?.name }}</div>
                                </div>
                            </li>

                            <li v-if="user.skype" class="list-group-item">
                                <div class="row">
                                    <div class="col-sm-5">
                                        <label class="text-truncate mb-0">Skype</label>
                                    </div>
                                    <div class="col-sm-7 text-end text-truncate" v-tooltip="user.skype">{{ user.skype }}</div>
                                </div>
                            </li>

                            <li v-if="user.manager" class="list-group-item">
                                <div class="row">
                                    <div class="col-sm-5">
                                        <label class="text-truncate mb-0" v-tooltip="__('message.account_manager')">{{ __('message.account_manager') }}</label>
                                    </div>
                                    <div class="col-sm-7 text-end text-truncate" v-tooltip="user.manager?.name">{{ user.manager?.name }}</div>
                                </div>
                            </li>

                            <li v-if="user.account_manager" class="list-group-item">
                                <div class="row">
                                    <div class="col-sm-5">
                                        <label class="text-truncate mb-0" v-tooltip="__('message.account_manager')">{{ __('message.account_manager') }}</label>
                                    </div>
                                    <div class="col-sm-7 text-end text-truncate" v-tooltip="user.account_manager?.name">{{ user.account_manager?.name }}</div>
                                </div>
                            </li>

                        </ul>

                    </div>
                </div>
            </div>

            <!-- ── Right content ───────────────────────────────────────────── -->
            <div class="col-md-8">

                <!-- Tabs card -->
                <div class="card card-light card-outline">

                    <div class="card-header border-0">
                        <!-- Action buttons — float-end, btn-xs btn-light matching favMer -->
                        <div class="float-end user_view">
                            <RouterLink :to="`/users/${userId}/edit`" class="btn btn-xs btn-light">
                                <i class="fas fa-edit">&nbsp;</i>{{ __('message.edit') }}
                            </RouterLink>
                            <RouterLink :to="`/invoices/create?clientid=${userId}`" class="btn btn-xs btn-light">
                                <i class="fas fa-file-invoice">&nbsp;</i>{{ __('message.create_invoice') || 'Create Invoice' }}
                            </RouterLink>
                            <RouterLink :to="`/users/${userId}/payments/create`" class="btn btn-xs btn-light">
                                <i class="fas fa-money-bill">&nbsp;</i>{{ __('message.create-payment') }}
                            </RouterLink>
                            <action-button v-if="user.is_2fa_enabled" variant="light" class="btn-xs" icon="fas fa-ban" :label="__('message.disable_2fa') || 'Disable 2FA'" type="button" @click="disable2fa" />
                        </div>
                    </div>

                    <div class="card-body">

                        <!-- Tab nav -->
                        <ul class="nav nav-tabs" role="tablist">
                            <li v-for="tab in tabs" :key="tab.key" class="nav-item">
                                <a
                                    class="nav-link"
                                    :class="{ active: activeTab === tab.key }"
                                    href="javascript:;"
                                    role="tab"
                                    @click="activateTab(tab.key)"
                                >
                                    {{ tab.label }}
                                    <span v-if="summary[tab.countKey] != null" class="badge bg-primary">
                                        {{ summary[tab.countKey] }}
                                    </span>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div class="active tab-pane" role="tabpanel">
                                <div class="mt-3">

                                    <!-- Invoices -->
                                    <div v-show="activeTab === 'invoices'">
                                        <DataTable
                                            v-if="tabMounted.invoices"
                                            ref="invDtRef"
                                            :url="invoicesUrl"
                                            :dataColumns="invoiceColumns"
                                            :option="invoiceOptions"
                                        >
                                            <template #bulk-actions>
                                                <div v-if="selInvoices.length > 0" class="dropdown">
                                                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        {{ __('message.bulk_action') }}
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><button class="dropdown-item" @click="startBulkDelete('invoices')">{{ __('message.Delete') }}</button></li>
                                                    </ul>
                                                </div>
                                            </template>
                                        </DataTable>
                                    </div>

                                    <!-- Payments -->
                                    <div v-show="activeTab === 'payments'">
                                        <DataTable
                                            v-if="tabMounted.payments"
                                            ref="payDtRef"
                                            :url="paymentsUrl"
                                            :dataColumns="paymentColumns"
                                            :option="paymentOptions"
                                        >
                                            <template #bulk-actions>
                                                <div v-if="selPayments.length > 0" class="dropdown">
                                                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        {{ __('message.bulk_action') }}
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><button class="dropdown-item" @click="startBulkDelete('payments')">{{ __('message.Delete') }}</button></li>
                                                    </ul>
                                                </div>
                                            </template>
                                        </DataTable>
                                    </div>

                                    <!-- Credits -->
                                    <div v-show="activeTab === 'credits'">
                                        <!-- Credit never crosses currencies, so the spendable
                                             figure is per currency, not the single total above. -->
                                        <div v-if="summary.credit_balances?.length" class="d-flex flex-wrap gap-2 mb-3">
                                            <span v-for="bal in summary.credit_balances" :key="bal.currency" class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                                {{ __('message.credit_balance') }} {{ formatMoney(bal.balance, bal.currency) }}
                                            </span>
                                        </div>
                                        <DataTable
                                            v-if="tabMounted.credits"
                                            :url="creditsUrl"
                                            :dataColumns="creditColumns"
                                            :option="creditOptions"
                                        />
                                    </div>

                                    <!-- Orders -->
                                    <div v-show="activeTab === 'orders'">
                                        <DataTable
                                            v-if="tabMounted.orders"
                                            ref="ordDtRef"
                                            :url="ordersUrl"
                                            :dataColumns="orderColumns"
                                            :option="orderOptions"
                                        >
                                            <template #bulk-actions>
                                                <div v-if="selOrders.length > 0" class="dropdown">
                                                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        {{ __('message.bulk_action') }}
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><button class="dropdown-item" @click="startBulkDelete('orders')">{{ __('message.Delete') }}</button></li>
                                                    </ul>
                                                </div>
                                            </template>
                                        </DataTable>
                                    </div>

                                    <!-- Comments -->
                                    <div v-show="activeTab === 'comments'">
                                        <div class="mb-3">
                                            <TextField
                                                name="newComment"
                                                type="textarea"
                                                :value="newComment"
                                                :onChange="(val) => newComment = val"
                                                :placehold="__('message.add_comment') || 'Add a comment…'"
                                                :rows="2"
                                                inputClass="form-control-sm"
                                            />
                                            <div class="d-flex justify-content-end mt-1">
                                                <action-button action="save" class="btn-xs" :disabled="!newComment.trim()" :loading="savingComment" @click="addComment" />
                                            </div>
                                        </div>

                                        <div v-if="loadingComments" class="row justify-content-center py-3"><loader /></div>
                                        <div v-else-if="comments.length === 0" class="text-center text-muted py-3">
                                            {{ __('message.no-record') }}
                                        </div>
                                        <div v-else>
                                            <div v-for="comment in comments" :key="comment.id" class="d-flex gap-2 mb-3">
                                                <div class="flex-shrink-0">
                                                    <span
                                                        class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center comment-avatar"
                                                    >{{ (comment.author || 'U').charAt(0).toUpperCase() }}</span>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <strong>{{ comment.author || 'Unknown' }}</strong>
                                                            <small class="text-muted ms-2">{{ timeAgo(comment.created_at) }}</small>
                                                        </div>
                                                        <div>
                                                            <a href="javascript:;" class="btn btn-xs btn-light me-1" @click="startEdit(comment)">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="javascript:;" class="btn btn-xs btn-light" @click="confirmDeleteComment(comment.id)">
                                                                <i class="fas fa-trash text-danger"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div v-if="editingComment?.id === comment.id" class="mt-1">
                                                        <TextField name="editDescription" type="textarea" :value="editingComment.description" :onChange="(val) => editingComment.description = val" :rows="2" inputClass="form-control-sm" />
                                                        <div class="d-flex gap-1 justify-content-end mt-1">
                                                            <action-button action="cancel" class="btn-xs" @click="editingComment = null" />
                                                            <action-button action="save" class="btn-xs" @click="saveEdit(comment)" />
                                                        </div>
                                                    </div>
                                                    <p v-else class="mb-0 mt-1 comment-text">{{ comment.description }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <DeleteModal
        v-if="pendingDelete"
        :showModal="true"
        :onClose="() => pendingDelete = null"
        :deleteUrl="pendingDeleteUrl"
        :deleteData="{}"
        :title="__('message.Delete')"
        :message="__('message.are_you_sure')"
        :componentName="COMPONENT"
        @deleted="() => { const id = pendingDelete?.commentId; pendingDelete = null; comments.value = comments.value.filter(c => c.id !== id) }"
    />

    <DeleteModal
        v-if="bulkDelete"
        :showModal="true"
        :onClose="() => bulkDelete = null"
        :deleteUrl="bulkDelete.url"
        :deleteData="bulkDelete.data"
        :title="__('message.confirm_delete') || 'Confirm Delete'"
        :message="__('message.are_you_sure') || 'Are you sure?'"
        :componentName="COMPONENT"
        @deleted="onBulkDeleted"
    />
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { h, withDirectives, resolveDirective } from 'vue'
import { useRoute, RouterLink }     from 'vue-router'
import http                         from '@/plugins/axios'
import { errorHandler }             from '@/helpers/responseHandler.js'
import TextField                    from '@/components/Reusable/FormField/TextField.vue'
import { asset }                    from '@/core/utils/asset.js'
import { useNotification }          from '@/core/composables/useNotification.js'
import { useDateTime }              from '@/core/composables/useDateTime'
import DeleteModal                  from '@/components/Reusable/DeleteModal.vue'
import PaymentTableActions          from './components/PaymentTableActions.vue'
import InvoiceTableActions          from '../invoices/components/InvoiceTableActions.vue'
import OrderTableActions            from '../orders/components/OrderTableActions.vue'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import { makeRequestAdapter } from '@/helpers/tableUtils'
import { useTableSelection } from '@/core/composables/useTableSelection'

const COMPONENT  = 'user-show'
const route      = useRoute()
const userId     = route.params.id
const { notify } = useNotification()
const { formatDate } = useDateTime()

const baseUrl = useBaseUrl()
const fallbackAvatar = asset('images/avatar.png')

// ── API URLs ──────────────────────────────────────────────────────────────────
const invoicesUrl = `/user/${userId}/invoices`
const paymentsUrl = `/user/${userId}/payments`
const creditsUrl  = `/user/${userId}/credits`
const ordersUrl   = `/orders`

// ── Multi-select + bulk delete (per table) ─────────────────────────────────────
const invDtRef = ref(null)
const payDtRef = ref(null)
const ordDtRef = ref(null)

const { selected: selInvoices, allSelected: allInvSelected, toggleRow: toggleInvoiceRow, toggleAll: toggleAllInvoices } = useTableSelection(invDtRef)
const { selected: selPayments, allSelected: allPaySelected, toggleRow: togglePaymentRow, toggleAll: toggleAllPayments } = useTableSelection(payDtRef)
const { selected: selOrders,   allSelected: allOrdSelected, toggleRow: toggleOrderRow,   toggleAll: toggleAllOrders }   = useTableSelection(ordDtRef)

function selectCheckbox(selRef, toggleRow, row) {
    return h('input', { type: 'checkbox', checked: selRef.value.includes(row.id), onChange: () => toggleRow(row.id) })
}

const bulkDelete = ref(null)
function startBulkDelete(kind) {
    const cfg = {
        invoices: { sel: selInvoices, url: `${baseUrl}/invoices`, key: 'invoice_ids', dt: invDtRef },
        payments: { sel: selPayments, url: `${baseUrl}/payments`, key: 'payment_ids', dt: payDtRef },
        orders:   { sel: selOrders,   url: `${baseUrl}/orders`,   key: 'order_ids',   dt: ordDtRef },
    }[kind]
    if (!cfg.sel.value.length) return
    bulkDelete.value = { url: cfg.url, data: { [cfg.key]: [...cfg.sel.value] }, sel: cfg.sel, dt: cfg.dt }
}
function onBulkDeleted() {
    if (bulkDelete.value) {
        bulkDelete.value.sel.value = []
        bulkDelete.value.dt.value?.refresh()
    }
    bulkDelete.value = null
}

// ── State ─────────────────────────────────────────────────────────────────────
const loading         = ref(true)
const loadingSummary  = ref(true)
const loadingComments = ref(false)
const copied          = ref('')

const user    = ref(null)
const summary = ref({ invoice_total: 0, amount_paid: 0, balance: 0, credit_balance: 0, credit_balances: [], unapplied_balance: 0, unapplied_balances: [], currency: '', invoice_count: null, payment_count: null, order_count: null, credit_count: null })

const comments       = ref([])
const newComment     = ref('')
const savingComment  = ref(false)
const editingComment = ref(null)
const pendingDelete  = ref(null)
const pendingDeleteUrl = computed(() =>
    pendingDelete.value ? `${baseUrl}/user/${userId}/comments/${pendingDelete.value.commentId}` : ''
)

// ── Tabs ──────────────────────────────────────────────────────────────────────
const tabs = [
    { key: 'orders',   label: __('message.orders')           || 'Orders',   countKey: 'order_count'   },
    { key: 'invoices', label: __('message.invoices')         || 'Invoices', countKey: 'invoice_count' },
    { key: 'payments', label: __('message.payments_section') || 'Payments', countKey: 'payment_count' },
    { key: 'credits',  label: __('message.credits')          || 'Credits',  countKey: 'credit_count'  },
    { key: 'comments', label: __('message.comments')         || 'Comments', countKey: null             },
]
const activeTab  = ref('orders')
const tabMounted = reactive({ invoices: false, payments: false, orders: false, credits: false })

function activateTab(key) {
    activeTab.value = key
    if (tabMounted[key] !== undefined && !tabMounted[key]) {
        tabMounted[key] = true
    }
    if (key === 'comments' && !comments.value.length && !loadingComments.value) {
        loadComments()
    }
}

// ── Mount ─────────────────────────────────────────────────────────────────────
onMounted(async () => {
    await loadUser()
    tabMounted.orders = true
    await Promise.all([loadSummary(), loadComments()])
})

// ── Loaders ───────────────────────────────────────────────────────────────────
async function loadUser() {
    try {
        const res  = await http.get(`/user/${userId}`)
        user.value = res.data?.data ?? null
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
}

async function loadSummary() {
    try {
        const res     = await http.get(`/user/${userId}/summary`)
        summary.value = { ...summary.value, ...(res.data?.data ?? {}) }
    } catch { /* silent */ } finally {
        loadingSummary.value = false
    }
}

async function loadComments() {
    loadingComments.value = true
    try {
        const res      = await http.get(`/user/${userId}/comments`)
        comments.value = res.data?.data ?? []
    } catch { comments.value = [] } finally {
        loadingComments.value = false
    }
}

// ── 2FA ───────────────────────────────────────────────────────────────────────
async function disable2fa() {
    if (!confirm(__('message.are_you_sure') || 'Are you sure?')) return
    try {
        await http.post(`/2fa/disable/${userId}`)
        notify(__('message.updated-successfully') || 'Updated', 'success')
        user.value.is_2fa_enabled = 0
    } catch (e) {
        notify(e?.response?.data?.message || 'Error', 'danger')
    }
}

// ── Comments CRUD ─────────────────────────────────────────────────────────────
async function addComment() {
    if (!newComment.value.trim()) return
    savingComment.value = true
    try {
        const res = await http.post(`/user/${userId}/comments`, { description: newComment.value })
        comments.value.unshift(res.data?.data)
        newComment.value = ''
        notify(__('message.saved-successfully') || 'Saved', 'success')
    } catch (e) {
        notify(e?.response?.data?.message || 'Error', 'danger')
    } finally {
        savingComment.value = false
    }
}

function startEdit(comment) {
    editingComment.value = { id: comment.id, description: comment.description }
}

async function saveEdit(comment) {
    try {
        await http.put(`/user/${userId}/comments/${comment.id}`, { description: editingComment.value.description })
        comment.description  = editingComment.value.description
        editingComment.value = null
        notify(__('message.updated-successfully') || 'Updated', 'success')
    } catch (e) {
        notify(e?.response?.data?.message || 'Error', 'danger')
    }
}

function confirmDeleteComment(commentId) {
    pendingDelete.value = { commentId }
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function copy(field, text) {
    navigator.clipboard?.writeText(text)
    copied.value = field
    setTimeout(() => { copied.value = '' }, 1000)
}

function formatMoney(amount, currency) {
    const num = parseFloat(amount) || 0
    if (currency) {
        try { return new Intl.NumberFormat(undefined, { style: 'currency', currency }).format(num) } catch { /* fall */ }
    }
    return num.toFixed(2)
}

function timeAgo(dateStr) {
    if (!dateStr) return ''
    const diff    = Date.now() - new Date(dateStr).getTime()
    const minutes = Math.floor(diff / 60000)
    if (minutes < 1)  return 'just now'
    if (minutes < 60) return `${minutes}m ago`
    const hours = Math.floor(minutes / 60)
    if (hours < 24) return `${hours}h ago`
    return formatDate(dateStr)
}

function fmtDate(val) {
    return val ? formatDate(val) : '—'
}

function statusBadge(status, map) {
    const cls = map[status?.toLowerCase()] ?? 'bg-secondary'
    return h('span', { class: `badge ${cls}` }, status || '—')
}

// ── DataTable column definitions ──────────────────────────────────────────────

const invoiceColumns = ['select', 'date', 'number', 'grand_total', 'paid', 'balance', 'status', 'action']
const invoiceOptions = {
    headings: {
        select:      () => h('input', { type: 'checkbox', checked: allInvSelected.value, onChange: toggleAllInvoices }),
        date:        __('message.date')       || 'Date',
        number:      __('message.invoice_no') || 'Invoice No',
        grand_total: __('message.total')      || 'Total',
        paid:        __('message.paid')       || 'Paid',
        balance:     __('message.balance')    || 'Balance',
        status:      __('message.status')     || 'Status',
        action:      __('message.actions')    || 'Actions',
    },
    columnsClasses: {
        select: 'dt-select',
        date: 'dt-date',
        number: 'dt-number',
        grand_total: 'dt-amount',
        paid: 'dt-amount',
        balance: 'dt-amount',
        status: 'dt-status',
        action: 'dt-action',
    },
    templates: {
        select:      (_, row) => selectCheckbox(selInvoices, toggleInvoiceRow, row),
        number:      (_, row) => row.number && row.id ? h(RouterLink, { to: '/invoices/' + row.id }, () => row.number) : (row.number || '—'),
        date:        (_, row) => fmtDate(row.date),
        grand_total: (_, row) => formatMoney(row.grand_total, row.currency),
        paid:        (_, row) => formatMoney(row.paid, row.currency),
        balance:     (_, row) => h('span', { class: row.balance > 0 ? 'text-danger' : '' }, formatMoney(row.balance, row.currency)),
        status:      (_, row) => statusBadge(row.status, { success: 'bg-success', pending: 'bg-warning text-dark', 'partially paid': 'bg-info text-dark' }),
        action:      (_, row) => h(InvoiceTableActions, { invoiceId: row.id, isExecuted: !!row.is_executed, isPaid: ['Paid', 'Success'].includes(row.status), showDelete: true, componentName: COMPONENT }),
    },
    sortable:   ['date', 'number', 'grand_total', 'status'],
    filterable: true,
    requestAdapter: makeRequestAdapter('date'),
    orderBy: { column: 'date', ascending: false },
}

const paymentColumns = ['select', 'invoices', 'date', 'payment_method', 'amount', 'unapplied', 'status', 'action']
const paymentOptions = {
    headings: {
        select:         () => h('input', { type: 'checkbox', checked: allPaySelected.value, onChange: toggleAllPayments }),
        invoices:       __('message.invoice_no')     || 'Invoice No',
        date:           __('message.date')           || 'Date',
        payment_method: __('message.payment-method') || 'Payment Method',
        amount:         __('message.total')          || 'Amount',
        unapplied:      __('message.unapplied_balance'),
        status:         __('message.status')         || 'Status',
        action:         __('message.actions')        || 'Actions',
    },
    columnsClasses: {
        select: 'dt-select',
        invoices: 'dt-number',
        date: 'dt-date',
        payment_method: 'dt-name',
        amount: 'dt-amount',
        unapplied: 'dt-amount',
        status: 'dt-status',
        action: 'dt-action',
    },
    templates: {
        select:         (_, row) => selectCheckbox(selPayments, togglePaymentRow, row),
        // One row per payment. A payment split over several invoices lists them
        // all here rather than appearing as several rows of money.
        invoices:       (_, row) => (row.invoices ?? []).length
            ? h('span', {}, (row.invoices ?? []).flatMap((inv, i) => [
                i ? h('span', {}, ', ') : null,
                h(RouterLink, { to: '/invoices/' + inv.id }, () => inv.number),
            ].filter(Boolean)))
            : h('span', { class: 'text-muted' }, '—'),
        date:           (_, row) => fmtDate(row.date),
        amount:         (_, row) => formatMoney(row.amount, row.currency),
        // Always a figure, never a dash — a money column that sometimes shows
        // "—" reads as "unknown" when the answer is a definite zero.
        unapplied:      (_, row) => row.unapplied > 0
            ? h('span', { class: 'badge bg-primary' }, formatMoney(row.unapplied, row.currency))
            : h('span', { class: 'text-muted' }, formatMoney(0, row.currency)),
        status:         (_, row) => statusBadge(row.status, { success: 'bg-success', pending: 'bg-warning text-dark', failed: 'bg-danger' }),
        action:         (_, row) => h(PaymentTableActions, {
            paymentId: row.id,
            // Only a payment with money left on it has anything to allocate.
            unapplied: row.unapplied,
            userId:    userId,
            baseUrl:   baseUrl,
        }),
    },
    sortable:   ['date', 'payment_method', 'amount', 'status'],
    filterable: true,
    requestAdapter: makeRequestAdapter('created_at', null, { date: 'created_at', status: 'payment_status' }),
    orderBy: { column: 'date', ascending: false },
}

const creditColumns = ['date', 'type', 'amount', 'invoice_number', 'note']
const creditOptions = {
    headings: {
        date:           __('message.date')           || 'Date',
        type:           __('message.credit_type')    || 'Type',
        amount:         __('message.total')          || 'Amount',
        invoice_number: __('message.invoice_no')     || 'Invoice No',
        note:           __('message.credit_note')    || 'Note',
    },
    columnsClasses: {
        date: 'dt-date',
        type: 'dt-status',
        amount: 'dt-amount',
        invoice_number: 'dt-number',
        note: 'dt-name',
    },
    templates: {
        date:           (_, row) => fmtDate(row.date),
        // Signed on purpose: a deposit reads +, a spend reads -, so the column
        // adds up to the balance shown above it.
        amount:         (_, row) => h('span', { class: row.amount < 0 ? 'text-danger' : 'text-success' }, formatMoney(row.amount, row.currency)),
        type:           (_, row) => h('span', { class: 'badge bg-secondary' }, row.type || '—'),
        invoice_number: (_, row) => row.invoice_number && row.invoice_id ? h(RouterLink, { to: '/invoices/' + row.invoice_id }, () => row.invoice_number) : '—',
        note:           (_, row) => row.note || '—',
    },
    sortable:   ['date', 'type'],
    filterable: false,
    requestAdapter: makeRequestAdapter('created_at', null, { date: 'created_at' }),
    orderBy: { column: 'date', ascending: false },
}

const orderColumns = ['select', 'order_date', 'product_name', 'number', 'version', 'order_status', 'action']
const orderOptions = {
    headings: {
        select:       () => h('input', { type: 'checkbox', checked: allOrdSelected.value, onChange: toggleAllOrders }),
        order_date:   __('message.date')     || 'Date',
        product_name: __('message.product')  || 'Product',
        number:       __('message.order_no') || 'Order No',
        version:      __('message.version')  || 'Version',
        order_status: __('message.status')   || 'Status',
        action:       __('message.actions')   || 'Actions',
    },
    columnsClasses: {
        select: 'dt-select',
        order_date: 'dt-date',
        product_name: 'dt-name',
        number: 'dt-number',
        version: 'dt-code',
        order_status: 'dt-status',
        action: 'dt-action',
    },
    templates: {
        select:       (_, row) => selectCheckbox(selOrders, toggleOrderRow, row),
        order_date:   (_, row) => fmtDate(row.order_date),
        product_name: (_, row) => row.product_name && row.product_id ? h(RouterLink, { to: '/products/' + row.product_id + '/edit' }, () => row.product_name) : (row.product_name || '—'),
        number:       (_, row) => row.number && row.id ? h(RouterLink, { to: `/orders/${row.id}` }, () => `#${row.number}`) : (row.number ? `#${row.number}` : '—'),
        version: (_, row) => {
            if (!row.versions?.length) return '—'
            const vTooltip = resolveDirective('tooltip')
            return h('div', { class: 'd-flex flex-wrap gap-1' },
                row.versions.map(({ version, active }) =>
                    withDirectives(
                        h('span', {
                            class: `badge ${active ? 'bg-success' : 'bg-danger'}`,
                            style: 'cursor:default',
                        }, version),
                        [[vTooltip, active ? 'Active' : 'Inactive']]
                    )
                )
            )
        },
        // 'executed' and 'terminated' are the only two order_status values
        // this app ever sets — the map used to only know about statuses
        // ('active', 'pending', ...) that don't exist here, so every real
        // order fell through to the plain gray default.
        order_status: (_, row) => statusBadge(row.order_status, { executed: 'bg-success', terminated: 'bg-danger' }),
        action:       (_, row) => h(OrderTableActions, { orderId: row.id, showDelete: true, componentName: COMPONENT }),
    },
    sortable:   ['order_date', 'number', 'order_status'],
    filterable: true,
    requestAdapter: makeRequestAdapter('created_at', ref({ client: userId }), { order_date: 'created_at' }),
    orderBy: { column: 'order_date', ascending: false },
}
</script>

<style scoped>

.img-circle {
  border-radius: 50%;
}

.profile-user-img {
  border: 3px solid #adb5bd;
  margin: 0 auto;
  padding: 3px;
  width: 100px;
}

/* Match favMer verification icon colours */
.icon-success { color: #017701 !important; cursor: pointer; padding: 3px; }
.icon-danger  { color: red     !important; cursor: pointer; padding: 3px; }

/* Copied tooltip — matches favMer .copyClip */
.copy-clip {
    position: absolute;
    background: black;
    color: white;
    border-radius: 0.2rem;
    padding: 2px 5px;
    font-size: 11px;
    bottom: 80%;
    left: 70%;
}
.copy-clip::after {
    content: '';
    position: absolute;
    top: 97%;
    margin-left: -5px;
    border-width: 5px;
    border-style: solid;
    border-color: black transparent transparent transparent;
}

/* Match favMer .user_view button spacing */
.user_view .btn { margin-left: 4px; }
.nowrap-hidden { white-space: nowrap; overflow: hidden; }
.truncate-with-copy { max-width: calc(100% - 22px); }
.comment-avatar { width: 32px; height: 32px; font-size: 13px; font-weight: 600; flex-shrink: 0; }
.comment-text { white-space: pre-wrap; }
</style>
