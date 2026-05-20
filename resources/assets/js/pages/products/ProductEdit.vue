<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.edit_product') }}</h4>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <template v-else>
                <div class="card-body p-0">
                    <ul class="nav nav-tabs px-3 pt-2" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link" :class="{ active: tab === 'details' }" href="#" @click.prevent="tab = 'details'">
                                <i class="fas fa-circle-info me-1"></i>{{ __('message.details') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" :class="{ active: tab === 'tax' }" href="#" @click.prevent="tab = 'tax'">
                                <i class="fas fa-receipt me-1"></i>{{ __('message.tax') }}
                            </a>
                        </li>
                    </ul>

                    <div class="p-3">
                        <!-- Details Tab -->
                        <div v-show="tab === 'details'">
                            <!-- Row 1: Name / License Type / Group -->
                            <div class="row">
                                <div class="col-md-4">
                                    <TextField name="name" :label="__('message.name')" :required="true" :value="form.name" :onChange="onChange" />
                                </div>
                                <div class="col-md-4">
                                    <DynamicSelect
                                        name="type"
                                        :label="__('message.license-type')"
                                        :required="true"
                                        :apiEndpoint="`${baseUrl}/dependency/license-types`"
                                        dataKey="license_types"
                                        :value="form.typeObj"
                                        :onChange="onChange"
                                        :placeholder="__('message.select_license_type')"
                                    />
                                </div>
                                <div class="col-md-4">
                                    <DynamicSelect
                                        name="group"
                                        :label="__('message.group')"
                                        :required="true"
                                        :apiEndpoint="`${baseUrl}/dependency/product-groups`"
                                        dataKey="product_groups"
                                        :value="form.groupObj"
                                        :onChange="onChange"
                                        :placeholder="__('message.select_group')"
                                    />
                                </div>
                            </div>

                            <!-- Descriptions (left) + Fields & Toggles (right) -->
                            <div class="row">
                                <div class="col-md-6">
                                    <TinyMCE name="description" :label="__('message.description')" :required="true" id="editor-description" :value="form.description" :onChange="onChange" />
                                    <TinyMCE name="short_description" :label="__('message.short_description')" :required="true" id="editor-short-description" :value="form.short_description" :onChange="onChange" />
                                </div>
                                <div class="col-md-6">
                                    <TextField name="product_sku" :label="__('message.product_sku')" :required="true" :value="form.product_sku" :onChange="onChange" />
                                    <DynamicSelect
                                        name="parent"
                                        :label="__('message.parent')"
                                        :apiEndpoint="`${baseUrl}/dependency/all-products`"
                                        dataKey="products"
                                        :value="form.parentObj"
                                        :onChange="onChange"
                                        :placeholder="__('message.select_parent')"
                                    />
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">{{ __('message.product_image') }}</label>
                                        <div v-if="form.currentImage" class="mb-2">
                                            <img :src="form.currentImage" alt="Current image" style="width:80px;height:80px;object-fit:cover;border-radius:4px;" class="img-thumbnail" />
                                        </div>
                                        <input type="file" class="form-control" accept="image/jpeg,image/png,image/jpg" @change="onImageChange" />
                                        <small class="text-muted">Leave empty to keep existing image.</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">{{ __('message.where_retrieve_files') }}</label>
                                        <div>
                                            <div v-if="githubEnabled" class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="file_source" id="file_source_github" value="github" v-model="form.file_source" />
                                                <label class="form-check-label" for="file_source_github">{{ __('message.github_heading') }}</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="file_source" id="file_source_filesystem" value="filesystem" v-model="form.file_source" />
                                                <label class="form-check-label" for="file_source_filesystem">{{ __('message.filesystem') }}</label>
                                            </div>
                                        </div>
                                        <div v-if="form.file_source === 'github'" class="mt-2">
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <TextField name="github_owner" :label="__('message.github-owner')" :value="form.github_owner" :onChange="onChange" />
                                                </div>
                                                <div class="col-md-6">
                                                    <TextField name="github_repository" :label="__('message.github-repository-name')" :value="form.github_repository" :onChange="onChange" />
                                                </div>
                                                <div class="col-md-6">
                                                    <TextField name="version" :label="__('message.version')" :value="form.version" :onChange="onChange" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <RadioButton
                                            name="show_agent"
                                            :label="__('message.show_cart_page')"
                                            :options="[{ name: __('message.agents'), value: 1 }, { name: __('message.product_quantity'), value: 0 }]"
                                            :value="form.show_agent ? 1 : 0"
                                            :onChange="(val) => form.show_agent = !!val"
                                            classname="mb-2"
                                        />
                                        <div v-show="form.show_agent" class="ms-1 mt-1">
                                            <Checkbox name="can_modify_agent" :label="__('message.allow_multiple_agents_quantity')" :value="form.can_modify_agent" :onChange="(val) => form.can_modify_agent = val" />
                                        </div>
                                        <div v-show="!form.show_agent" class="ms-1 mt-1">
                                            <Checkbox name="can_modify_quantity" :label="__('message.allow_multiple_product_quantity')" :value="form.can_modify_quantity" :onChange="(val) => form.can_modify_quantity = val" />
                                        </div>
                                    </div>

                                    <TextField
                                        name="shoping_cart_link"
                                        :label="__('message.shoping-cart-link')"
                                        :value="`${baseUrl}/cart?id=${route.params.id}`"
                                        :disabled="true"
                                        :onChange="() => {}"
                                    />

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">{{ __('message.hidden') }}</label>
                                        <div class="d-flex flex-wrap gap-3">
                                            <div class="d-flex align-items-center gap-1">
                                                <Checkbox name="hidden" :label="__('message.hidden_pricing_page')" :value="form.hidden" :onChange="(val) => form.hidden = val" />
                                                <Tooltip :message="__('message.tick-to-hide-from-order-form')" />
                                            </div>
                                            <div class="d-flex align-items-center gap-1">
                                                <Checkbox name="invoice_hidden" :label="__('message.hidden_admin_dropdown')" :value="form.invoice_hidden" :onChange="(val) => form.invoice_hidden = val" />
                                                <Tooltip :message="__('message.tick-to-hide-from-invoice')" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-2 d-flex align-items-center gap-1">
                                        <Checkbox name="require_domain" :label="__('message.require_domain')" :value="form.require_domain" :onChange="(val) => form.require_domain = val" />
                                        <Tooltip :message="__('message.tick-to-show-domain-registration-options')" />
                                    </div>

                                    <div class="mb-2 d-flex align-items-center gap-1">
                                        <Checkbox name="highlight" :label="__('message.highlight')" :value="form.highlight" :onChange="(val) => form.highlight = val" />
                                        <Tooltip :message="__('message.tick-to-highlight-product')" />
                                    </div>

                                    <div class="mb-2 d-flex align-items-center gap-1">
                                        <Checkbox name="add_to_contact" :label="__('message.contact_to_sales')" :value="form.add_to_contact" :onChange="(val) => form.add_to_contact = val" />
                                        <Tooltip :message="__('message.tick-to-add_to_contact-product')" />
                                    </div>

                                    <div class="mb-3 d-flex align-items-center gap-1">
                                        <Checkbox name="whatsapp_integration" :label="__('message.whatsapp_product_heading')" :value="form.whatsapp_integration" :onChange="(val) => form.whatsapp_integration = val" />
                                        <Tooltip :message="__('message.whatsapp_product_explanation')" />
                                    </div>
                                </div>
                            </div>

                            <!-- Row 5: Product Description (full width) -->
                            <div class="row">
                                <div class="col-md-12">
                                    <TinyMCE name="product_description" :label="__('message.product_description')" :required="true" id="editor-product-description" :value="form.product_description" :onChange="onChange" />
                                </div>
                            </div>
                        </div>

                        <!-- Tax Tab -->
                        <div v-show="tab === 'tax'">
                            <p class="text-muted mb-3">{{ __('message.select_tax_classes_text') }}</p>
                            <div class="row">
                                <div v-for="tc in taxClasses" :key="tc.id" class="col-md-3 mb-2">
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            :id="`tax-${tc.id}`"
                                            :value="tc.id"
                                            v-model="form.tax"
                                        />
                                        <label class="form-check-label" :for="`tax-${tc.id}`">{{ tc.name }}</label>
                                    </div>
                                </div>
                                <div v-if="!taxClasses.length" class="col-12 text-muted">{{ __('message.no_tax_classes') }}</div>
                            </div>

                            <div class="card card-light mt-4">
                                <div class="card-header">
                                    <h4 class="card-title">{{ __('message.plans') }}</h4>
                                    <div class="card-tools">
                                        <router-link
                                            to="/products/plans/create"
                                            class="btn btn-tool"
                                            :title="__('message.create_product_plan')"
                                            v-tooltip
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
                    </div>
                </div>

                <div class="card-footer">
                    <action-button action="update" :loading="saving" @click="submit" />
                    <action-button action="cancel" to="/products" class="ms-2" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { h, reactive, ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { useFormValidation } from '@/composables/useFormValidation'
import { productRules } from './productValidation.js'
import Checkbox from '@/components/Reusable/FormField/Checkbox.vue'
import RadioButton from '@/components/Reusable/FormField/RadioButton.vue'
import Tooltip from '@/components/Reusable/Tooltip.vue'

const COMPONENT = 'products-edit'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const route = useRoute()
const router = useRouter()

const { validate, clearFieldError, clearAllErrors } = useFormValidation()

const loading = ref(true)
const saving = ref(false)
const taxClasses = ref([])
const tab = ref('details')
const githubEnabled = ref(false)

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
        action: (f, row) => h('a', {
            href: `${baseUrl}/plans/${row.id}/edit`,
            class: 'btn btn-secondary btn-sm',
        }, [h('i', { class: 'fas fa-edit me-1' }), __('message.edit')]),
    },
    sortable: ['name'],
    filterable: true,
    requestAdapter(data) {
        return {
            'sort-field':   data.orderBy ?? 'name',
            'sort-order':   data.ascending ? 'asc' : 'desc',
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
    image: null,
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
    tax: [],
    file_source: 'filesystem',
    github_owner: '',
    github_repository: '',
    version: '',
})

function onChange(val, name) {
    clearFieldError(name)
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

function onImageChange(e) {
    form.image = e.target.files[0] ?? null
}

onMounted(async () => {
    clearAllErrors()
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
        form.tax                  = (p.taxes ?? []).map(t => t.id)
        form.github_owner         = p.github_owner ?? ''
        form.github_repository    = p.github_repository ?? ''
        form.version              = p.version ?? ''
        form.file_source          = p.github_owner ? 'github' : 'filesystem'

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
    const isValid = validate(productRules(form, __))
    if (!isValid) return

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
        form.tax.forEach(id => fd.append('tax[]', id))
        if (form.image) fd.append('image', form.image)
        if (form.file_source === 'github') {
            fd.append('github_owner', form.github_owner)
            fd.append('github_repository', form.github_repository)
            fd.append('version', form.version)
        } else {
            fd.append('github_owner', '')
            fd.append('github_repository', '')
        }

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

