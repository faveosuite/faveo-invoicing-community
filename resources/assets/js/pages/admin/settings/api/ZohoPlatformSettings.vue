<template>
    <div>
        <AppAlert :componentName="COMPONENT" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title text-capitalize">{{ platformLabel }}</h4>
                <div class="card-tools">
                    <button v-if="activeTab === 'field-mapping'"
                        class="btn btn-tool" v-tooltip="__('message.sync_fields')"
                        :disabled="syncing" @click="syncFields">
                        <i class="fas fa-sync" :class="{ 'fa-spin': syncing }"></i>
                    </button>
                </div>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

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

                    <!-- ── Tab 1: Connection ─────────────────────────── -->
                    <div v-show="activeTab === 'connection'">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <TextField
                                    name="client_id"
                                    :label="__('message.client_id')"
                                    :value="form.client_id"
                                    :required="true"
                                    :error="errors.client_id"
                                    :onChange="(val, key) => { setFieldError(key, undefined); form[key] = val }"
                                />
                            </div>
                            <div class="col-md-6 mb-3">
                                <TextField
                                    name="client_secret"
                                    type="password"
                                    :label="__('message.client_secret')"
                                    :value="form.client_secret"
                                    :required="true"
                                    :error="errors.client_secret"
                                    :onChange="(val, key) => { setFieldError(key, undefined); form[key] = val }"
                                />
                            </div>
                            <div class="col-md-6 mb-3">
                                <SelectField
                                    name="region"
                                    :label="__('message.region')"
                                    :elements="regionOptions"
                                    :value="selectedRegion"
                                    :required="true"
                                    :error="errors.region"
                                    :clearable="false"
                                    :searchable="false"
                                    :onChange="(val) => { setFieldError('region', undefined); form.region = val?.id ?? null }"
                                />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('message.redirect_uri') }}</label>
                                <div class="input-group">
                                    <input type="text" class="form-control bg-light" :value="redirectUri" readonly style="cursor:default">
                                    <button type="button" class="btn btn-light border" :title="__('message.copy')" @click="copyRedirectUri">
                                        <i class="fa" :class="uriCopied ? 'fa-check text-success' : 'fa-copy'"></i>
                                    </button>
                                </div>
                                <small class="text-muted">{{ __('message.zoho_redirect_uri_help') }}</small>
                            </div>
                        </div>
                        <action-button action="save" :label="__('message.connect')" :loading="saving" @click="saveConnection" />
                    </div>

                    <!-- ── Tab 2: Field Mapping ──────────────────────── -->
                    <div v-show="activeTab === 'field-mapping'">
                        <ul v-if="moduleTabs.length > 1" class="nav nav-tabs mb-4">
                            <li class="nav-item" v-for="tab in moduleTabs" :key="tab.id">
                                <a class="nav-link" :class="{ active: activeModule === tab.id }"
                                   href="#" @click.prevent="switchModule(tab.id)">
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
                                        <SelectField
                                            :name="`zoho_field_${idx}`"
                                            :label="idx === 0 ? __('message.zoho') : ''"
                                            :elements="zohoFieldOptions"
                                            :value="zohoFieldOptions.find(o => o.id === row.zohoId) ?? null"
                                            :searchable="true"
                                            :clearable="true"
                                            :placeholder="__('message.pipe_select_option')"
                                            :onChange="(val) => onZohoFieldChange(row, val)"
                                        />
                                    </div>
                                    <div class="col-5">
                                        <SelectField
                                            :name="`target_field_${idx}`"
                                            :label="idx === 0 ? __('message.field_mapping') : ''"
                                            :elements="row.targetOptions"
                                            :value="row.targetOptions.find(o => o.id === row.targetValue) ?? null"
                                            :searchable="true"
                                            :clearable="true"
                                            :placeholder="__('message.pipe_select_option')"
                                            :onChange="(val) => onTargetChange(row, val)"
                                        />
                                    </div>
                                    <div class="col-2 mb-3">
                                        <button v-if="idx > 0" type="button" class="btn btn-light table_btn"
                                            v-tooltip="__('message.delete_attribute')" @click="removeRow(idx)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <action-button action="add" type="button"
                                        :label="__('message.add-new')" @click="addRow" />
                                </div>
                                <action-button action="save" :label="__('message.save_mapping')" :loading="savingMapping" @click="saveMapping" />
                            </template>
                        </template>
                    </div>

                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { validateForm } from '@/helpers/formUtils.js'
import { zohoCredentialsSchema } from '@/validations/admin/zohoValidations.js'
import TextField   from '@/components/Reusable/FormField/TextField.vue'
import SelectField from '@/components/Reusable/FormField/SelectField.vue'

const COMPONENT = 'zoho-platform-settings'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const route   = useRoute()

const platform = route.params.platform

const { errors, setErrors, setFieldError } = useForm()

// ── Labels ────────────────────────────────────────────────────────────────
const platformLabel = computed(() => {
    const map = { crm: __('message.zoho_crm'), campaigns: __('message.zoho_campaigns') }
    return map[platform] ?? `Zoho ${platform}`
})

// ── Tabs ──────────────────────────────────────────────────────────────────
const activeTab = ref('connection')
const tabs = [
    { key: 'connection',   icon: 'fas fa-plug',    label: __('message.connection')    },
    { key: 'field-mapping', icon: 'fas fa-sliders', label: __('message.field_mapping') },
]

// ── Flags ─────────────────────────────────────────────────────────────────
const loading       = ref(true)
const saving        = ref(false)
const savingMapping = ref(false)
const syncing       = ref(false)
const loadingModule = ref(false)
const uriCopied     = ref(false)

// ── Connection form ───────────────────────────────────────────────────────
const integrationId = ref(null)

const regionOptions = [
    { id: 'in', name: 'India'     },
    { id: 'us', name: 'US'        },
    { id: 'eu', name: 'Europe'    },
    { id: 'au', name: 'Australia' },
]

const form = reactive({ client_id: '', client_secret: '', region: 'in' })

const selectedRegion = computed(() => regionOptions.find(o => o.id === form.region) ?? null)

const redirectUri = `${baseUrl}/zoho/oauth/callback`

async function copyRedirectUri() {
    try {
        await navigator.clipboard.writeText(redirectUri)
        uriCopied.value = true
        setTimeout(() => { uriCopied.value = false }, 2000)
    } catch { /* selectable for manual copy */ }
}

// ── Field mapping ─────────────────────────────────────────────────────────
const moduleTabs = platform === 'crm'
    ? [
        { id: 'contacts', label: 'Contacts', icon: 'fas fa-user'     },
        { id: 'accounts', label: 'Accounts', icon: 'fas fa-building' },
      ]
    : [
        { id: 'contacts', label: 'Contacts', icon: 'fas fa-user' },
      ]

const activeModule  = ref('contacts')
const zohoFields    = ref([])
const rows          = reactive([])

const zohoFieldOptions = computed(() =>
    zohoFields.value.map(z => ({ id: z.id, name: z.field_name }))
)

function addRow()          { rows.push({ zohoId: null, targetOptions: [], targetValue: null, targetType: '' }) }
function removeRow(idx)    { rows.splice(idx, 1) }

async function fetchOptions(zohoId) {
    const res = await http.get(`${baseUrl}/zoho/options/${zohoId}`)
    return (res.data ?? []).map(o => ({ id: o.value, name: o.label, type: o.type }))
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
        http.get(`${baseUrl}/zoho/${platform}/${mod}/fields`),
        http.get(`${baseUrl}/zoho/${platform}/${mod}/mapping/data`),
    ])
    zohoFields.value = fieldsRes.data?.data ?? []
    const mappings   = mappingRes.data?.data ?? []
    rows.splice(0)
    if (!mappings.length) { addRow(); return }
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

async function switchModule(tabId) {
    if (tabId === activeModule.value) return
    activeModule.value  = tabId
    zohoFields.value    = []
    rows.splice(0)
    loadingModule.value = true
    try { await loadMappings() } catch (e) { errorHandler(e, COMPONENT) }
    finally { loadingModule.value = false }
}

// ── Init ──────────────────────────────────────────────────────────────────
onMounted(async () => {
    try {
        const [intRes] = await Promise.all([
            http.get(`${baseUrl}/zoho/integrations`),
        ])

        const integration = (intRes.data?.data ?? []).find(i => i.platform === platform)
        integrationId.value = integration?.id ?? null

        if (integrationId.value) {
            const keysData = await http.get(`${baseUrl}/zoho/getKeys/${integrationId.value}`)
            const d = keysData.data?.data
            if (d) {
                form.client_id     = d.client_id     ?? ''
                form.client_secret = d.client_secret ?? ''
                form.region        = d.region        ?? 'in'
            }

            await loadMappings()
        }
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

// ── Save connection ───────────────────────────────────────────────────────
async function saveConnection() {
    if (!await validateForm(zohoCredentialsSchema, form, setErrors)) return

    saving.value = true
    try {
        const res = await http.post(`${baseUrl}/zoho/saveKeys`, {
            integration_id: integrationId.value,
            client_id:      form.client_id,
            client_secret:  form.client_secret,
            region:         form.region,
        })
        const redirectUrl = res.data?.data?.redirect_url
        if (redirectUrl) {
            window.location.href = redirectUrl
            return
        }
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}

// ── Sync fields ───────────────────────────────────────────────────────────
async function syncFields() {
    syncing.value = true
    try {
        const res = await http.get(`${baseUrl}/zoho/${platform}/sync`)
        successHandler(res, COMPONENT)
        await loadMappings()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        syncing.value = false
    }
}

// ── Save mapping ──────────────────────────────────────────────────────────
async function saveMapping() {
    const mappings = rows
        .filter(r => r.zohoId && r.targetValue && r.targetType)
        .map(r => ({
            zoho_field_id: r.zohoId,
            selected: { type: r.targetType, value: r.targetValue },
        }))

    savingMapping.value = true
    try {
        const res = await http.post(`${baseUrl}/zoho/mapping/save`, {
            module:         activeModule.value,
            integration_id: integrationId.value,
            mappings,
        })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        savingMapping.value = false
    }
}
</script>
