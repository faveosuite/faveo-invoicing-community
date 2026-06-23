<template>
    <div>
        <AppAlert :componentName="COMPONENT" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.cloud_hub') }}</h4>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">
                    <ul class="nav nav-tabs mb-4">
                        <li class="nav-item" v-for="tab in tabs" :key="tab.key">
                            <a
                                class="nav-link"
                                :class="{ active: activeTab === tab.key }"
                                href="#"
                                @click.prevent="switchTab(tab.key)"
                            >
                                <i :class="tab.icon + ' me-1'"></i>{{ tab.label }}
                            </a>
                        </li>
                    </ul>

                    <!-- Tab: Settings -->
                    <div v-show="activeTab === 'settings'">
                        <div class="card card-light">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('message.cloud_hub') }}</h4>
                            </div>
                            <div class="card-body">
                                <p class="text-muted fw-bold mb-2">{{ __('message.cloud_server') }}</p>
                                <div class="row">
                                    <div class="col-md-6">
                                        <TextField
                                            name="cloud_central_domain"
                                            :label="__('message.cloud_central_domain')"
                                            :value="form.cloud_central_domain"
                                            :onChange="(val) => { setFieldError('cloud_central_domain', undefined); form.cloud_central_domain = val }"
                                            :placehold="'https://example.com'"
                                            :error="errors.cloud_central_domain"
                                        />
                                    </div>
                                    <div class="col-md-6">
                                        <TextField
                                            name="cloud_cname"
                                            :label="__('message.cloud_cname')"
                                            :value="form.cloud_cname"
                                            :onChange="(val) => { setFieldError('cloud_cname', undefined); form.cloud_cname = val }"
                                            :error="errors.cloud_cname"
                                        />
                                    </div>
                                </div>
                                <hr />
                                <p class="text-muted fw-bold mb-2">{{ __('message.customise_cloud_popup') }}</p>
                                <div class="row">
                                    <div class="col-md-4">
                                        <TextField
                                            name="cloud_top_message"
                                            :label="__('message.cloud_top_message')"
                                            :value="popup.cloud_top_message"
                                            :onChange="v => { setFieldError('cloud_top_message', undefined); popup.cloud_top_message = v }"
                                            :error="errors.cloud_top_message"
                                        />
                                    </div>
                                    <div class="col-md-4">
                                        <TextField
                                            name="cloud_label_field"
                                            :label="__('message.cloud_label_field')"
                                            :value="popup.cloud_label_field"
                                            :onChange="v => { setFieldError('cloud_label_field', undefined); popup.cloud_label_field = v }"
                                            :error="errors.cloud_label_field"
                                        />
                                    </div>
                                    <div class="col-md-4">
                                        <TextField
                                            name="cloud_label_radio"
                                            :label="__('message.cloud_label_radio')"
                                            :value="popup.cloud_label_radio"
                                            :onChange="v => { setFieldError('cloud_label_radio', undefined); popup.cloud_label_radio = v }"
                                            :error="errors.cloud_label_radio"
                                        />
                                    </div>
                                </div>
                                <hr />
                                <p class="text-muted fw-bold mb-2">{{ __('message.set_cloud_free_trial') }}</p>
                                <label class="form-label fw-bold d-block">{{ __('message.cloud_free_trial') }}</label>
                                <Switch name="cloud_button" :value="form.cloud_button" :onChange="(val) => form.cloud_button = val" />
                            </div>
                            <div class="card-footer">
                                <action-button action="save" :loading="saving" @click="saveSettings" />
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Products -->
                    <div v-show="activeTab === 'products'">
                        <div class="card card-light">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('message.cloud_product_configuration') }}</h4>
                                <div class="card-tools">
                                    <button class="btn btn-tool" v-tooltip="__('message.add')" @click="openProductModal">
                                        <i class="fas fa-plus fw-bold"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <DataTable
                                    ref="productDtRef"
                                    :url="`${baseUrl}/fetch-data`"
                                    :dataColumns="productColumns"
                                    :option="productTableOptions"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Data Centers -->
                    <div v-show="activeTab === 'datacenters'">
                        <div class="card card-light">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('message.cloud_data_centers') }}</h4>
                                <div class="card-tools">
                                    <button class="btn btn-tool" v-tooltip="__('message.add')" @click="openDCModal">
                                        <i class="fas fa-plus fw-bold"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="cloud-map" style="height: 450px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Tenants -->
                    <div v-show="activeTab === 'tenants'">
                        <div class="card card-light">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('message.tenants') }}</h4>
                                <div class="card-tools">
                                    <button
                                        class="btn btn-tool"
                                        v-tooltip="__('message.export')"
                                        :disabled="exportingTenants"
                                        @click="exportTenants"
                                    >
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <DataTable
                                    ref="tenantDtRef"
                                    :url="`${baseUrl}/get-tenants`"
                                    :dataColumns="tenantColumns"
                                    :option="tenantTableOptions"
                                >
                                    <template #table-tools>
                                        <ColumnSelector
                                            entityType="tenats"
                                            :labels="tenantColumnLabels"
                                            :pinStart="[]"
                                            componentName="cloud-details"
                                            @change="onTenantColumnsChange"
                                        />
                                    </template>
                                </DataTable>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Product Config Modal -->
    <AppModal :showModal="showProductModal" :onClose="closeProductModal" :showCloseBtn="false">
        <template #title>
            <h4>{{ __('message.cloud_product_configuration') }}</h4>
        </template>
        <template #fields>
            <SelectField
                name="cloud_product"
                :label="__('message.cloud_product')"
                :elements="products"
                :value="productForm.cloud_product"
                :onChange="v => { setFieldError('cloud_product', undefined); productForm.cloud_product = v }"
                :searchable="true"
                :error="errors.cloud_product"
            />
            <SelectField
                name="cloud_free_plan"
                :label="__('message.cloud_free_plan')"
                :elements="plans"
                :value="productForm.cloud_free_plan"
                :onChange="v => { setFieldError('cloud_free_plan', undefined); productForm.cloud_free_plan = v }"
                :searchable="true"
                :error="errors.cloud_free_plan"
            />
            <TextField
                name="cloud_product_key"
                :label="__('message.cloud_product_key')"
                :value="productForm.cloud_product_key"
                :onChange="v => { setFieldError('cloud_product_key', undefined); productForm.cloud_product_key = v }"
                :error="errors.cloud_product_key"
            />
        </template>
        <template #controls>
            <action-button action="save" type="button" :loading="savingProduct" @click="saveProduct" />
        </template>
    </AppModal>

    <!-- Tenant Delete Confirm Modal -->
    <DeleteModal
        v-if="pendingDeleteTenant"
        :showModal="true"
        :onClose="() => pendingDeleteTenant = null"
        :deleteUrl="`${baseUrl}/delete-tenant`"
        :deleteData="{ id: pendingDeleteTenant.tenantId, orderId: pendingDeleteTenant.orderNumber }"
        :title="__('message.delete_tenant')"
        :message="__('message.are_you_sure')"
        :componentName="COMPONENT"
        @deleted="() => { pendingDeleteTenant = null; tenantDtRef?.refresh() }"
    />

    <!-- Product Delete Confirm Modal -->
    <DeleteModal
        v-if="pendingDeleteProduct"
        :showModal="true"
        :onClose="() => pendingDeleteProduct = null"
        :deleteUrl="`${baseUrl}/delete-cloud-product`"
        :deleteData="{ id: pendingDeleteProduct.id }"
        :title="__('message.Delete')"
        :message="__('message.are_you_sure')"
        :componentName="COMPONENT"
        @deleted="() => { pendingDeleteProduct = null; productDtRef?.refresh() }"
    />

    <!-- Data Center Config Modal -->
    <AppModal :showModal="showDCModal" :onClose="closeDCModal" :showCloseBtn="false">
        <template #title>
            <h4>{{ __('message.cloud_data_centers') }}</h4>
        </template>
        <template #fields>
            <SelectField
                name="cloud_countries"
                :label="__('message.country')"
                :elements="countries"
                :value="dcForm.cloud_countries"
                :onChange="v => { dcForm.cloud_countries = v; fetchStates() }"
                :searchable="true"
            />
            <SelectField
                name="cloud_state"
                :label="__('message.state')"
                :elements="states"
                :value="dcForm.cloud_state"
                :onChange="v => dcForm.cloud_state = v"
                optionLabel="state_subdivision_name"
                :searchable="true"
            />
            <TextField
                name="cloud_city"
                :label="__('message.city')"
                :value="dcForm.cloud_city"
                :onChange="v => dcForm.cloud_city = v"
            />
        </template>
        <template #controls>
            <action-button action="save" type="button" :loading="savingDC" @click="saveDataCenter" />
        </template>
    </AppModal>
</template>

<script setup>
import { h, ref, reactive, onMounted, nextTick, watch } from 'vue'
import { useForm } from 'vee-validate'
import { validateForm } from '@/helpers/formUtils.js'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import SelectField from '@/components/Reusable/FormField/SelectField.vue'
import TextField from '@/components/Reusable/FormField/TextField.vue'
import Switch from '@/components/Reusable/FormField/Switch.vue'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'
import { cloudSettingsSchema, cloudProductSchema } from '@/validations/admin/cloudValidations'
import ColumnSelector from '@/components/Reusable/ColumnSelector.vue'

const COMPONENT = 'cloud-details'
const el        = document.getElementById('app-root')
const baseUrl   = el?.dataset?.baseUrl ?? ''

const { errors, setErrors, setFieldError, resetForm } = useForm()

const loading       = ref(true)
const saving        = ref(false)
const savingProduct = ref(false)
const savingDC      = ref(false)
const activeTab     = ref('settings')

const showProductModal = ref(false)
const showDCModal      = ref(false)

const pendingDeleteTenant  = ref(null)   // { tenantId, orderNumber }
const pendingDeleteProduct = ref(null)   // { id }

function openProductModal()  { resetForm(); showProductModal.value = true }
function closeProductModal() { showProductModal.value = false; productForm.cloud_product = null; productForm.cloud_free_plan = null; productForm.cloud_product_key = '' }
function openDCModal()  { showDCModal.value = true }
function closeDCModal() { showDCModal.value = false; dcForm.cloud_countries = null; dcForm.cloud_state = null; dcForm.cloud_city = ''; states.value = [] }

const form        = reactive({ cloud_central_domain: '', cloud_cname: '', cloud_button: false })
const popup       = reactive({ cloud_top_message: '', cloud_label_field: '', cloud_label_radio: '' })
const productForm = reactive({ cloud_product: null, cloud_free_plan: null, cloud_product_key: '' })
const dcForm      = reactive({ cloud_countries: null, cloud_state: null, cloud_city: '' })

const products    = ref([])
const plans       = ref([])
const countries   = ref([])
const states      = ref([])
const regions     = ref([])
const productDtRef = ref(null)
let leafletMap = null

const tabs = [
    { key: 'settings',    label: __('message.settings'),          icon: 'fas fa-cog' },
    { key: 'products',    label: __('message.products'),          icon: 'fas fa-box' },
    { key: 'datacenters', label: __('message.cloud_data_centers'), icon: 'fas fa-map-marker-alt' },
    { key: 'tenants',     label: __('message.tenants'),           icon: 'fas fa-users' },
]

function switchTab(key) {
    activeTab.value = key
}

watch(activeTab, async (val) => {
    if (val === 'datacenters') {
        await nextTick()
        leafletMap ? leafletMap.invalidateSize() : initMap()
    }
})

onMounted(async () => {
    try {
        const res  = await http.get(`${baseUrl}/settings/cloud-details`)
        const data = res.data?.data ?? {}
        Object.assign(form, {
            cloud_central_domain: data.cloud_central_domain ?? '',
            cloud_cname:          data.cloud_cname          ?? '',
            cloud_button:         data.cloud_button         ?? false,
        })
        Object.assign(popup, {
            cloud_top_message: data.cloud_top_message ?? '',
            cloud_label_field: data.cloud_label_field ?? '',
            cloud_label_radio: data.cloud_label_radio ?? '',
        })
        products.value  = data.products  ?? []
        plans.value     = data.plans     ?? []
        countries.value = data.countries ?? []
        regions.value   = data.regions   ?? []
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

// ── Leaflet ──────────────────────────────────────────────────────────────────

function loadLeaflet() {
    return new Promise((resolve, reject) => {
        if (!document.getElementById('leaflet-css')) {
            const link = document.createElement('link')
            link.id = 'leaflet-css'; link.rel = 'stylesheet'
            link.href = 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css'
            document.head.appendChild(link)
        }
        if (window.L) { resolve(); return }
        const script = document.createElement('script')
        script.src     = 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js'
        script.onload  = resolve
        script.onerror = reject
        document.head.appendChild(script)
    })
}

async function initMap() {
    try { await loadLeaflet() } catch { return }
    const mapEl = document.getElementById('cloud-map')
    if (!mapEl || leafletMap) return
    const L = window.L
    leafletMap = L.map('cloud-map', { minZoom: 2 }).setView([0, 0], 2)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(leafletMap)
    addMapMarkers()
}

function addMapMarkers() {
    if (!leafletMap || !window.L) return
    const L = window.L
    regions.value.forEach(region => {
        const marker = L.marker([region.latitude, region.longitude]).addTo(leafletMap)
        marker.bindPopup(region.name)
        let clicks = 0
        marker.on('click', async () => {
            if (++clicks === 2) { clicks = 0; await removeRegion(region.name, marker) }
        })
        marker.on('popupclose', () => { clicks = 0 })
    })
}

async function removeRegion(name, marker) {
    try {
        await http.delete(`${baseUrl}/remove-location`, { data: { location_id: name } })
        leafletMap?.removeLayer(marker)
        regions.value = regions.value.filter(r => r.name !== name)
    } catch (e) { errorHandler(e, COMPONENT) }
}

// ── Save handlers ─────────────────────────────────────────────────────────────

async function saveSettings() {
    if (!await validateForm(cloudSettingsSchema, { ...form, ...popup }, setErrors)) return
    saving.value = true
    try {
        const [res] = await Promise.all([
            http.post(`${baseUrl}/cloud-details`, {
                cloud_central_domain: form.cloud_central_domain,
                cloud_cname:          form.cloud_cname,
            }),
            http.post(`${baseUrl}/enable/cloud`, { debug: form.cloud_button ? 'true' : 'false' }),
            http.post(`${baseUrl}/cloud-pop-up`, popup),
        ])
        successHandler(res, COMPONENT)
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { saving.value = false }
}

async function saveProduct() {
    if (!await validateForm(cloudProductSchema, productForm, setErrors)) return
    savingProduct.value = true
    try {
        const res = await http.post(`${baseUrl}/cloud-product-store`, {
            cloud_product:     productForm.cloud_product?.id     ?? '',
            cloud_free_plan:   productForm.cloud_free_plan?.id   ?? '',
            cloud_product_key: productForm.cloud_product_key,
        })
        successHandler(res, COMPONENT)
        closeProductModal()
        productDtRef.value?.refresh()
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { savingProduct.value = false }
}

async function saveDataCenter() {
    savingDC.value = true
    try {
        const res = await http.post(`${baseUrl}/cloud-data-center-store`, {
            cloud_countries: dcForm.cloud_countries?.code ?? '',
            cloud_state:     dcForm.cloud_state?.iso2     ?? '',
            cloud_city:      dcForm.cloud_city,
        })
        successHandler(res, COMPONENT)
        const dr = await http.get(`${baseUrl}/settings/cloud-details`)
        regions.value = dr.data?.data?.regions ?? []
        if (leafletMap && window.L) {
            leafletMap.eachLayer(l => { if (l instanceof window.L.Marker) leafletMap.removeLayer(l) })
            addMapMarkers()
        }
        closeDCModal()
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { savingDC.value = false }
}

async function fetchStates() {
    dcForm.cloud_state = null
    states.value = []
    if (!dcForm.cloud_countries?.code) return
    try {
        const res = await http.get(`${baseUrl}/get-state/${dcForm.cloud_countries.code.toUpperCase()}`)
        states.value = res.data?.data?.states ?? []
    } catch (e) { errorHandler(e, COMPONENT) }
}

// ── Products DataTable ────────────────────────────────────────────────────────

async function toggleTrialStatus(id, status) {
    try {
        await http.post(`${baseUrl}/update-trial-status`, { id, status })
    } catch (e) { errorHandler(e, COMPONENT); productDtRef.value?.refresh() }
}

function confirmDeleteProduct(id) {
    pendingDeleteProduct.value = { id }
}

const productColumns = ['cloud_product', 'cloud_free_plan', 'cloud_product_key', 'trial_status', 'action']

const productTableOptions = reactive({
    headings: {
        cloud_product:     __('message.cloud_product'),
        cloud_free_plan:   __('message.cloud_free_plan'),
        cloud_product_key: __('message.cloud_product_key'),
        trial_status:      __('message.trial_status_heading'),
        action:            __('message.action'),
    },
    columnsClasses: {
        cloud_product: 'dt-name',
        cloud_free_plan: 'dt-name',
        cloud_product_key: 'dt-code',
        trial_status: 'dt-status',
        action: 'dt-action',
    },
    templates: {
        cloud_product:     (f, row) => row.cloud_product     || '—',
        cloud_free_plan:   (f, row) => row.cloud_free_plan   || '—',
        cloud_product_key: (f, row) => row.cloud_product_key || '—',
        trial_status: (f, row) => h('input', {
            type:     'checkbox',
            checked:  row.trial_status,
            class:    'form-check-input',
            onChange: (e) => toggleTrialStatus(row.id, e.target.checked ? 1 : 0),
        }),
        action: (f, row) => h('button', {
            class:   'btn btn-light table_btn',
            title:   __('message.Delete'),
            onClick: () => confirmDeleteProduct(row.id),
        }, [h('i', { class: 'fas fa-trash' })]),
    },
    sortable:   ['cloud_product'],
    filterable: true,
    requestAdapter(data) {
        return {
            'sort-field':   data.orderBy ?? 'updated_at',
            'sort-order':   data.ascending ? 'asc' : 'desc',
            'search-query': (data.query ?? '').trim(),
            page:           data.page,
            limit:          data.limit,
        }
    },
    responseAdapter({ data }) {
        const res = data?.data
        return { data: res?.data ?? [], count: res?.total ?? 0 }
    },
    orderBy: { column: 'updated_at', ascending: false },
})

// ── Tenants DataTable ─────────────────────────────────────────────────────────

const tenantDtRef = ref(null)
const exportingTenants = ref(false)

async function exportTenants() {
    if (exportingTenants.value) return
    exportingTenants.value = true
    try {
        const res = await http.get(`${baseUrl}/export-tenats`)
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        exportingTenants.value = false
    }
}

function confirmDeleteTenant(tenantId, orderNumber) {
    pendingDeleteTenant.value = { tenantId, orderNumber }
}

// report_columns.key (type 'tenats') ↔ DataTable column names
const TENANT_REPORT_TO_COL = {
    Order:          'order',
    name:           'user',
    email:          'email',
    mobile:         'mobile',
    country:        'country',
    'Expiry day':   'expiry',
    'Deletion day': 'deletion',
    plan:           'plan',
    tenants:        'tenant',
    domain:         'domain',
    db_name:        'db_name',
    db_username:    'db_username',
    action:         'action',
}

const tenantColumnLabels = {
    Order:          __('message.order'),
    name:           __('message.user'),
    email:          __('message.email'),
    mobile:         __('message.mobile'),
    country:        __('message.country'),
    'Expiry day':   __('message.expiry_day'),
    'Deletion day': __('message.deletion_day'),
    plan:           __('message.plan_status'),
    tenants:        __('message.tenant'),
    domain:         __('message.admin_domain'),
    db_name:        __('message.db_name'),
    db_username:    __('message.db_username'),
}

const DEFAULT_TENANT_COLUMNS = [
    'order', 'user', 'email', 'mobile', 'country',
    'expiry', 'deletion', 'plan', 'tenant', 'domain',
    'db_name', 'db_username', 'action',
]

const tenantColumns = ref([...DEFAULT_TENANT_COLUMNS])

function onTenantColumnsChange(reportKeys) {
    const mapped = reportKeys.map(k => TENANT_REPORT_TO_COL[k]).filter(Boolean)
    tenantColumns.value = mapped.length ? mapped : [...DEFAULT_TENANT_COLUMNS]
}

const tenantTableOptions = reactive({
    headings: {
        order:       __('message.order'),
        user:        __('message.user'),
        email:       __('message.email'),
        mobile:      __('message.mobile'),
        country:     __('message.country'),
        expiry:      __('message.expiry_day'),
        deletion:    __('message.deletion_day'),
        plan:        __('message.plan_status'),
        tenant:      __('message.tenant'),
        domain:      __('message.admin_domain'),
        db_name:     __('message.db_name'),
        db_username: __('message.db_username'),
        action:      __('message.action'),
    },
    columnsClasses: {
        order: 'dt-number',
        user: 'dt-name',
        email: 'dt-email',
        mobile: 'dt-mobile',
        country: 'dt-country',
        expiry: 'dt-date',
        deletion: 'dt-date',
        plan: 'dt-name',
        tenant: 'dt-name',
        domain: 'dt-text',
        db_name: 'dt-text',
        db_username: 'dt-name',
        action: 'dt-action',
    },
    templates: {
        order:       (f, row) => row.order?.order_number        || '—',
        user:        (f, row) => row.user?.name                 || '—',
        email:       (f, row) => row.user?.email                || '—',
        mobile:      (f, row) => row.user?.mobile               || '—',
        country:     (f, row) => row.user?.country              || '—',
        expiry:      (f, row) => row.dates?.subscription_expiry || '—',
        deletion:    (f, row) => row.dates?.deletion_date       || '—',
        plan:        (f, row) => row.order?.subscription        || '—',
        tenant:      (f, row) => row.tenant_id                  || '—',
        domain:      (f, row) => row.links?.tenant_domain
            ? h('a', { href: row.links.tenant_domain, target: '_blank', rel: 'noopener' }, row.links.tenant_domain)
            : '—',
        db_name:     (f, row) => row.database?.name             || '—',
        db_username: (f, row) => row.database?.username         || '—',
        action:      (f, row) => h('button', {
            class:   'btn btn-light table_btn',
            title:   __('message.Delete'),
            onClick: () => confirmDeleteTenant(row.action?.delete?.tenant_id, row.action?.delete?.order_number),
        }, [h('i', { class: 'fas fa-trash' })]),
    },
    sortable:   [],
    filterable: true,
    requestAdapter(data) {
        return {
            'sort-field':   data.orderBy ?? 'created_at',
            'sort-order':   data.ascending ? 'asc' : 'desc',
            'search-query': (data.query ?? '').trim(),
            page:           data.page,
            limit:          data.limit,
        }
    },
    requestFunction(data) {
        return http.get(`${baseUrl}/get-tenants`, { params: data })
            .catch(e => {
                errorHandler(e, COMPONENT)
                return { data: { data: { data: [], total: 0, per_page: data.limit || 10, current_page: 1, from: null, to: null, next_page_url: null, prev_page_url: null } } }
            })
    },
})
</script>
