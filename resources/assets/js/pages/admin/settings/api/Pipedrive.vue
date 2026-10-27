<template>
    <div>
        <AppAlert :componentName="COMPONENT" />

        <div class="card card-light">
            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">
                    <ul class="nav nav-tabs mb-4">
                        <li class="nav-item" v-for="tab in tabs" :key="tab.key">
                            <a class="nav-link" :class="{ active: activeTab === tab.key }"
                               href="#" @click.prevent="activeTab = tab.key">
                                <i :class="tab.icon + ' me-1'"></i>{{ tab.label }}
                            </a>
                        </li>
                    </ul>

                    <!-- Tab: Connection -->
                    <div v-show="activeTab === 'connection'">
                        <div class="card card-light">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('message.pipedrive') }}</h4>
                                <span v-if="connected" class="badge bg-success ms-2">{{ __('message.connected') }}</span>
                                <span v-else class="badge bg-warning text-dark ms-2">{{ __('message.zoho_not_connected') }}</span>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <TextField
                                            name="apiKey"
                                            type="password"
                                            :label="__('message.pipedrive_key')"
                                            :value="form.apiKey"
                                            :required="!form.hasKey"
                                            :placeholder="form.hasKey ? __('message.zoho_secret_saved_placeholder') : __('message.enter_pipedrive_api')"
                                            :error="errors.apiKey"
                                            :onChange="(val, key) => { setFieldError(key, undefined); form[key] = val }"
                                        />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <span class="form-label fw-bold d-block">
                                            {{ __('message.user_verification') }}
                                            <ToolTip :message="__('message.pipedrive_user_verification_tooltip')" size="small" />
                                        </span>
                                        <Switch name="requireVerification" :value="form.requireVerification" :onChange="(val) => form.requireVerification = val" />
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <action-button action="save" :icon="connected ? 'fas fa-save' : 'fas fa-plug'"
                                    :label="connected ? __('message.save') : __('message.connect')"
                                    :loading="savingSettings" @click="saveSettings" />
                                <action-button v-if="connected" variant="secondary" icon="fas fa-unlink" class="ms-2"
                                    :label="__('message.zoho_disconnect')" @click="showDisconnect = true" />
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Field Mapping -->
                    <div v-show="activeTab === 'field-mapping'">
                        <div class="card card-light">
                            <div class="card-header">
                                <h4 class="card-title">
                                    {{ __('message.pipedrive_mapping') }}
                                    <ToolTip :message="__('message.pipedrive_config_info')" size="small" />
                                </h4>
                                <div class="card-tools">
                                    <button class="btn btn-tool" v-tooltip="__('message.sync_fields')"
                                        :disabled="syncing" @click="syncFields">
                                        <i class="fas fa-sync" :class="{ 'fa-spin': syncing }"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <ul class="nav nav-tabs mb-3">
                                    <li class="nav-item" v-for="tab in groupTabs" :key="tab.groupId">
                                        <a class="nav-link" :class="{ active: activeGroupId === tab.groupId }"
                                           href="#" @click.prevent="switchGroup(tab.groupId)">
                                            <i :class="tab.icon + ' me-1'"></i>{{ tab.label }}
                                        </a>
                                    </li>
                                </ul>

                                <div v-if="loadingMapping" class="row justify-content-center py-3"><loader /></div>

                                <template v-else>
                                    <div v-if="!allPipedriveOptions.length" class="text-muted">
                                        {{ __('message.pipedrive_no_fields') }}
                                    </div>
                                    <template v-else>
                                        <div v-if="missingRequired.length" class="alert alert-warning py-2">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            {{ __('message.pipedrive_required_missing', { fields: missingRequired.join(', ') }) }}
                                        </div>

                                        <div v-for="(row, index) in rows" :key="row.key" class="row mb-3 align-items-end">
                                            <div class="col-5">
                                                <DynamicSelect
                                                    :name="'select1_' + index"
                                                    :label="index === 0 ? __('message.pipedrive_fields') : ''"                                                    :elements="availablePipedriveOptions(index)"
                                                    :value="row.pipedriveField"
                                                    :onChange="(val) => onPipedriveChange(index, val)"
                                                    :searchable="true"
                                                    :clearable="true"
                                                />
                                            </div>
                                            <div class="col-5">
                                                <DynamicSelect
                                                    :name="'select2_' + index"
                                                    :label="index === 0 ? __('message.faveo_field') : ''"
                                                    :elements="row.faveoOptions"
                                                    :value="row.faveoField"
                                                    :multiple="row.isMulti"
                                                    :onChange="(val) => { row.faveoField = val }"
                                                    :searchable="true"
                                                    :clearable="true"
                                                />
                                            </div>
                                            <div class="col-2 mb-3">
                                                <button type="button" class="btn btn-light table_btn"
                                                    v-tooltip="__('message.delete_attribute')" @click="deleteRow(index)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <p v-if="hasRequiredFields" class="text-muted small mb-3"><span class="text-danger">*</span> {{ __('message.pipedrive_required_legend') }}</p>
                                        <action-button action="add" type="button"
                                            :disabled="rows.length >= allPipedriveOptions.length"
                                            :label="__('message.add-new')" @click="addRow" />
                                    </template>
                                </template>
                            </div>
                            <div v-if="allPipedriveOptions.length" class="card-footer">
                                <action-button action="save" :label="__('message.save_mapping')" :loading="savingMapping" @click="saveMapping" />
                                <action-button variant="secondary" icon="fas fa-paper-plane" class="ms-2"
                                    :label="__('message.zoho_send_test')" v-tooltip="__('message.pipedrive_send_test_tooltip')"
                                    :loading="testing" @click="sendTest" />
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <DeleteModal
            v-if="showDisconnect"
            :showModal="true"
            :onClose="() => showDisconnect = false"
            deleteUrl="/settings/pipedrive/disconnect"
            method="post"
            :title="__('message.zoho_disconnect')"
            :message="__('message.pipedrive_disconnect_confirm')"
            :btnLabel="__('message.zoho_disconnect')"
            btnIcon="fa-unlink"
            :componentName="COMPONENT"
            @deleted="loadSettings"
        />
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { useAlertStore } from '@/core/stores/alert.js'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { validateForm } from '@/helpers/formUtils.js'
import TextField from '@/components/Reusable/FormField/TextField.vue'
import DynamicSelect from '@/components/Reusable/FormField/DynamicSelect.vue'
import Switch from '@/components/Reusable/FormField/Switch.vue'
import ToolTip from '@/components/Reusable/Tooltip.vue'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'
import { apiKeySchema } from '@/validations/admin/pipedriveValidations'

const COMPONENT = 'pipedrive-settings'

const { errors, setErrors, setFieldError } = useForm()

function showError(message) {
    useAlertStore().setAlert({ type: 'danger', message, component_name: COMPONENT })
}

const loading        = ref(true)
const savingSettings = ref(false)
const loadingMapping = ref(false)
const savingMapping  = ref(false)
const syncing        = ref(false)
const testing        = ref(false)
const showDisconnect = ref(false)

// hasKey: a key is saved server-side; leaving the field blank keeps it.
const form      = reactive({ apiKey: '', hasKey: false, requireVerification: false })
const connected = computed(() => form.hasKey)

// ── Tabs ──────────────────────────────────────────────────────────────────
const activeTab = ref('connection')

// Field mapping only makes sense once Pipedrive is connected.
const tabs = computed(() => [
    { key: 'connection', icon: 'fas fa-plug', label: __('message.connection') },
    ...(connected.value ? [{ key: 'field-mapping', icon: 'fas fa-sliders', label: __('message.field_mapping') }] : []),
])

// ── Group tabs (Person / Organization / Deal) ─────────────────────────────
const groupTabs           = ref([])
const activeGroupId       = ref(null)
const allPipedriveOptions = ref([])
const localOptions        = ref([])
const rows                = ref([])
let rowKey = 0

const hasRequiredFields = computed(() => allPipedriveOptions.value.some(o => o.mandatory))

// Only once something is mapped: an unmapped group is simply not sent to Pipedrive.
const missingRequired = computed(() => {
    if (!rows.value.some(r => r.pipedriveField)) return []
    const mapped = new Set(rows.value.map(r => r.pipedriveField?.id))
    return allPipedriveOptions.value.filter(o => o.mandatory && !mapped.has(o.id)).map(o => o.label)
})

// ── Init ──────────────────────────────────────────────────────────────────
async function loadSettings() {
    const res = await http.get(`/settings/pipedrive`)
    const d   = res.data?.data ?? {}

    form.apiKey              = d.pipedrive_key ?? ''
    form.hasKey              = !!d.has_key
    form.requireVerification = d.require_pipedrive_user_verification ?? false

    const groups = d.groups ?? {}
    if (groups.personId) {
        groupTabs.value = [
            { groupId: groups.personId,       label: __('message.pipe_person'),  icon: 'fas fa-user'      },
            { groupId: groups.organizationId, label: __('message.organization'), icon: 'fas fa-building'  },
            { groupId: groups.dealId,         label: __('message.pipe_deal'),    icon: 'fas fa-handshake' },
        ]
        activeGroupId.value ??= groups.personId
    }

    if (!connected.value) activeTab.value = 'connection'
}

onMounted(async () => {
    try {
        await loadSettings()
        if (connected.value) await loadMappingForGroup(activeGroupId.value)
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        loading.value = false
    }
})

// ── Save settings (key + verification toggle, validates against Pipedrive) ─
async function saveSettings() {
    if (!await validateForm(apiKeySchema, form, setErrors)) return

    savingSettings.value = true
    try {
        const res = await http.patch(`/settings/pipedrive`, {
            pipedrive_key:                       form.apiKey || null,
            require_pipedrive_user_verification: form.requireVerification,
            status: 1,
        })
        successHandler(res, COMPONENT)
        const wasConnected = connected.value
        await loadSettings()
        if (!wasConnected && activeGroupId.value) await loadMappingForGroup(activeGroupId.value)
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        savingSettings.value = false
    }
}

// ── Load mapping for a group ──────────────────────────────────────────────
async function loadMappingForGroup(groupId) {
    if (!groupId) return
    loadingMapping.value = true
    try {
        const res = await http.get(`/pipedrive/mapping/${groupId}`)
        applyMapping(res.data?.data ?? {})
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        loadingMapping.value = false
    }
}

const emptyRow = () => ({ key: ++rowKey, pipedriveField: null, faveoField: null, faveoOptions: localOptions.value, isFaveoField: true, isMulti: false })

function applyMapping(data) {
    const pipedriveFields = data.pipedriveData?.pipedrive_fields ?? []
    const localFields     = data.pipedriveData?.local_fields     ?? []

    allPipedriveOptions.value = pipedriveFields.map(f => ({
        id:        f.id,
        name:      f.field_name,
        markRequired: !!f.is_mandatory,
        label:     f.field_name,
        mandatory: !!f.is_mandatory,
    }))
    localOptions.value = toOptions(localFields, 'field_name')

    const mapped = pipedriveFields.filter(f => f.selected_field && Object.keys(f.selected_field).length > 0)

    rows.value = mapped.map(f => {
        const isLocal    = !(f.pipedrive_options?.length)
        const isMulti    = !isLocal && f.field_type === 'set'
        const faveoOpts  = isLocal ? localOptions.value : toOptions(f.pipedrive_options ?? [], 'value')
        const faveoField = isMulti
            ? faveoOpts.filter(o => (f.selected_field ?? []).some(s => s.id === o.id))
            : faveoOpts.find(o => o.id === (f.selected_field?.id ?? null)) ?? null
        return {
            key:            ++rowKey,
            pipedriveField: allPipedriveOptions.value.find(o => o.id === f.id) ?? null,
            faveoField,
            faveoOptions:   faveoOpts,
            isFaveoField:   isLocal,
            isMulti,
        }
    })
    if (!rows.value.length) rows.value = [emptyRow()]
}

function toOptions(arr, nameKey) {
    return (arr ?? []).map(f => ({ id: f.id, name: f[nameKey] ?? f.value ?? '' }))
}

// ── Group tab switch ──────────────────────────────────────────────────────
async function switchGroup(groupId) {
    if (groupId === activeGroupId.value) return
    activeGroupId.value = groupId
    await loadMappingForGroup(groupId)
}

// ── Pipedrive field change ────────────────────────────────────────────────
async function onPipedriveChange(index, val) {
    const row = rows.value[index]
    row.pipedriveField = val
    row.faveoField     = null
    row.isMulti        = false

    if (!val) {
        row.faveoOptions = localOptions.value
        row.isFaveoField = true
        return
    }

    try {
        const res = await http.post(`/pipedrive/get-dropdown`, { pipedrive_field_id: val.id })
        const d = res.data?.data ?? {}
        row.faveoOptions = toOptions(d.options ?? [], 'value')
        row.isFaveoField = d.is_faveo_options ?? true
        row.isMulti      = d.is_multi ?? false
        if (row.isMulti) row.faveoField = []
    } catch {
        row.faveoOptions = localOptions.value
        row.isFaveoField = true
    }
}

// A Pipedrive field can be mapped once: hide the ones other rows already use.
function availablePipedriveOptions(rowIndex) {
    const used = new Set(rows.value
        .filter((_, i) => i !== rowIndex)
        .map(r => r.pipedriveField?.id)
        .filter(Boolean))
    return allPipedriveOptions.value.filter(o => !used.has(o.id))
}

// ── Add / delete row ──────────────────────────────────────────────────────
function addRow() {
    if (rows.value.length >= allPipedriveOptions.value.length) return
    rows.value.push(emptyRow())
}

function deleteRow(index) {
    rows.value.splice(index, 1)
    if (!rows.value.length) rows.value.push(emptyRow())
}

// ── Save mapping ──────────────────────────────────────────────────────────
const hasFaveo = (r) => r.isMulti ? !!r.faveoField?.length : !!r.faveoField

async function saveMapping() {
    // A row with only one side picked would otherwise be dropped (and its old mapping cleared) without a word.
    if (rows.value.some(r => !!r.pipedriveField !== hasFaveo(r))) {
        showError(__('message.zoho_incomplete_rows'))
        return
    }

    if (missingRequired.value.length) {
        showError(__('message.pipedrive_required_missing', { fields: missingRequired.value.join(', ') }))
        return
    }

    const filled = rows.value.filter(r => r.pipedriveField && hasFaveo(r))

    savingMapping.value = true
    try {
        const res = await http.post(`/sync/pipedrive`, {
            group_id: activeGroupId.value,
            select1:  filled.map(r => ({ id: r.pipedriveField.id })),
            select2:  filled.map(r => ({
                id: r.isMulti ? r.faveoField.map(o => o.id) : r.faveoField.id,
                faveo_fields: r.isFaveoField,
            })),
        })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        savingMapping.value = false
    }
}

// ── Sync fields ───────────────────────────────────────────────────────────
async function syncFields() {
    syncing.value = true
    try {
        const res = await http.post(`/syncing/pipedriveFields`)
        successHandler(res, COMPONENT)
        await loadMappingForGroup(activeGroupId.value)
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        syncing.value = false
    }
}

// ── Send test ─────────────────────────────────────────────────────────────
async function sendTest() {
    testing.value = true
    try {
        const res = await http.post(`/pipedrive/test`)
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        testing.value = false
    }
}
</script>
