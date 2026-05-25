<template>
    <div>
        <AppAlert :componentName="COMPONENT" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.mapping_fields') }}</h4>
                <div class="card-tools">
                    <button
                        class="btn btn-tool"
                        v-tooltip
                        :title="__('message.click_pipedrive_sync')"
                        :disabled="syncing"
                        @click="syncFields"
                    >
                        <i class="fas fa-sync" :class="{ 'fa-spin': syncing }"></i>
                    </button>
                </div>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <template v-else>
                <div class="card-body">
                    <!-- Info alert -->
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>{{ __('message.pipedrive_config_info') }}
                    </div>

                    <!-- Tabs -->
                    <ul class="nav nav-tabs mb-4">
                        <li class="nav-item" v-for="tab in tabs" :key="tab.groupId">
                            <a
                                class="nav-link"
                                :class="{ active: activeGroupId === tab.groupId }"
                                href="#"
                                @click.prevent="switchTab(tab.groupId)"
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

                        <div class="mb-1">
                            <action-button
                                action="add"
                                type="button"
                                :disabled="rows.length >= allPipedriveOptions.length"
                                :label="__('message.add-new')"
                                @click="addRow"
                            />
                        </div>
                    </template>
                </div>

                <div class="card-footer" v-if="!loading && !loadingMapping">
                    <action-button action="save" :loading="savingMapping" @click="saveMapping" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import SelectField from '@/themes/adminlte/components/forms/SelectField.vue'

const COMPONENT = 'pipedrive-settings'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading        = ref(true)
const loadingMapping = ref(false)
const savingMapping  = ref(false)
const syncing        = ref(false)
const activeGroupId  = ref(null)

const tabs               = ref([])
const allPipedriveOptions = ref([])
const localOptions        = ref([])
const rows                = ref([])

// ── Helpers ───────────────────────────────────────────────────────────────────
function toOptions(arr, nameKey) {
    return (arr ?? []).map(f => ({ id: f.id, name: f[nameKey] ?? f.value ?? '' }))
}

function availablePipedriveOptions(rowIndex) {
    const used = rows.value
        .filter((_, i) => i !== rowIndex)
        .map(r => r.pipedriveField?.id)
        .filter(Boolean)
    return allPipedriveOptions.value.filter(o => !used.includes(o.id))
}

// ── Mount ─────────────────────────────────────────────────────────────────────
onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/settings/pipedrive`)
        const groups = res.data?.data?.groups ?? {}
        if (groups.personId) {
            tabs.value = [
                { groupId: groups.personId,       label: __('message.pipe_person'),  icon: 'fas fa-user' },
                { groupId: groups.organizationId, label: __('message.organization'), icon: 'fas fa-building' },
                { groupId: groups.dealId,         label: __('message.pipe_deal'),    icon: 'fas fa-handshake' },
            ]
            activeGroupId.value = groups.personId
        }
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }

    if (activeGroupId.value) {
        await loadMappingForGroup(activeGroupId.value)
    }
})

// ── Load mapping for a group ──────────────────────────────────────────────────
async function loadMappingForGroup(groupId) {
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
            const isLocal   = !(f.pipedrive_options?.length)
            const faveoOpts = isLocal
                ? localOptions.value
                : toOptions(f.pipedrive_options ?? [], 'value')
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

// ── Tab switch ────────────────────────────────────────────────────────────────
async function switchTab(groupId) {
    if (groupId === activeGroupId.value) return
    activeGroupId.value = groupId
    await loadMappingForGroup(groupId)
}

// ── Pipedrive field change ────────────────────────────────────────────────────
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

// ── Add / delete row ──────────────────────────────────────────────────────────
function addRow() {
    if (rows.value.length >= allPipedriveOptions.value.length) return
    rows.value.push({ pipedriveField: null, faveoField: null, faveoOptions: localOptions.value, isFaveoField: true })
}

function deleteRow(index) {
    rows.value.splice(index, 1)
}

// ── Save mapping ──────────────────────────────────────────────────────────────
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
        await http.post(`${baseUrl}/licenseStatus`, { pipedrivestatus: 1 })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        savingMapping.value = false
    }
}

// ── Sync fields ───────────────────────────────────────────────────────────────
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
