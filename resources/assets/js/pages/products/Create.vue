<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h4 class="card-title">Create Product</h4>
            </div>

            <div class="card-body p-0">
                <ul class="nav nav-tabs px-3 pt-2" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link" :class="{ active: tab === 'details' }" href="#" @click.prevent="tab = 'details'">
                            <i class="fas fa-circle-info me-1"></i>Details
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" :class="{ active: tab === 'tax' }" href="#" @click.prevent="tab = 'tax'">
                            <i class="fas fa-receipt me-1"></i>Tax
                        </a>
                    </li>
                </ul>

                <div class="p-3">
                    <!-- Details Tab -->
                    <div v-show="tab === 'details'">
                        <!-- Row 1: Name / License Type / Group -->
                        <div class="row">
                            <div class="col-md-4">
                                <TextField name="name" label="Name *" :value="form.name" :onChange="onChange" />
                            </div>
                            <div class="col-md-4">
                                <DynamicSelect
                                    name="type"
                                    label="License Type *"
                                    :apiEndpoint="`${baseUrl}/dependency/license-types`"
                                    dataKey="license_types"
                                    :value="form.typeObj"
                                    :onChange="onChange"
                                    placeholder="Select license type"
                                />
                            </div>
                            <div class="col-md-4">
                                <DynamicSelect
                                    name="group"
                                    label="Group *"
                                    :apiEndpoint="`${baseUrl}/dependency/product-groups`"
                                    dataKey="product_groups"
                                    :value="form.groupObj"
                                    :onChange="onChange"
                                    placeholder="Select group"
                                />
                            </div>
                        </div>

                        <!-- Descriptions (left) + Fields & Toggles (right) -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Description *</label>
                                    <TinyMCE name="description" id="editor-description" :value="form.description" :onChange="onChange" />
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Short Description *</label>
                                    <TinyMCE name="short_description" id="editor-short-description" :value="form.short_description" :onChange="onChange" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <TextField name="product_sku" label="Product SKU *" :value="form.product_sku" :onChange="onChange" />
                                <DynamicSelect
                                    name="parent"
                                    label="Parent"
                                    :apiEndpoint="`${baseUrl}/dependency/all-products`"
                                    dataKey="products"
                                    :value="form.parentObj"
                                    :onChange="onChange"
                                    placeholder="Select parent"
                                />
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Product Image</label>
                                    <input type="file" class="form-control" accept="image/jpeg,image/png,image/jpg" @change="onImageChange" />
                                    <small class="text-muted">JPEG, PNG, JPG — max 2MB</small>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold d-block">Show Agent</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" v-model="form.show_agent" id="showAgent" />
                                                <label class="form-check-label" for="showAgent">{{ form.show_agent ? 'Yes' : 'No' }}</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold d-block">Highlight</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" v-model="form.highlight" id="highlight" />
                                                <label class="form-check-label" for="highlight">{{ form.highlight ? 'Yes' : 'No' }}</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold d-block">Add to Contact</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" v-model="form.add_to_contact" id="addToContact" />
                                                <label class="form-check-label" for="addToContact">{{ form.add_to_contact ? 'Yes' : 'No' }}</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold d-block">Can Modify Agent</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" v-model="form.can_modify_agent" id="canModifyAgent" />
                                                <label class="form-check-label" for="canModifyAgent">{{ form.can_modify_agent ? 'Yes' : 'No' }}</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold d-block">Can Modify Quantity</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" v-model="form.can_modify_quantity" id="canModifyQty" />
                                                <label class="form-check-label" for="canModifyQty">{{ form.can_modify_quantity ? 'Yes' : 'No' }}</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold d-block">Require Domain</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" v-model="form.require_domain" id="requireDomain" />
                                                <label class="form-check-label" for="requireDomain">{{ form.require_domain ? 'Yes' : 'No' }}</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold d-block">Hidden (Pricing Page)</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" v-model="form.hidden" id="hidden" />
                                                <label class="form-check-label" for="hidden">{{ form.hidden ? 'Yes' : 'No' }}</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold d-block">Hidden (Admin Dropdown)</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" v-model="form.invoice_hidden" id="invoiceHidden" />
                                                <label class="form-check-label" for="invoiceHidden">{{ form.invoice_hidden ? 'Yes' : 'No' }}</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold d-block">WhatsApp Signup Flow</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" v-model="form.whatsapp_integration" id="whatsappIntegration" />
                                                <label class="form-check-label" for="whatsappIntegration">{{ form.whatsapp_integration ? 'Yes' : 'No' }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Row 5: Product Description (full width) -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Product Description *</label>
                                    <TinyMCE name="product_description" id="editor-product-description" :value="form.product_description" :onChange="onChange" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tax Tab -->
                    <div v-show="tab === 'tax'">
                        <p class="text-muted mb-3">Select the tax classes that apply to this product.</p>
                        <div v-if="loadingTax" class="text-center py-4">
                            <span class="spinner-border text-secondary"></span>
                        </div>
                        <div v-else class="row">
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
                            <div v-if="!taxClasses.length" class="col-12 text-muted">No tax classes found.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button class="btn btn-primary" @click="submit" :disabled="saving">
                    <span v-if="saving" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Save
                </button>
                <router-link to="/products" class="btn btn-secondary ms-2">Cancel</router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'products-create'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const router = useRouter()

const saving = ref(false)
const loadingTax = ref(true)
const taxClasses = ref([])
const tab = ref('details')

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
    show_agent: false,
    highlight: false,
    add_to_contact: false,
    can_modify_agent: false,
    can_modify_quantity: false,
    require_domain: false,
    hidden: false,
    invoice_hidden: false,
    whatsapp_integration: false,
    tax: [],
})

function onChange(val, name) {
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
    try {
        const res = await http.get(`${baseUrl}/dependency/tax-classes`, { params: { limit: 'all' } })
        taxClasses.value = res.data?.data?.tax_classes ?? []
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loadingTax.value = false
    }
})

async function submit() {
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

        const res = await http.put(`${baseUrl}/product`, fd, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })
        successHandler(res, COMPONENT)
        router.push('/products')
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
