<template>
    <div>
        <AppAlert :componentName="COMPONENT" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.cloud_hub') }}</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

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
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">{{ __('message.cloud_central_domain') }}</label>
                                        <input class="form-control" v-model="form.cloud_central_domain" placeholder="https://example.com" />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">{{ __('message.cloud_cname') }}</label>
                                        <input class="form-control" v-model="form.cloud_cname" />
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
                                            :onChange="v => popup.cloud_top_message = v"
                                        />
                                    </div>
                                    <div class="col-md-4">
                                        <TextField
                                            name="cloud_label_field"
                                            :label="__('message.cloud_label_field')"
                                            :value="popup.cloud_label_field"
                                            :onChange="v => popup.cloud_label_field = v"
                                        />
                                    </div>
                                    <div class="col-md-4">
                                        <TextField
                                            name="cloud_label_radio"
                                            :label="__('message.cloud_label_radio')"
                                            :value="popup.cloud_label_radio"
                                            :onChange="v => popup.cloud_label_radio = v"
                                        />
                                    </div>
                                </div>
                                <hr />
                                <p class="text-muted fw-bold mb-2">{{ __('message.set_cloud_free_trial') }}</p>
                                <label class="form-label fw-bold">{{ __('message.cloud_free_trial') }}</label>
                                <div class="form-check form-switch mt-1">
                                    <input id="cloudButton" class="form-check-input" type="checkbox" v-model="form.cloud_button" />
                                    <label class="form-check-label" for="cloudButton">
                                        {{ form.cloud_button ? __('message.enable') : __('message.disable') }}
                                    </label>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button class="btn btn-primary" @click="saveSettings" :disabled="saving">
                                    <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                                    <i v-else class="fas fa-save me-1"></i>
                                    {{ __('message.save') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Products -->
                    <div v-show="activeTab === 'products'">
                        <div class="card card-light">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('message.cloud_product_configuration') }}</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <SelectField
                                            name="cloud_product"
                                            :label="__('message.cloud_product')"
                                            :elements="products"
                                            :value="productForm.cloud_product"
                                            :onChange="v => productForm.cloud_product = v"
                                            :searchable="true"
                                        />
                                    </div>
                                    <div class="col-md-4">
                                        <SelectField
                                            name="cloud_free_plan"
                                            :label="__('message.cloud_free_plan')"
                                            :elements="plans"
                                            :value="productForm.cloud_free_plan"
                                            :onChange="v => productForm.cloud_free_plan = v"
                                            :searchable="true"
                                        />
                                    </div>
                                    <div class="col-md-4">
                                        <TextField
                                            name="cloud_product_key"
                                            :label="__('message.cloud_product_key')"
                                            :value="productForm.cloud_product_key"
                                            :onChange="v => productForm.cloud_product_key = v"
                                        />
                                    </div>
                                </div>
                                <button class="btn btn-primary mb-3" @click="saveProduct" :disabled="savingProduct">
                                    <span v-if="savingProduct" class="spinner-border spinner-border-sm me-1"></span>
                                    <i v-else class="fas fa-plus me-1"></i>
                                    {{ __('message.add') }}
                                </button>
                                <hr />
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
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <SelectField
                                            name="cloud_countries"
                                            :label="__('message.cloud_country')"
                                            :elements="countries"
                                            :value="dcForm.cloud_countries"
                                            :onChange="v => { dcForm.cloud_countries = v; fetchStates() }"
                                            :searchable="true"
                                        />
                                    </div>
                                    <div class="col-md-4">
                                        <SelectField
                                            name="cloud_state"
                                            :label="__('message.cloud_state')"
                                            :elements="states"
                                            :value="dcForm.cloud_state"
                                            :onChange="v => dcForm.cloud_state = v"
                                            optionLabel="state_subdivision_name"
                                            :searchable="true"
                                        />
                                    </div>
                                    <div class="col-md-4">
                                        <TextField
                                            name="cloud_city"
                                            :label="__('message.cloud_city')"
                                            :value="dcForm.cloud_city"
                                            :onChange="v => dcForm.cloud_city = v"
                                        />
                                    </div>
                                </div>
                                <button class="btn btn-primary mb-3" @click="saveDataCenter" :disabled="savingDC">
                                    <span v-if="savingDC" class="spinner-border spinner-border-sm me-1"></span>
                                    <i v-else class="fas fa-plus me-1"></i>
                                    {{ __('message.add') }}
                                </button>
                                <hr />
                                <div id="cloud-map" style="height: 450px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Tenants -->
                    <div v-show="activeTab === 'tenants'">
                        <div class="card card-light">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('message.tenants') }}</h4>
                            </div>
                            <div class="card-body">
                                <DataTable
                                    ref="tenantDtRef"
                                    :url="`${baseUrl}/get-tenants`"
                                    :dataColumns="tenantColumns"
                                    :option="tenantTableOptions"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { h, ref, reactive, onMounted, nextTick, watch } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import SelectField from '@/themes/adminlte/components/forms/SelectField.vue'

const COMPONENT = 'cloud-details'
const el        = document.getElementById('app-root')
const baseUrl   = el?.dataset?.baseUrl ?? ''

const loading       = ref(true)
const saving        = ref(false)
const savingProduct = ref(false)
const savingDC      = ref(false)
const activeTab     = ref('settings')

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
    { key: 'settings',    label: 'Settings',     icon: 'fas fa-cog' },
    { key: 'products',    label: 'Products',     icon: 'fas fa-box' },
    { key: 'datacenters', label: 'Data Centers', icon: 'fas fa-map-marker-alt' },
    { key: 'tenants',     label: 'Tenants',      icon: 'fas fa-users' },
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
    savingProduct.value = true
    try {
        const res = await http.post(`${baseUrl}/cloud-product-store`, {
            cloud_product:     productForm.cloud_product?.id     ?? '',
            cloud_free_plan:   productForm.cloud_free_plan?.id   ?? '',
            cloud_product_key: productForm.cloud_product_key,
        })
        successHandler(res, COMPONENT)
        productForm.cloud_product = productForm.cloud_free_plan = null
        productForm.cloud_product_key = ''
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
        dcForm.cloud_countries = dcForm.cloud_state = null
        dcForm.cloud_city = ''
        states.value = []
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

async function deleteProduct(id) {
    try {
        await http.delete(`${baseUrl}/delete-cloud-product`, { data: { id } })
        productDtRef.value?.refresh()
    } catch (e) { errorHandler(e, COMPONENT) }
}

const productColumns = ['cloud_product', 'cloud_free_plan', 'cloud_product_key', 'trial_status', 'action']

const productTableOptions = reactive({
    headings: {
        cloud_product:     'Cloud Product',
        cloud_free_plan:   'Free Plan',
        cloud_product_key: 'Product Key',
        trial_status:      'Trial Status',
        action:            'Action',
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
            title:   'Delete',
            onClick: () => deleteProduct(row.id),
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

async function deleteTenant(tenantId, orderId) {
    try {
        await http.delete(`${baseUrl}/delete-tenant`, { data: { id: tenantId, orderId } })
        tenantDtRef.value?.refresh()
    } catch (e) { errorHandler(e, COMPONENT) }
}

const tenantColumns = [
    'order', 'user', 'email', 'mobile', 'country',
    'expiry', 'deletion', 'plan', 'tenant', 'domain',
    'db_name', 'db_username', 'action',
]

const tenantTableOptions = reactive({
    headings: {
        order:       'Order',
        user:        'User',
        email:       'Email',
        mobile:      'Mobile',
        country:     'Country',
        expiry:      'Expiry Day',
        deletion:    'Deletion Day',
        plan:        'Plan Status',
        tenant:      'Tenant',
        domain:      'Admin Domain',
        db_name:     'DB Name',
        db_username: 'DB Username',
        action:      'Action',
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
            title:   'Delete',
            onClick: () => deleteTenant(row.action?.delete?.tenant_id, row.order?.order_id),
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
