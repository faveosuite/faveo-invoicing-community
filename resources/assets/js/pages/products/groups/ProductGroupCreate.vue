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
                            :placeholder="__('message.choose')"
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
            </div>

            <div class="card-footer">
                <action-button action="save" :loading="saving" @click="submit" />
                <action-button action="cancel" to="/products/groups" class="ms-2" />
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
import { productGroupSchema } from '@/validations/productGroupValidations'
import RadioButton from '@/components/Reusable/FormField/RadioButton.vue'
import Switch from '@/components/Reusable/FormField/Switch.vue'

const COMPONENT = 'groups-create'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const router = useRouter()

const { errors, setErrors, setFieldError } = useForm()

const saving = ref(false)
const form = reactive({
    name: '',
    headline: '',
    tagline: '',
    hidden: 0,
    pricing_templates_id: null,
    templateObj: null,
    status: 0,
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
    try {
        productGroupSchema.validateSync(form, { abortEarly: false })
    } catch (err) {
        const errMap = {}
        err.inner?.forEach(e => { if (e.path && !errMap[e.path]) errMap[e.path] = e.message })
        setErrors(errMap)
        return
    }

    saving.value = true
    try {
        const res = await http.put(`${baseUrl}/group`, {
            name:                form.name,
            headline:            form.headline || null,
            tagline:             form.tagline || null,
            hidden:              form.hidden ? 1 : 0,
            pricing_templates_id: form.pricing_templates_id,
            status:              form.status,
        })
        successHandler(res, COMPONENT)
        setTimeout(() => router.push('/products/groups'), 2000)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
