<template>
    <div>
        <AppAlert :componentName="COMPONENT" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title text-capitalize">
                    Zoho {{ platform }} {{ __('message.mapping') }}
                </h4>
                <div class="card-tools">
                    <button
                        class="btn btn-tool"
                        v-tooltip="__('message.sync_fields')"
                        :disabled="syncing"
                        @click="syncFields"
                    >
                        <i class="fas fa-sync" :class="{ 'fa-spin': syncing }"></i>
                    </button>
                </div>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">
                    <!-- Tabs: only CRM has multiple modules -->
                    <ul v-if="tabs.length > 1" class="nav nav-tabs mb-4">
                        <li class="nav-item" v-for="tab in tabs" :key="tab.id">
                            <a
                                class="nav-link"
                                :class="{ active: activeModule === tab.id }"
                                href="#"
                                @click.prevent="switchTab(tab.id)"
                            >
                                <i :class="tab.icon + ' me-1'"></i>{{ tab.label }}
                            </a>
                        </li>
                    </ul>

                    <div v-if="loadingModule" class="row justify-content-center py-3"><loader /></div>

                    <template v-else>
                        <div v-if="!zohoFields.length" class="text-muted">
                            {{ __('message.zoho_no_fields') }}
                        </div>

                        <template v-else>
                            <div v-for="(row, idx) in rows" :key="idx" class="row mb-3 align-items-end">
                                <div class="col-5">
                                    <DynamicSelect
                                        :name="`zoho_field_${idx}`"
                                        :label="idx === 0 ? __('message.zoho') : ''"
                                        :elements="zohoFieldOptions"
                                        :value="zohoFieldOptions.find(o => o.id === row.zohoId) ?? null"
                                        :searchable="true"
                                        :clearable="true"
                                        :onChange="(val) => onZohoFieldChange(row, val)"
                                    />
                                </div>
                                <div class="col-5">
                                    <DynamicSelect
                                        :name="`target_field_${idx}`"
                                        :label="idx === 0 ? __('message.field_mapping') : ''"
                                        :elements="row.targetOptions"
                                        :value="row.targetOptions.find(o => o.id === row.targetValue) ?? null"
                                        :searchable="true"
                                        :clearable="true"
                                        :onChange="(val) => onTargetChange(row, val)"
                                    />
                                </div>
                                <div class="col-2 mb-3">
                                    <button
                                        v-if="idx > 0"
                                        type="button"
                                        class="btn btn-light table_btn"
                                        v-tooltip="__('message.delete_attribute')"
                                        @click="removeRow(idx)"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-1">
                                <action-button
                                    action="add"
                                    type="button"
                                    :label="__('message.add-new')"
                                    @click="addRow"
                                />
                            </div>
                        </template>
                    </template>
                </div>

                <div v-if="!loadingModule && zohoFields.length" class="card-footer">
                    <action-button action="save" :label="__('message.save_mapping')" :loading="saving" @click="save" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import DynamicSelect from '@/components/Reusable/FormField/DynamicSelect.vue'

const COMPONENT = 'zoho-mapping'
const route   = useRoute()

const platform = route.params.platform

const tabs = platform === 'crm'
    ? [
        { id: 'contacts', label: 'Contacts', icon: 'fas fa-user' },
        { id: 'accounts', label: 'Accounts', icon: 'fas fa-building' },
      ]
    : [
        { id: 'contacts', label: 'Contacts', icon: 'fas fa-user' },
      ]

const activeModule  = ref(route.params.module ?? 'contacts')
const loading       = ref(true)
const loadingModule = ref(false)
const saving        = ref(false)
const syncing       = ref(false)

const integrationId = ref(null)
const zohoFields    = ref([])
const rows          = reactive([])

const zohoFieldOptions = computed(() =>
    zohoFields.value.map(z => ({ id: z.id, name: z.field_name }))
)

async function fetchOptions(zohoId) {
    const res = await http.get(`/zoho/options/${zohoId}`)
    return (res.data ?? []).map(o => ({ id: o.value, name: o.label, type: o.type }))
}

function addRow() {
    rows.push({ zohoId: null, targetOptions: [], targetValue: null, targetType: '' })
}

function removeRow(idx) {
    rows.splice(idx, 1)
}

async function onZohoFieldChange(row, val) {
    row.zohoId        = val?.id ?? null
    row.targetValue   = null
    row.targetType    = ''
    row.targetOptions = row.zohoId ? await fetchOptions(row.zohoId) : []
}

function onTargetChange(row, val) {
    row.targetValue = val?.id ?? null
    row.targetType  = val?.type ?? ''
}

async function loadMappings() {
    const mod = activeModule.value

    const [fieldsRes, mappingRes] = await Promise.all([
        http.get(`/zoho/${platform}/${mod}/fields`),
        http.get(`/zoho/${platform}/${mod}/mapping/data`),
    ])

    zohoFields.value = fieldsRes.data?.data ?? []
    const mappings   = mappingRes.data?.data ?? []

    rows.splice(0)

    if (!mappings.length) {
        addRow()
        return
    }

    for (const mapping of mappings) {
        const options  = await fetchOptions(mapping.zoho_field_id)
        const selected = options.find(o =>
            o.type === mapping.selected?.type &&
            String(o.id) === String(mapping.selected?.value)
        )
        rows.push({
            zohoId:        mapping.zoho_field_id,
            targetOptions: options,
            targetValue:   selected?.id ?? null,
            targetType:    selected?.type ?? '',
        })
    }
}

async function switchTab(tabId) {
    if (tabId === activeModule.value) return
    activeModule.value = tabId
    zohoFields.value   = []
    rows.splice(0)
    loadingModule.value = true
    try {
        await loadMappings()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loadingModule.value = false
    }
}

onMounted(async () => {
    loading.value = true
    try {
        const res = await http.get(`/zoho/integrations`)
        const integration = (res.data?.data ?? []).find(i => i.platform === platform)
        integrationId.value = integration?.id ?? null

        await loadMappings()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function syncFields() {
    syncing.value = true
    try {
        const res = await http.get(`/zoho/${platform}/sync`)
        successHandler(res, COMPONENT)
        await loadMappings()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        syncing.value = false
    }
}

async function save() {
    const mappings = rows
        .filter(r => r.zohoId && r.targetValue && r.targetType)
        .map(r => ({
            zoho_field_id: r.zohoId,
            selected: { type: r.targetType, value: r.targetValue },
        }))

    saving.value = true
    try {
        const res = await http.post(`/zoho/mapping/save`, {
            module:         activeModule.value,
            integration_id: integrationId.value,
            mappings,
        })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
