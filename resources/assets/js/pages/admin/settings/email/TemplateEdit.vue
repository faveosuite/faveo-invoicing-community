<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title mb-0">{{ form.name || __('message.edit_template') }}</h4>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">

                    <!-- Collapsible shortcodes card -->
                    <div v-if="form.codes" class="card card-light mb-3" :class="{ 'collapsed-card': shortcodesCollapsed }">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ __('message.list_of_available_shortcodes') }}</h5>
                            <div class="card-tools">
                                <button
                                    type="button"
                                    class="btn btn-tool"
                                    @click="shortcodesCollapsed = !shortcodesCollapsed"
                                    :title="shortcodesCollapsed ? __('message.expand') : __('message.collapse')"
                                >
                                    <i :class="shortcodesCollapsed ? 'fas fa-plus' : 'fas fa-minus'"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ __('message.copy_shortcode_info') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" :aria-label="__('message.close')"></button>
                            </div>
                            <ul class="list-inline mb-0">
                                <li
                                    v-for="(shortcode, label) in form.codes"
                                    :key="label"
                                    class="list-inline-item mb-1 shortcode-item"
                                >
                                    <span v-tooltip="label">{{ shortcode }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <TextField
                                name="name"
                                :label="__('message.name')"
                                :required="true"
                                :value="form.name"
                                :onChange="onChange"
                                :error="errors.name"
                            />
                        </div>
                        <div class="col-md-4">
                            <DynamicSelect
                                name="type"
                                :label="__('message.type')"
                                :required="true"
                                :elements="typeOptions"
                                :value="selectedType"
                                :onChange="onTypeChange"
                                :searchable="true"
                                :clearable="false"
                                :error="errors.type"
                            />
                        </div>
                        <div class="col-md-4">
                            <TextField
                                name="reply_to"
                                :label="__('message.reply_email')"
                                :value="form.reply_to"
                                :onChange="onChange"
                            />
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('message.content') }} <span class="text-danger ms-1">*</span></label>
                        <TinyMCE
                            name="data"
                            id="editor-template"
                            :value="form.data"
                            :onChange="onContentChange"
                            :error="errors.data"
                        />
                    </div>
                </div>

                <div class="card-footer">
                    <action-button action="update" :loading="saving" @click="save" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { validateForm } from '@/helpers/formUtils.js'
import DynamicSelect from '@/components/Reusable/FormField/DynamicSelect.vue'
import { templateEditSchema } from '@/validations/admin/emailValidations'

const COMPONENT = 'template-edit'
const route  = useRoute()
const router = useRouter()
const id = route.params.id

const { errors, setErrors, setFieldError } = useForm()
const loading  = ref(true)
const saving   = ref(false)
const shortcodesCollapsed = ref(false)

const form = reactive({
    name: '', type: '', reply_to: '', data: '', codes: null,
})

const typeOptions = ref([])

const LABEL_OVERRIDES = {
    welcome_mail:                   __('message.welcome-mail'),
    forgot_password_mail:           __('message.forgot-password'), // NOSONAR
    subscription_going_to_end_mail: __('message.subscription-going-to-end'),
    subscription_over_mail:         __('message.subscription-over'),
    invoice_mail:                   __('message.invoice'),
    order_mail:                     __('message.order-mail'),
    auto_subscription_going_to_end: __('message.auto_renewal_reminder'),
    payment_successfull:            __('message.auto_payment_success'),
    payment_failed:                 __('message.auto_payment_failed'),
    cloud_deleted:                  __('message.urgent_order_deleted'),
    cloud_created:                  __('message.new_instance_created'),
    contact_us:                     __('message.contact_us'),
    demo_request:                   __('message.request_demo'),
    registration_mail:              __('message.register_mail'),
    sales_manager_email:            __('message.new_sales_manager'),
    account_manager_email:          __('message.new_account_manager'),
}

function toLabel(name) {
    return LABEL_OVERRIDES[name]
        ?? name.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
}

const selectedType = computed(() =>
    typeOptions.value.find(t => t.id === String(form.type)) ?? null
)

function onChange(val, key) {
    setFieldError(key, undefined)
    form[key] = val
}

function onTypeChange(val) {
    setFieldError('type', undefined)
    form.type = val?.id ?? ''
}

function onContentChange(val, key) {
    setFieldError('data', undefined)
    form[key] = val
}

onMounted(async () => {
    try {
        const res = await http.get(`/template/edit/${id}`)
        const d = res.data?.data ?? {}

        form.name    = d.template?.name     ?? ''
        form.type    = String(d.template?.type ?? '')
        form.reply_to = d.template?.reply_to ?? ''
        form.data    = d.template?.data      ?? ''
        form.codes   = d.codes               ?? null

        typeOptions.value = Object.entries(d.type ?? {}).map(([id, name]) => ({
            id,
            name: toLabel(name),
        }))
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        loading.value = false
    }
})

async function save() {
    if (!await validateForm(templateEditSchema, form, setErrors)) return

    saving.value = true
    try {
        const res = await http.put(`/template/update/${id}`, {
            name:     form.name,
            type:     form.type,
            reply_to: form.reply_to,
            data:     form.data,
        })
        successHandler(res, COMPONENT)
        setTimeout(() => router.push('/settings/email/templates'), 2000)
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        saving.value = false
    }
}
</script>

<style scoped>
.shortcode-item { width: 23%; }
</style>
