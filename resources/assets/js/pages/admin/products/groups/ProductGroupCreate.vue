<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.create_product_group') }}</h4>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <TextField name="name" :label="__('message.name')" :required="true" :value="form.name" :onChange="onChange" :error="errors.name" />
                    </div>
                    <div class="col-md-4">
                        <TextField name="headline" :label="__('message.headline')" :value="form.headline" :onChange="onChange" />
                    </div>
                    <div class="col-md-4">
                        <TextField name="tagline" :label="__('message.tagline')" :value="form.tagline" :onChange="onChange" />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <DynamicSelect
                            name="pricing_templates_id"
                            :label="__('message.design_template')"
                            :required="true"
                            :apiEndpoint="`${baseUrl}/dependency/pricing-templates`"
                            dataKey="pricing_templates"
                            :value="form.templateObj"
                            :onChange="onChange"
                            :error="errors.pricing_templates_id"
                        />
                    </div>
                    <div class="col-md-4">
                        <RadioButton
                            name="status"
                            :label="__('message.status')"
                            :options="[{ name: __('message.active'), value: 1 }, { name: __('message.inactive'), value: 0 }]"
                            :value="form.status"
                            :onChange="(val) => form.status = val"
                        />
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold d-block">{{ __('message.hidden_group') }}</label>
                            <Switch name="hidden" :value="!!form.hidden" :onChange="(val) => form.hidden = val ? 1 : 0" />
                        </div>
                    </div>
                </div>

                <SeoFieldsCard
                    :form="form"
                    :errors="errors"
                    :onChange="onChange"
                    :ogSameAsMeta="ogSameAsMeta"
                    @update:ogSameAsMeta="ogSameAsMeta = $event"
                    :ogImagePreview="ogImagePreview"
                    :componentName="COMPONENT"
                    @image-change="onImageChange"
                />
            </div>

            <div class="card-footer">
                <action-button action="save" :loading="saving" @click="submit" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { validateForm } from '@/helpers/formUtils.js'
import { productGroupSchema } from '@/validations/admin/productGroupValidations'
import RadioButton from '@/components/Reusable/FormField/RadioButton.vue'
import Switch from '@/components/Reusable/FormField/Switch.vue'
import SeoFieldsCard from '@/components/Reusable/FormField/SeoFieldsCard.vue'
import { useBaseUrl } from '@/core/composables/useBaseUrl'

const COMPONENT = 'groups-create'
const baseUrl = useBaseUrl()
const router = useRouter()

const { errors, setErrors, setFieldError } = useForm()

const saving = ref(false)

const ogImagePreview = ref('')
const selectedOgImage = ref(null)
const selectedOgImageName = ref('')
const ogSameAsMeta = ref(false)

function onImageChange(value) {
    ogImagePreview.value = value.image
    selectedOgImage.value = value.file
    selectedOgImageName.value = value.name
}

const form = reactive({
    name: '',
    headline: '',
    tagline: '',
    hidden: 0,
    pricing_templates_id: null,
    templateObj: null,
    status: 0,
    meta_title: '',
    meta_description: '',
    og_title: '',
    og_description: '',
})

function onChange(val, name) {
    setFieldError(name, undefined)
    if (name === 'pricing_templates_id') {
        form.templateObj = val
        form.pricing_templates_id = val?.id ?? null
    } else {
        form[name] = val
    }
}

async function submit() {
    if (!await validateForm(productGroupSchema, form, setErrors)) return

    saving.value = true
    try {
        const fd = new FormData()
        fd.append('name', form.name)
        fd.append('headline', form.headline ?? '')
        fd.append('tagline', form.tagline ?? '')
        fd.append('hidden', form.hidden ? 1 : 0)
        fd.append('pricing_templates_id', form.pricing_templates_id ?? '')
        fd.append('status', form.status)
        fd.append('meta_title', form.meta_title ?? '')
        fd.append('meta_description', form.meta_description ?? '')
        fd.append('og_title', form.og_title ?? '')
        fd.append('og_description', form.og_description ?? '')
        fd.append('og_same_as_meta', ogSameAsMeta.value ? 1 : 0)
        if (selectedOgImage.value) {
            fd.append('og_image', selectedOgImage.value, selectedOgImageName.value)
        }

        const res = await http.post(`/group`, fd, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })
        successHandler(res, COMPONENT)
        setTimeout(() => router.push('/products/groups'), 2000)
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors, excludeFields: ['og_image'] })
    } finally {
        saving.value = false
    }
}
</script>
