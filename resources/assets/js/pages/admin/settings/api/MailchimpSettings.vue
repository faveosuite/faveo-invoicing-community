<template>
    <div>
        <AppAlert :componentName="COMPONENT" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.mailchimp') }}</h4>
                <div class="card-tools">
                    <button v-if="activeTab === 'field-mapping' && form.listId"
                        class="btn btn-tool" v-tooltip="__('message.sync_fields')"
                        :disabled="syncingFields" @click="syncFields">
                        <i class="fas fa-sync" :class="{ 'fa-spin': syncingFields }"></i>
                    </button>
                    <button v-if="activeTab === 'interest-groups' && form.listId"
                        class="btn btn-tool" v-tooltip="__('message.sync_groups')"
                        :disabled="syncingGroups" @click="syncGroups">
                        <i class="fas fa-sync" :class="{ 'fa-spin': syncingGroups }"></i>
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

                    <!-- ── Tab 1: Connection ─────────────────────────────── -->
                    <div v-show="activeTab === 'connection'">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    {{ __('message.mailchimp_key') }}<span class="text-danger ms-1">*</span>
                                </label>
                                <div class="input-group">
                                    <input
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.apiKey || connectionStatus === 'failed' }"
                                        :value="form.apiKey"
                                        placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx-us21"
                                        @input="e => { form.apiKey = e.target.value; connectionStatus.value = 'idle'; setFieldError('apiKey', undefined) }"
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
                                    {{ __('message.mailchimp_apikey_error') }}
                                </div>
                            </div>
                        </div>

                        <template v-if="connectionStatus === 'connected'">
                            <div class="row">
                                <div class="col-md-5 mb-3">
                                    <label class="form-label fw-bold">
                                        {{ __('message.list_id') }}<span class="text-danger ms-1">*</span>
                                    </label>
                                    <v-select
                                        v-model="selectedList"
                                        :options="lists"
                                        :filterable="true"
                                        :searchable="true"
                                        :clearable="true"
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
                                            <li v-else-if="listsHasMore" ref="listSentinelRef"
                                                class="py-1 text-muted small text-center list-unstyled">
                                                {{ __('message.loading') }}…
                                            </li>
                                            </ul>
                                        </template>
                                        <template #no-options="{ search }">
                                            <span v-if="search">{{ __('message.no_results') }}: <em>{{ search }}</em></span>
                                            <span v-else>{{ __('message.no_results') }}</span>
                                        </template>
                                    </v-select>
                                    <div v-if="errors.listId" class="text-danger small mt-1">{{ errors.listId }}</div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <SelectField
                                        name="subscribe_status"
                                        :label="__('message.subscribe_status')"
                                        :required="true"
                                        :elements="subscribeOptions"
                                        :value="subscribeOptions.find(o => o.id === form.subscribeStatus) ?? null"
                                        :onChange="val => { form.subscribeStatus = val?.id ?? 'subscribed'; setFieldError('subscribeStatus', undefined) }"
                                        :clearable="false"
                                        :searchable="false"
                                        :error="errors.subscribeStatus"
                                    />
                                </div>
                            </div>

                            <action-button action="save" :loading="savingConnection" @click="saveConnection" />
                        </template>
                    </div>

                    <!-- ── Tab 2: Field Mapping ──────────────────────────── -->
                    <div v-show="activeTab === 'field-mapping'">
                        <div v-if="!form.listId" class="alert alert-warning py-2 mb-3">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            {{ __('message.mailchimp_select_list_first') }}
                        </div>

                        <template v-else>
                            <div v-if="syncingFields" class="row justify-content-center py-3"><loader /></div>

                            <template v-else>
                                <div v-for="(row, idx) in fieldRows" :key="idx" class="row mb-3 align-items-end">
                                    <div class="col-5">
                                        <SelectField
                                            :name="'faveo_field_' + idx"
                                            :label="idx === 0 ? __('message.faveo_fields') : ''"
                                            :elements="userFields"
                                            :value="userFields.find(f => f.id === row.faveoFieldId) ?? null"
                                            :onChange="val => { row.faveoFieldId = val?.id ?? null; setFieldError('field_row_' + idx, undefined) }"
                                            :searchable="true"
                                            :clearable="true"
                                            :placeholder="__('message.select_field')"
                                            :error="errors['field_row_' + idx]"
                                        />
                                    </div>
                                    <div class="col-5">
                                        <SelectField
                                            :name="'merge_tag_' + idx"
                                            :label="idx === 0 ? __('message.mailchimp_merge_tag') : ''"
                                            :elements="mergeTagOptions"
                                            :value="mergeTagOptions.find(t => t.id === row.mergeTagId) ?? null"
                                            :onChange="val => { row.mergeTagId = val?.id ?? null; setFieldError('field_row_' + idx, undefined) }"
                                            :searchable="true"
                                            :clearable="true"
                                            :placeholder="__('message.select_merge_tag')"
                                        />
                                    </div>
                                    <div class="col-2 mb-3">
                                        <button
                                            type="button"
                                            class="btn btn-light table_btn"
                                            v-tooltip="__('message.Delete')"
                                            @click="removeFieldRow(idx)"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <action-button
                                        action="add"
                                        type="button"
                                        :disabled="fieldRows.length >= userFields.length"
                                        :label="__('message.add-new')"
                                        @click="addFieldRow"
                                    />
                                </div>
                            </template>
                        </template>
                    </div>

                    <!-- ── Tab 3: Interest Groups ────────────────────────── -->
                    <div v-show="activeTab === 'interest-groups'">
                        <div v-if="!form.listId" class="alert alert-warning py-2 mb-3">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            {{ __('message.mailchimp_select_list_first') }}
                        </div>

                        <template v-else>
                            <!-- Map Is Paid Group -->
                            <div class="border rounded p-3 mb-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="flex-grow-1 me-3">
                                        <h6 class="fw-bold mb-1">{{ __('message.map_is_paid_group') }}</h6>
                                        <p class="text-muted small mb-0">{{ __('message.map_is_paid_hint') }}</p>
                                    </div>
                                    <div class="form-check form-switch mb-0 flex-shrink-0">
                                        <input class="form-check-input clickable" type="checkbox" role="switch"
                                            v-model="isPaidStatus" />
                                    </div>
                                </div>

                                <template v-if="isPaidStatus">
                                    <div class="row mt-2">
                                        <div class="col-md-5">
                                            <SelectField
                                                name="isPaid_category"
                                                :label="__('message.select_a_group')"
                                                :required="true"
                                                :elements="categoryOptions"
                                                :value="categoryOptions.find(o => o.id === isPaidCategoryId) ?? null"
                                                :onChange="val => { isPaidCategoryId = val?.id ?? null; setFieldError('isPaidCategoryId', undefined) }"
                                                :searchable="true"
                                                :clearable="true"
                                                :placeholder="__('message.select_group')"
                                                :error="errors.isPaidCategoryId"
                                            />
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Group Fields Mapping -->
                            <div class="border rounded p-3 mb-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="flex-grow-1 me-3">
                                        <h6 class="fw-bold mb-1">{{ __('message.group_fields_mapping') }}</h6>
                                        <p class="text-muted small mb-0">{{ __('message.group_fields_mapping_hint') }}</p>
                                    </div>
                                    <div class="form-check form-switch mb-0 flex-shrink-0">
                                        <input class="form-check-input clickable" type="checkbox" role="switch"
                                            v-model="productStatus" />
                                    </div>
                                </div>

                                <template v-if="productStatus">
                                    <div v-if="syncingGroups" class="text-muted small">
                                        <span class="spinner-border spinner-border-sm me-1"></span>
                                        {{ __('message.loading') }}…
                                    </div>

                                    <template v-else-if="interestGroups.length">
                                        <div v-for="(row, idx) in productRows" :key="idx" class="row mb-3 align-items-end">
                                            <div class="col-5">
                                                <SelectField
                                                    :name="'product_' + idx"
                                                    :label="idx === 0 ? __('message.product') : ''"
                                                    :elements="productOptions"
                                                    :value="productOptions.find(o => o.id === String(row.productId)) ?? null"
                                                    :onChange="val => { row.productId = val?.id ?? null; setFieldError('product_row_' + idx, undefined) }"
                                                    :searchable="true"
                                                    :clearable="true"
                                                    :placeholder="__('message.select_product')"
                                                    :error="errors['product_row_' + idx]"
                                                />
                                            </div>
                                            <div class="col-5">
                                                <SelectField
                                                    :name="'group_' + idx"
                                                    :label="idx === 0 ? __('message.interest_group') : ''"
                                                    :elements="interestGroupOptions"
                                                    :value="interestGroupOptions.find(o => o.id === row.groupId) ?? null"
                                                    :onChange="val => { row.groupId = val?.id ?? null; setFieldError('product_row_' + idx, undefined) }"
                                                    :searchable="true"
                                                    :clearable="true"
                                                    :placeholder="__('message.select_group')"
                                                />
                                            </div>
                                            <div class="col-2 mb-3">
                                                <button
                                                    type="button"
                                                    class="btn btn-light table_btn"
                                                    v-tooltip="__('message.Delete')"
                                                    @click="removeProductRow(idx)"
                                                >
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="mb-1">
                                            <action-button
                                                action="add"
                                                type="button"
                                                :disabled="productRows.length >= productOptions.length"
                                                :label="__('message.add-new')"
                                                @click="addProductRow"
                                            />
                                        </div>
                                    </template>

                                    <p v-else class="text-muted small">
                                        {{ __('message.no_interest_groups_found') }}
                                    </p>
                                </template>
                            </div>
                        </template>
                    </div>

                </div>

                <!-- Card footer — save only shown on tabs 2 & 3 when list is set -->
                <div v-if="activeTab === 'field-mapping' && form.listId" class="card-footer">
                    <action-button action="save" :loading="savingMapping" @click="saveMapping" />
                </div>
                <div v-if="activeTab === 'interest-groups' && form.listId" class="card-footer">
                    <action-button action="save" :loading="savingGroups" @click="saveInterestGroups" />
                </div>

            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, nextTick, watch } from 'vue'
import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import SelectField from '@/components/Reusable/FormField/SelectField.vue'
import { connectionSchema, listSchema } from '@/validations/admin/mailchimpValidations'

const COMPONENT = 'mailchimp-settings'

const { errors, setErrors, setFieldError } = useForm()

// ── Tabs ───────────────────────────────────────────────────────────────────
const activeTab = ref('connection')
const tabs = [
    { key: 'connection',      icon: 'fas fa-plug',    label: __('message.connection')      },
    { key: 'field-mapping',   icon: 'fas fa-sliders', label: __('message.field_mapping')   },
    { key: 'interest-groups', icon: 'fas fa-tags',    label: __('message.interest_groups') },
]

watch(activeTab, async tab => {
    if (tab === 'field-mapping' && form.listId && !syncingFields.value) {
        if (!mergeTags.value.length) await syncFields()
    }
    if (tab === 'interest-groups' && form.listId && !syncingGroups.value) {
        // Always reload mapping data (product rows) from DB on tab open
        await loadMappingData()
        // Only call Mailchimp API if no groups synced yet
        if (!interestGroups.value.length) await syncGroups()
    }
})

// ── Flags ──────────────────────────────────────────────────────────────────
const loading          = ref(true)
const connecting       = ref(false)
const savingConnection = ref(false)
const savingMapping    = ref(false)
const savingGroups     = ref(false)
const syncingFields    = ref(false)
const syncingGroups    = ref(false)
const connectionStatus = ref('idle')   // 'idle' | 'connected' | 'failed'

// ── Lists (infinite scroll) ────────────────────────────────────────────────
const lists           = ref([])
const listsHasMore    = ref(false)
const listsLoading    = ref(false)
const listsOffset     = ref(0)
const PAGE_SIZE       = 20
const listSentinelRef = ref(null)
let   listObserver    = null

// ── Connection form ────────────────────────────────────────────────────────
const form = reactive({ apiKey: '', listId: null, subscribeStatus: 'subscribed' })

const selectedList = computed({
    get: () => lists.value.find(l => l.id === form.listId) ?? null,
    set: val => { form.listId = val?.id ?? null },
})

const subscribeOptions = [
    { id: 'subscribed',   name: __('message.subscribed')    },
    { id: 'unsubscribed', name: __('message.unsubscribed')  },
    { id: 'pending',      name: __('message.pending_optin') },
]

// ── User fields (fixed list for left column) ───────────────────────────────
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

// ── Field mapping rows ─────────────────────────────────────────────────────
// Rows store IDs only (not object references) to avoid Vue proxy ≠ plain-object
// mismatch that causes SelectField's elements watcher to clear selections.
const mergeTags = ref([])
const fieldRows = ref([{ faveoFieldId: null, mergeTagId: null }])

const mergeTagOptions = computed(() =>
    mergeTags.value.map(t => ({ id: t.tag, name: `${t.name} (${t.tag})` }))
)

function addFieldRow()       { fieldRows.value.push({ faveoFieldId: null, mergeTagId: null }) }
function removeFieldRow(idx) { fieldRows.value.splice(idx, 1) }

// ── Interest groups ────────────────────────────────────────────────────────
const productStatus      = ref(false)
const isPaidStatus       = ref(false)
const interestGroups     = ref([])
const interestCategories = ref([])
const products           = ref([])
const productRows        = ref([{ productId: null, groupId: null }])
const isPaidCategoryId   = ref(null)

const productOptions = computed(() => products.value.map(p => ({ id: String(p.id), name: p.name })))

// All interest group options (for isPaid category selector — shows category-level)
const categoryOptions = computed(() => interestCategories.value.map(c => ({ id: c.id, name: c.title ?? c.name })))

// Product mapping dropdown: exclude whichever category is designated for isPaid (YES/NO)
// so those options don't pollute the product-to-group mapping list.
const interestGroupOptions = computed(() => {
    const isPaidCategory = isPaidCategoryId.value
    return interestGroups.value
        .filter(g => g.category_id !== isPaidCategory)
        .map(g => ({ id: g.category_option_id, name: g.category_name }))
})

function addProductRow()       { productRows.value.push({ productId: null, groupId: null }) }
function removeProductRow(idx) { productRows.value.splice(idx, 1) }

// ── Init ───────────────────────────────────────────────────────────────────
onMounted(async () => {
    try {
        const res = await http.get(`/settings/mailchimp`)
        const d   = res.data?.data ?? {}

        form.apiKey          = d.api_key          ?? ''
        form.listId          = d.list_id          ?? null
        form.subscribeStatus = d.subscribe_status ?? 'subscribed'
        lists.value           = d.lists            ?? []
        listsHasMore.value    = d.lists_has_more   ?? false
        listsOffset.value     = lists.value.length

        if (form.apiKey)  connectionStatus.value = 'connected'
        if (form.listId)  loadMappingData()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

// ── Connect ────────────────────────────────────────────────────────────────
async function connect() {
    try { await connectionSchema.validate({ apiKey: form.apiKey }) }
    catch (err) { setErrors({ apiKey: err.message }); return }
    connecting.value = true
    try {
        const res = await http.post(`/updateMailchimpDetails`, {
            mailchimp_auth_key: form.apiKey,
            status: 1,
        })
        const d = res.data?.data ?? {}
        lists.value        = d.lists          ?? []
        listsHasMore.value = d.lists_has_more ?? false
        listsOffset.value  = lists.value.length
        connectionStatus.value = 'connected'
        successHandler(res, COMPONENT)
    } catch (e) {
        connectionStatus.value = 'failed'
        errorHandler(e, COMPONENT)
    } finally {
        connecting.value = false
    }
}

// ── Save connection ────────────────────────────────────────────────────────
async function saveConnection() {
    if (connectionStatus.value !== 'connected') {
        setErrors({ apiKey: __('message.enter_mailchimp_key') })
        return
    }
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
    savingConnection.value = true
    try {
        const res = await http.patch(`/mailchimp`, {
            list_id:          form.listId,
            subscribe_status: form.subscribeStatus,
        })
        successHandler(res, COMPONENT)
        loadMappingData()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        savingConnection.value = false
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
        errorHandler(e, COMPONENT)
    } finally {
        listsLoading.value = false
    }
}

// ── Load mapping data ──────────────────────────────────────────────────────
async function loadMappingData() {
    try {
        const res = await http.get(`/mailchimp/mapping-data`)
        const d   = res.data?.data ?? {}

        // Merge tags
        const fields = d.fields ?? {}
        mergeTags.value = Object.entries(fields).map(([tag, name]) => ({ tag, name }))

        // Field rows — store IDs only, never object references
        const relation = d.relation ?? {}
        const mapped   = userFields.filter(f => relation[f.id])
        fieldRows.value = mapped.length
            ? mapped.map(f => ({ faveoFieldId: f.id, mergeTagId: relation[f.id] }))
            : [{ faveoFieldId: null, mergeTagId: null }]

        // Interest groups
        productStatus.value      = !!(d.status?.mailchimp_product_status)
        isPaidStatus.value       = !!(d.status?.mailchimp_ispaid_status)
        interestGroups.value     = d.groups      ?? []
        interestCategories.value = d.categories  ?? []
        products.value           = Object.entries(d.products ?? {}).map(([id, name]) => ({ id: String(id), name }))

        const relations = d.group_relations ?? []
        productRows.value = relations.length
            ? relations.map(r => ({ productId: String(r.agora_product_id), groupId: r.mailchimp_group_cat_id }))
            : [{ productId: null, groupId: null }]

        if (d.relation?.is_paid_yes) {
            const match = interestGroups.value.find(g => g.category_option_id === d.relation.is_paid_yes)
            isPaidCategoryId.value = match?.category_id ?? null
        }
    } catch (e) { errorHandler(e, COMPONENT) }
}

// ── Sync fields ────────────────────────────────────────────────────────────
async function syncFields() {
    syncingFields.value = true
    try {
        const res    = await http.post(`/mailchimp/sync-fields`)
        const fields = res.data?.data?.fields ?? {}
        mergeTags.value = Object.entries(fields).map(([tag, name]) => ({ tag, name }))
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        syncingFields.value = false
    }
}

// ── Save field mapping ─────────────────────────────────────────────────────
async function saveMapping() {
    // Validate: each row must have both fields filled or both empty
    const rowErrors = {}
    fieldRows.value.forEach((row, idx) => {
        if (row.faveoFieldId && !row.mergeTagId)
            rowErrors[`field_row_${idx}`] = __('message.select_merge_tag')
        else if (!row.faveoFieldId && row.mergeTagId)
            rowErrors[`field_row_${idx}`] = __('message.select_field')
    })
    if (Object.keys(rowErrors).length) { setErrors(rowErrors); return }

    const payload = {}
    userFields.forEach(f => { payload[f.id] = null })
    fieldRows.value.forEach(row => {
        if (row.faveoFieldId && row.mergeTagId) payload[row.faveoFieldId] = row.mergeTagId
    })
    savingMapping.value = true
    try {
        const res = await http.patch(`/mail-chimp/mapping`, payload)
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        savingMapping.value = false
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
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        syncingGroups.value = false
    }
}

// ── Save interest groups ───────────────────────────────────────────────────
async function saveInterestGroups() {
    const groupErrors = {}

    // isPaid: category required if toggle is on
    if (isPaidStatus.value && !isPaidCategoryId.value)
        groupErrors.isPaidCategoryId = __('message.select_a_group')

    // Product rows: both product and group required if either is filled
    if (productStatus.value) {
        productRows.value.forEach((row, idx) => {
            if (row.productId && !row.groupId)
                groupErrors[`product_row_${idx}`] = __('message.select_group')
            else if (!row.productId && row.groupId)
                groupErrors[`product_row_${idx}`] = __('message.select_product')
        })
    }

    if (Object.keys(groupErrors).length) { setErrors(groupErrors); return }

    savingGroups.value = true
    try {
        await Promise.all([
            http.post(`/mailchimp-prod-status`, { status: productStatus.value ? 1 : 0 }),
            http.post(`/mailchimp-paid-status`, { status: isPaidStatus.value  ? 1 : 0 }),
        ])
        const extra = []
        if (productStatus.value) {
            const rows = productRows.value.filter(r => r.productId && r.groupId)
            if (rows.length)
                extra.push(http.patch(`/mailchimp-group/mapping`, { row: rows.map(r => [r.productId, r.groupId]) }))
        }
        if (isPaidStatus.value && isPaidCategoryId.value)
            extra.push(http.patch(`/mailchimp-ispaid/mapping`, { group: isPaidCategoryId.value }))
        if (extra.length) await Promise.all(extra)

        successHandler({ data: { message: __('message.updated-successfully') } }, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        savingGroups.value = false
    }
}
</script>

<style scoped>
.clickable { cursor: pointer; }
</style>
