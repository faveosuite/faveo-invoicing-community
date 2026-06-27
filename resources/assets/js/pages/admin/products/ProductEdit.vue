<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.edit_product') }}</h4>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body px-0 pt-0">
                    <ul class="nav nav-tabs px-3 pt-2" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link" :class="{ active: tab === 'details' }" href="#" @click.prevent="tab = 'details'">
                                <i class="fas fa-circle-info me-1"></i>{{ __('message.details') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" :class="{ active: tab === 'plans' }" href="#" @click.prevent="tab = 'plans'">
                                <i class="fas fa-list me-1"></i>{{ __('message.plans') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" :class="{ active: tab === 'versions' }" href="#" @click.prevent="tab = 'versions'">
                                <i class="fas fa-code-branch me-1"></i>{{ __('message.versions') }}
                            </a>
                        </li>
                        <li class="nav-item" v-if="!isPluginProduct">
                            <a class="nav-link" :class="{ active: tab === 'plugins' }" href="#" @click.prevent="tab = 'plugins'">
                                <i class="fas fa-puzzle-piece me-1"></i>Plugins
                            </a>
                        </li>
                    </ul>

                    <div class="p-3">
                        <!-- Details Tab -->
                        <div v-show="tab === 'details'">
                            <div class="row">
                                <!-- Left Column (Primary Editorial Content) -->
                                <div class="col-lg-7">
                                    <!-- Basic Information & Content -->
                                    <div class="card card-light mb-4">
                                        <div class="card-header">
                                            <h4 class="card-title">
                                                <i class="fas fa-circle-info text-muted me-2"></i>Basic Information &amp; Content
                                            </h4>
                                        </div>
                                        <div class="card-body">
                                            <!-- Basic Information fields -->
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <TextField name="name" :label="__('message.name')" :required="true" :value="form.name" :onChange="onChange" :error="errors.name" />
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <TextField name="product_sku" :label="__('message.product_sku')" :required="true" :value="form.product_sku" :onChange="onChange" :error="errors.product_sku" />
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <DynamicSelect
                                                        name="type"
                                                        :label="__('message.license-type')"
                                                        :required="true"
                                                        :apiEndpoint="`${baseUrl}/dependency/license-types`"
                                                        dataKey="license_types"
                                                        :value="form.typeObj"
                                                        :onChange="onChange"
                                                        :placeholder="__('message.select_license_type')"
                                                        :error="errors.type"
                                                    />
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <DynamicSelect
                                                        name="group"
                                                        :label="__('message.group')"
                                                        :required="true"
                                                        :apiEndpoint="`${baseUrl}/dependency/product-groups`"
                                                        dataKey="product_groups"
                                                        :value="form.groupObj"
                                                        :onChange="onChange"
                                                        :placeholder="__('message.select_group')"
                                                        :error="errors.group"
                                                    />
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <DynamicSelect
                                                        name="parent"
                                                        :label="__('message.parent')"
                                                        :apiEndpoint="`${baseUrl}/dependency/all-products`"
                                                        dataKey="products"
                                                        :value="form.parentObj"
                                                        :onChange="onChange"
                                                        :placeholder="__('message.select_parent')"
                                                    />
                                                </div>
                                            </div>

                                            <!-- Content fields -->
                                            <div class="mb-3">
                                                <TinyMCE name="description" :label="__('message.description')" :required="true" id="editor-description" :value="form.description" :onChange="onChange" :error="errors.description" />
                                            </div>
                                            <div class="mb-3">
                                                <TinyMCE name="short_description" :label="__('message.short_description')" :required="true" id="editor-short-description" :value="form.short_description" :onChange="onChange" :error="errors.short_description" />
                                            </div>
                                            <div class="mb-3">
                                                <TinyMCE name="product_description" :label="__('message.product_description')" :required="true" id="editor-product-description" :value="form.product_description" :onChange="onChange" :error="errors.product_description" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column (Secondary Settings/Media) -->
                                <div class="col-lg-5">
                                    <!-- Product Image & File Source -->
                                    <div class="card card-light mb-4">
                                        <div class="card-header">
                                            <h4 class="card-title">
                                                <i class="fas fa-image text-muted me-2"></i>Product Image &amp; Source
                                            </h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-4">
                                                <ImageField
                                                    name="image"
                                                    :label="__('message.product_image')"
                                                    :value="form.currentImage"
                                                    :componentName="COMPONENT"
                                                    :onChange="onImageChange"
                                                />
                                            </div>

                                            <div class="mb-3" v-if="githubEnabled">
                                                <SelectField
                                                    name="file_source"
                                                    :label="__('message.where_retrieve_files') || 'File Source'"
                                                    :elements="fileSourceOptions"
                                                    :value="fileSourceValue"
                                                    :onChange="(val) => form.file_source = val?.id ?? 'filesystem'"
                                                    :clearable="false"
                                                    :searchable="false"
                                                />

                                                <!-- GitHub Sync configuration card -->
                                                <div v-if="form.file_source === 'github'" class="mt-3 p-3 border rounded-3 bg-white">
                                                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                                        <i class="fab fa-github text-dark fs-5"></i>
                                                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 13px;">GitHub Repository Integration</h6>
                                                    </div>
                                                    <div class="row g-2">
                                                        <div class="col-md-12 mb-2">
                                                            <TextField name="github_owner" :label="__('message.github-owner')" :required="true" :value="form.github_owner" :onChange="onChange" :error="errors.github_owner" />
                                                        </div>
                                                        <div class="col-md-12 mb-2">
                                                            <TextField name="github_repository" :label="__('message.github-repository-name')" :required="true" :value="form.github_repository" :onChange="onChange" :error="errors.github_repository" />
                                                        </div>
                                                        <div class="col-md-12 mb-2">
                                                            <TextField name="version" :label="__('message.version')" :value="form.version" :onChange="onChange" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tax -->
                                    <div class="card card-light mb-4">
                                        <div class="card-header">
                                            <h4 class="card-title">
                                                <i class="fas fa-receipt text-muted me-2"></i>Tax
                                            </h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-2">
                                                    <SelectField
                                                        name="tax_status"
                                                        :label="__('message.tax_status')"
                                                        :elements="taxStatusOptions"
                                                        :value="taxStatusOptions.find(o => o.id === form.tax_status) ?? taxStatusOptions[0]"
                                                        :onChange="(val) => form.tax_status = val?.id ?? 0"
                                                        :clearable="false"
                                                        :searchable="false"
                                                    />
                                                </div>
                                                <div class="col-md-6 mb-2" v-if="form.tax_status === 1">
                                                    <SelectField
                                                        name="tax_class_id"
                                                        :label="__('message.tax_class')"
                                                        :elements="taxClasses"
                                                        :value="taxClasses.find(c => c.id === form.tax_class_id) ?? null"
                                                        :onChange="(val) => form.tax_class_id = val?.id ?? ''"
                                                        :clearable="false"
                                                        :searchable="false"
                                                    />
                                                </div>
                                            </div>
                                            <div v-if="!taxClasses.length" class="text-muted mt-2">
                                                <small>{{ __('message.no_tax_classes') }}</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Cart & Display -->
                                    <div class="card card-light mb-4">
                                        <div class="card-header">
                                            <h4 class="card-title">
                                                <i class="fas fa-shopping-cart text-muted me-2"></i>Cart &amp; Display
                                            </h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <RadioButton
                                                    name="show_agent"
                                                    :label="__('message.show_cart_page')"
                                                    :options="[{ name: __('message.agents'), value: 1 }, { name: __('message.product_quantity'), value: 0 }]"
                                                    :value="form.show_agent ? 1 : 0"
                                                    :onChange="(val) => form.show_agent = !!val"
                                                    classname="mb-2"
                                                />
                                            </div>

                                            <div class="mb-3">
                                                <div v-show="form.show_agent" class="ms-1">
                                                    <Checkbox name="can_modify_agent" :label="__('message.allow_multiple_agents_quantity')" :value="form.can_modify_agent" :onChange="(val) => form.can_modify_agent = val" />
                                                </div>
                                                <div v-show="!form.show_agent" class="ms-1">
                                                    <Checkbox name="can_modify_quantity" :label="__('message.allow_multiple_product_quantity')" :value="form.can_modify_quantity" :onChange="(val) => form.can_modify_quantity = val" />
                                                </div>
                                            </div>

                                            <div class="mb-2">
                                                <TextField
                                                    name="shoping_cart_link"
                                                    :label="__('message.shoping-cart-link')"
                                                    :value="form.shoping_cart_link"
                                                    :required="true"
                                                    :onChange="onChange"
                                                    :error="errors.shoping_cart_link"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Visibility & Behavior -->
                                    <div class="card card-light mb-4">
                                        <div class="card-header">
                                            <h4 class="card-title">
                                                <i class="fas fa-sliders-h text-muted me-2"></i>Visibility &amp; Behavior
                                            </h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3 d-flex align-items-center gap-1">
                                                <Checkbox name="hidden" :label="__('message.hidden_pricing_page')" :value="form.hidden" :onChange="(val) => form.hidden = val" />
                                                <Tooltip :message="__('message.tick-to-hide-from-order-form')" />
                                            </div>

                                            <div class="mb-3 d-flex align-items-center gap-1">
                                                <Checkbox name="invoice_hidden" :label="__('message.hidden_admin_dropdown')" :value="form.invoice_hidden" :onChange="(val) => form.invoice_hidden = val" />
                                                <Tooltip :message="__('message.tick-to-hide-from-invoice')" />
                                            </div>

                                            <div class="mb-3 d-flex align-items-center gap-1">
                                                <Checkbox name="require_domain" :label="__('message.require_domain')" :value="form.require_domain" :onChange="(val) => form.require_domain = val" />
                                                <Tooltip :message="__('message.tick-to-show-domain-registration-options')" />
                                            </div>

                                            <div class="mb-3 d-flex align-items-center gap-1">
                                                <Checkbox name="highlight" :label="__('message.highlight')" :value="form.highlight" :onChange="(val) => form.highlight = val" />
                                                <Tooltip :message="__('message.tick-to-highlight-product')" />
                                            </div>

                                            <div class="mb-3 d-flex align-items-center gap-1">
                                                <Checkbox name="add_to_contact" :label="__('message.contact_to_sales')" :value="form.add_to_contact" :onChange="(val) => form.add_to_contact = val" />
                                                <Tooltip :message="__('message.tick-to-add_to_contact-product')" />
                                            </div>

                                            <div class="mb-2 d-flex align-items-center gap-1">
                                                <Checkbox name="whatsapp_integration" :label="__('message.whatsapp_product_heading')" :value="form.whatsapp_integration" :onChange="(val) => form.whatsapp_integration = val" />
                                                <Tooltip :message="__('message.whatsapp_product_explanation')" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Plans Tab -->
                        <div v-show="tab === 'plans'">
                            <div class="card card-light">
                                <div class="card-header">
                                    <h4 class="card-title">{{ __('message.plans') }}</h4>
                                    <div class="card-tools">
                                        <router-link
                                            to="/products/plans/create"
                                            class="btn btn-tool"
                                            v-tooltip="__('message.create_product_plan')"
                                        >
                                            <i class="fas fa-plus"></i>
                                        </router-link>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <DataTable
                                        :url="plansApiUrl"
                                        :dataColumns="planColumns"
                                        :option="planTableOptions"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Versions Tab -->
                        <div v-show="tab === 'versions'">
                            <div class="card card-light">
                                <div class="card-header">
                                    <h4 class="card-title">{{ __('message.versions') }}</h4>
                                    <div class="card-tools">
                                        <router-link
                                            :to="`/products/${route.params.id}/versions/create`"
                                            class="btn btn-tool"
                                            v-tooltip="__('message.add_version')"
                                        >
                                            <i class="fas fa-plus"></i>
                                        </router-link>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <DataTable
                                        ref="dtVersionRef"
                                        :url="versionsApiUrl"
                                        :dataColumns="versionColumns"
                                        :option="versionTableOptions"
                                    >
                                        <template #bulk-actions>
                                            <div v-if="selectedVersions.length > 0" class="dropdown">
                                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    {{ __('message.bulk_action') }}
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <button class="dropdown-item" @click="confirmBulkDeleteVersions">{{ __('message.Delete') }}</button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </template>
                                    </DataTable>
                                </div>
                            </div>
                        </div>
                        <!-- Plugins Tab -->
                        <div v-if="!isPluginProduct" v-show="tab === 'plugins'">
                            <ProductPluginMapping :productId="route.params.id" :baseUrl="baseUrl" />
                        </div>
                    </div>
                </div>

                <div class="card-footer" v-if="tab === 'details'">
                    <action-button action="update" :loading="saving" @click="submit" />
                </div>
            </template>
        </div>
    </div>

    <DeleteModal
        v-if="pendingDeleteVersions"
        :showModal="true"
        :onClose="() => pendingDeleteVersions.value = null"
        :deleteUrl="versionDeleteUrl"
        :deleteData="pendingDeleteVersions"
        :title="__('message.Delete')"
        :message="__('message.are_you_sure')"
        componentName="products-edit"
        @deleted="() => { pendingDeleteVersions.value = null; selectedVersions.value = []; dtVersionRef.value?.refresh() }"
    />
</template>

<script setup>
import { h, reactive, ref, computed, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { validateForm } from '@/helpers/formUtils.js'
import { productSchema } from '@/validations/admin/productValidations'
import Checkbox from '@/components/Reusable/FormField/Checkbox.vue'
import RadioButton from '@/components/Reusable/FormField/RadioButton.vue'
import Tooltip from '@/components/Reusable/Tooltip.vue'
import ImageField from '@/components/Reusable/FormField/ImageField.vue'
import VersionTableActions from './components/VersionTableActions.vue'
import ProductPluginMapping from './components/ProductPluginMapping.vue'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'

const COMPONENT = 'products-edit'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const route = useRoute()
const router = useRouter()

const { errors, setErrors, setFieldError } = useForm()

const loading = ref(true)
const saving = ref(false)
const selectedImage = ref(null)
const taxClasses = ref([])
const tab = ref(route.query.tab === 'versions' ? 'versions' : 'details')
const githubEnabled = ref(false)

const isPluginProduct = computed(() => form.typeObj?.name?.toLowerCase() === 'plugin')

const fileSourceOptions = computed(() => {
    const list = [
        { id: 'filesystem', name: __('message.filesystem') || 'Local Filesystem' }
    ]
    if (githubEnabled.value) {
        list.push({ id: 'github', name: __('message.github_heading') || 'GitHub Integration' })
    }
    return list
})

const fileSourceValue = computed(() => {
    return fileSourceOptions.value.find(o => o.id === form.file_source) ?? fileSourceOptions.value[0]
})

// ── Versions (product uploads) ─────────────────────────────────────────────
const versionsApiUrl = `${baseUrl}/product/uploads/${route.params.id}`
const dtVersionRef = ref(null)
const selectedVersions = ref([])
const pendingDeleteVersions = ref(null)
const versionDeleteUrl = `${baseUrl}/product/upload`

const allVersionsSelected = computed(() => {
    const data = dtVersionRef.value?.tableData ?? []
    return data.length > 0 && data.every(r => selectedVersions.value.includes(r.id))
})

function toggleVersion(id) {
    const idx = selectedVersions.value.indexOf(id)
    if (idx === -1) selectedVersions.value.push(id)
    else selectedVersions.value.splice(idx, 1)
}

function toggleAllVersions(e) {
    const data = dtVersionRef.value?.tableData ?? []
    if (e.target.checked) {
        const ids = data.map(r => r.id).filter(id => !selectedVersions.value.includes(id))
        selectedVersions.value.push(...ids)
    } else {
        const ids = data.map(r => r.id)
        selectedVersions.value = selectedVersions.value.filter(id => !ids.includes(id))
    }
}

function confirmBulkDeleteVersions() {
    if (!selectedVersions.value.length) return
    pendingDeleteVersions.value = { select: [...selectedVersions.value] }
}

const versionColumns = ['select', 'version', 'description', 'release_type', 'file', 'action']
const versionTableOptions = reactive({
    headings: {
        select:       () => h('div',{}, [h('input', { type: 'checkbox', checked: allVersionsSelected.value, onChange: toggleAllVersions })]),
        version:      __('message.version'),
        description:  __('message.description'),
        release_type: __('message.release_type'),
        file:         __('message.file'),
        action:       __('message.actions'),
    },
    columnsClasses: {
        select: 'dt-select', version: 'dt-code', description: 'dt-name',
        release_type: 'dt-name', file: 'dt-name', action: 'dt-action',
    },
    templates: {
        select:       (f, row) => h('div', {}, [h('input', { type: 'checkbox', checked: selectedVersions.value.includes(row.id), onChange: () => toggleVersion(row.id) })]),
        version:      (f, row) => row.version || '—',
        description:  (f, row) => row.description || '—',
        release_type: (f, row) => row.release_type || '—',
        file:         (f, row) => row.file || '—',
        action:       (f, row) => h(VersionTableActions, { productId: route.params.id, versionId: row.id, baseUrl, onDeleted: () => dtVersionRef.value?.refresh() }),
    },
    sortable: ['version', 'release_type'],
    filterable: true,
    requestAdapter(data) {
        return {
            'sort-field':   data.orderBy ?? 'created_at',
            'sort-order':   data.orderBy ? (data.ascending ? 'asc' : 'desc') : 'desc',
            'search-query': (data.query ?? '').trim(),
            page:           data.page,
            limit:          data.limit,
        }
    },
    responseAdapter({ data }) {
        const res = data?.data
        return { data: res?.data ?? [], count: res?.total ?? 0 }
    },
    orderBy: { column: 'created_at', ascending: false },
})

const taxStatusOptions = [
    { id: 1, name: __('message.taxable') },
    { id: 0, name: __('message.none') },
]

const plansApiUrl = `${baseUrl}/dependency/product-plans?product_id=${route.params.id}`

const planColumns = ['name', 'months', 'action']

const planTableOptions = reactive({
    headings: {
        name:   __('message.name'),
        months: __('message.months'),
        action: __('message.action'),
    },
    columnsClasses: {
        name:   'dt-name',
        months: 'dt-number',
        action: 'dt-action',
    },
    templates: {
        months: (f, row) => row.days ? Math.round(row.days / 30) : '—',
        action: (f, row) => h(RouterLink, { to: `/products/plans/${row.id}/edit`, class: 'btn btn-light table_btn', title: __('message.edit') }, () => h('i', { class: 'fas fa-edit' })),
    },
    sortable: ['name'],
    filterable: true,
    requestAdapter(data) {
        return {
            'sort-field':   data.orderBy ?? 'name',
            'sort-order':   data.orderBy ? (data.ascending ? 'asc' : 'desc') : 'desc',
            'search-query': (data.query ?? '').trim(),
            page:           data.page,
            limit:          data.limit,
            paginate:       1,
        }
    },
    responseAdapter({ data }) {
        const res = data?.data
        return {
            data:  res?.data ?? [],
            count: res?.total ?? 0,
        }
    },
    orderBy: { column: 'name', ascending: true },
})

const form = reactive({
    name: '',
    type: null,
    typeObj: null,
    group: null,
    groupObj: null,
    parent: null,
    parentObj: null,
    description: '',
    short_description: '',
    product_description: '',
    product_sku: '',
    currentImage: null,
    show_agent: true,
    highlight: false,
    add_to_contact: false,
    can_modify_agent: false,
    can_modify_quantity: false,
    require_domain: false,
    hidden: false,
    invoice_hidden: false,
    whatsapp_integration: false,
    tax_status: 1,
    tax_class_id: '',
    file_source: 'filesystem',
    github_owner: '',
    github_repository: '',
    version: '',
    shoping_cart_link: '',
})

function onChange(val, name) {
    setFieldError(name, undefined)
    if (name === 'type') {
        form.typeObj = val
        form.type = val?.id ?? null
    } else if (name === 'group') {
        form.groupObj = val
        form.group = val?.id ?? null
    } else if (name === 'parent') {
        form.parentObj = val
        form.parent = val?.id ?? null
    } else {
        form[name] = val
    }
}

function onImageChange(value) {
    selectedImage.value = value ?? null
    if (value?.image) form.currentImage = value.image
}

onMounted(async () => {
    try {
        const [taxRes, productRes] = await Promise.all([
            http.get(`${baseUrl}/dependency/tax-classes`, { params: { limit: 'all' } }),
            http.get(`${baseUrl}/product/${route.params.id}`),
        ])
        taxClasses.value = taxRes.data?.data?.tax_classes ?? []

        const resData = productRes.data?.data ?? productRes.data
        const p = resData?.product ?? resData
        githubEnabled.value = resData?.github_status ?? false

        form.name                 = p.name ?? ''
        form.product_sku          = p.product_sku ?? ''
        form.description          = p.description ?? ''
        form.short_description    = p.short_description ?? ''
        form.product_description  = p.product_description ?? ''
        form.currentImage         = p.image ?? null
        form.show_agent           = Boolean(p.show_agent)
        form.highlight            = Boolean(p.highlight)
        form.add_to_contact       = Boolean(p.add_to_contact)
        form.can_modify_agent     = Boolean(p.can_modify_agent)
        form.can_modify_quantity  = Boolean(p.can_modify_quantity)
        form.require_domain       = Boolean(p.require_domain)
        form.hidden               = Boolean(p.hidden)
        form.invoice_hidden       = Boolean(p.invoice_hidden)
        form.whatsapp_integration = Boolean(p.whatsapp_integration)
        form.type                 = p.type ?? null
        form.group                = p.group ?? null
        form.parent               = p.parent ?? null
        // Tax status is driven by whether the product has a tax class assigned.
        const assigned = (p.taxes ?? [])
        form.tax_status   = assigned.length ? 1 : 0
        const standard    = taxClasses.value.find(c => c.name === 'Standard') ?? taxClasses.value[0]
        form.tax_class_id = assigned[0]?.id ?? standard?.id ?? ''
        form.github_owner         = p.github_owner ?? ''
        form.github_repository    = p.github_repository ?? ''
        form.version              = p.version ?? ''
        form.file_source          = p.github_owner ? 'github' : 'filesystem'
        form.shoping_cart_link    = p.shoping_cart_link || `${baseUrl}/pricing?id=${route.params.id}`

        const lt = p.license_type ?? p.licenseType
        if (lt) form.typeObj = { id: p.type, name: lt.name }

        const gr = p.group_relation ?? p.groupRelation
        if (gr) form.groupObj = { id: p.group, name: gr.name }

        const pr = p.parent_relation ?? p.parentRelation
        if (pr) form.parentObj = { id: p.parent, name: pr.name }
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function submit() {
    if (!await validateForm(productSchema, form, setErrors)) return

    saving.value = true
    try {
        const fd = new FormData()
        fd.append('name', form.name)
        fd.append('type', form.type ?? '')
        fd.append('group', form.group ?? '')
        fd.append('parent', form.parent ?? '')
        fd.append('description', form.description)
        fd.append('short_description', form.short_description)
        fd.append('product_description', form.product_description)
        fd.append('product_sku', form.product_sku)
        fd.append('show_agent', form.show_agent ? '1' : '0')
        fd.append('highlight', form.highlight ? '1' : '0')
        fd.append('add_to_contact', form.add_to_contact ? '1' : '0')
        fd.append('can_modify_agent', form.can_modify_agent ? '1' : '0')
        fd.append('can_modify_quantity', form.can_modify_quantity ? '1' : '0')
        fd.append('require_domain', form.require_domain ? '1' : '0')
        fd.append('hidden', form.hidden ? '1' : '0')
        fd.append('invoice_hidden', form.invoice_hidden ? '1' : '0')
        fd.append('whatsapp_integration', form.whatsapp_integration ? '1' : '0')
        fd.append('tax_status', form.tax_status ? '1' : '0')
        if (form.tax_status === 1 && form.tax_class_id) fd.append('tax_class_id', form.tax_class_id)
        if (selectedImage.value?.file) fd.append('image', selectedImage.value.file, selectedImage.value.name || 'image.jpg')
        if (form.file_source === 'github') {
            fd.append('github_owner', form.github_owner)
            fd.append('github_repository', form.github_repository)
            fd.append('version', form.version)
        } else {
            fd.append('github_owner', '')
            fd.append('github_repository', '')
        }

        fd.append('shoping_cart_link', form.shoping_cart_link)
        fd.append('_method', 'PATCH')
        const res = await http.post(`${baseUrl}/product/${route.params.id}`, fd, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })
        successHandler(res, COMPONENT)
        setTimeout(() => router.push('/products'), 2000)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>

