<template>
    <div>
        <AppAlert componentName="whatsapp-panel" />

        <!-- Add new number -->
        <div v-if="order.whatsapp_signup_enabled && order.whatsapp_app_id && order.whatsapp_config_id"
             class="d-flex justify-content-end mb-3">
            <button type="button" class="btn btn-primary btn-sm btn-modern" @click="openAddNumber">
                <i class="fab fa-whatsapp me-1"></i>{{ __('message.add_new_number') }}
            </button>
        </div>

        <DataTable v-if="active" :key="tableKey" :url="numbersUrl" :dataColumns="columns" :option="options">
            <template #phone_number_id="{ row }">
                <div class="d-flex align-items-center gap-2">
                    <span>••••</span>
                    <button class="btn btn-light btn-sm"
                            v-tooltip="copiedId === row.id ? __('message.copied') : __('message.copy')"
                            @click="copyValue(row.id, row.phone_number_id)">
                        <i :class="copiedId === row.id ? 'fas fa-check text-success' : 'fas fa-copy'"></i>
                    </button>
                </div>
            </template>
            <template #action="{ row }">
                <div class="d-flex gap-1">
                    <button class="btn btn-light btn-sm"
                            v-tooltip="__('message.edit_webhook_url')"
                            @click="openEdit(row)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-light btn-sm"
                            v-tooltip="__('message.Delete')"
                            @click="confirmDelete(row)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </template>
        </DataTable>

        <!-- Add: webhook URL modal (before launching Facebook signup) -->
        <Modal :showModal="showWebhookModal" :onClose="closeWebhookModal" :showCloseBtn="false">
            <template #title>
                <h5 class="modal-title fw-bold">{{ __('message.whatsapp_product_heading') }}</h5>
            </template>
            <template #fields>
                <ClientField type="text" name="webhookUrl" required
                             :label="__('message.callback_url')"
                             v-model="webhookUrl"
                             placeholder="https://example.com"
                             :error="webhookError" />
            </template>
            <template #controls>
                <action-button action="confirm"
                               :label="__('message.save')"
                               :loading="webhookBusy"
                               :disabled="!webhookUrl"
                               @click="submitWebhook" />
            </template>
        </Modal>

        <!-- Edit webhook URL modal -->
        <Modal :showModal="showEditModal" :onClose="closeEditModal" :showCloseBtn="false">
            <template #title>
                <h5 class="modal-title fw-bold">{{ __('message.edit_webhook_url') }}</h5>
            </template>
            <template #fields>
                <ClientField type="text" name="editWebhookUrl" required
                             :label="__('message.callback_url')"
                             v-model="editUrl"
                             placeholder="https://example.com"
                             :error="editError" />
            </template>
            <template #controls>
                <action-button action="save"
                               :loading="editBusy"
                               :disabled="!editUrl"
                               @click="submitEdit" />
            </template>
        </Modal>

        <!-- Delete confirmation -->
        <Modal :showModal="showDeleteModal" :onClose="closeDeleteModal" :showCloseBtn="false">
            <template #title>
                <h5 class="modal-title">{{ __('message.are_you_sure') }}</h5>
            </template>
            <template #fields>
                <p class="mb-0">{{ __('message.delete_whatsapp_user_confirm') }} {{ deleteRow?.phone_number || '' }}?</p>
            </template>
            <template #controls>
                <button type="button" class="btn btn-light me-2" @click="closeDeleteModal">{{ __('message.cancel') }}</button>
                <action-button action="delete" :loading="deleteBusy" @click="submitDelete" />
            </template>
        </Modal>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import Modal from '@/themes/porto/components/common/Modal.vue'
import AppAlert from '@/components/Reusable/Alert.vue'

const props = defineProps({
    order:  { type: Object, default: null },
    active: { type: Boolean, default: false },
})

const el      = document.getElementById('app-client')
const baseUrl = el?.dataset?.baseUrl ?? ''
const COMPONENT  = 'whatsapp-panel'

const copiedId = ref(null)

// DataTable: lazy-mounted when the tab is active (v-if="active"); bump tableKey to refresh.
const tableKey   = ref(0)
const numbersUrl = computed(() => `${baseUrl}/whatsapp-client-numbers/${props.order.id}`)
const columns    = ['phone_number', 'waba_id', 'phone_number_id', 'business_id', 'created_at', 'action']
const options    = reactive({
    headings: {
        phone_number:    () => __('message.phone_number'),
        waba_id:         () => __('message.waba_id'),
        phone_number_id: () => __('message.phone_number_id'),
        business_id:     () => __('message.business_id'),
        created_at:      () => __('message.create_at'),
        action:          () => __('message.action'),
    },
    sortable:   ['phone_number', 'waba_id', 'business_id', 'created_at'],
    filterable: true,
})

function refreshTable() {
    tableKey.value++
}

async function copyValue(id, text) {
    if (!text) return
    try { await navigator.clipboard.writeText(text) } catch { /* ignore */ }
    copiedId.value = id
    setTimeout(() => { copiedId.value = null }, 2000)
}

/* ── Add new number: webhook URL → Facebook embedded signup ── */
const showWebhookModal = ref(false)
const webhookUrl       = ref('')
const webhookError     = ref('')
const webhookBusy      = ref(false)

function isValidUrl(value) {
    try { new URL(value); return true } catch { return false }
}

function openAddNumber() {
    webhookUrl.value   = ''
    webhookError.value = ''
    showWebhookModal.value = true
}
function closeWebhookModal() { showWebhookModal.value = false }

async function submitWebhook() {
    webhookError.value = ''
    if (!isValidUrl(webhookUrl.value)) {
        webhookError.value = __('message.enter_valid_url')
        return
    }
    webhookBusy.value = true
    try {
        // Store the webhook URL server-side (session) before the FB signup flow.
        await http.post(`${baseUrl}/url-save`, { url: webhookUrl.value })
        closeWebhookModal()
        await launchWhatsAppSignup()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        webhookBusy.value = false
    }
}

/* ── Facebook SDK + embedded signup ─────────────────────────── */
let fbData  = null
let fbToken = null

function loadFbSdk(appId) {
    return new Promise((resolve) => {
        if (window.FB) { resolve(); return }
        window.fbAsyncInit = function () {
            window.FB.init({ appId, autoLogAppEvents: true, xfbml: true, version: 'v24.0' })
            resolve()
        }
        if (document.getElementById('facebook-jssdk')) { resolve(); return }
        const s = document.createElement('script')
        s.id = 'facebook-jssdk'
        s.async = true
        s.defer = true
        s.crossOrigin = 'anonymous'
        s.src = 'https://connect.facebook.net/en_US/sdk.js'
        document.head.appendChild(s)
    })
}

function onFbMessage(event) {
    if (!event.origin.endsWith('facebook.com')) return
    try {
        const data = JSON.parse(event.data)
        if (data.type === 'WA_EMBEDDED_SIGNUP') {
            fbData = data
            saveWaba()
        }
    } catch { /* non-JSON message from FB iframe — ignore */ }
}

function fbLoginCallback(response) {
    if (response.authResponse) {
        fbToken = response.authResponse.code
        saveWaba()
    }
}

async function launchWhatsAppSignup() {
    fbData = null
    fbToken = null
    await loadFbSdk(props.order.whatsapp_app_id)
    window.FB.login(fbLoginCallback, {
        config_id: props.order.whatsapp_config_id,
        response_type: 'code',
        override_default_response_type: true,
        extras: { setup: {} },
    })
}

async function saveWaba() {
    // Fires from two async sources (FB.login callback + window message);
    // only proceed once both the signup payload and the auth code arrived.
    if (!fbData || !fbToken) return
    const payload = {
        waba_id:         fbData.data?.waba_id,
        phone_number_id: fbData.data?.phone_number_id,
        business_id:     fbData.data?.business_id,
        code:            fbToken,
        order_id:        props.order.id,
    }
    fbData = null
    fbToken = null
    try {
        const res = await http.post(`${baseUrl}/save-waba-id`, payload)
        successHandler(res, COMPONENT)
        refreshTable()
    } catch (e) {
        errorHandler(e, COMPONENT)
    }
}

onMounted(() => window.addEventListener('message', onFbMessage))
onBeforeUnmount(() => window.removeEventListener('message', onFbMessage))

/* ── Edit webhook URL ───────────────────────────────────────── */
const showEditModal = ref(false)
const editId        = ref(null)
const editUrl       = ref('')
const editError     = ref('')
const editBusy      = ref(false)

async function openEdit(row) {
    editError.value = ''
    editId.value    = row.id
    editUrl.value   = row.callback_url ?? ''
    showEditModal.value = true
    // Pull the freshest stored URL.
    try {
        const res = await http.get(`${baseUrl}/get-webhook-url`, { params: { id: row.id } })
        editUrl.value = res.data?.data?.url ?? editUrl.value
    } catch { /* keep the row value */ }
}
function closeEditModal() { showEditModal.value = false }

async function submitEdit() {
    editError.value = ''
    if (!isValidUrl(editUrl.value)) {
        editError.value = __('message.enter_valid_url')
        return
    }
    editBusy.value = true
    try {
        const res = await http.post(`${baseUrl}/webhook-url-edit`, { id: editId.value, url: editUrl.value })
        successHandler(res, COMPONENT)
        closeEditModal()
        refreshTable()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        editBusy.value = false
    }
}

/* ── Delete / deregister ────────────────────────────────────── */
const showDeleteModal = ref(false)
const deleteRow       = ref(null)
const deleteBusy      = ref(false)

function confirmDelete(row) {
    deleteRow.value = row
    showDeleteModal.value = true
}
function closeDeleteModal() { showDeleteModal.value = false }

async function submitDelete() {
    deleteBusy.value = true
    try {
        const res = await http.post(`${baseUrl}/whatsapp-deregister`, { id: deleteRow.value?.id })
        successHandler(res, COMPONENT)
        closeDeleteModal()
        refreshTable()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        deleteBusy.value = false
    }
}
</script>
