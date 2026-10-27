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
                                <h4 class="card-title">{{ __('message.mailchimp') }}</h4>
                                <span v-if="connected" class="badge bg-success ms-2">{{ __('message.connected') }}</span>
                                <span v-else class="badge bg-warning text-dark ms-2">{{ __('message.not_connected') }}</span>
                                <!-- card-title floats left in AdminLTE; clear it so this sits on its own line under the title -->
                                <small v-if="connected" class="d-block text-muted clear-both">{{ connectionDetail }}</small>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <TextField
                                            name="apiKey"
                                            type="password"
                                            :label="__('message.mailchimp_key')"
                                            :value="form.apiKey"
                                            :required="!account.has_key"
                                            :placeholder="account.has_key ? __('message.mailchimp_key_saved_placeholder') : 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx-us21'"
                                            :error="errors.apiKey"
                                            :onChange="(val, key) => { setFieldError(key, undefined); form[key] = val }"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <action-button action="save" :icon="connected ? 'fas fa-sync-alt' : 'fas fa-plug'"
                                    :label="connected ? __('message.save') : __('message.connect')"
                                    :loading="connecting" @click="connect" />
                                <action-button v-if="connected" variant="secondary" icon="fas fa-unlink" class="ms-2"
                                    :label="__('message.mailchimp_disconnect')" @click="showDisconnect = true" />
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Field Mapping -->
                    <div v-show="activeTab === 'field-mapping'">
                        <!-- Which audience new subscribers go to -->
                        <div class="card card-light">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('message.mailchimp_audience') }}</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold" for="mailchimp-audience">
                                            {{ __('message.mailchimp_audience') }}<span class="text-danger ms-1">*</span>
                                            <ToolTip :message="__('message.mailchimp_audience_help')" size="small" />
                                        </label>
                                        <v-select
                                            input-id="mailchimp-audience"
                                            v-model="selectedList"
                                            :options="lists"
                                            :filterable="true"
                                            :searchable="true"
                                            :clearable="false"
                                            label="name"
                                            :placeholder="__('message.select_list')"
                                            @open="onListOpen"
                                            @update:modelValue="val => { form.listId = val?.id ?? null; setFieldError('listId', undefined) }"
                                            :class="['faveo-dynamic-select', { 'is-invalid': errors.listId }]"
                                        >
                                            <template #list-footer>
                                                <ul class="list-unstyled m-0 p-0">
                                                    <li v-if="listsLoading" class="text-center py-2 text-muted small">
                                                        <span class="spinner-border spinner-border-sm me-1"></span>
                                                        {{ __('message.loading') }}…
                                                    </li>
                                                    <li v-else-if="listsHasMore" ref="listSentinelRef" class="py-1 text-muted small text-center">
                                                        {{ __('message.loading') }}…
                                                    </li>
                                                </ul>
                                            </template>
                                            <template #no-options="{ search }">
                                                <span v-if="search">{{ __('message.no_results') }}: <em>{{ search }}</em></span>
                                                <span v-else-if="!listsLoading">{{ __('message.no_results') }}</span>
                                                <span v-else></span>
                                            </template>
                                        </v-select>
                                        <div v-if="errors.listId" class="text-danger small mt-1">{{ errors.listId }}</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <DynamicSelect
                                            name="subscribe_status"
                                            :label="__('message.subscribe_status')"
                                            :required="true"
                                            :elements="subscribeOptions"
                                            :value="subscribeOptions.find(o => o.id === form.subscribeStatus) ?? null"
                                            :tooltip="__('message.mailchimp_subscribe_status_help')"
                                            :onChange="val => { form.subscribeStatus = val?.id ?? 'subscribed'; setFieldError('subscribeStatus', undefined) }"
                                            :clearable="false"
                                            :searchable="false"
                                            :error="errors.subscribeStatus"
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
                                <h4 class="card-title">{{ __('message.mailchimp_mapping') }}</h4>
                                <div v-if="savedListId" class="card-tools">
                                    <button class="btn btn-tool" v-tooltip="__('message.sync_fields')"
                                        :disabled="syncingFields" @click="syncFields">
                                        <i class="fas fa-sync" :class="{ 'fa-spin': syncingFields }"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div v-if="!savedListId" class="text-muted">
                                    {{ __('message.mailchimp_select_list_first') }}
                                </div>

                                <div v-else-if="syncingFields" class="row justify-content-center py-3"><loader /></div>

                                <template v-else>
                                    <div v-if="missingRequired.length" class="alert alert-warning py-2">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        {{ __('message.mailchimp_required_missing', { fields: missingRequired.join(', ') }) }}
                                    </div>

                                    <div v-for="(row, idx) in fieldRows" :key="row.key" class="row mb-3 align-items-end">
                                        <div class="col-5">
                                            <DynamicSelect
                                                :name="`merge_tag_${idx}`"
                                                :label="idx === 0 ? __('message.mailchimp_field') : ''"
                                                :elements="tagOptionsFor(row)"
                                                :value="mergeTagOptions.find(o => o.id === row.tag) ?? null"
                                                :searchable="true"
                                                :clearable="true"
                                                :onChange="val => row.tag = val?.id ?? null"
                                            />
                                        </div>
                                        <div class="col-5">
                                            <DynamicSelect
                                                :name="`faveo_field_${idx}`"
                                                :label="idx === 0 ? __('message.faveo_field') : ''"
                                                :elements="userFieldOptionsFor(row)"
                                                :value="userFields.find(f => f.id === row.faveoFieldId) ?? null"
                                                :searchable="true"
                                                :clearable="true"
                                                :onChange="val => row.faveoFieldId = val?.id ?? null"
                                            />
                                        </div>
                                        <div class="col-2 mb-3">
                                            <button type="button" class="btn btn-light table_btn"
                                                v-tooltip="__('message.delete_attribute')" @click="removeFieldRow(idx)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <p v-if="hasRequiredFields" class="text-muted small mb-3"><span class="text-danger">*</span> {{ __('message.mailchimp_required_legend') }}</p>
                                    <action-button action="add" type="button" :label="__('message.add-new')"
                                        :disabled="fieldRows.length >= userFields.length" @click="addFieldRow" />
                                </template>
                            </div>
                            <div v-if="savedListId" class="card-footer">
                                <action-button action="save" :label="__('message.save_mapping')" :loading="savingMapping" @click="saveMapping" />
                                <action-button variant="secondary" icon="fas fa-paper-plane" class="ms-2"
                                    :label="__('message.mailchimp_send_test')" v-tooltip="__('message.mailchimp_send_test_tooltip')"
                                    :loading="testing" @click="sendTest" />
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Interest Groups -->
                    <div v-show="activeTab === 'interest-groups'">
                        <!-- What came from Mailchimp + Sync -->
                        <div class="card card-light">
                            <div class="card-body d-flex align-items-center flex-wrap gap-3">
                                <i class="fab fa-mailchimp fs-3 text-body-secondary" aria-hidden="true"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">{{ __('message.mailchimp_groups_summary', { groups: groupList.length, options: interestGroups.length }) }}</div>
                                    <small class="text-muted">{{ __('message.mailchimp_groups_summary_hint') }}</small>
                                </div>
                                <action-button variant="secondary" icon="fas fa-sync" :label="__('message.sync_groups')"
                                    :loading="syncingGroups" @click="syncGroups" />
                            </div>
                        </div>

                        <!-- Sync refreshes the groups both cards use: one loader in their place. -->
                        <div v-if="syncingGroups" class="row justify-content-center py-4"><loader /></div>
                        <template v-else>
                            <!-- Is-paid group -->
                            <div class="card card-light">
                                <div class="card-header">
                                    <h4 class="card-title">
                                        {{ __('message.map_is_paid_group') }}
                                        <ToolTip :message="__('message.map_is_paid_hint')" size="small" />
                                    </h4>
                                    <div class="card-tools">
                                        <Switch name="isPaidStatus" :value="isPaidStatus" :onChange="val => isPaidStatus = val" />
                                    </div>
                                </div>
                                <div class="card-body">
                                    <template v-if="isPaidStatus">
                                        <p class="mb-3">{{ __('message.mailchimp_is_paid_explain') }}</p>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <DynamicSelect
                                                    name="isPaid_category"
                                                    :label="__('message.mailchimp_group')"
                                                    :required="true"
                                                    :elements="paidGroupOptions"
                                                    :value="paidGroupOptions.find(o => o.id === isPaidCategoryId) ?? null"
                                                    :onChange="val => { isPaidCategoryId = val?.id ?? null; setFieldError('isPaidCategoryId', undefined) }"
                                                    :selectable="o => o.valid || o.id === isPaidCategoryId"
                                                    :searchable="true"
                                                    :clearable="true"
                                                    :error="errors.isPaidCategoryId"
                                                >
                                                    <template #option="o">
                                                        <span class="d-flex justify-content-between gap-3">
                                                            <span>{{ o.name }}</span>
                                                            <small :class="o.valid ? 'text-muted' : 'text-warning'">{{ o.hint }}</small>
                                                        </span>
                                                    </template>
                                                </DynamicSelect>
                                            </div>
                                        </div>
                                    </template>
                                    <p v-else class="text-muted text-center py-3 mb-0">{{ __('message.mailchimp_is_paid_off') }}</p>
                                </div>
                                <div class="card-footer">
                                    <action-button action="save" :loading="savingIsPaid" @click="saveIsPaid" />
                                </div>
                            </div>

                            <!-- Product → interest group option -->
                            <div class="card card-light">
                                <div class="card-header">
                                    <h4 class="card-title">
                                        {{ __('message.group_fields_mapping') }}
                                        <ToolTip :message="__('message.group_fields_mapping_hint')" size="small" />
                                        <span v-if="productStatus" class="badge text-bg-light border fw-normal ms-2">
                                            {{ __('message.mailchimp_products_mapped', { mapped: mappedCount, total: productOptions.length }) }}
                                        </span>
                                    </h4>
                                    <div class="card-tools">
                                        <Switch name="productStatus" :value="productStatus" :onChange="val => productStatus = val" />
                                    </div>
                                </div>
                                <div class="card-body">
                                    <template v-if="productStatus">
                                        <p class="mb-3">{{ __('message.mailchimp_product_mapping_explain') }}</p>

                                        <template v-if="interestGroupTree.length">
                                            <div v-for="(row, idx) in productRows" :key="row.key" class="row mb-3 align-items-end">
                                                <div class="col-5">
                                                    <DynamicSelect
                                                        :name="'product_' + idx"
                                                        :label="idx === 0 ? __('message.product') : ''"
                                                        :elements="productOptionsFor(row)"
                                                        :value="productOptions.find(o => o.id === row.productId) ?? null"
                                                        :onChange="val => row.productId = val?.id ?? null"
                                                        :searchable="true"
                                                        :clearable="true"
                                                    />
                                                </div>
                                                <div class="col-5">
                                                    <!-- Groups are branches (not selectable); a value reads "Group › Option",
                                                         since option names repeat across groups (YES/NO). -->
                                                    <TreeSelect
                                                        :name="'group_' + idx"
                                                        :label="idx === 0 ? __('message.mailchimp_option') : ''"
                                                        :elements="interestGroupTree"
                                                        :value="row.groupId"
                                                        :onChange="val => row.groupId = val ?? null"
                                                    >
                                                        <template #value-label="{ node }">
                                                            {{ node.parentNode ? `${node.parentNode.label} › ${node.label}` : node.label }}
                                                        </template>
                                                    </TreeSelect>
                                                </div>
                                                <div class="col-2 mb-3">
                                                    <button type="button" class="btn btn-light table_btn"
                                                        v-tooltip="__('message.delete_attribute')" @click="removeProductRow(idx)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <action-button action="add" type="button" :label="__('message.add-new')"
                                                :disabled="productRows.length >= productOptions.length" @click="addProductRow" />
                                        </template>

                                        <p v-else class="text-muted mb-0">{{ __('message.no_interest_groups_found') }}</p>
                                    </template>
                                    <p v-else class="text-muted text-center py-3 mb-0">{{ __('message.mailchimp_product_mapping_off') }}</p>
                                </div>
                                <div class="card-footer">
                                    <action-button action="save" :loading="savingGroups" @click="saveProductGroups" />
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <DeleteModal
            v-if="showDisconnect"
            :showModal="true"
            :onClose="() => showDisconnect = false"
            deleteUrl="/mailchimp/disconnect"
            method="post"
            :title="__('message.mailchimp_disconnect')"
            :message="__('message.mailchimp_disconnect_confirm')"
            :btnLabel="__('message.mailchimp_disconnect')"
            btnIcon="fa-unlink"
            :componentName="COMPONENT"
            @deleted="onDisconnected"
        />
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, nextTick, watch } from 'vue'
import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { useAlertStore } from '@/core/stores/alert.js'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import TextField from '@/components/Reusable/FormField/TextField.vue'
import DynamicSelect from '@/components/Reusable/FormField/DynamicSelect.vue'
import TreeSelect from '@/components/Reusable/FormField/TreeSelect.vue'
import Switch from '@/components/Reusable/FormField/Switch.vue'
import ToolTip from '@/components/Reusable/Tooltip.vue'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'
import { connectionSchema, listSchema } from '@/validations/admin/mailchimpValidations'

const COMPONENT = 'mailchimp-settings'

const { errors, setErrors, setFieldError } = useForm()

function showError(message) {
    useAlertStore().setAlert({ type: 'danger', message, component_name: COMPONENT })
}

// ── Flags ──────────────────────────────────────────────────────────────────
const loading        = ref(true)
const connecting     = ref(false)
const savingList     = ref(false)
const savingMapping  = ref(false)
const savingIsPaid   = ref(false)
const savingGroups   = ref(false)
const syncingFields  = ref(false)
const syncingGroups  = ref(false)
const testing        = ref(false)
const showDisconnect = ref(false)

// ── Connection ─────────────────────────────────────────────────────────────
// has_key says a key is saved; key_hint is its last 4 characters for the header line.
const account   = reactive({ has_key: false, key_hint: null, data_center: null, account_name: null })
const connected = computed(() => account.has_key)
const form      = reactive({ apiKey: '', listId: null, subscribeStatus: 'subscribed' })

// Audience last saved on the server — mapping and interest groups belong to it.
const savedListId = ref(null)

const connectionDetail = computed(() =>
    [account.account_name ?? (account.key_hint ? `•••• ${account.key_hint}` : null), account.data_center]
        .filter(Boolean).join(' · ')
)

// ── Tabs ───────────────────────────────────────────────────────────────────
// Mapping needs a connection; interest groups also need a saved audience.
const tabs = computed(() => [
    { key: 'connection', icon: 'fas fa-plug', label: __('message.connection') },
    ...(connected.value ? [{ key: 'field-mapping', icon: 'fas fa-sliders', label: __('message.field_mapping') }] : []),
    ...(connected.value && savedListId.value ? [{ key: 'interest-groups', icon: 'fas fa-tags', label: __('message.interest_groups') }] : []),
])
const activeTab = ref('connection')

watch(activeTab, async tab => {
    if (tab === 'field-mapping' && savedListId.value && !mergeTags.value.length && !syncingFields.value) {
        await syncFields()
    }
    if (tab === 'interest-groups' && savedListId.value && !syncingGroups.value) {
        await loadMappingData()
        if (!interestGroups.value.length) await syncGroups()
    }
})

// ── Lists (infinite scroll) ────────────────────────────────────────────────
const lists           = ref([])
const listsHasMore    = ref(false)
const listsLoading    = ref(false)
const listsOffset     = ref(0)
const PAGE_SIZE       = 20
const listSentinelRef = ref(null)
let   listObserver    = null

const selectedList = computed({
    get: () => lists.value.find(l => l.id === form.listId) ?? null,
    set: val => { form.listId = val?.id ?? null },
})

const subscribeOptions = [
    { id: 'subscribed',   name: __('message.subscribed')    },
    { id: 'unsubscribed', name: __('message.unsubscribed')  },
    { id: 'pending',      name: __('message.pending_optin') },
]

// ── Field mapping ──────────────────────────────────────────────────────────
const userFields = [
    { id: 'first_name', name: __('message.first_name') },
    { id: 'last_name',  name: __('message.last_name')  },
    { id: 'company',    name: __('message.company')    },
    { id: 'mobile',     name: __('message.mobile')     },
    { id: 'address',    name: __('message.address')    },
    { id: 'town',       name: __('message.town')       },
    { id: 'country',    name: __('message.country')    },
    { id: 'state',      name: __('message.state')      },
    { id: 'zip',        name: __('message.zip')        },
    { id: 'active',     name: __('message.active')     },
    { id: 'role',       name: __('message.role')       },
    { id: 'source',     name: __('message.source')     },
]

let rowKey = 0
const mergeTags = ref([])   // [{ tag, name, type, required }]
const fieldRows = ref([])   // [{ key, tag, faveoFieldId }] — IDs only, never option objects

const mergeTagOptions = computed(() =>
    mergeTags.value.map(t => ({ id: t.tag, name: `${t.name} (${t.tag})`, markRequired: t.required }))
)

// Each Mailchimp field and each Faveo field can be used once: hide the ones other rows already use.
function tagOptionsFor(row) {
    const taken = new Set(fieldRows.value.filter(r => r !== row && r.tag).map(r => r.tag))
    return mergeTagOptions.value.filter(o => !taken.has(o.id))
}

function userFieldOptionsFor(row) {
    const taken = new Set(fieldRows.value.filter(r => r !== row && r.faveoFieldId).map(r => r.faveoFieldId))
    return userFields.filter(f => !taken.has(f.id))
}

const hasRequiredFields = computed(() => mergeTags.value.some(t => t.required))

// Mailchimp rejects every subscriber without its required fields, so they must always be mapped.
const missingRequired = computed(() => {
    const mapped = new Set(fieldRows.value.filter(r => r.tag && r.faveoFieldId).map(r => r.tag))
    return mergeTags.value.filter(t => t.required && !mapped.has(t.tag)).map(t => t.name)
})

function addFieldRow() { fieldRows.value.push({ key: ++rowKey, tag: null, faveoFieldId: null }) }

function removeFieldRow(idx) {
    fieldRows.value.splice(idx, 1)
    if (!fieldRows.value.length) addFieldRow()
}

// ── Interest groups ────────────────────────────────────────────────────────
const productStatus      = ref(false)
const isPaidStatus       = ref(false)
const interestGroups     = ref([])
const interestCategories = ref([])
const products           = ref([])
const productRows        = ref([])
const isPaidCategoryId   = ref(null)

const productOptions  = computed(() => products.value.map(p => ({ id: String(p.id), name: p.name })))
const categoryOptions = computed(() => interestCategories.value.map(c => ({ id: c.id, name: c.title ?? c.name })))

const YES = new Set(['yes', 'true'])
const NO  = new Set(['no', 'false'])

// The saved audience's groups with their options, in Mailchimp's order. A group's title
// comes from the live categories; without them (Mailchimp unreachable) it stays null.
const groupList = computed(() => {
    const byId = new Map(categoryOptions.value.map(c => [c.id, { id: c.id, name: c.name, options: [] }]))
    interestGroups.value.forEach(g => {
        if (!byId.has(g.category_id)) byId.set(g.category_id, { id: g.category_id, name: null, options: [] })
        byId.get(g.category_id).options.push(g)
    })
    return [...byId.values()]
})

// Is-paid picker: every group, with its Yes/No — or why it can't be used (the server refuses those too).
const paidGroupOptions = computed(() => groupList.value.filter(g => g.name).map(g => {
    const yes   = g.options.find(o => YES.has(String(o.category_name).toLowerCase()))
    const no    = g.options.find(o => NO.has(String(o.category_name).toLowerCase()))
    const valid = !!(yes && no)
    return {
        id: g.id, name: g.name, valid,
        yes: yes?.category_name, no: no?.category_name,
        hint: valid ? `${yes.category_name} / ${no.category_name}` : __('message.mailchimp_no_yes_no'),
    }
}))

// While is-paid is on, its group's Yes/No mean paid/free — offering them for products too would mix the two.
const reservedGroupId = computed(() => (isPaidStatus.value ? isPaidCategoryId.value : null))

// Product mapping tree for TreeSelect: a branch per group, its options as leaves. Without a
// group title (Mailchimp unreachable) the options are listed on their own.
const interestGroupTree = computed(() => groupList.value
    .filter(g => g.id !== reservedGroupId.value && g.options.length)
    .flatMap(g => {
        const leaves = g.options.map(o => ({ id: o.category_option_id, name: o.category_name }))
        return g.name ? [{ id: `group:${g.id}`, name: g.name, children: leaves }] : leaves
    }))

const mappedCount = computed(() => productRows.value.filter(r => r.productId && r.groupId).length)

// One group per product: a purchase only ever applies the first mapping for a product.
function productOptionsFor(row) {
    const taken = new Set(productRows.value.filter(r => r !== row && r.productId).map(r => r.productId))
    return productOptions.value.filter(o => !taken.has(o.id))
}

function addProductRow() { productRows.value.push({ key: ++rowKey, productId: null, groupId: null }) }

function removeProductRow(idx) {
    productRows.value.splice(idx, 1)
    if (!productRows.value.length) addProductRow()
}

// ── Init ───────────────────────────────────────────────────────────────────
async function loadSettings() {
    const res = await http.get(`/settings/mailchimp`)
    const d   = res.data?.data ?? {}

    account.has_key      = !!d.has_key
    account.key_hint     = d.key_hint     ?? null
    account.data_center  = d.data_center  ?? null
    account.account_name = d.account_name ?? null

    form.apiKey          = d.api_key ?? ''
    form.listId          = d.list_id          ?? null
    form.subscribeStatus = d.subscribe_status ?? 'subscribed'
    savedListId.value    = d.list_id          ?? null
    lists.value          = d.lists            ?? []
    listsHasMore.value   = d.lists_has_more   ?? false
    listsOffset.value    = lists.value.length
}

onMounted(async () => {
    try {
        await loadSettings()
        if (savedListId.value) await loadMappingData()
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        loading.value = false
    }
})

// ── Connect (validate + save key, fetch lists) ─────────────────────────────
async function connect() {
    // A saved key may be kept by leaving the field blank.
    if (!account.has_key) {
        try { await connectionSchema.validate({ apiKey: form.apiKey }) }
        catch (err) { setErrors({ apiKey: err.message }); return }
    }

    connecting.value = true
    try {
        const res = await http.post(`/updateMailchimpDetails`, {
            mailchimp_auth_key: form.apiKey || null,
            status: 1,
        })
        successHandler(res, COMPONENT)
        await loadSettings()
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        connecting.value = false
    }
}

function onDisconnected() {
    account.has_key      = false
    account.key_hint     = null
    account.data_center  = null
    account.account_name = null
    savedListId.value    = null
    form.listId          = null
    lists.value          = []
    activeTab.value      = 'connection'
}

// ── Save audience + subscribe status ───────────────────────────────────────
async function saveList() {
    try {
        await listSchema.validate(
            { listId: form.listId, subscribeStatus: form.subscribeStatus },
            { abortEarly: false }
        )
    } catch (err) {
        const map = {}
        err.inner?.forEach(e => { if (e.path && !map[e.path]) map[e.path] = e.message })
        setErrors(map)
        return
    }

    savingList.value = true
    try {
        const res = await http.patch(`/mailchimp`, {
            list_id:          form.listId,
            subscribe_status: form.subscribeStatus,
        })
        const changed = savedListId.value !== form.listId
        savedListId.value = form.listId
        successHandler(res, COMPONENT)
        // A different audience has its own merge fields and groups.
        if (changed) {
            mergeTags.value = []
            interestGroups.value = []
            await syncFields()
        }
        await loadMappingData()
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        savingList.value = false
    }
}

// ── Infinite scroll ────────────────────────────────────────────────────────
function onListOpen() { nextTick(() => setupObserver()) }
function setupObserver() {
    if (listObserver) listObserver.disconnect()
    if (!listSentinelRef.value) return
    listObserver = new IntersectionObserver(entries => {
        if (entries[0].isIntersecting && listsHasMore.value && !listsLoading.value)
            loadMoreLists()
    }, { threshold: 0.1 })
    listObserver.observe(listSentinelRef.value)
}
watch(listSentinelRef, el => { if (el) setupObserver() })

async function loadMoreLists() {
    if (listsLoading.value || !listsHasMore.value) return
    listsLoading.value = true
    try {
        const res = await http.get(`/mailchimp/lists`, { params: { count: PAGE_SIZE, offset: listsOffset.value } })
        const d   = res.data?.data ?? {}
        const ids = new Set(lists.value.map(l => l.id))
        lists.value.push(...(d.lists ?? []).filter(l => !ids.has(l.id)))
        listsHasMore.value = d.has_more ?? false
        listsOffset.value  = lists.value.length
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        listsLoading.value = false
    }
}

// ── Load mapping data ──────────────────────────────────────────────────────
function applyFields(fields) {
    mergeTags.value = (fields ?? []).map(f => ({ ...f, required: !!f.required }))
}

async function loadMappingData() {
    try {
        const res = await http.get(`/mailchimp/mapping-data`)
        const d   = res.data?.data ?? {}

        applyFields(d.fields)

        // A tag from another audience isn't selectable: leave it blank so the row shows as incomplete.
        const known    = new Set(mergeTags.value.map(t => t.tag))
        const relation = d.relation ?? {}
        fieldRows.value = userFields
            .filter(f => relation[f.id])
            .map(f => ({ key: ++rowKey, tag: known.has(relation[f.id]) ? relation[f.id] : null, faveoFieldId: f.id }))
        if (!fieldRows.value.length) addFieldRow()

        productStatus.value      = !!(d.status?.mailchimp_product_status)
        isPaidStatus.value       = !!(d.status?.mailchimp_ispaid_status)
        interestGroups.value     = d.groups     ?? []
        interestCategories.value = d.categories ?? []
        products.value           = Object.entries(d.products ?? {}).map(([id, name]) => ({ id: String(id), name }))

        const seen = new Set()
        productRows.value = (d.group_relations ?? [])
            .filter(r => !seen.has(String(r.agora_product_id)) && seen.add(String(r.agora_product_id)))
            .map(r => ({ key: ++rowKey, productId: String(r.agora_product_id), groupId: r.mailchimp_group_cat_id }))
        if (!productRows.value.length) addProductRow()

        if (d.relation?.is_paid_yes) {
            const match = interestGroups.value.find(g => g.category_option_id === d.relation.is_paid_yes)
            isPaidCategoryId.value = match?.category_id ?? null
        }
    } catch (e) { errorHandler(e, COMPONENT, { setErrors }) }
}

// ── Sync fields ────────────────────────────────────────────────────────────
async function syncFields() {
    syncingFields.value = true
    try {
        const res = await http.post(`/mailchimp/sync-fields`)
        applyFields(res.data?.data?.fields)
        // Sync drops mappings to fields this audience no longer has; reload so those rows go too.
        await loadMappingData()
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        syncingFields.value = false
    }
}

// ── Save field mapping ─────────────────────────────────────────────────────
async function saveMapping() {
    // A row with only one side picked would otherwise be dropped without a word.
    if (fieldRows.value.some(r => !!r.tag !== !!r.faveoFieldId)) {
        showError(__('message.mailchimp_incomplete_rows'))
        return
    }

    if (missingRequired.value.length) {
        showError(__('message.mailchimp_required_missing', { fields: missingRequired.value.join(', ') }))
        return
    }

    const payload = {}
    userFields.forEach(f => { payload[f.id] = null })
    fieldRows.value.forEach(row => {
        if (row.faveoFieldId && row.tag) payload[row.faveoFieldId] = row.tag
    })

    savingMapping.value = true
    try {
        const res = await http.patch(`/mail-chimp/mapping`, payload)
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        savingMapping.value = false
    }
}

// ── Send test ──────────────────────────────────────────────────────────────
async function sendTest() {
    testing.value = true
    try {
        const res = await http.post(`/mailchimp/test`)
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        testing.value = false
    }
}

// ── Sync interest groups ───────────────────────────────────────────────────
async function syncGroups() {
    syncingGroups.value = true
    try {
        const res = await http.post(`/mailchimp/sync-groups`)
        const d   = res.data?.data ?? {}
        interestGroups.value     = d.groups     ?? interestGroups.value
        interestCategories.value = d.categories ?? interestCategories.value
        // Sync drops product → group mappings to groups this audience no longer has; reload so those rows go too.
        await loadMappingData()
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        syncingGroups.value = false
    }
}

// ── Save is-paid group ─────────────────────────────────────────────────────
async function saveIsPaid() {
    if (isPaidStatus.value && !isPaidCategoryId.value) {
        setErrors({ isPaidCategoryId: __('message.select_a_group') })
        return
    }

    savingIsPaid.value = true
    try {
        await http.post(`/mailchimp-paid-status`, { status: isPaidStatus.value ? 1 : 0 })
        const res = isPaidStatus.value
            ? await http.patch(`/mailchimp-ispaid/mapping`, { group: isPaidCategoryId.value })
            : { data: { message: __('message.updated-successfully') } }
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        savingIsPaid.value = false
    }
}

// ── Save product → group mapping ───────────────────────────────────────────
async function saveProductGroups() {
    if (productStatus.value && productRows.value.some(r => !!r.productId !== !!r.groupId)) {
        showError(__('message.mailchimp_incomplete_rows'))
        return
    }

    savingGroups.value = true
    try {
        await http.post(`/mailchimp-prod-status`, { status: productStatus.value ? 1 : 0 })
        // Sent even when empty, so removing every row clears the mapping.
        const res = productStatus.value
            ? await http.patch(`/mailchimp-group/mapping`, {
                row: productRows.value.filter(r => r.productId && r.groupId).map(r => [r.productId, r.groupId]),
            })
            : { data: { message: __('message.updated-successfully') } }
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        savingGroups.value = false
    }
}
</script>

<style scoped>
/* card-title floats left in AdminLTE; put the connected line on its own row under it */
.clear-both { clear: both; }
</style>
