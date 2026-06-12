<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h3 class="card-title">{{ __('log.system_logs') }}</h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-secondary" v-tooltip="'Delete Logs'" @click="showDeleteModal = true">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">

                <!-- Log type selector -->
                <div class="row mb-3">
                    <div v-for="type in logTypes" :key="type.key" class="col-md-2 col-sm-4 col-6">
                        <div
                            class="d-flex flex-column align-items-center text-center gap-2 py-2"
                            style="cursor:pointer"
                            @click="switchType(type.key)"
                        >
                            <span class="settings-icon" :class="activeType === type.key && 'settings-icon--active'">
                                <i :class="`fas ${type.icon}`"></i>
                            </span>
                            <small class="text-body-secondary lh-sm">{{ type.label }}</small>
                        </div>
                    </div>
                </div>

                <!-- Filter card -->
                <div class="card card-light">
                    <div class="card-header">
                        <h3 class="card-title">{{ filterTitle }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Category boxes -->
                            <div class="col-12 col-lg-9 mb-3 mb-lg-0">
                                <div v-if="loadingCategories" class="text-center py-4">
                                    <span class="spinner-border text-secondary"></span>
                                </div>
                                <div v-else-if="!categories.length" class="text-center text-muted py-4">
                                    {{ __('log.no_categories_found') }}
                                </div>
                                <div v-else class="row">
                                    <!-- Cron categories -->
                                    <template v-if="activeType === 'cron'">
                                        <div v-for="cat in categories" :key="cat.command" class="col-md-4 col-sm-8 mb-3">
                                            <div :class="['info-box bg-gradient-light h-100 category-box', selectedCategoryKey === cat.command && 'selected']">
                                                <div class="info-box-content">
                                                    <span class="info-box-text">{{ cat.name }}</span>
                                                    <span class="info-box-number d-flex justify-content-between">
                                                        <span class="text-blue me-2 log-status-btn" @click="selectCategory(cat, 'completed')">
                                                            {{ cat.completed || 0 }} {{ __('log.completed') }}
                                                        </span>
                                                        <span class="text-red log-status-btn" @click="selectCategory(cat, 'failed')">
                                                            {{ cat.failed || 0 }} {{ __('log.failed') }}
                                                        </span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Exception categories -->
                                    <template v-else-if="activeType === 'exception'">
                                        <div v-for="cat in categories" :key="cat.id" class="col-md-4 col-sm-8 mb-3">
                                            <div
                                                :class="['info-box bg-gradient-light h-100 category-box log-status-btn', selectedCategoryKey === cat.id && 'selected']"
                                                @click="selectCategory(cat, null)"
                                            >
                                                <div class="info-box-content">
                                                    <span class="info-box-text">{{ cat.name }}</span>
                                                    <span class="info-box-number">
                                                        <span class="text-blue">{{ cat.count }} {{ __('log.logs') }}</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Mail categories -->
                                    <template v-else-if="activeType === 'mail'">
                                        <div v-for="cat in categories" :key="cat.id" class="col-md-4 col-sm-8 mb-3">
                                            <div :class="['info-box bg-gradient-light h-100 category-box', selectedCategoryKey === cat.id && 'selected']">
                                                <div class="info-box-content">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="info-box-text" :title="cat.name">{{ cat.name }}</span>
                                                        <span class="text-blue info-box-number log-status-btn" @click="selectCategory(cat, 'queued')">
                                                            {{ cat.queued || 0 }} {{ __('log.queued') }}
                                                        </span>
                                                    </div>
                                                    <div class="info-box-number d-flex justify-content-between mt-1">
                                                        <span class="text-blue log-status-btn" @click="selectCategory(cat, 'sent')">
                                                            {{ cat.sent || 0 }} {{ __('log.sent') }}
                                                        </span>
                                                        <span class="text-red log-status-btn" @click="selectCategory(cat, 'failed')">
                                                            {{ cat.failed || 0 }} {{ __('log.failed') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Inline calendar -->
                            <div class="col-12 col-lg-3 d-flex justify-content-center">
                                <div class="inline-calendar">
                                    <VueDatePicker
                                        :value="selectedDate"
                                        type="date"
                                        format="YYYY-MM-DD"
                                        value-type="YYYY-MM-DD"
                                        :open="true"
                                        :append-to-body="false"
                                        :editable="false"
                                        :clearable="false"
                                        @change="onDateChange"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logs table card -->
                <div v-if="showLogsCard" class="card card-light mt-3">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('log.logs') }}</h3>
                    </div>
                    <div class="card-body">
                        <DataTable
                            v-if="activeType === 'cron'"
                            :key="tableKey"
                            :url="tableUrl"
                            :dataColumns="cronColumns"
                            :option="cronOptions"
                        />
                        <DataTable
                            v-else-if="activeType === 'exception'"
                            :key="tableKey"
                            :url="tableUrl"
                            :dataColumns="exceptionColumns"
                            :option="exceptionOptions"
                        />
                        <DataTable
                            v-else-if="activeType === 'mail'"
                            :key="tableKey"
                            :url="tableUrl"
                            :dataColumns="mailColumns"
                            :option="mailOptions"
                        />
                    </div>
                </div>

            </div>
        </div>

        <!-- Code / trace modal -->
        <AppModal :showModal="showCodeModal" :onClose="() => showCodeModal = false" :containerStyle="{ maxWidth: '1000px' }" classname="modal-xl">
            <template #title><h4>{{ __('log.log_details') }}</h4></template>
            <template #fields>
                <div class="code-container">
                    <button :class="['copy-btn', codeCopied && 'copied']" @click="copyCode">
                        <i :class="codeCopied ? 'fas fa-check' : 'fas fa-copy'"></i>
                        <span>{{ codeCopied ? __('log.copied') : __('log.copy') }}</span>
                    </button>
                    <pre class="code-block" :style="{ whiteSpace: codeWrap ? 'pre-wrap' : 'pre', wordWrap: codeWrap ? 'break-word' : 'normal' }">{{ codeContent }}</pre>
                </div>
            </template>
        </AppModal>

        <!-- Retry modal -->
        <AppModal :showModal="showRetryModal" :onClose="() => showRetryModal = false" classname="modal-sm">
            <template #title><h4>{{ __('log.retry_mail') }}</h4></template>
            <template #fields>
                <p class="mb-0">{{ __('log.are_you_sure_you_want_to_retry_this_mail') }}</p>
            </template>
            <template #controls>
                <button class="btn btn-primary" :disabled="retrying" @click="confirmRetry">
                    <span v-if="retrying" class="spinner-border spinner-border-sm me-1"></span>
                    {{ __('log.retry') }}
                </button>
            </template>
        </AppModal>

        <!-- Email body modal -->
        <AppModal
            :showModal="showEmailModal"
            :onClose="() => showEmailModal = false"
            :containerStyle="{ maxWidth: '1000px' }"
            classname="modal-lg"
            modalBodyClass="p-0"
        >
            <template #title><h4>{{ __('log.log_details') }}</h4></template>
            <template #fields>
                <iframe
                    ref="emailIframe"
                    sandbox="allow-same-origin"
                    style="width:100%; height:75vh; border:none; display:block;"
                ></iframe>
            </template>
        </AppModal>

        <!-- Delete logs modal -->
        <AppModal :showModal="showDeleteModal" :onClose="closeDeleteModal" classname="modal-md">
            <template #title><h4>{{ __('log.delete_logs') }}</h4></template>
            <template #fields>
                <div class="mb-3">
                    <DatePicker
                        name="delete_date"
                        :label="__('log.delete_logs_entries')"
                        :value="deleteDate"
                        :clearable="false"
                        :onChange="(val) => deleteDate = val"
                    />
                </div>
                <div class="mb-2">
                    <label class="form-label">{{ __('log.log_types') }}</label>
                    <div class="row">
                        <div v-for="t in deletableTypes" :key="t.value" class="col-md-6">
                            <div class="form-check mb-2">
                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    :id="`del-${t.value}`"
                                    :value="t.value"
                                    v-model="deleteTypes"
                                />
                                <label class="form-check-label" :for="`del-${t.value}`">{{ t.label }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="deleteError" class="text-danger small">{{ deleteError }}</div>
            </template>
            <template #controls>
                <button class="btn btn-primary" :disabled="deleting" @click="confirmDelete">
                    <span v-if="deleting" class="spinner-border spinner-border-sm me-1"></span>
                    {{ __('log.delete_logs') }}
                </button>
            </template>
        </AppModal>
    </div>
</template>

<script setup>
import { h, ref, computed, onMounted, nextTick } from 'vue'
import { DateTime } from 'luxon'
import { useDateTimeStore } from '@/core/stores/dateTimeStore'
import VueDatePicker from 'vue-datepicker-next'
import 'vue-datepicker-next/index.css'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'billing-log-viewer'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const logTypes = [
    { key: 'cron',      label: 'Cron Logs',     icon: 'fa-clock'    },
    { key: 'exception', label: 'Exception Logs', icon: 'fa-bug'      },
    { key: 'mail',      label: 'Mail Logs',      icon: 'fa-envelope' },
]

const deletableTypes = [
    { value: 'mail',        label: 'Mail Logs'      },
    { value: 'cron',        label: 'Cron Logs'      },
    { value: 'exception',   label: 'Exception Logs' },
    { value: 'failed_jobs', label: 'Failed Jobs'    },
]

// ── filter state ─────────────────────────────────────────────────────────────
const activeType          = ref('cron')
const selectedDate        = ref(DateTime.now().setZone(useDateTimeStore().timezone).toFormat('yyyy-MM-dd'))
const categories          = ref([])
const loadingCategories   = ref(false)
const selectedCategoryKey = ref(null)
const selectedStatus      = ref(null)
const showLogsCard        = ref(false)

// ── modals ───────────────────────────────────────────────────────────────────
const showCodeModal = ref(false)
const codeContent   = ref('')
const codeWrap      = ref(false)
const codeCopied    = ref(false)

const showRetryModal = ref(false)
const retryId        = ref(null)
const retrying       = ref(false)

const showEmailModal = ref(false)
const emailIframe    = ref(null)

const showDeleteModal = ref(false)
const deleteDate      = ref(DateTime.now().setZone(useDateTimeStore().timezone).toFormat('yyyy-MM-dd'))
const deleteTypes     = ref([])
const deleteError     = ref('')
const deleteAttempted = ref(false)
const deleting        = ref(false)

// ── computed ─────────────────────────────────────────────────────────────────
const filterTitle = computed(() => {
    const map = { cron: 'Cron Logs', exception: 'Exception Logs', mail: 'Mail Logs' }
    return map[activeType.value] || 'Filter Logs'
})

const tableUrl = computed(() => {
    const params = new URLSearchParams({ date: selectedDate.value, category: selectedCategoryKey.value ?? '' })
    if (selectedStatus.value) params.set('status', selectedStatus.value)
    return `${baseUrl}/logs/${activeType.value}?${params}`
})

const tableKey = computed(() =>
    `${activeType.value}|${selectedDate.value}|${selectedCategoryKey.value}|${selectedStatus.value}`
)

// ── category / filter helpers ─────────────────────────────────────────────────
function switchType(type) {
    if (activeType.value === type) return
    activeType.value          = type
    categories.value          = []
    selectedCategoryKey.value = null
    selectedStatus.value      = null
    showLogsCard.value        = false
    loadCategories()
}

async function loadCategories() {
    loadingCategories.value = true
    try {
        const res = await http.get(`${baseUrl}/log-category-list`, {
            params: { date: selectedDate.value, log_type: activeType.value },
        })
        categories.value = res.data?.data ?? []
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loadingCategories.value = false
    }
}

function selectCategory(cat, status) {
    selectedCategoryKey.value = activeType.value === 'cron' ? cat.command : cat.id
    selectedStatus.value      = status
    showLogsCard.value        = true
}

function onDateChange(val) {
    selectedDate.value        = val
    selectedCategoryKey.value = null
    selectedStatus.value      = null
    showLogsCard.value        = false
    if (val) loadCategories()
}

// ── table column definitions ──────────────────────────────────────────────────
const cronColumns = ['command', 'description', 'duration', 'created_at', 'status']

const cronOptions = {
    filterable: true,
    sortable: ['command', 'description', 'duration', 'created_at', 'status'],
    headings: {
        command:    __('log.command'),
        description:__('log.description'),
        duration:   __('log.duration'),
        created_at: __('log.created_at'),
        status:     __('log.status'),
    },
}

const exceptionColumns = ['file', 'line', 'message', 'trace', 'created_at']

const exceptionOptions = computed(() => ({
    filterable: true,
    sortable: ['file', 'line', 'created_at'],
    headings: {
        file:       __('log.file'),
        line:       __('log.line'),
        message:    __('log.message'),
        trace:      __('log.trace'),
        created_at: __('log.created_at'),
    },
    templates: {
        message: (f, row) => {
            if (!row.message || row.message.length <= 100) return row.message || ''
            return h('span', [
                row.message.substring(0, 100),
                h('a', {
                    href: '#',
                    class: 'text-primary ms-1',
                    onClick: (e) => { e.preventDefault(); openCode(row.message, true) },
                }, h('u', __('log.read_more'))),
            ])
        },
        trace: (f, row) => {
            if (!row.trace || row.trace.length <= 100) return row.trace || ''
            return h('span', [
                row.trace.substring(0, 100) + '...',
                h('a', {
                    href: '#',
                    class: 'text-primary ms-1',
                    onClick: (e) => { e.preventDefault(); openCode(row.trace, false) },
                }, h('u', __('log.read_more'))),
            ])
        },
    },
}))

const mailColumns = ['sender_mail', 'receiver_mail', 'subject', 'created_at', 'updated_at', 'status', 'action']

const mailOptions = computed(() => ({
    filterable: true,
    sortable: ['sender_mail', 'receiver_mail', 'subject', 'created_at', 'updated_at', 'status'],
    headings: {
        sender_mail:   __('log.sender_mail'),
        receiver_mail: __('log.receiver_mail'),
        subject:       __('log.subject'),
        created_at:    __('log.created_at'),
        updated_at:    __('log.updated_at'),
        status:        __('log.status'),
        action:        __('log.action'),
    },
    templates: {
        sender_mail:   (f, row) => truncate(row.sender_mail, 35),
        receiver_mail: (f, row) => truncate(row.receiver_mail, 35),
        subject: (f, row) => h('a', {
            href: '#',
            onClick: (e) => { e.preventDefault(); openEmail(row.body) },
        }, row.subject),
        action: (f, row) => h('button', {
            class: 'btn btn-light table_btn',
            disabled: !row.is_retry,
            title: __('log.retry_log'),
            onClick: () => openRetry(row.id),
        }, h('i', { class: 'fas fa-redo' })),
    },
}))

// ── modal helpers ─────────────────────────────────────────────────────────────
function openCode(text, wrap = false) {
    codeContent.value   = text
    codeWrap.value      = wrap
    codeCopied.value    = false
    showCodeModal.value = true
}

async function copyCode() {
    try {
        await navigator.clipboard.writeText(codeContent.value)
    } catch {
        const ta = document.createElement('textarea')
        ta.value = codeContent.value
        document.body.appendChild(ta)
        ta.select()
        document.execCommand('copy')
        document.body.removeChild(ta)
    }
    codeCopied.value = true
    setTimeout(() => { codeCopied.value = false }, 1500)
}

async function openEmail(body) {
    showEmailModal.value = true
    await nextTick()
    if (emailIframe.value) emailIframe.value.srcdoc = body || ''
}

function openRetry(id) {
    retryId.value        = id
    showRetryModal.value = true
}

async function confirmRetry() {
    retrying.value = true
    try {
        const res = await http.get(`${baseUrl}/retry/mail-log/${retryId.value}`)
        successHandler(res, COMPONENT)
        showRetryModal.value = false
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        retrying.value = false
    }
}

function closeDeleteModal() {
    showDeleteModal.value = false
    deleteError.value     = ''
    deleteAttempted.value = false
}

async function confirmDelete() {
    deleteAttempted.value = true
    deleteError.value     = ''
    if (!deleteDate.value)         { deleteError.value = 'Please select a date.'; return }
    if (!deleteTypes.value.length) { deleteError.value = 'Please select at least one log type.'; return }
    deleting.value = true
    try {
        const res = await http.delete(`${baseUrl}/logs/delete`, {
            data: { to_date: deleteDate.value, log_types: deleteTypes.value },
        })
        successHandler(res, COMPONENT)
        closeDeleteModal()
        deleteTypes.value = []
        loadCategories()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        deleting.value = false
    }
}

function truncate(str, len) {
    if (!str) return '---'
    return str.length > len ? str.substring(0, len) + '...' : str
}

onMounted(() => loadCategories())
</script>

<style scoped>
/* ── Log type selector — same style as settings/Index.vue ── */
.settings-icon {
    width: 68px;
    height: 68px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    flex-shrink: 0;
    border: 5px solid #C4D8E4;
    color: #3c8dbc;
    transition: border-color 0.2s;
}
.settings-icon:hover,
.settings-icon--active { border-color: #3c8dbc; }

/* ── Inline calendar ── */
.inline-calendar {
    display: inline-block;
    background: #fff;
    border-radius: 0.25rem;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    padding: 30px;
    overflow: hidden;
}
.inline-calendar :deep(.mx-input-wrapper)    { display: none; }
.inline-calendar :deep(.mx-datepicker-popup) {
    position: static !important;
    box-shadow: none !important;
    border: none !important;
    margin-top: 0 !important;
    z-index: auto !important;
}
.inline-calendar :deep(.mx-calendar)              { color: #333 !important; }
.inline-calendar :deep(.mx-table th)              { color: #555 !important; }
.inline-calendar :deep(.mx-table td .cell)        { color: #333 !important; }
.inline-calendar :deep(.mx-table td.today .cell)  { color: #1890ff !important; font-weight: 600; }
.inline-calendar :deep(.mx-table td.active .cell) { color: #fff !important; }
.inline-calendar :deep(.mx-table td.disabled .cell) { color: #ccc !important; }
.inline-calendar :deep(.mx-calendar-header-label) { color: #333 !important; }

/* ── Category boxes ── */
.category-box { transition: all 0.3s ease; }
.category-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.category-box.selected {
    border: 2px solid #3c8dbc;
    background-color: #f8f9fa !important;
}
.log-status-btn { cursor: pointer; }

/* ── Code / trace modal ── */
.code-container {
    position: relative;
    background-color: #000;
    border-radius: 8px;
}
.code-block {
    background-color: #000 !important;
    color: #fff;
    font-family: 'Courier New', monospace;
    font-size: 14px;
    padding: 50px 20px 20px;
    border-radius: 8px;
    overflow: auto;
    max-height: 600px;
    border: none;
    margin: 0;
}
.code-block::-webkit-scrollbar       { width: 8px; height: 8px; }
.code-block::-webkit-scrollbar-track { background: #222; border-radius: 4px; }
.code-block::-webkit-scrollbar-thumb { background: #888; border-radius: 4px; }
.code-block::-webkit-scrollbar-thumb:hover { background: #aaa; }

.copy-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #333;
    border: 1px solid #555;
    color: #fff;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    z-index: 10;
    transition: all 0.3s ease;
}
.copy-btn:hover  { background: #555; border-color: #777; }
.copy-btn.copied { background: #28a745; border-color: #28a745; }
.copy-btn i      { margin-right: 5px; }
</style>
