<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.create_product') }}</h4>
            </div>

            <div class="card-body px-0 pt-0">
                <div class="p-3">
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
                            <!-- Product Image -->
                            <div class="card card-light mb-4">
                                <div class="card-header">
                                    <h4 class="card-title">
                                        <i class="fas fa-image text-muted me-2"></i>Product Image
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <ImageField
                                        name="image"
                                        :label="__('message.product_image')"
                                        :componentName="COMPONENT"
                                        :onChange="onImageChange"
                                    />
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
                                    <div v-if="loadingTax" class="row justify-content-center py-3"><loader /></div>
                                    <template v-else>
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
                                    </template>
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
            </div>

            <div class="card-footer">
                <action-button action="save" :loading="saving" @click="submit" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { validateForm } from '@/helpers/formUtils.js'
import { productSchema } from '@/validations/admin/productValidations'
import Checkbox from '@/components/Reusable/FormField/Checkbox.vue'
import RadioButton from '@/components/Reusable/FormField/RadioButton.vue'
import Tooltip from '@/components/Reusable/Tooltip.vue'
import ImageField from '@/components/Reusable/FormField/ImageField.vue'

const COMPONENT = 'products-create'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const router = useRouter()

const { errors, setErrors, setFieldError } = useForm()

const saving = ref(false)
const selectedImage = ref(null)
const loadingTax = ref(true)
const taxClasses = ref([])

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
})

const taxStatusOptions = [
    { id: 1, name: __('message.taxable') },
    { id: 0, name: __('message.none') },
]

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
}

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/dependency/tax-classes`, { params: { limit: 'all' } })
        taxClasses.value = res.data?.data?.tax_classes ?? []
        const standard = taxClasses.value.find(c => c.name === 'Standard') ?? taxClasses.value[0]
        if (standard && !form.tax_class_id) form.tax_class_id = standard.id
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loadingTax.value = false
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

        const res = await http.put(`${baseUrl}/product`, fd, {
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
