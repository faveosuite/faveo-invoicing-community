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
                                <h4 class="card-title">{{ platformLabel }}</h4>
                                <span v-if="connected" class="badge bg-success ms-2">{{ __('message.connected') }}</span>
                                <span v-else class="badge bg-warning text-dark ms-2">{{ __('message.zoho_not_connected') }}</span>
                                <!-- card-title floats left in AdminLTE; clear it so this sits on its own line under the title -->
                                <small v-if="connected" class="d-block text-muted clear-both">
                                    {{ integration.region }} · {{ __('message.zoho_connected_since', { date: formatDate(integration.connected_at) }) }}
                                </small>
                            </div>
                            <div class="card-body">
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
                                            :required="!form.has_secret"
                                            :placeholder="form.has_secret ? __('message.zoho_secret_saved_placeholder') : ''"
                                            :error="errors.client_secret"
                                            :onChange="(val, key) => { setFieldError(key, undefined); form[key] = val }"
                                        />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <DynamicSelect
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
                                        <label class="form-label fw-bold" for="zoho-redirect-uri">{{ __('message.redirect_uri') }}</label>
                                        <div class="input-group">
                                            <input id="zoho-redirect-uri" type="text" class="form-control bg-light cursor-default" :value="redirectUri" readonly>
                                            <span class="input-group-text cursor-pointer" @click="copyRedirectUri">
                                                <i :class="uriCopied ? 'fas fa-check text-success' : 'fas fa-copy'"></i>
                                                {{ uriCopied ? __('message.copied') : __('message.copy') }}
                                            </span>
                                        </div>
                                        <small class="text-muted">{{ __('message.zoho_redirect_uri_help') }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <action-button action="save" :icon="connected ? 'fas fa-sync-alt' : 'fas fa-plug'"
                                    :label="connected ? __('message.zoho_reconnect') : __('message.connect')"
                                    :loading="saving" @click="saveConnection" />
                                <action-button v-if="connected" variant="secondary" icon="fas fa-unlink" class="ms-2"
                                    :label="__('message.zoho_disconnect')" @click="showDisconnect = true" />
                            </div>
                        </div>

                    </div>

                    <!-- Tab: Field Mapping -->
                    <div v-show="activeTab === 'field-mapping'">
                        <!-- Campaigns: which mailing list new subscribers go to -->
                        <div v-if="platform === 'campaigns' && connected" class="card card-light">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('message.zoho_mailing_list') }}</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <DynamicSelect
                                            name="list_key"
                                            :label="__('message.zoho_mailing_list')"
                                            :elements="listOptions"
                                            :value="listOptions.find(o => o.id === selectedList) ?? null"
                                            :clearable="false"
                                            :searchable="true"
                                            :tooltip="__('message.zoho_mailing_list_help')"
                                            :onChange="(val) => selectedList = val?.id ?? DEFAULT_LIST"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <action-button action="save" :loading="savingList" @click="saveList" />
                            </div>
                        </div>
                        <div class="card card-light">
                            <div class="card-header">
                                <h4 class="card-title">{{ platformLabel }} {{ __('message.mapping') }}</h4>
                                <div class="card-tools">
                                    <button class="btn btn-tool" v-tooltip="__('message.sync_fields')"
                                        :disabled="syncing || !connected" @click="syncFields">
                                        <i class="fas fa-sync" :class="{ 'fa-spin': syncing }"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <ul v-if="moduleTabs.length > 1" class="nav nav-tabs mb-3">
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
                                        <div v-if="missingRequired.length" class="alert alert-warning py-2">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            {{ __('message.zoho_required_missing', { fields: missingRequired.join(', ') }) }}
                                        </div>

                                        <div v-for="(row, idx) in rows" :key="row.key" class="row mb-3 align-items-end">
                                            <div class="col-5">
                                                <DynamicSelect
                                                    :name="`zoho_field_${idx}`"
                                                    :label="idx === 0 ? __('message.zoho_field') : ''"
                                                    :elements="zohoOptionsFor(row)"
                                                    :value="zohoFieldOptions.find(o => o.id === row.zohoId) ?? null"
                                                    :searchable="true"
                                                    :clearable="true"
                                                    :onChange="(val) => onZohoFieldChange(row, val)"
                                                />
                                            </div>
                                            <div class="col-5">
                                                <DynamicSelect
                                                    :name="`target_field_${idx}`"
                                                    :label="idx === 0 ? __('message.faveo_field') : ''"
                                                    :elements="row.targetOptions"
                                                    :value="row.targetOptions.find(o => o.id === row.targetValue) ?? null"
                                                    :searchable="true"
                                                    :clearable="true"
                                                    :onChange="(val) => onTargetChange(row, val)"
                                                />
                                            </div>
                                            <div class="col-2 mb-3">
                                                <button type="button" class="btn btn-light table_btn"
                                                    v-tooltip="__('message.delete_attribute')" @click="removeRow(idx)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <p v-if="hasRequiredFields" class="text-muted small mb-3"><span class="text-danger">*</span> {{ __('message.zoho_required_legend') }}</p>
                                        <action-button action="add" type="button" :label="__('message.add-new')" @click="addRow" />
                                    </template>
                                </template>
                            </div>
                            <div v-if="zohoFields.length" class="card-footer">
                                <action-button action="save" :label="__('message.save_mapping')" :loading="savingMapping" @click="saveMapping" />
                                <action-button variant="secondary" icon="fas fa-paper-plane" class="ms-2"
                                    :label="__('message.zoho_send_test')" v-tooltip="__('message.zoho_send_test_tooltip')"
                                    :loading="testing" :disabled="!connected" @click="sendTest" />
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
            :deleteUrl="`/zoho/integrations/${integrationId}/disconnect`"
            method="post"
            :title="__('message.zoho_disconnect')"
            :message="__('message.zoho_disconnect_confirm')"
            :btnLabel="__('message.zoho_disconnect')"
            btnIcon="fa-unlink"
            :componentName="COMPONENT"
            @deleted="loadIntegration"
        />
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { useAlertStore } from '@/core/stores/alert.js'
import { useDateTime } from '@/core/composables/useDateTime'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { validateForm } from '@/helpers/formUtils.js'
import { zohoCredentialsSchema } from '@/validations/admin/zohoValidations.js'
import TextField   from '@/components/Reusable/FormField/TextField.vue'
import DynamicSelect from '@/components/Reusable/FormField/DynamicSelect.vue'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'

const COMPONENT = 'zoho-platform-settings'
const route   = useRoute()
const router  = useRouter()

const platform = route.params.platform

const { errors, setErrors, setFieldError } = useForm()
const { formatDate } = useDateTime()

function showError(message) {
    useAlertStore().setAlert({ type: 'danger', message, component_name: COMPONENT })
}

// ── Labels ────────────────────────────────────────────────────────────────
const platformLabel = computed(() => {
    const map = { crm: __('message.zoho_crm'), campaigns: __('message.zoho_campaigns') }
    return map[platform] ?? `Zoho ${platform}`
})

// ── Tabs ──────────────────────────────────────────────────────────────────
const activeTab = ref('connection')
// Field mapping only makes sense once Zoho is connected (there are no Zoho fields to map before that).
const tabs = computed(() => [
    { key: 'connection',   icon: 'fas fa-plug',    label: __('message.connection')    },
    ...(connected.value ? [{ key: 'field-mapping', icon: 'fas fa-sliders', label: __('message.field_mapping') }] : []),
])

// ── Flags ─────────────────────────────────────────────────────────────────
const loading        = ref(true)
const saving         = ref(false)
const savingMapping  = ref(false)
const savingList     = ref(false)
const syncing        = ref(false)
const testing        = ref(false)
const loadingModule  = ref(false)
const uriCopied      = ref(false)
const showDisconnect = ref(false)

// ── Connection ────────────────────────────────────────────────────────────
const integration   = ref({})
const integrationId = computed(() => integration.value.id ?? null)
const connected     = computed(() => !!integration.value.has_token)

const regionOptions = [
    { id: 'in', name: 'India'     },
    { id: 'us', name: 'US'        },
    { id: 'eu', name: 'Europe'    },
    { id: 'au', name: 'Australia' },
    { id: 'jp', name: 'Japan'     },
    { id: 'cn', name: 'China'     },
    // Zoho Campaigns has no Canada data center
    ...(platform === 'crm' ? [{ id: 'ca', name: 'Canada' }] : []),
]

// has_secret: a secret is saved server-side; leaving the field blank keeps it.
const form = reactive({ client_id: '', client_secret: '', region: 'in', has_secret: false })

const selectedRegion = computed(() => regionOptions.find(o => o.id === form.region) ?? null)

const redirectUri = `${http.defaults.baseURL}/zoho/oauth/callback`

async function copyRedirectUri() {
    try {
        await navigator.clipboard.writeText(redirectUri)
        uriCopied.value = true
        setTimeout(() => { uriCopied.value = false }, 2000)
    } catch { /* selectable for manual copy */ }
}

async function loadIntegration() {
    const res = await http.get(`/zoho/integrations`)
    integration.value = (res.data?.data ?? []).find(i => i.platform === platform) ?? {}
}

// ── Mailing list (Campaigns) ──────────────────────────────────────────────
const DEFAULT_LIST = '__default'
const mailingLists    = ref([])
const defaultListName = ref('')

const listOptions = computed(() => [
    { id: DEFAULT_LIST, name: __('message.zoho_mailing_list_default', { name: defaultListName.value }) },
    ...mailingLists.value.map(l => ({ id: l.key, name: l.name })),
])

async function loadLists() {
    selectedList.value = integration.value.list_key ?? DEFAULT_LIST
    const res = await http.get(`/zoho/campaigns/lists`)
    mailingLists.value    = res.data?.data?.lists ?? []
    defaultListName.value = res.data?.data?.default_list_name ?? ''
}

// Picked in the dropdown, sent only on Save.
const selectedList = ref(DEFAULT_LIST)

async function saveList() {
    const listKey = selectedList.value !== DEFAULT_LIST ? selectedList.value : null
    savingList.value = true
    try {
        const res = await http.post(`/zoho/campaigns/list`, { list_key: listKey })
        integration.value.list_key = listKey
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        savingList.value = false
    }
}

// ── Field mapping ─────────────────────────────────────────────────────────
const moduleTabs = platform === 'crm'
    ? [
        { id: 'contacts', label: __('message.contacts'), icon: 'fas fa-user'     },
        { id: 'accounts', label: __('message.accounts'), icon: 'fas fa-building' },
      ]
    : [
        { id: 'contacts', label: __('message.contacts'), icon: 'fas fa-user' },
      ]

const activeModule  = ref('contacts')
const zohoFields    = ref([])
const rows          = reactive([])
let rowKey = 0

const zohoFieldOptions = computed(() =>
    zohoFields.value.map(z => ({ id: z.id, name: z.field_name, markRequired: !!z.is_mandatory }))
)

// A Zoho field can be mapped once: hide the ones other rows already use.
function zohoOptionsFor(row) {
    const taken = new Set(rows.filter(r => r !== row && r.zohoId).map(r => r.zohoId))
    return zohoFieldOptions.value.filter(o => !taken.has(o.id))
}

const hasRequiredFields = computed(() => zohoFields.value.some(z => z.is_mandatory))

// Only once something is mapped: an unmapped module is simply not sent to Zoho.
const missingRequired = computed(() => {
    if (!rows.some(r => r.zohoId)) return []
    const mapped = new Set(rows.map(r => r.zohoId))
    return zohoFields.value.filter(z => z.is_mandatory && !mapped.has(z.id)).map(z => z.field_name)
})

function addRow() { rows.push({ key: ++rowKey, zohoId: null, targetOptions: [], targetValue: null, targetType: '' }) }

function removeRow(idx) {
    rows.splice(idx, 1)
    if (!rows.length) addRow()
}

async function fetchOptions(zohoId) {
    const res = await http.get(`/zoho/options/${zohoId}`)
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
        http.get(`/zoho/${platform}/${mod}/fields`),
        http.get(`/zoho/${platform}/${mod}/mapping/data`),
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
            key:           ++rowKey,
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
    try { await loadMappings() } catch (e) { errorHandler(e, COMPONENT, { setErrors }) }
    finally { loadingModule.value = false }
}

// ── Init ──────────────────────────────────────────────────────────────────
onMounted(async () => {
    // Back from Zoho's sign-in: the OAuth callback redirects here with the outcome in the query string.
    if (route.query.zoho_status) {
        useAlertStore().setAlert({
            type:           route.query.zoho_status === 'success' ? 'success' : 'danger',
            message:        route.query.message ?? '',
            component_name: COMPONENT,
        })
        // Drop the outcome from the address bar so a reload doesn't show it again.
        router.replace({ query: {} })
    }

    try {
        await loadIntegration()

        if (integrationId.value) {
            const keysData = await http.get(`/zoho/getKeys/${integrationId.value}`)
            const d = keysData.data?.data
            if (d) {
                form.client_id     = d.client_id     ?? ''
                form.client_secret = d.client_secret ?? ''
                form.region        = d.region        ?? 'in'
                form.has_secret = !!d.has_secret
            }

            await Promise.all([
                loadMappings(),
                platform === 'campaigns' && connected.value ? loadLists() : null,
            ])
        }
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        loading.value = false
    }
})

// ── Save connection ───────────────────────────────────────────────────────
async function saveConnection() {
    if (!await validateForm(zohoCredentialsSchema, form, setErrors)) return

    saving.value = true
    try {
        const res = await http.post(`/zoho/saveKeys`, {
            integration_id: integrationId.value,
            client_id:      form.client_id,
            client_secret:  form.client_secret || null,
            region:         form.region,
        })
        const redirectUrl = res.data?.data?.redirect_url
        if (redirectUrl) {
            globalThis.location.href = redirectUrl
            return
        }
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        saving.value = false
    }
}

// ── Sync fields ───────────────────────────────────────────────────────────
async function syncFields() {
    syncing.value = true
    try {
        const res = await http.get(`/zoho/${platform}/sync`)
        successHandler(res, COMPONENT)
        await loadMappings()
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
        const res = await http.post(`/zoho/${platform}/test`)
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        testing.value = false
    }
}

// ── Save mapping ──────────────────────────────────────────────────────────
async function saveMapping() {
    // A row with only one side picked would otherwise be dropped (and its old mapping deleted) without a word.
    if (rows.some(r => !!r.zohoId !== !!r.targetValue)) {
        showError(__('message.zoho_incomplete_rows'))
        return
    }

    if (missingRequired.value.length) {
        showError(__('message.zoho_required_missing', { fields: missingRequired.value.join(', ') }))
        return
    }

    const mappings = rows
        .filter(r => r.zohoId && r.targetValue && r.targetType)
        .map(r => ({
            zoho_field_id: r.zohoId,
            selected: { type: r.targetType, value: r.targetValue },
        }))

    savingMapping.value = true
    try {
        const res = await http.post(`/zoho/mapping/save`, {
            module:         activeModule.value,
            integration_id: integrationId.value,
            mappings,
        })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        savingMapping.value = false
    }
}
</script>

<style scoped>
.cursor-default { cursor: default; }
.clear-both { clear: both; }
</style>
