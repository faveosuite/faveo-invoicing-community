<template>
    <div>
        <AppAlert :componentName="COMPONENT" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.pipedrive_settings') }}</h4>
                <div class="card-tools">
                    <button v-if="activeTab === 'field-mapping' && connectionStatus === 'connected'"
                        class="btn btn-tool" v-tooltip :title="__('message.click_pipedrive_sync')"
                        :disabled="syncing" @click="syncFields">
                        <i class="fas fa-sync" :class="{ 'fa-spin': syncing }"></i>
                    </button>
                </div>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <template v-else>
                <div class="card-body">

                    <!-- Tabs -->
                    <ul class="nav nav-tabs mb-4">
                        <li class="nav-item" v-for="tab in tabs" :key="tab.key">
                            <a class="nav-link" :class="{ active: activeTab === tab.key }"
                               href="#" @click.prevent="activeTab = tab.key">
                                <i :class="tab.icon + ' me-1'"></i>{{ tab.label }}
                            </a>
                        </li>
                    </ul>

                    <!-- ── Tab 1: Connection ────────────────────────────── -->
                    <div v-show="activeTab === 'connection'">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    {{ __('message.pipedrive_key') }}<span class="text-danger ms-1">*</span>
                                </label>
                                <div class="input-group">
                                    <input
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.apiKey || connectionStatus === 'failed' }"
                                        :value="form.apiKey"
                                        :placeholder="__('message.enter_pipedrive_api')"
                                        @input="e => { form.apiKey = e.target.value; connectionStatus = 'idle'; setFieldError('apiKey', undefined) }"
                                        @keyup.enter="connect"
                                    />
                                    <button
                                        class="btn"
                                        :class="connectionStatus === 'connected' ? 'btn-success' : 'btn-outline-secondary'"
                                        :disabled="connecting || !form.apiKey.trim()"
                                        @click="connect"
                                    >
                                        <span v-if="connecting" class="spinner-border spinner-border-sm me-1"></span>
                                        <i v-else-if="connectionStatus === 'connected'" class="fas fa-check me-1"></i>
                                        <i v-else class="fas fa-plug me-1"></i>
                                        {{ connectionStatus === 'connected' ? __('message.connected') : __('message.connect') }}
                                    </button>
                                </div>
                                <div v-if="errors.apiKey" class="text-danger small mt-1">{{ errors.apiKey }}</div>
                                <div v-else-if="connectionStatus === 'failed'" class="text-danger small mt-1">
                                    {{ __('message.pipedrive_error') }}
                                </div>
                            </div>
                        </div>

                        <template v-if="connectionStatus === 'connected'">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            role="switch"
                                            id="requireVerification"
                                            v-model="form.requireVerification"
                                            style="cursor:pointer"
                                        />
                                        <label class="form-check-label" for="requireVerification" v-tooltip
                                            :title="__('message.pipedrive_user_verification_tooltip')">
                                            {{ __('message.user_verification') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <action-button action="save" :loading="savingSettings" @click="saveSettings" />
                        </template>
                    </div>

                    <!-- ── Tab 2: Field Mapping ─────────────────────────── -->
                    <div v-show="activeTab === 'field-mapping'">
                        <div v-if="connectionStatus !== 'connected'" class="alert alert-warning py-2 mb-3">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            {{ __('message.pipedrive_config_info') }}
                        </div>

                        <template v-else>
                            <!-- Group Tabs -->
                            <ul class="nav nav-tabs mb-3">
                                <li class="nav-item" v-for="tab in groupTabs" :key="tab.groupId">
                                    <a
                                        class="nav-link"
                                        :class="{ active: activeGroupId === tab.groupId }"
                                        href="#"
                                        @click.prevent="switchGroup(tab.groupId)"
                                    >
                                        <i :class="tab.icon + ' me-1'"></i>{{ tab.label }}
                                    </a>
                                </li>
                            </ul>

                            <inline-loader v-if="loadingMapping" />

                            <template v-else>
                                <div v-for="(row, index) in rows" :key="index" class="row mb-3 align-items-end">
                                    <div class="col-5">
                                        <SelectField
                                            :name="'select1_' + index"
                                            :label="index === 0 ? __('message.pipedrive_fields') : ''"
                                            :elements="availablePipedriveOptions(index)"
                                            :value="row.pipedriveField"
                                            :onChange="(val) => onPipedriveChange(index, val)"
                                            :searchable="true"
                                            :clearable="true"
                                            :placeholder="__('message.pipe_select_option')"
                                        />
                                    </div>
                                    <div class="col-5">
                                        <SelectField
                                            :name="'select2_' + index"
                                            :label="index === 0 ? __('message.faveo_invoicing_fields') : ''"
                                            :elements="row.faveoOptions"
                                            :value="row.faveoField"
                                            :onChange="(val) => { row.faveoField = val }"
                                            :searchable="true"
                                            :clearable="true"
                                            :placeholder="__('message.pipe_select_option')"
                                        />
                                    </div>
                                    <div class="col-2 mb-3">
                                        <button
                                            v-if="index > 0"
                                            type="button"
                                            class="btn btn-light table_btn"
                                            :title="__('message.delete_attribute')"
                                            v-tooltip
                                            @click="deleteRow(index)"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <action-button
                                        action="add"
                                        type="button"
                                        :disabled="rows.length >= allPipedriveOptions.length"
                                        :label="__('message.add-new')"
                                        @click="addRow"
                                    />
                                </div>

                                <action-button action="save" :loading="savingMapping" @click="saveMapping" />
                            </template>
                        </template>
                    </div>

                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import SelectField from '@/themes/adminlte/components/forms/SelectField.vue'
import { apiKeySchema } from '@/validations/admin/pipedriveValidations'

const COMPONENT = 'pipedrive-settings'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const { errors, setErrors, setFieldError } = useForm()

const loading        = ref(true)
const connecting     = ref(false)
const savingSettings = ref(false)
const loadingMapping = ref(false)
const savingMapping  = ref(false)
const syncing        = ref(false)

const connectionStatus = ref('idle')   // 'idle' | 'connected' | 'failed'
const activeTab        = ref('connection')

const tabs = [
    { key: 'connection',   icon: 'fas fa-plug',    label: __('message.connection')    },
    { key: 'field-mapping', icon: 'fas fa-sliders', label: __('message.field_mapping') },
]

const form = reactive({ apiKey: '', requireVerification: false })

// ── Group tabs (Person / Organization / Deal) ─────────────────────────────
const groupTabs      = ref([])
const activeGroupId  = ref(null)
const allPipedriveOptions = ref([])
const localOptions        = ref([])
const rows                = ref([])

watch(activeTab, async tab => {
    if (tab === 'field-mapping' && connectionStatus.value === 'connected' && activeGroupId.value && !rows.value.length) {
        await loadMappingForGroup(activeGroupId.value)
    }
})

// ── Init ──────────────────────────────────────────────────────────────────
onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/settings/pipedrive`)
        const d   = res.data?.data ?? {}

        form.apiKey              = d.pipedrive_key                       ?? ''
        form.requireVerification = d.require_pipedrive_user_verification ?? false

        const groups = d.groups ?? {}
        if (groups.personId) {
            groupTabs.value = [
                { groupId: groups.personId,       label: __('message.pipe_person'),  icon: 'fas fa-user'      },
                { groupId: groups.organizationId, label: __('message.organization'), icon: 'fas fa-building'  },
                { groupId: groups.dealId,         label: __('message.pipe_deal'),    icon: 'fas fa-handshake' },
            ]
            activeGroupId.value = groups.personId
        }

        if (form.apiKey) {
            connectionStatus.value = 'connected'
            await loadMappingForGroup(activeGroupId.value)
        }
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

// ── Connect ────────────────────────────────────────────────────────────────
async function connect() {
    try { await apiKeySchema.validate({ apiKey: form.apiKey }) }
    catch (err) { setErrors({ apiKey: err.message }); return }

    connecting.value = true
    try {
        const res = await http.post(`${baseUrl}/updatepipedriveDetails`, {
            pipedrive_key:                      form.apiKey,
            require_pipedrive_user_verification: form.requireVerification,
            status: 1,
        })
        connectionStatus.value = 'connected'
        successHandler(res, COMPONENT)
        if (activeGroupId.value && !rows.value.length) {
            await loadMappingForGroup(activeGroupId.value)
        }
    } catch (e) {
        connectionStatus.value = 'failed'
        errorHandler(e, COMPONENT)
    } finally {
        connecting.value = false
    }
}

// ── Save settings (verification toggle) ───────────────────────────────────
async function saveSettings() {
    savingSettings.value = true
    try {
        const res = await http.patch(`${baseUrl}/settings/pipedrive`, {
            pipedrive_key:                      form.apiKey,
            require_pipedrive_user_verification: form.requireVerification,
            status: true,
        })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        savingSettings.value = false
    }
}

// ── Load mapping for a group ──────────────────────────────────────────────
async function loadMappingForGroup(groupId) {
    if (!groupId) return
    loadingMapping.value = true
    try {
        const res = await http.get(`${baseUrl}/pipedrive/mapping/${groupId}`)
        applyMapping(res.data?.data ?? {})
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loadingMapping.value = false
    }
}

function applyMapping(data) {
    const pipedriveFields = data.pipedriveData?.pipedrive_fields ?? []
    const localFields     = data.pipedriveData?.local_fields     ?? []

    allPipedriveOptions.value = toOptions(pipedriveFields, 'field_name')
    localOptions.value        = toOptions(localFields,     'field_name')

    const mapped = pipedriveFields.filter(f => f.selected_field && Object.keys(f.selected_field).length > 0)

    if (mapped.length > 0) {
        rows.value = mapped.map(f => {
            const isLocal    = !(f.pipedrive_options?.length)
            const faveoOpts  = isLocal ? localOptions.value : toOptions(f.pipedrive_options ?? [], 'value')
            const faveoField = faveoOpts.find(o => o.id === (f.selected_field?.id ?? null)) ?? null
            return {
                pipedriveField: allPipedriveOptions.value.find(o => o.id === f.id) ?? null,
                faveoField,
                faveoOptions:  faveoOpts,
                isFaveoField:  isLocal,
            }
        })
    } else {
        rows.value = [{ pipedriveField: null, faveoField: null, faveoOptions: localOptions.value, isFaveoField: true }]
    }
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
    rows.value[index].pipedriveField = val
    rows.value[index].faveoField     = null

    if (!val) {
        rows.value[index].faveoOptions = localOptions.value
        rows.value[index].isFaveoField = true
        return
    }

    try {
        const res = await http.post(`${baseUrl}/pipedrive/get-dropdown`, { pipedrive_field_id: val.id })
        const d = res.data?.data ?? {}
        rows.value[index].faveoOptions = toOptions(d.options ?? [], 'value')
        rows.value[index].isFaveoField = d.is_faveo_options ?? true
    } catch {
        rows.value[index].faveoOptions = localOptions.value
        rows.value[index].isFaveoField = true
    }
}

function availablePipedriveOptions(rowIndex) {
    const used = rows.value
        .filter((_, i) => i !== rowIndex)
        .map(r => r.pipedriveField?.id)
        .filter(Boolean)
    return allPipedriveOptions.value.filter(o => !used.includes(o.id))
}

// ── Add / delete row ──────────────────────────────────────────────────────
function addRow() {
    if (rows.value.length >= allPipedriveOptions.value.length) return
    rows.value.push({ pipedriveField: null, faveoField: null, faveoOptions: localOptions.value, isFaveoField: true })
}

function deleteRow(index) {
    rows.value.splice(index, 1)
}

// ── Save mapping ──────────────────────────────────────────────────────────
async function saveMapping() {
    const hasEmpty = rows.value.some(r => !r.pipedriveField || !r.faveoField)
    if (hasEmpty) {
        errorHandler({ message: __('message.pipe_select_field') }, COMPONENT)
        return
    }

    savingMapping.value = true
    try {
        const res = await http.post(`${baseUrl}/sync/pipedrive`, {
            group_id: activeGroupId.value,
            select1:  rows.value.map(r => ({ id: r.pipedriveField.id })),
            select2:  rows.value.map(r => ({ id: r.faveoField.id, faveo_fields: r.isFaveoField })),
        })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        savingMapping.value = false
    }
}

// ── Sync fields ───────────────────────────────────────────────────────────
async function syncFields() {
    syncing.value = true
    try {
        const res = await http.get(`${baseUrl}/syncing/pipedriveFields`)
        successHandler(res, COMPONENT)
        await loadMappingForGroup(activeGroupId.value)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        syncing.value = false
    }
}
</script>
