<template>
    <div>
        <AppAlert :componentName="COMPONENT" />

        <inline-loader v-if="loading" />

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
                                @error="e => e.target.src = fallbackAvatar"
                            />
                        </div>

                        <h3 class="profile-username text-center" v-tooltip="user.full_name">
                            {{ user.full_name }}
                        </h3>

                        <p class="text-muted text-center">
                            <span
                                class="fas fa-envelope"
                                :class="user.email_verified ? 'icon-success' : 'icon-danger'"
                                :title="user.email_verified ? __('message.email_verified') : __('message.email_not_verified')"
                                v-tooltip
                            ></span>
                            &nbsp;
                            <span
                                class="fas fa-mobile-alt"
                                :class="user.mobile_verified ? 'icon-success' : 'icon-danger'"
                                :title="user.mobile_verified ? __('message.mobile_verified') : __('message.mobile_not_verified')"
                                v-tooltip
                            ></span>
                            &nbsp;
                            <span
                                class="fas fa-shield-alt"
                                :class="user.is_2fa_enabled ? 'icon-success' : 'icon-danger'"
                                :title="user.is_2fa_enabled ? __('message.2fa_enabled') : __('message.2fa_not_enabled')"
                                v-tooltip
                            ></span>
                        </p>

                        <!-- Financial summary — shown above user details -->
                        <div v-if="!loadingSummary" class="row text-center g-0 border rounded mb-3">
                            <div class="col-4 border-end py-2">
                                <div class="small text-muted">{{ __('message.invoice-total') || 'Invoiced' }}</div>
                                <div class="fw-bold small">{{ formatMoney(summary.invoice_total, summary.currency) }}</div>
                            </div>
                            <div class="col-4 border-end py-2">
                                <div class="small text-muted">{{ __('message.paid') }}</div>
                                <div class="fw-bold small text-success">{{ formatMoney(summary.amount_paid, summary.currency) }}</div>
                            </div>
                            <div class="col-4 py-2">
                                <div class="small text-muted">{{ __('message.balance') }}</div>
                                <div class="fw-bold small" :class="summary.balance > 0 ? 'text-danger' : 'text-success'">
                                    {{ formatMoney(summary.balance, summary.currency) }}
                                </div>
                            </div>
                        </div>
                        <inline-loader v-else :min-height="60" :size="30" />

                        <ul class="list-group list-group-unbordered mb-3">

                            <li class="list-group-item">
                                <div class="row align-items-center">
                                    <div class="col-sm-5">
                                        <span class="text-truncate mb-0 fw-bold" v-tooltip="__('message.user_name')">{{ __('message.user_name') }}</span>
                                    </div>
                                    <div class="col-sm-7 text-end" style="white-space:nowrap;overflow:hidden;">
                                        <span class="d-inline-block text-truncate align-middle" style="max-width:calc(100% - 22px)" v-tooltip="user.user_name">{{ user.user_name }}</span>
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
                                    <div class="col-sm-7 text-end" style="white-space:nowrap;overflow:hidden;">
                                        <span class="d-inline-block text-truncate align-middle" style="max-width:calc(100% - 22px)" v-tooltip="user.email">{{ user.email }}</span>
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
                            <a :href="`${baseUrl}/newPayment/receive?clientid=${userId}`" class="btn btn-xs btn-light">
                                <i class="fas fa-money-bill">&nbsp;</i>{{ __('message.create-payment') }}
                            </a>
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
                                            :url="invoicesUrl"
                                            :dataColumns="invoiceColumns"
                                            :option="invoiceOptions"
                                        />
                                    </div>

                                    <!-- Payments -->
                                    <div v-show="activeTab === 'payments'">
                                        <DataTable
                                            v-if="tabMounted.payments"
                                            :url="paymentsUrl"
                                            :dataColumns="paymentColumns"
                                            :option="paymentOptions"
                                        />
                                    </div>

                                    <!-- Orders -->
                                    <div v-show="activeTab === 'orders'">
                                        <DataTable
                                            v-if="tabMounted.orders"
                                            :url="ordersUrl"
                                            :dataColumns="orderColumns"
                                            :option="orderOptions"
                                        />
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

                                        <inline-loader v-if="loadingComments" />
                                        <div v-else-if="comments.length === 0" class="text-center text-muted py-3">
                                            {{ __('message.no-record') }}
                                        </div>
                                        <div v-else>
                                            <div v-for="comment in comments" :key="comment.id" class="d-flex gap-2 mb-3">
                                                <div class="flex-shrink-0">
                                                    <span
                                                        class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center"
                                                        style="width:32px;height:32px;font-size:13px;font-weight:600;flex-shrink:0"
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
                                                    <p v-else class="mb-0 mt-1" style="white-space:pre-wrap">{{ comment.description }}</p>
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
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { h }                        from 'vue'
import { useRoute, RouterLink }     from 'vue-router'
import http                         from '@/plugins/axios'
import { errorHandler }             from '@/helpers/responseHandler.js'
import TextField                    from '@/components/Reusable/FormField/TextField.vue'
import { asset }                    from '@/core/utils/asset.js'
import { useNotification }          from '@/core/composables/useNotification.js'
import DeleteModal                  from '@/themes/adminlte/components/common/DeleteModal.vue'

const COMPONENT  = 'user-show'
const route      = useRoute()
const userId     = route.params.id
const { notify } = useNotification()

const el             = document.getElementById('app-root')
const baseUrl        = el?.dataset?.baseUrl ?? ''
const fallbackAvatar = asset('themes/adminlte/assets/img/avatar.png')

// ── API URLs ──────────────────────────────────────────────────────────────────
const invoicesUrl = `${baseUrl}/user/${userId}/invoices`
const paymentsUrl = `${baseUrl}/user/${userId}/payments`
const ordersUrl   = `${baseUrl}/orders`

// ── State ─────────────────────────────────────────────────────────────────────
const loading         = ref(true)
const loadingSummary  = ref(true)
const loadingComments = ref(false)
const copied          = ref('')

const user    = ref(null)
const summary = ref({ invoice_total: 0, amount_paid: 0, balance: 0, currency: '', invoice_count: null, payment_count: null, order_count: null })

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
    { key: 'comments', label: __('message.comments')         || 'Comments', countKey: null             },
]
const activeTab  = ref('orders')
const tabMounted = reactive({ invoices: false, payments: false, orders: false })

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
        const res  = await http.get(`${baseUrl}/user/${userId}`)
        user.value = res.data?.data ?? null
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
}

async function loadSummary() {
    try {
        const res     = await http.get(`${baseUrl}/user/${userId}/summary`)
        summary.value = { ...summary.value, ...(res.data?.data ?? {}) }
    } catch { /* silent */ } finally {
        loadingSummary.value = false
    }
}

async function loadComments() {
    loadingComments.value = true
    try {
        const res      = await http.get(`${baseUrl}/user/${userId}/comments`)
        comments.value = res.data?.data ?? []
    } catch { comments.value = [] } finally {
        loadingComments.value = false
    }
}

// ── 2FA ───────────────────────────────────────────────────────────────────────
async function disable2fa() {
    if (!confirm(__('message.are_you_sure') || 'Are you sure?')) return
    try {
        await http.post(`${baseUrl}/2fa/disable/${userId}`)
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
        const res = await http.post(`${baseUrl}/user/${userId}/comments`, { description: newComment.value })
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
        await http.put(`${baseUrl}/user/${userId}/comments/${comment.id}`, { description: editingComment.value.description })
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
    return new Date(dateStr).toLocaleDateString()
}

function fmtDate(val) {
    return val ? new Date(val).toLocaleDateString() : '—'
}

function statusBadge(status, map) {
    const cls = map[status?.toLowerCase()] ?? 'bg-secondary'
    return h('span', { class: `badge ${cls}` }, status || '—')
}

// ── DataTable column definitions ──────────────────────────────────────────────

const invoiceColumns = ['date', 'number', 'grand_total', 'paid', 'balance', 'status', 'action']
const invoiceOptions = {
    headings: {
        date:        __('message.date')       || 'Date',
        number:      __('message.invoice_no') || 'Invoice No',
        grand_total: __('message.total')      || 'Total',
        paid:        __('message.paid')       || 'Paid',
        balance:     __('message.balance')    || 'Balance',
        status:      __('message.status')     || 'Status',
        action:      '',
    },
    columnsClasses: {
        date: 'dt-date',
        number: 'dt-number',
        grand_total: 'dt-amount',
        paid: 'dt-amount',
        balance: 'dt-amount',
        status: 'dt-status',
        action: 'dt-action',
    },
    templates: {
        number:      (_, row) => row.number && row.id ? h(RouterLink, { to: '/invoices/' + row.id }, () => row.number) : (row.number || '—'),
        date:        (_, row) => fmtDate(row.date),
        grand_total: (_, row) => formatMoney(row.grand_total, row.currency),
        paid:        (_, row) => formatMoney(row.paid, row.currency),
        balance:     (_, row) => h('span', { class: row.balance > 0 ? 'text-danger' : '' }, formatMoney(row.balance, row.currency)),
        status:      (_, row) => statusBadge(row.status, { success: 'bg-success', pending: 'bg-warning text-dark', 'partially paid': 'bg-info text-dark' }),
        action:      (_, row) => h('a', {
            href:   `${baseUrl}/invoices/show?invoiceid=${row.id}`,
            target: '_blank',
            class:  'btn btn-xs btn-light',
        }, h('i', { class: 'fas fa-eye' })),
    },
    sortable:   ['date', 'number', 'grand_total', 'status'],
    filterable: true,
    requestAdapter(data) {
        return {
            'sort-field':   data.orderBy ?? 'date',
            'sort-order':   data.ascending ? 'asc' : 'desc',
            'search-query': (data.query ?? '').trim(),
            page:           data.page,
            limit:          data.limit,
        }
    },
    orderBy: { column: 'date', ascending: false },
}

const paymentColumns = ['invoice_number', 'date', 'payment_method', 'amount', 'status', 'action']
const paymentOptions = {
    headings: {
        invoice_number: __('message.invoice_no')     || 'Invoice No',
        date:           __('message.date')           || 'Date',
        payment_method: __('message.payment_method') || 'Payment Method',
        amount:         __('message.total')          || 'Amount',
        status:         __('message.status')         || 'Status',
        action:         '',
    },
    columnsClasses: {
        invoice_number: 'dt-number',
        date: 'dt-date',
        payment_method: 'dt-name',
        amount: 'dt-amount',
        status: 'dt-status',
        action: 'dt-action',
    },
    templates: {
        invoice_number: (_, row) => row.invoice_number && row.invoice_id ? h(RouterLink, { to: '/invoices/' + row.invoice_id }, () => row.invoice_number) : (row.invoice_number || '—'),
        date:           (_, row) => fmtDate(row.date),
        amount:         (_, row) => formatMoney(row.amount, row.currency),
        status:         (_, row) => statusBadge(row.status, { success: 'bg-success', pending: 'bg-warning text-dark', failed: 'bg-danger' }),
        action:         (_, row) => row.invoice_id
            ? h('a', { href: `${baseUrl}/invoices/show?invoiceid=${row.invoice_id}`, target: '_blank', class: 'btn btn-xs btn-light' }, h('i', { class: 'fas fa-eye' }))
            : '—',
    },
    sortable:   ['date', 'payment_method', 'amount', 'status'],
    filterable: true,
    requestAdapter(data) {
        const columnMap = { date: 'created_at', status: 'payment_status' }
        return {
            'sort-field':   columnMap[data.orderBy] ?? data.orderBy ?? 'created_at',
            'sort-order':   data.ascending ? 'asc' : 'desc',
            'search-query': (data.query ?? '').trim(),
            page:           data.page,
            limit:          data.limit,
        }
    },
    orderBy: { column: 'date', ascending: false },
}

const orderColumns = ['order_date', 'product_name', 'number', 'version', 'order_status', 'action']
const orderOptions = {
    headings: {
        order_date:   __('message.date')     || 'Date',
        product_name: __('message.product')  || 'Product',
        number:       __('message.order_no') || 'Order No',
        version:      __('message.version')  || 'Version',
        order_status: __('message.status')   || 'Status',
        action:       '',
    },
    columnsClasses: {
        order_date: 'dt-date',
        product_name: 'dt-name',
        number: 'dt-number',
        version: 'dt-code',
        order_status: 'dt-status',
        action: 'dt-action',
    },
    templates: {
        order_date:   (_, row) => fmtDate(row.order_date),
        product_name: (_, row) => row.product_name && row.product_id ? h(RouterLink, { to: '/products/' + row.product_id + '/edit' }, () => row.product_name) : (row.product_name || '—'),
        number:       (_, row) => row.number && row.id ? h(RouterLink, { to: `/orders/${row.id}` }, () => `#${row.number}`) : (row.number ? `#${row.number}` : '—'),
        version:      (_, row) => row.version || '—',
        order_status: (_, row) => statusBadge(row.order_status, { active: 'bg-success', pending: 'bg-warning text-dark', cancelled: 'bg-danger', expired: 'bg-secondary', terminated: 'bg-dark' }),
        action:       (_, row) => h(RouterLink, { to: `/orders/${row.id}`, class: 'btn btn-xs btn-light' }, () => h('i', { class: 'fas fa-eye' })),
    },
    sortable:   ['order_date', 'number', 'order_status'],
    filterable: true,
    requestAdapter(data) {
        return {
            'sort-field':   data.orderBy === 'order_date' ? 'created_at' : (data.orderBy ?? 'created_at'),
            'sort-order':   data.ascending ? 'asc' : 'desc',
            'search-query': (data.query ?? '').trim(),
            page:           data.page,
            limit:          data.limit,
            client:         userId,
        }
    },
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
</style>
